<?php
namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Catalog;

use Nicelizhi\Shopify\Models\ShopifyProduct;
use Nicelizhi\Manage\Helpers\SSP;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Illuminate\Support\Facades\Artisan;
use Nicelizhi\Shopify\Models\ShopifyStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Models\ProductFlat;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use Webkul\Product\Repositories\ProductReviewRepository;
use Illuminate\Support\Facades\Event;


class OfferController extends CatalogController
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductAttributeValueRepository $productAttributeValueRepository,
        protected ShopifyStore $ShopifyStore,
        protected ProductReviewRepository $productReviewRepository,
        protected ShopifyProduct $ShopifyProduct
    )
    {
    }

    // clear cache
    public function clearCache($slug)
    {
        $product = $this->productRepository->findBySlug($slug);
        if(is_null($product)) {
            echo "not found it, you don't clean it cache";
            return ;
        }
        \Nicelizhi\Shopify\Helpers\Utils::clearCache($product->id, $slug);


        return response()->json([
            'message' => 'Cache cleared successfully',
        ]);
        
    }

    // sell point
    public function sellPoint($slug, Request $request)
    {
        $product = $this->productRepository->findBySlug($slug);
        if(is_null($product)) {
            return "please import products first";
        }
        $act_type = "checkoutv2";

        $redis = Redis::connection('default');

        $sell_points_key = "sell_points_".$slug;

        $sell_points = $redis->hgetall($sell_points_key);
        if(count($sell_points)==0) {
            for($i=1;$i<=5;$i++) {
                $redis->hset($sell_points_key, $i, "");
            }
        }
        $sell_points = $redis->hgetall($sell_points_key);
        ksort($sell_points);
        if ($request->isMethod('POST'))
        {
            $sell_points = $request->input('sell_points');
            foreach($sell_points as $key=>$value) {
                $redis->hset($sell_points_key, $key, $value);
            }
            
            \Nicelizhi\Shopify\Helpers\Utils::clearCache($product->id, $slug);

        }
    }

    // view sell point
    public function viewSellPoint($slug)
    {
        $product = $this->productRepository->findBySlug($slug);
        if(is_null($product)) {
            return "please import products first";
        }
        $act_type = "checkoutv2";

        $redis = Redis::connection('default');

        $sell_points_key = "sell_points_".$slug;

        $sell_points = $redis->hgetall($sell_points_key);
        ksort($sell_points);
        return response()->json($sell_points);
    }

    // view offer configuration
    /**
     * 
     * @param  string $slug
     * 
     */
    public function viewOfferConfiguration($slug) {
        $codeKeys = [
            'title' => '',
            'title_activity' => '',
            'activity_time' => '', // actiovity time
            'activity_title' => '', // activity title
            'ad_message' => '', // ad message
        ];

        $redis = Redis::connection('default');

        $checkoutItems = \Nicelizhi\Shopify\Helpers\Utils::getAllCheckoutVersion();

        $checkoutList = [];
        foreach($checkoutItems as $key=>$item) {

            // foreach codekeys and get date from redis

            foreach($codeKeys as $kk=>$value) {
                $cachek_key = "checkout_".$item."_".$slug;
                //echo $cachek_key;
                //echo $kk."\r\n";
                $cacheData = $redis->get($cachek_key);
                if(empty($cacheData)) {
                    $cacheData = "";
                }else{
                    $cacheData = json_decode($cacheData,true);
                }
                //var_dump($cacheData);
                if(isset($cacheData[$kk])) {
                    $checkoutList[$key][$kk] = $cacheData[$kk];
                }else{
                    $checkoutList[$key][$kk] = "";
                }
                //$checkoutList[$key][$kk] = $cacheData;
            }


            // $cachek_key = "checkout_".$item."_".$product_id;
            // //echo $cachek_key;
            // $cacheData = $redis->get($cachek_key);
            // if(empty($cacheData)) {
            //     $cacheData = json_encode($codeKeys);
            // }
            // $checkoutItems[$key] = $cacheData;
        }  
        return response()->json($checkoutList);
    }

    // configu
    public function offerConfiguration($slug, Request $request)
    {
        $product = $this->productRepository->findBySlug($slug);
        if(is_null($product)) {
            return "please import products first";
        }

        $redis = Redis::connection('default');
        
        $checkoutItems = $request->input('checkoutItems');

        //var_dump($checkoutItems);

        foreach($checkoutItems as $key=>$value) {
            $new_key = str_replace("checkoutItems[","",$key);
            //var_dump($value, $key, $new_key);exit;

            $cachek_key = "checkout_".$new_key."_".$slug;
            
            $redis->set($cachek_key, json_encode($value));

        }

        //exit;
        
        \Nicelizhi\Shopify\Helpers\Utils::clearCache($product->id, $slug);

        return response()->json([
            'slug' => $slug,
            'message' => "success"
        ]);
    }

    // offer images
    public function offerImages($slug, $version, Request $request)
    {
        $product = $this->productRepository->findBySlug($slug);
        if(is_null($product)) {
            return "please import products first";
        }

        $redis = Redis::connection('default');

        $request->validate([
            'pc_banner' => 'mimes:jpg,png,webp|max:2048',
             'mobile_bg' => 'mimes:jpg,png,webp|max:2048',
             'product_size' => 'mimes:jpg,png,webp|max:2048',
        ]);

        $file = $request->file('pc_banner');
        if(!empty($file)) {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('product/'.$product->id, "public");
            
            if(!empty($filePath)) {
                $productBgAttribute = ProductAttributeValue::where("product_id", $product->id)->where("attribute_id", 29)->first();
                if(is_null($productBgAttribute)) $productBgAttribute = new ProductAttributeValue();
                $productBgAttribute->product_id = $product->id;
                $productBgAttribute->attribute_id = 29;
                $productBgAttribute->text_value = $filePath;
                $productBgAttribute->save();

            }
        }


        $file2 = $request->file('mobile_bg');
        if(!empty($file2)) {
            $fileName = $file2->getClientOriginalName();
            $filePath = $file2->store('product/'.$product->id, "public");
            
            if(!empty($filePath)) {
                $productBgAttribute = ProductAttributeValue::where("product_id", $product->id)->where("attribute_id", 30)->first();
                if(is_null($productBgAttribute)) $productBgAttribute = new ProductAttributeValue();
                $productBgAttribute->product_id = $product->id;
                $productBgAttribute->attribute_id = 30;
                $productBgAttribute->text_value = $filePath;
                $productBgAttribute->save();

            }
        }


        if($version == "v1" || $version=='v2') {
            $file3 = $request->file('product_size');
            if(!empty($file3)) {
                $fileName = $file3->getClientOriginalName();
                $filePath = $file3->store('product/'.$product->id, "public");
                
                if(!empty($filePath)) {
                    $productBgAttribute = ProductAttributeValue::where("product_id", $product->id)->where("attribute_id", 32)->first();
                    if(is_null($productBgAttribute)) $productBgAttribute = new ProductAttributeValue();
                    $productBgAttribute->product_id = $product->id;
                    $productBgAttribute->attribute_id = 32;
                    $productBgAttribute->text_value = $filePath;
                    $productBgAttribute->save();
                }
            }

        }

        if($version=='v3') {
            $product_image_list = [];
            $product_1 = $request->file("product_1");
            if(!empty($product_1)) {
                $fileName = $product_1->getClientOriginalName();
                $filePath = $product_1->store('product/'.$product->id, "public");
                if($filePath) {
                    array_push($product_image_list, ['key'=> 1, 'value'=> $filePath]);
                }
            }
            $product_2 = $request->file("product_2");
            if(!empty($product_2)) {
                $fileName = $product_2->getClientOriginalName();
                $filePath = $product_2->store('product/'.$product->id, "public");
                if($filePath) {
                    array_push($product_image_list, ['key'=> 2, 'value'=> $filePath]);
                }
            }
            $product_3 = $request->file("product_3");
            if(!empty($product_3)) {
                $fileName = $product_3->getClientOriginalName();
                $filePath = $product_3->store('product/'.$product->id, "public");
                if($filePath) {
                    array_push($product_image_list, ['key'=> 3, 'value'=> $filePath]);
                }
            }
            $product_4 = $request->file("product_4");
            if(!empty($product_4)) {
                $fileName = $product_4->getClientOriginalName();
                $filePath = $product_4->store('product/'.$product->id, "public");
                if($filePath) {
                    array_push($product_image_list, ['key'=> 4, 'value'=> $filePath]);
                }
            }

            //insert the cache
            Cache::put("product_image_lists_".$product->id, $product_image_list);

        }

        \Nicelizhi\Shopify\Helpers\Utils::clearCache($product->id, $slug);

        return response()->json([
            'slug' => $slug,
            'message' => "success"
        ]);
    }

    // offer images view
    public function offerImagesView($slug, $version)
    {
        $product = $this->productRepository->findBySlug($slug);
        if(is_null($product)) {
            return "please import products first";
        }

        $productBgAttribute = $this->productAttributeValueRepository->findOneWhere([
            'product_id'   => $product->id,
            'attribute_id' => 29,
        ]);


        $productBgAttribute_mobile = $this->productAttributeValueRepository->findOneWhere([
            'product_id'   => $product->id,
            'attribute_id' => 30,
        ]);

        $productSizeImage = $this->productAttributeValueRepository->findOneWhere([
            'product_id'   => $product->id,
            'attribute_id' => 32,
        ]);

         // products display image
         $product_image_lists = Cache::get("product_image_lists_".$product->id);

        $data = [
            'productBgAttribute' => $productBgAttribute,
            'productBgAttribute_mobile' => $productBgAttribute_mobile,
            'productSizeImage' => $productSizeImage,
            'product_image_lists' => $product_image_lists,
        ];


        return response()->json($data);
    }




}