<?php

namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminCacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $cacheTime = 60)
    {
        // url the url path and query string as cache key, need sort query string
        // when query string include clean-cache, and it's value is true, then clean cache

        $cacheKey = $this->makePageCacheKey($request->fullUrl());
        
        $cacheKey = 'api_cache_' . $cacheKey;

        if (Cache::has($cacheKey)) {
            $cacheData = Cache::get($cacheKey);
            $cacheData = json_decode($cacheData, true);
            return response()->json($cacheData);
        }

        $response = $next($request);

        Cache::put($cacheKey, $response->getContent(), $cacheTime); // Cache for 

        return $response;
    }

    private function makePageCacheKey($url){
        return 'api_cache_' . Str::slug($url);
    }
}