<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => 'api/v1/admin',
    'middleware' => ['sanctum.locale','assign_request_id','api.admin.option.log'],
], function () {
    /**
     * Authentication routes.
     */
    require 'auth-routes.php';

    /**
     * Sale routes.
     */
    require 'sales-routes.php';

    /**
     * Catalog routes.
     */
    require 'catalog-routes.php';

    /**
     * Customer routes.
     */
    require 'customers-routes.php';

    /**
     * Marketing routes.
     */
    require 'marketing-routes.php';

    /**
     * CMS routes.
     */
    require 'cms-routes.php';

    /**
     * Setting routes.
     */
    require 'settings-routes.php';

    /**
     * Configuration routes.
     */
    require 'configuration-routes.php';

    /**
     * Reporting routes.
     */
    require 'reporting-routes.php';

    /**
     * System routes.
     */
    require 'system-routes.php';

        /**
     * System routes.
     */
    require 'template-routes.php';

    require 'country-routes.php';

    require 'email-routes.php';
});
