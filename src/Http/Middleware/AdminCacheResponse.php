<?php

namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use NexaMerchant\Apis\Enum\ApiCacheKey;

class AdminCacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $cacheTime = 1 * 24 * 3600, ...$tags)
    {
        // if the debug mode is on, then don't cache the response
        if (config('app.debug')) {
            return $next($request);
        }

        // url the url path and query string as cache key, need sort query string
        // when query string include clean-cache, and it's value is true, then clean cache

        $cacheKey = $this->makePageCacheKey($request->fullUrl());
        
        $cacheKey = 'api_admin_cache_' . $cacheKey;

        if (Cache::tags($tags)->has($cacheKey)) {
            $cacheData = Cache::tags($tags)->get($cacheKey);
            $cacheData = json_decode($cacheData, true);
            return response()->json($cacheData);
        }

        $response = $next($request);

        Cache::tags($tags)->put($cacheKey, $response->getContent(), $cacheTime); // Cache for 

        return $response;
    }

    private function makePageCacheKey($url){
        return 'api_cache_' . Str::slug($url);
    }
}