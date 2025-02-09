<?php

namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheResponse
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
        $cacheKey = 'api_cache_' . md5($request->fullUrl().$request->ip());

        $cleanCache = $request->input('clean-cache');

        if (Cache::has($cacheKey) && ! $cleanCache) {
            $cacheData = Cache::get($cacheKey);
            $cacheData = json_decode($cacheData, true);
            return response()->json($cacheData);
        }

        $response = $next($request);

        Cache::put($cacheKey, $response->getContent(), 60); // Cache for 60 minutes

        return $response;
    }
}