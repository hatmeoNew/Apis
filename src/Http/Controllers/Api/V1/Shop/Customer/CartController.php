<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Nicelizhi\Airwallex\Payment\Airwallex;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Repositories\WishlistRepository;
use Webkul\Paypal\Payment\SmartButton;
use Webkul\Product\Repositories\ProductRepository;
use NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Checkout\CartResource;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\Redis;

class CartController extends CustomerController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected WishlistRepository $wishlistRepository,
        protected ProductRepository $productRepository,
        protected CartRuleCouponRepository $cartRuleCouponRepository,
        protected \Webkul\Checkout\Repositories\CartRepository $cartRepository,


        protected SmartButton $smartButton,
        protected OrderRepository $orderRepository,
        protected Airwallex $airwallex,
    ) {
    }

    /**
     * Resource class name.
     *
     * @return string
     */
    public function resource()
    {
        return CartResource::class;
    }

    /**
     * Get the customer cart.
     */
    public function index(): JsonResponse
    {
        Cart::collectTotals();

        return response()->json([
            'data' => ($cart = Cart::getCart()) ? app()->make($this->resource(), ['resource' => $cart]) : null,
        ]);
    }

    /**
     * 
     * Batch Store items to the cart.
     */
    public function batchStore($productId, Request $request): JsonResponse
    {

//        $products = $request->input('products');
        $products = $request->input('items');
        $cart = Cart::getCart();
//        var_dump($cart);exit;
        foreach ($products as $product_info) {
            $product = $this->productRepository->with('parent')->find($productId);


            $product_info['product_id']=$productId;
            Event::dispatch('checkout.cart.item.add.before', $productId);

            if ($cart) {
                $cart = Cart::addProduct($productId, $product_info);
            } else {
                $cart = Cart::addProduct($productId, $product_info);
            }

            if (
                is_array($cart)
                && isset($cart['warning'])
            ) {
                return response()->json([
                    'message' => $cart['warning'],
                ], 400);
            }

            if ($cart) {
                $customer = $this->resolveShopUser(request());

                if ($customer) {
                    $this->wishlistRepository->deleteWhere([
                        'product_id'  => $productId,
                        'customer_id' => $productId,
                    ]);
                }

                Event::dispatch('checkout.cart.item.add.after', $cart);
            }
        }

        Cart::collectTotals();

        return response()->json([
            'data'    => app()->make($this->resource(), ['resource' => Cart::getCart()]),
            'message' => trans('Apis::app.shop.checkout.cart.item.success'),
        ]);
    }

    /**
     * Store items to the cart.
     */
    public function store($productId): JsonResponse
    {

        try {
            $product = $this->productRepository->with('parent')->find($productId);

            Event::dispatch('checkout.cart.item.add.before', $product->id);

            if (request()->get('is_buy_now')) {
                Cart::deActivateCart();
            }
//            var_dump(request()->all());exit;
            $cart = Cart::addProduct($product->id, request()->all());
//            var_dump($cart);exit;
            if (
                is_array($cart)
                && isset($cart['warning'])
            ) {
                return response()->json([
                    'message' => $cart['warning'],
                ], 400);
            }

            if ($cart) {
                $customer = $this->resolveShopUser(request());

                if ($customer) {
                    $this->wishlistRepository->deleteWhere([
                        'product_id'  => $product->id,
                        'customer_id' => $customer->id,
                    ]);
                }

                Event::dispatch('checkout.cart.item.add.after', $cart);

                if (request()->get('is_buy_now')) {
                    Event::dispatch('shop.item.buy-now', request()->input('product_id'));

                    return response()->json([
                        'data'     => app()->make($this->resource(), ['resource' => Cart::getCart()]),
                        'message'  => trans('Apis::app.shop.checkout.cart.item.success'),
                    ]);
                }

                return response()->json([
                    'data'    => app()->make($this->resource(), ['resource' => Cart::getCart()]),
                    'message' => trans('Apis::app.shop.checkout.cart.item.success'),
                ]);
            }

            return response()->json([
                'data'    => null,
                'message' => trans('Apis::app.shop.checkout.cart.item.success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message'      => $exception->getMessage(),
            ], 400);
        }
    }
    public function add($product_id)
    {
       print_r(123);exit;
    }


    /**
     * Updates the quantity of the items present in the cart.
     */
    public function update(): JsonResponse
    {
        foreach (request()->qty as $qty) {
            if (! $qty) {
                return response()->json([
                    'message' => trans('Apis::app.shop.checkout.cart.quantity.illegal'),
                ], 400);
            }
        }

        try {
            Cart::updateItems(request()->input());

            return response()->json([
                'data'    => app()->make($this->resource(), ['resource' => Cart::getCart()]),
                'message' => trans('Apis::app.shop.checkout.cart.quantity.success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Remove item from the cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function removeItem(int $cartItemId)
    {
        Event::dispatch('checkout.cart.item.delete.before', $cartItemId);

        Cart::removeItem($cartItemId);

        Event::dispatch('checkout.cart.item.delete.after', $cartItemId);

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response([
            'data'    => $cart ? app()->make($this->resource(), ['resource' => $cart]) : null,
            'message' => trans('Apis::app.shop.checkout.cart.item.success-remove'),
        ]);
    }

    /**
     * Empty the cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function empty()
    {
        Event::dispatch('checkout.cart.delete.before');

        Cart::deActivateCart();

        Event::dispatch('checkout.cart.delete.after');

        $cart = Cart::getCart();

        return response([
            'data'    => $cart ? app()->make($this->resource(), ['resource' => $cart]) : null,
            'message' => trans('Apis::app.shop.checkout.cart.item.success-remove'),
        ]);
    }

    /**
     * Apply the coupon code.
     *
     * @return \Illuminate\Http\Response
     */
    public function applyCoupon(Request $request)
    {
        $couponCode = $request->code;

        try {
            if (strlen($couponCode)) {
                Cart::setCouponCode($couponCode)->collectTotals();

                if (Cart::getCart()->coupon_code == $couponCode) {

                    $cart = Cart::getCart();

                    return response([
                        'data'    => $cart ? app()->make($this->resource(), ['resource' => $cart]) : null,
                        'message' => trans('Apis::app.shop.checkout.cart.coupon.success'),
                    ]);
                }
            }

            return response([
                'message' => trans('Apis::app.shop.checkout.cart.coupon.invalid'),
            ], 400);
        } catch (\Exception $e) {
            report($e);

            return response([
                'message' => trans('Apis::app.shop.checkout.cart.coupon.apply-issue'),
            ], 400);
        }
    }

    /**
     * Remove the coupon code.
     *
     * @return \Illuminate\Http\Response
     */
    public function removeCoupon()
    {   
        Cart::removeCouponCode()->collectTotals();

        $cart = Cart::getCart();

        return response([
            'data'    => $cart ? app()->make($this->resource(), ['resource' => $cart]) : null,
            'message' => __('Apis::app.shop.checkout.cart.coupon.success-remove'),
        ]);
    }

    /**
     * Move cart item to wishlist.
     *
     * @return \Illuminate\Http\Response
     */
    public function moveToWishlist(int $cartItemId)
    {
        Event::dispatch('checkout.cart.item.move-to-wishlist.before', $cartItemId);

        Cart::moveToWishlist($cartItemId);

        Event::dispatch('checkout.cart.item.move-to-wishlist.after', $cartItemId);

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response([
            'data'    => $cart ? app()->make($this->resource(), ['resource' => $cart]) : null,
            'message' => __('Apis::app.shop.checkout.cart.move-wishlist.success'),
        ]);
    }


    public function OrderAddrAfter(Request $request) {

        $input = $request->all();
        $last_order_id = "";
        $force = $request->input("force");

        $cart_id = isset($input['cart_id']) ? trim($input['cart_id']) : "";
        if($cart_id) {
            $cart = $this->cartRepository->find($cart_id);
            Cart::setCart($cart);
        }

        Log::info("last order id " . $last_order_id);

        if(!empty($last_order_id) && $force !="1") {
            return response()->json(['error' => 'You Have already placed order, if you want to place another order please confirm your order','code'=>'202'], 400);
        }
        $refer = isset($input['refer']) ? trim($input['refer']) : "";

        $products = $request->input("products");
        Log::info("products". json_encode($products));

        $input = [];
        $input['address'] = "";
        $input['city'] = "";
        $input['country'] = "";
        $input['email'] = "";
        $input['first_name'] = "";
        $input['second_name'] = "";
        $input['phone_full'] = "";
        $input['code'] = "";
        $input['province'] = "";


        $addressData = [];
        $addressData['billing'] = [];
        $address1 = [];
        array_push($address1, $input['address']);
        $addressData['billing']['city'] = $input['city'];
        $addressData['billing']['country'] = "";
        $addressData['billing']['email'] = $input['email'];
        $addressData['billing']['first_name'] = $input['first_name'];
        $addressData['billing']['last_name'] = $input['second_name'];
        $input['phone_full'] = str_replace('undefined+','', $input['phone_full']);
        $addressData['billing']['phone'] = $input['phone_full'];
        $addressData['billing']['postcode'] = $input['code'];
        $addressData['billing']['state'] = $input['province'];
        $addressData['billing']['use_for_shipping'] = true;
        $addressData['billing']['address1'] = $address1;
        $addressData['shipping'] = [];
        $addressData['shipping']['isSaved'] = false;
        $address1 = [];
        array_push($address1, "");
        $addressData['shipping']['address1'] = $address1;

        $addressData['billing']['address1'] = implode(PHP_EOL, $addressData['billing']['address1']);

        $addressData['shipping']['address1'] = implode(PHP_EOL, $addressData['shipping']['address1']);

        Log::info("paypal pay ".$refer.'--'.json_encode($addressData));

        Cart::saveCustomerAddress($addressData);

        $shippingMethod = "free_free"; // 包邮
        $shippingMethod = "flatrate_flatrate";
        // Cart::saveShippingMethod($shippingMethod);

        Cart::saveShippingMethod($shippingMethod);


        Cart::collectTotals();

        $payment = [];
        $payment['description'] = "PayPal-".$refer;
        $payment['method'] = "paypal_smart_button";
        $payment['method_title'] = "PayPal Smart Button-".$refer;
        $payment['sort'] = "1";
        // Cart::savePaymentMethod($payment);

        Cart::savePaymentMethod($payment);


        try {
            $order = $this->smartButton->createOrder($this->buildRequestBody($input));

            

            //$order_utm = $this->orderUtmRepository->create($order_utm);



            $data = [];
            $data['order'] = $order;
            $data['code'] = 200;
            $data['result'] = 200;
            $data['cart'] = Cart::getCart();
            return response()->json($data);
            //return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(json_decode($e->getMessage()), 400);
        }
    }

    public function OrderStatus(Request $request) {
        try {
            $order = $this->smartButton->getOrder(request()->input('orderData.orderID'));

            $cartId = $request->input('orderData.cartId');
            if(empty($cartId)) {
                $cartId = $request->input('data.cart.id');
            }

            if(!empty($cartId)) {
                
                $cart = $this->cartRepository->find($cartId);
                Cart::setCart($cart);
                
            }

            $refer = $request->input("refer");

        
            $params = request()->input("params");
            if(!empty($params)) {

                $addressData = [];
                $addressData['billing'] = [];
                $address1 = [];
                array_push($address1, $params['address']);

                $addressData['billing']['city'] = $params['city'];
                $addressData['billing']['email'] = $params['email'];
                $addressData['billing']['country'] = $params['country'];
                $addressData['billing']['first_name'] = $params['first_name'];
                $addressData['billing']['last_name'] = $params['second_name'];
                $addressData['billing']['phone'] = $params['phone_full'];
                $addressData['billing']['phone'] = $params['phone_full'];
                $addressData['billing']['address1'] = $address1;
                
                $addressData['billing']['state'] = $params['province'];
                $addressData['billing']['postcode'] = $params['code'];

                //$addressData['shipping'] = [];
                $addressData['shipping']['isSaved'] = false;
                //$address1 = [];
                //array_push($address1, "");
                $addressData['shipping']['address1'] = $address1;

                $addressData['billing']['address1'] = implode(PHP_EOL, $addressData['billing']['address1']);

                $addressData['shipping']['address1'] = implode(PHP_EOL, $addressData['shipping']['address1']);
                if(!isset($addressData['shipping']['email'])) {
                    $addressData['shipping'] = $addressData['billing'];
                }
                

                Log::error("paypal pay address ".$refer.'--'.json_encode($addressData));

                if (
                    Cart::hasError()
                    || ! Cart::saveCustomerAddress($addressData)
                ) {
                    return new JsonResource([
                        'redirect' => true,
                        'data'     => route('shop.checkout.cart.index'),
                    ]);
                }

                // if the status not eq completed, then capture the order

                if($order->result->status != "COMPLETED") {
                    $this->smartButton->captureOrder(request()->input('orderData.orderID'));
                }
                //$this->smartButton->captureOrder(request()->input('orderData.orderID'));
    
                //$this->smartButton->AuthorizeOrder(request()->input('orderData.orderID'));
    
                //$request->session()->put('last_order_id', request()->input('orderData.orderID'));

            }else{

                $order = (array)$order;

                //var_dump($order);

                $purchase_units = (array)$order['result']->purchase_units;
                $input = (array)$purchase_units[0]->shipping;
                $payer = (array)$order['result']->payer;
                $payment_source = (array)$order['result']->payment_source;
                $payment_source_paypal = (array)$payment_source['paypal'];

                //Log::info("paypal source".json_encode($payment_source));
                //Log::info("paypal source paypal".json_encode($payment_source_paypal));

                // 添加地址内容
                $addressData = [];
                $addressData['billing'] = [];
                $address1 = [];
                $address_line_2 = isset($input['address']->address_line_2) ? $input['address']->address_line_2 : "";
                array_push($address1, $input['address']->address_line_1. $address_line_2);
                $addressData['billing']['city'] = isset($input['address']->admin_area_2) ? $input['address']->admin_area_2 : "";
                $addressData['billing']['country'] = $input['address']->country_code;
                $addressData['billing']['email'] = $payer['email_address'];
                $addressData['billing']['first_name'] = $payer['name']->given_name;
                $addressData['billing']['last_name'] = $payer['name']->surname;
                $national_number = isset($payment_source_paypal['phone_number']) ? $payment_source_paypal['phone_number']->national_number : "";
                $addressData['billing']['phone'] =  $national_number;
                $addressData['billing']['postcode'] = isset($input['address']->postal_code) ? $input['address']->postal_code : "";
                $addressData['billing']['state'] = isset($input['address']->admin_area_1) ? $input['address']->admin_area_1 : "";
                $addressData['billing']['use_for_shipping'] = true;
                $addressData['billing']['address1'] = $address1;
                $addressData['shipping'] = [];
                $addressData['shipping']['isSaved'] = false;
                $address1 = [];
                array_push($address1, "");
                $addressData['shipping']['address1'] = $address1;

                $addressData['billing']['address1'] = implode(PHP_EOL, $addressData['billing']['address1']);

                $addressData['shipping']['address1'] = implode(PHP_EOL, $addressData['shipping']['address1']);

                if (
                    Cart::hasError()
                    || ! Cart::saveCustomerAddress($addressData)
                ) {
                    return new JsonResource([
                        'redirect' => true,
                        'data'     => route('shop.checkout.cart.index'),
                    ]);
                }
    
                if($order['result']->status != "COMPLETED") {
                    $this->smartButton->captureOrder(request()->input('orderData.orderID'));
                }
    

            }

            $orderRes = $this->saveOrder();

            // get order transaction info
            $order = $this->orderRepository->find($orderRes->id);

            // save utm for order
            $utm_source = $request->input("utm_source");
            $utm_medium = $request->input("utm_medium");
            $utm_campaign = $request->input("utm_campaign");
            $utm_term = $request->input("utm_term");
            $utm_content = $request->input("utm_content");

            // insert utm info to order_utm table
            $order_utm = [];
            $order_utm['order_id'] = $orderRes->id;
            $order_utm['utm_source'] = $utm_source;
            $order_utm['utm_medium'] = $utm_medium;
            $order_utm['utm_campaign'] = $utm_campaign;
            $order_utm['utm_term'] = $utm_term;
            $order_utm['utm_content'] = $utm_content;

            \NexaMerchant\Apis\Models\OrderUtm::create($order_utm);

            $data = [];
            $data['order'] = $order;
            $data['transaction'] = $order->transactions;
            $data['code'] = 200;
            $data['result'] = 200;
            $data['order_id'] = $orderRes->id;

            return response()->json($data);

        } catch (\Exception $e) {
            Log::info("paypal pay exception". json_encode($e->getMessage()));
            return response()->json($e->getMessage());
            return response()->json(json_decode($e->getMessage()), 400);
        }
    }

    /**
     *
     * @link https://developer.paypal.com/docs/multiparty/checkout/save-payment-methods/during-purchase/js-sdk/paypal/
     *
     */
    protected function buildRequestBody()
    {
        $cart = \Webkul\Checkout\Facades\Cart::getCart();


        $billingAddressLines = $this->getAddressLines($cart->billing_address->address1);

        $data = [
            'intent' => 'CAPTURE',
            'application_context' => [
                //'shipping_preference' => 'NO_SHIPPING',
                'shipping_preference' => 'GET_FROM_FILE', // 用户选择自己的地址内容
            ],


            'purchase_units' => [
                [
                    'amount'   => [
                        'value'         => $this->smartButton->formatCurrencyValue((float) $cart->sub_total + $cart->tax_total + ($cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0) - $cart->discount_amount),
                        'currency_code' => $cart->cart_currency_code,

                        'breakdown'     => [
                            'item_total' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value'         => $this->smartButton->formatCurrencyValue((float) $cart->sub_total),
                            ],

                            'shipping'   => [
                                'currency_code' => $cart->cart_currency_code,
                                'value'         => $this->smartButton->formatCurrencyValue((float) ($cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0)),
                            ],

                            'tax_total'  => [
                                'currency_code' => $cart->cart_currency_code,
                                'value'         => $this->smartButton->formatCurrencyValue((float) $cart->tax_total),
                            ],

                            'discount'   => [
                                'currency_code' => $cart->cart_currency_code,
                                'value'         => $this->smartButton->formatCurrencyValue((float) $cart->discount_amount),
                            ],
                        ],
                    ],

                    'items'    => $this->getLineItems($cart),
                ],
            ],
        ];

        if (! empty($cart->billing_address->phone)) {
            $data['payer']['phone'] = [
                'phone_type'   => 'MOBILE',

                'phone_number' => [
                    'national_number' => $this->smartButton->formatPhone($cart->billing_address->phone),
                ],
            ];
        }

        if (
            $cart->haveStockableItems()
            && $cart->shipping_address
        ) {
            //$data['application_context']['shipping_preference'] = 'SET_PROVIDED_ADDRESS';

            /*
            $data['purchase_units'][0] = array_merge($data['purchase_units'][0], [
                'shipping' => [
                    'address' => [
                        'address_line_1' => current($billingAddressLines),
                        'address_line_2' => last($billingAddressLines),
                        'admin_area_2'   => $cart->shipping_address->city,
                        'admin_area_1'   => $cart->shipping_address->state,
                        'postal_code'    => $cart->shipping_address->postcode,
                        'country_code'   => $cart->shipping_address->country,
                    ],
                ],
            ]);
            */
        }



        //var_dump($data);exit;

        return $data;
    }

    protected function getAddressLines($address)
    {
        $address = explode(PHP_EOL, $address, 2);

        $addressLines = [current($address)];

        if (isset($address[1])) {
            $addressLines[] = str_replace(["\r\n", "\r", "\n"], ' ', last($address));
        } else {
            $addressLines[] = '';
        }

        return $addressLines;
    }

    /**
     * Return cart items.
     *
     * @param  string  $cart
     * @return array
     */
    protected function getLineItems($cart)
    {
        $lineItems = [];

        foreach ($cart->items as $item) {
            if(empty($item->name)) {
                $item->name = "Product";
            }else{
                $item->name = mb_substr($item->name, 0, 50);
            }
            $lineItems[] = [
                'unit_amount' => [
                    'currency_code' => $cart->cart_currency_code,
                    'value'         => $this->smartButton->formatCurrencyValue((float) $item->price),
                ],
                'quantity'    => $item->quantity,
                'name'        => $item->name,
                'sku'         => $item->sku,
                'category'    => $item->getTypeInstance()->isStockable() ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS',
            ];
        }

        return $lineItems;
    }

    /**
     * Order confirm
     *
     * @return \Illuminate\Http\Response
     */
    public function OrderConfirm(Request $request) {
        $payment_intent_id = $request->input("payment_intent_id");
        $order_id = $request->input("order_id");

        $order = $this->orderRepository->find($order_id);

        $transactionManager = $this->airwallex->confirmPayment($payment_intent_id, $order);

        $data = [];
        $data['payment'] = $transactionManager;
        $data['code'] = 200;
        $data['result'] = 200;
        $data['order_id'] = $order_id;
        $data['order_id'] = $order_id;
        return response()->json($data);
    }

    // airwallex payment
    public function OrderAddSync(Request $request) {
        $input = $request->all();
        $addressData = [];

        $payment_method_input = $request->input('payment_method');
        $refer = isset($input['refer']) ? trim($input['refer']) : "";
        $cart_id = isset($input['cart_id']) ? trim($input['cart_id']) : "";
        if($cart_id) {
            $cart = $this->cartRepository->find($cart_id);
            Cart::setCart($cart);
        }

        $addressData['billing'] = [];
        $address1 = [];
        array_push($address1, $input['address']);
        $addressData['billing']['city'] = $input['city'];
        $addressData['billing']['country'] = $input['country'];
        $addressData['billing']['email'] = $input['email'];
        $addressData['billing']['first_name'] = $input['first_name'];
        $addressData['billing']['last_name'] = $input['second_name'];
        $input['phone_full'] = str_replace('undefined+','', $input['phone_full']);
        $addressData['billing']['phone'] = $input['phone_full'];
        $addressData['billing']['postcode'] = $input['code'];
        $addressData['billing']['state'] = $input['province'];
        $addressData['billing']['use_for_shipping'] = true;
        $addressData['billing']['address1'] = $address1;

        $addressData['billing']['address1'] = implode(PHP_EOL, $addressData['billing']['address1']);

        $shipping = [];
        $address1 = [];
        array_push($address1, $input['address']);
        $shipping['city'] = $input['city'];
        $shipping['country'] = $input['country'];
        $shipping['email'] = $input['email'];
        $shipping['first_name'] = $input['first_name'];
        $shipping['last_name'] = $input['second_name'];
        //undefined+
        $input['phone_full'] = str_replace('undefined+','', $input['phone_full']);
        $shipping['phone'] = $input['phone_full'];
        $shipping['postcode'] = $input['code'];
        $shipping['state'] = $input['province'];
        $shipping['use_for_shipping'] = true;
        $shipping['address1'] = $address1;
        $shipping['address1'] = implode(PHP_EOL, $shipping['address1']);


        $addressData['shipping'] = $shipping;
        $addressData['shipping']['isSaved'] = false;
        $address1 = [];
        array_push($address1, $input['address']);
        $addressData['shipping']['address1'] = $address1;
        $addressData['shipping']['address1'] = implode(PHP_EOL, $addressData['shipping']['address1']);

        // customer bill address info
        if(@$input['shipping_address']=="other") {
            $address1 = [];
            array_push($address1, $input['bill_address']);
            $billing = [];
            $billing['city'] = $input['bill_city'];
            $billing['country'] = $input['bill_country'];
            $billing['email'] = $input['email'];
            $billing['first_name'] = $input['bill_first_name'];
            $billing['last_name'] = $input['bill_second_name'];
            //undefined+
            $input['phone_full'] = str_replace('undefined+','', $input['phone_full']);
            $billing['phone'] = $input['phone_full'];
            $billing['postcode'] = $input['bill_code'];
            $billing['state'] = $input['bill_province'];
            //$billing['use_for_shipping'] = true;
            $billing['address1'] = $address1;
            $billing['address1'] = implode(PHP_EOL, $billing['address1']);

        // $billing['address1'] = implode(PHP_EOL, $billing['address1']);

            $addressData['billing'] = $billing;
        }


        Log::info("address" . json_encode($addressData));

        if (
            Cart::hasError()
            || ! Cart::saveCustomerAddress($addressData)
        ) {
            return new JsonResource([
                'redirect' => false,
                'data'     => Cart::getCart(),
            ]);
        }



        //
        $shippingMethod = "free_free"; // free shipping
        $shippingMethod = "flatrate_flatrate";

        if (
            Cart::hasError()
            || ! $shippingMethod
            || ! Cart::saveShippingMethod($shippingMethod)
        ) {
            return response()->json([
                'redirect_url' => route('shop.checkout.cart.index'),
            ], Response::HTTP_FORBIDDEN);
        }

        Cart::collectTotals();


        if($payment_method_input=="airwallex_klarna") $payment_method = "airwallex";
        if($payment_method_input=="airwallex_dropin") $payment_method = "airwallex";
        if($payment_method_input=="airwallex_google") $payment_method = "airwallex";
        if($payment_method_input=="airwallex_apple") $payment_method = "airwallex";
        if($payment_method_input=="airwallex") $payment_method = "airwallex";

        // when enable the upselling and can config the upselling rule for carts
        if($payment_method=='airwallex') {
            //
            $payment = [];
            $payment['description'] = $payment_method."-".$refer;
            $payment['method'] = $payment_method;
            $payment['method_title'] = $payment_method."-".$refer;
            $payment['sort'] = "2";
            // Cart::savePaymentMethod($payment);

            if (
                Cart::hasError()
                || ! $payment
                || ! Cart::savePaymentMethod($payment)
            ) {
                return response()->json([
                    'redirect_url' => route('shop.checkout.cart.index'),
                ], Response::HTTP_FORBIDDEN);
            }

            
            Cart::collectTotals();
            $this->validateOrder();
            $cart = Cart::getCart();

            $order = $this->orderRepository->create(Cart::prepareDataForOrder());
            // Cart::deActivateCart();
            // Cart::activateCartIfSessionHasDeactivatedCartId();
            $data['result'] = 200;
            $data['order'] = $order;
            if ($order) {
                $orderId = $order->id;

                // save utm for order
                $utm_source = $request->input("utm_source");
                $utm_medium = $request->input("utm_medium");
                $utm_campaign = $request->input("utm_campaign");
                $utm_term = $request->input("utm_term");
                $utm_content = $request->input("utm_content");

                // insert utm info to order_utm table
                $order_utm = [];
                $order_utm['order_id'] = $order->id;
                $order_utm['utm_source'] = $utm_source;
                $order_utm['utm_medium'] = $utm_medium;
                $order_utm['utm_campaign'] = $utm_campaign;
                $order_utm['utm_term'] = $utm_term;
                $order_utm['utm_content'] = $utm_content;

                \NexaMerchant\Apis\Models\OrderUtm::create($order_utm);

                //customer id
                $cus_id = isset($input['cus_id']) ? trim($input['cus_id']) : null;

                $airwallex_customer = [];
                if(is_null($cus_id)) {
                    //Step 1: Create a Customer
                    //var_dump($order->id);
                    try {
                        $airwallex_customer = $this->airwallex->createCustomer($cart, $order->id);
                        $cus_id = $airwallex_customer->id;
                    } catch (\Exception $e) {
                        return response()->json(['error' => $e->getMessage(),'code'=>'203'], 400);
                    }
                }else{
                    $airwallex_customer['id'] = $cus_id;
                }

                //create a airwallex payment order
                $transactionManager = $this->airwallex->createPaymentOrder($cart, $order->id, $cus_id);
                //Step 2: Generate a client secret for the Customer
                $customerClientSecret = $this->airwallex->createCustomerClientSecret($cus_id);
                if(!isset($transactionManager->client_secret)) {
                    response()->json(['error' => $transactionManager->body->message,'code'=>'203'], 400);
                }

                //$transactionManager = $this->airwallex->createPaymentOrder($cart, $order->id);
                Log::info("airwallex-".$order->id."--".json_encode($transactionManager));
                $data['client_secret'] = $transactionManager->client_secret;
                $data['payment_intent_id'] = $transactionManager->id;
                $data['currency'] = $transactionManager->currency;
                $data['transaction'] = $transactionManager;
                $data['customer'] = $airwallex_customer;
                $data['customer_client_secret'] = $customerClientSecret;
                $data['country'] = $input['country'];
                $data['billing'] = $addressData['billing'];
                $data['airwallex'] = $this->airwallex;

                // redis save the customer id from airwallex
                Redis::set("airwallex_customer_".$order->id, $cus_id);
            }

            return response()->json($data);
        }

    }

    /**
     * Validate order before creation.
     *
     * @return void|\Exception
     */
    public function validateOrder()
    {
        $cart = Cart::getCart();

        $minimumOrderAmount = core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;

        if (
            auth()->guard('customer')->check()
            && auth()->guard('customer')->user()->is_suspended
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.suspended-account-message'));
        }

        if (
            auth()->guard('customer')->user()
            && ! auth()->guard('customer')->user()->status
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.inactive-account-message'));
        }

        if (! $cart->checkMinimumOrder()) {
            throw new \Exception(trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        if ($cart->haveStockableItems() && ! $cart->shipping_address) {
            throw new \Exception(trans('shop::app.checkout.cart.check-shipping-address'));
        }

        if (! $cart->billing_address) {
            throw new \Exception(trans('shop::app.checkout.cart.check-billing-address'));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->selected_shipping_rate
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-shipping-method'));
        }

        if (! $cart->payment) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-payment-method'));
        }
    }

}
