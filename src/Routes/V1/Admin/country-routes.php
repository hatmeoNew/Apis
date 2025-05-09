<?php

use Illuminate\Support\Facades\Route;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Country\CountryController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Country\CountryStateController;

Route::group(['middleware' => ['auth:sanctum', 'sanctum.admin']], function () {
    /**
     * CMS page routes.
     */
    Route::controller(CountryController::class)->prefix('country')->group(function () {
        // Country CRUD Routes
        Route::get('countries', [CountryController::class, 'index']);           // 获取所有国家
        Route::get('countries/{id}', [CountryController::class, 'show']);       // 获取单个国家
        Route::post('countries', [CountryController::class, 'store']);          // 创建国家
        Route::put('countries/{id}', [CountryController::class, 'update']);     // 更新国家
        Route::delete('countries/{id}', [CountryController::class, 'destroy']); // 删除国家

        // Country State CRUD Routes
        Route::get('country-states', [CountryStateController::class, 'index']);           // 获取所有州
        Route::get('country-states/{id}', [CountryStateController::class, 'show']);       // 获取单个州
        Route::get('countries/{countryCode}/states', [CountryStateController::class, 'getByCountryCode']);
        Route::post('country-states', [CountryStateController::class, 'store']);          // 创建州
        Route::put('country-states/{id}', [CountryStateController::class, 'update']);     // 更新州
        Route::delete('country-states/{id}', [CountryStateController::class, 'destroy']); // 删除州
    });
});
