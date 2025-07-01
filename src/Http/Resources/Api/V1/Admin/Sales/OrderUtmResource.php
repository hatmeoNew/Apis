<?php

namespace NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderUtmResource extends JsonResource
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
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'utm_source'   => $this->utm_source,
            'utm_medium'   => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_term'     => $this->utm_term,
            'utm_content'  => $this->utm_content,
            'gclid'        => $this->gclid,
            'yclid'        => $this->yclid,
            'msclkid'      => $this->msclkid,
            'fbclid'       => $this->fbclid,
            'dclid'        => $this->dclid,
            'mcid'         => $this->mcid,
            'gclsrc'       => $this->gclsrc,
            'utmcsr'       => $this->utmcsr,
            'utmccn'       => $this->utmccn,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
