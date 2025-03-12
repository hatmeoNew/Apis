<?php
namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\System;

use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use NexaMerchant\Apis\Models\Template;
use NexaMerchant\Apis\Enum\ApiCacheKey;
use Illuminate\Support\Facades\Cache;


class TemplateController extends AdminController{

    public function configure($id) {
        // check the template id is exists
        $template = Template::find($id);
        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $template->template_countent
        ]);
    }

    public function saveConfigure($id, Request $request) {
        
        // check the template id is exists
        $template = Template::find($id);
        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found'
            ]);
        }

        $data = $request->all();
        $template->template_countent = $data;
        $template->save();

        // clear cache by tags
        Cache::tags([ApiCacheKey::API_SHOP_TEMPLATE])->flush();

        return response()->json([
            'status' => 'success',
            'message' => 'Template saved'
        ]);

    }
}