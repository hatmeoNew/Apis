<?php

use Illuminate\Support\Facades\Route;

use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\System\TinyMCEController;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\System\EmailController;

/**
 * System routes.
 */
Route::group([
    'middleware' => ['auth:sanctum', 'sanctum.admin'],
    'prefix'     => 'system',
], function () {
    /**
     * TinyMCE routes.
     */
    Route::controller(TinyMCEController::class)->prefix('tinymce')->group(function () {
        Route::post('upload', 'upload');
    });

    // email routes
    Route::controller(EmailController::class)->prefix('email')->group(function () {
        Route::post('send-order-email/{order_id}/{email_type}', 'sendOrderEmail');
    });
});