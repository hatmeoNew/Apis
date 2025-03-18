<?php
namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\System;

use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;
use Illuminate\Support\Facades\Artisan;


class CacheController extends AdminController
{
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');


        return response()->json([
            'message' => 'Cache cleared successfully'
        ]);
    }
}