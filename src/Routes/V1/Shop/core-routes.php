<?php

use Illuminate\Support\Facades\Route;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core\ChannelController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core\CoreController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core\CountryController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core\CountryStateController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core\CurrencyController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core\LocaleController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core\ThemeController;
use NexaMerchant\Apis\Enum\ApiCacheKey;

/**
 * Core configs.
 */
Route::controller(CoreController::class)->prefix('core-configs')->middleware('cache.response')->group(function () {
    Route::get('', 'getCoreConfigs');
});

Route::controller(CoreController::class)->prefix('core-config-fields')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');
});

/**
 * Locale routes.
 */
Route::controller(LocaleController::class)->prefix('locales')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');
});

/**
 * Currency routes.
 */
Route::controller(CurrencyController::class)->prefix('currencies')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');
});

/**
 * Channel routes.
 */
Route::controller(ChannelController::class)->prefix('channels')->middleware('cache.response:360000,'.ApiCacheKey::API_SHOP_CHANNEL.','.ApiCacheKey::API_SHOP)->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');

    Route::get('by-url/{url}', 'getChannelByUrl');

    // get the channel countries
    Route::get('countries/{url}', 'getChannelCountries');
});

/**
 * Country routes.
 */
Route::controller(CountryController::class)->prefix('countries')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');

    Route::get('states/groups', 'getCountryStateGroups');

});

Route::controller(CountryStateController::class)->prefix('countries-states')->middleware('cache.response')->group(function () {
    Route::get('', 'allResources');
});

/**
 * Theme routes.
 */
Route::controller(ThemeController::class)->prefix('theme/customizations')->middleware('cache.response')->group(function () {
    Route::get('', 'getThemeCustomizations');

    Route::get('{id}', 'getResource');
});
