<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Sales;

use Illuminate\Http\Request;
use NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Sales\OrderUtmResource;
use Webkul\Sales\Repositories\OrderUtmRepository;

class OrderUtmController extends SalesController
{
    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return OrderUtmRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return OrderUtmResource::class;
    }
}
