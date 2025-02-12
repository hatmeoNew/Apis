<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Customer;

use Illuminate\Http\Request;
use NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Sales\OrderResource;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class OrderController extends CustomerController
{
    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return OrderRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return OrderResource::class;
    }

    public function getResource(Request $request, $id)
    {
        $resourceClassName = $this->resource();
        $resource = $this->getRepositoryInstance()->findOrFail($id);


        return new $resourceClassName($resource);
    }

    /**
     * Cancel customer's order.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request, int $id)
    {
        $order = $this->resolveShopUser($request)->orders()->find($id);

        if ($order && $this->getRepositoryInstance()->cancel($order)) {
            return response([
                'message' => trans('Apis::app.shop.sales.cancel'),
            ]);
        }

        return response([
            'message' => trans('Apis::app.shop.sales.orders.error.cancel-error'),
        ]);
    }

    /**
     * Get the guest order info.
     * 
     * @param  \Illuminate\Http\Request  $request
     * 
     * @param  string  $key
     *
     * @return \Illuminate\Http\Response
     */
    public function guestOrderInfo($key, Request $request)
    {
        // try {
        //     $decryptedData = json_decode(Crypt::decrypt($key), true);

        //     if (!isset($decryptedData['id']) || !isset($decryptedData['expiry'])) {
        //         return response()->json(['message' => 'Invalid key format.'], 400);
        //     }

        //     $id = $decryptedData['id'];
        //     $expiry = Carbon::parse($decryptedData['expiry']);

        //     if ($expiry->isPast()) {
        //         return response()->json(['message' => 'Key has expired.'], 400);
        //     }
        // } catch (\Exception $e) {
        //     return response()->json(['message' => 'Invalid key.'], 400);
        // }
        $id = $key;

        $order = $this->getRepositoryInstance()->findOrFail($id);

        return response()->json([
            'data' => $order,
        ]);
    }
}
