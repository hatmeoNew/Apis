<?php

namespace NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'sku' => $this->resource->sku,
        ];
    }
}