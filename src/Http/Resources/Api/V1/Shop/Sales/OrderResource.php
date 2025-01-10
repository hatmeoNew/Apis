<?php

namespace NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Sales;

use NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Sales\OrderResource as BaseOrderResource;

class OrderResource extends BaseOrderResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            // 'customer' => $this->customer,
            'id' => $this->id,
            'channel_name' => $this->channel_name,
            'channel_url' => $this->channel_url,
            'channel_logo' => $this->channel_logo,
             'shipping_address' => $this->shipping_address,
             'billing_address' => $this->billing_address,
            'payment' => $this->payment,
            'shipping' => $this->shipping,
            'items' => $this->items,
            'invoices' => $this->invoices,
            'shipments' => $this->shipments,
            'refunds' => $this->refunds,
            'transactions' => $this->transactions,
            'status_label' => $this->status_label,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
