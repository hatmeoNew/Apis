<?php

use Illuminate\Support\Facades\Route;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Template\TemplateController;


Route::group([
    'middleware' => ['auth:sanctum', 'sanctum.admin']
], function () {
    

    /**
     * Category routes.
     */
    Route::controller(TemplateController::class)->prefix('template')->group(function () {
        Route::get('template-list', 'getTemplateList');
        Route::get('edit/{templateId}', 'editTemplate');
        Route::get('detail/{templateId}', 'detailTemplate');
        Route::post('add-template', 'addTemplate');
        Route::get('get-cms/{id}', 'getCmsList');
       
        Route::delete('delete/{id}', 'delTemplate');

        Route::post('edit-content', 'editTemplateContent');

 
        Route::get('cms-detail/{templateId}', 'getCmsDetail');
        Route::get('template-content/{templateId}', 'templateContent');

        Route::get('product-info', 'productInfo');
    });

  
});
