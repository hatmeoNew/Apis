<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Email;

use Illuminate\Http\Request;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;
use Nicelizhi\Shopify\Models\EmailSendRecords;

class EmailSendRecordsController extends AdminController
{
    public function index(Request $request)
    {
        $query = EmailSendRecords::query();

        if ($search = $request->input('code')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        if ($search = $request->input('name')) {
            $query->where(function ($q) use ($search) {
                $q->Where('name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20); // 默认每页 20 条
        $countries = $query->paginate($perPage);

        return response()->json($countries);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:countries,code',
            'name' => 'required',
        ]);

        $country = EmailSendRecords::create($validated);
        return response()->json($country, 200);
    }

    public function update(Request $request, $id)
    {
        $country = EmailSendRecords::findOrFail($id);
        $country->update($request->all());
        return response()->json($country);
    }

    public function destroy($id)
    {
        EmailSendRecords::destroy($id);
        return response()->json(['message' => 'Country deleted successfully.']);
    }

    /**
     * 根据订单ID获取邮件发送记录
     *
     * @param  Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\Response
     */
    public function getByOrderId($orderId)
    {
        try {
            // 验证订单ID
            if (!is_numeric($orderId) || $orderId <= 0) {
                return response()->json([
                    'code' => 400,
                    'message' => '无效的订单ID'
                ], 400);
            }

            // 查询邮件记录
            $records = EmailSendRecords::where('order_id', $orderId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'code' => 200,
                'message' => '查询成功',
                'data' => $records
            ], 200);

        } catch (\Exception $e) {
            Log::error('获取邮件记录失败: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'code' => 500,
                'message' => '服务器内部错误'
            ], 500);
        }
    }
}
