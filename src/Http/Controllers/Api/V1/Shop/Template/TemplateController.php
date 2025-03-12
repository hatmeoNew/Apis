<?php
namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Template;

use Illuminate\Http\Request;

use NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\ShopController;
use NexaMerchant\Apis\Models\Template;

class TemplateController extends ShopController
{
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
}