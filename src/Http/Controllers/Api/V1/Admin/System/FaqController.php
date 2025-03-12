<?php
namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\System;

use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\ShipmentRepository;
use Illuminate\Support\Facades\Mail;
use Webkul\Shop\Mail\Order\CreatedNotification;
use Webkul\Shop\Mail\Order\CanceledNotification;
use Webkul\Sales\Contracts\OrderComment;
use Webkul\Shop\Mail\Order\ShipmentNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;


class FaqController extends AdminController
{

    // load the faq list
    public function index() {
        $faqs = Redis::hgetall('faq');

        ksort($faqs);

        return response()->json([
            'status' => 'success',
            'data' => $faqs
        ]);
    }

    // save the faq data in redis
    public function store(Request $request) {
        $data = $request->all();

        $key = isset($data['key']) ? $data['key'] : time();
        $q = $data['q'];
        $a = $data['a'];

        $faq = Redis::hset('faq', $key, json_encode(['q' => $q, 'a' => $a]));

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ has been saved successfully'
        ]);
    }

    // delete the faq data from redis
    public function destroy($key) {
        $faq = Redis::hdel('faq', $key);

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ has been deleted successfully'
        ]);
    }
}