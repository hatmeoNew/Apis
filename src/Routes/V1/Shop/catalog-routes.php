<?php

use Illuminate\Support\Facades\Route;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Catalog\AttributeController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Catalog\AttributeFamilyController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Catalog\CategoryController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Catalog\ProductController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Catalog\ProductReviewController;
use NexaMerchant\Apis\Enum\ApiCacheKey;

/**
 * Product routes.
 */
Route::controller(ProductController::class)->prefix('products')->middleware('cache.response:360000,'.ApiCacheKey::API_SHOP_PRODUCTS.','.ApiCacheKey::API_SHOP)->group(function () {

    Route::get('get-index/{template_id}', 'getIndexContent');
    Route::get('get-recommend/{product_id}', 'getRecommend');

    Route::get('', 'allResources');

    Route::get('/{id}', 'getResource');

    Route::get('{slug}/slug', 'slug');

    Route::get('{id}/additional-information', 'additionalInformation');

    Route::get('{id}/configurable-config', 'configurableConfig');

    

});

Route::group(['middleware' => ['auth:sanctum', 'sanctum.customer']], function () {
    /**
     * Review routes.
     */
    Route::controller(ProductReviewController::class)->prefix('products')->group(function () {
        Route::post('{product_id}/reviews', 'store');
    });
});

/**
 * Category routes.
 */
Route::controller(CategoryController::class)->prefix('categories')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');
    Route::get('get-cms/{id}', 'getCmsList');
    Route::get('cms-detail/{templateId}', 'getCmsDetail');
    Route::Post('email', 'addEmail');

});

/**
 * descendant category routes.
 */
Route::controller(CategoryController::class)->prefix('descendant-categories')->middleware('cache.response')->group(function () {
    Route::get('', 'descendantCategories');
});

/**
 * Attribute routes.
 */
Route::controller(AttributeController::class)->prefix('attributes')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');
});

/**
 * Attribute family routes.
 */
Route::controller(AttributeFamilyController::class)->prefix('attribute-families')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');
});
