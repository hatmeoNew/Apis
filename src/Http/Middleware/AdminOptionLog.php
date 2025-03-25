<?php
namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

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

           

            // how to get authorization token user id
            $user_id = $request->user()->id;
            $log = [
                'user_id' => $user_id,
                'path'    => substr($request->path(), 0, 255),
                'method'  => $request->method(),
                'ip'      => $request->getClientIp(),
                'input'   => json_encode($request->input()),
            ];


            \Nicelizhi\Manage\Models\AdminOperationLog::create($log);
            
        } catch (\Exception $exception) {
            // pass
            Log::error($exception->getMessage());
        }
        return $next($request);
    }
}