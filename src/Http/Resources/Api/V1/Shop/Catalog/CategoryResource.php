<?php

namespace NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Catalog;

use Illuminate\Support\Facades\Log;
use NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Catalog\CategoryResource as AdminCategoryResource;

class CategoryResource extends AdminCategoryResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /* assign category */
        $category = $this->category ? $this->category : $this;

        // get products
        $products = [];
        //if ($category->products_count > 0) {
            $products = $category->products()->limit(10)->get();
       // }

        Log::info('CategoryResource:toArray:category'.json_encode($category));



        /* generating resource */
        return [
            /* category's information */
            'id'                 => $category->id,
            'name'               => $category->name,
            'slug'               => $category->slug,
            'description'        => $category->description,
            'logo_url'           => $category->logo_url,
            'banner_url'         => $category->banner_url,
            'status'             => $category->status,
            'products_count'     => $category->products_count,
            'parent_id'          => $category->parent_id,
            'products'           => ProductResource::collection($products), // limit 10
            'created_at'         => $category->created_at,
            'updated_at'         => $category->updated_at,
        ];
    }
}
