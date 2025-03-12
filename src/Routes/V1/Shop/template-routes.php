<?php

use Illuminate\Support\Facades\Route;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Template\TemplateController;
use NexaMerchant\Apis\Enum\ApiCacheKey;

/**
 * Template routes.
 */
Route::controller(TemplateController::class)->prefix('templates')->middleware('cache.response:360000,'.ApiCacheKey::API_SHOP_TEMPLATE.','.ApiCacheKey::API_SHOP)->group(function () {
    Route::get('configure/{id}', 'configure');
});