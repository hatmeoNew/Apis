<?php

namespace NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Core;

use NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Settings\ChannelResource as AdminChannelResource;

class ChannelResource extends AdminChannelResource
{

    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'base_currency_id' => $this->base_currency_id,
            'base_currency_code' => core()->getBaseCurrencyCode(),
            'currencySymbol' => core()->currencySymbol(core()->getBaseCurrencyCode()),
            'home_seo' => $this->home_seo,
        ]);
    }

}
