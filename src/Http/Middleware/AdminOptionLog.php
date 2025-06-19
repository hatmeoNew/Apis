<?php
namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminOptionLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $now = Carbon::now(); // 使用Carbon对象
            // how to get authorization token user id
            $user_id = $request->user()->id;
            $log = [
                'user_id' => $user_id,
                'path'    => substr($request->path(), 0, 255),
                'method'  => $request->method(),
                'ip'      => $request->getClientIp(),
                'input'   => json_encode($request->input()),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // save log use DB

            DB::table('admin_operation_logs')->insert($log);

        } catch (\Exception $exception) {
            // pass
            Log::error($exception->getMessage());
        }
        return $next($request);
    }
}