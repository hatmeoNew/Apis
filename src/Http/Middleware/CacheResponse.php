<?php

namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        // url the url path and query string as cache key, need sort query string
        // when query string include clean-cache, and it's value is true, then clean cache
        $url = $request->fullUrl();
        $cleanCache = $request->get('clean-cache', false);
        $urlParts = parse_url($url);
        $query = [];
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $query);
        }
        ksort($query);
        unset($query['clean-cache']);
        $queryStr = http_build_query($query);
        $cacheKey = md5($urlParts['path'] . '?' . $queryStr);

        $cacheKey = $this->makePageCacheKey($request->fullUrl());
        
        $cacheKey = 'api_cache_' . $cacheKey;

        if (Cache::has($cacheKey) && ! $cleanCache) {
            $cacheData = Cache::get($cacheKey);
            $cacheData = json_decode($cacheData, true);
            return response()->json($cacheData);
        }

        $response = $next($request);

        Cache::put($cacheKey, $response->getContent(), 30*24*3600); // Cache for 1 day

        return $response;
    }

    private function makePageCacheKey($url){
        return 'api_cache_' . Str::slug($url);
    }
}