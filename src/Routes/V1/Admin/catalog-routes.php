<?php

use Illuminate\Support\Facades\Route;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Catalog\AttributeController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Catalog\AttributeFamilyController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Catalog\CategoryController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Catalog\ProductController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Catalog\OfferController;

Route::group([
    'middleware' => ['auth:sanctum', 'sanctum.admin'],
    'prefix'     => 'catalog',
], function () {
    /**
     * Product routes.
     */
    Route::controller(ProductController::class)->prefix('products')->group(function () {
        Route::get('', 'allResources');

        Route::post('', 'store');

        Route::get('{id}', 'getResource');

        Route::put('{id}', 'update');

        Route::post('{id}/inventories', 'updateInventories');

        Route::delete('{id}', 'destroy');

        Route::post('mass-update', 'massUpdate');

        Route::post('mass-destroy', 'massDestroy');

        Route::post('quick-create', 'quickCreate');
    });

    /**
     * Category routes.
     */
    Route::controller(CategoryController::class)->prefix('categories')->group(function () {
        Route::get('', 'allResources');

        Route::post('', 'store');

        Route::get('{id}', 'getResource');

        Route::put('{id}', 'update');

        Route::delete('{id}', 'destroy');

        Route::post('mass-update', 'massUpdate');

        Route::post('mass-destroy', 'massDestroy');
    });

    /**
     * Attribute routes.
     */
    Route::controller(AttributeController::class)->prefix('attributes')->group(function () {
        Route::get('', 'allResources');

        Route::post('', 'store');

        Route::get('{id}', 'getResource');

        Route::put('{id}', 'update');

        Route::delete('{id}', 'destroy');

        Route::post('mass-destroy', 'massDestroy');
    });

    /**
     * Attribute family routes.
     */
    Route::controller(AttributeFamilyController::class)->prefix('attribute-families')->group(function () {
        Route::get('', 'allResources');

        Route::post('', 'store');

        Route::get('{id}', 'getResource');

        Route::put('{id}', 'update');

        Route::delete('{id}', 'destroy');
    });

    /**
     * 
     * offer routes.
     * 
     */
    Route::controller(OfferController::class)->prefix('offers')->group(function () {
        
        // clear cache
        Route::post('clear-cache/{slug}', 'clearCache');

        // sell point
        Route::post('sell-point/{slug}', 'sellPoint');

        // view sell point
        Route::get('view-sell-point/{slug}', 'viewSellPoint');

        // offer configuration
        Route::post('offer-configuration/{slug}', 'offerConfiguration');

        // view offer configuration
        Route::get('view-offer-configuration/{slug}', 'viewOfferConfiguration');

        // offer images save
        Route::post('offer-images-save/{slug}/{version}', 'offerImagesSave');

        // offer images view
        Route::get('offer-images-view/{slug}/{version}', 'offerImagesView');

    });
});
