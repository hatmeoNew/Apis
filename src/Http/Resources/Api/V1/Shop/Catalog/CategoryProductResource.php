<?php

namespace NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Helpers\BundleOption;
use Illuminate\Support\Facades\Redis;

class CategoryProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /* assign product */
        $product = $this->product ? $this->product : $this;

        /* get type instance */
        $productTypeInstance = $product->getTypeInstance();
        
        /* generating resource */
        return [
            /* product's information */
            'id'                 => $product->id,
            'sku'                => $product->sku,
            'type'               => $product->type,
            'name'               => $product->name,
            'url_key'            => $product->url_key,
            'price'              => core()->convertPrice($productTypeInstance->getMinimalPrice()),
            'compare_at_price'   => isset($product->compare_at_price) ? core()->convertPrice($product->compare_at_price) : null,
            'formatted_price'    => core()->currency($productTypeInstance->getMinimalPrice()),
            'short_description'  => $product->short_description,
            'description'        => $product->description,
            'images'             => ProductImageResource::collection($product->images),
            'videos'             => ProductVideoResource::collection($product->videos),
            'base_image'         => ProductImage::getProductBaseImage($product),
            'created_at'         => $product->created_at,
            'updated_at'         => $product->updated_at,
            'product_size_img'   => $product->product_size_img,


            /* product's checks */
            'package_products' => $packages_package,
            'crm_channel' => config('onebuy.crm_channel'),
            'gtag' => config('onebuy.gtag'),

            'sell_point' => $sell_points,

        ];
    }
}