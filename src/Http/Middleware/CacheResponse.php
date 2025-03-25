<?php

namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use NexaMerchant\Apis\Enum\ApiCacheKey;

class CacheResponse
{
    protected $cacheTime = 30 * 24 * 3600; // Default: 30 days

    protected $cacheTagMap = [];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $cacheTime = 1 * 24 * 3600, ...$tags)
    {
        // Check if we should clean the cache

        
        // Create a normalized URL for cache key (without clean-cache parameter)
        $url = $request->fullUrl();
        
        $cacheKey = $this->makePageCacheKey($url);
        $cacheKey = 'api_cache_' . $cacheKey;

        // add the cache key to response header

        // if the debug mode is on, then don't cache the response
        if (config('app.debug')) {
            return $next($request);
        }

        if (Cache::tags($tags)->has($cacheKey)) {
            $cacheData = Cache::tags($tags)->get($cacheKey);
            $cacheData = json_decode($cacheData, true);
            return response()->json($cacheData);
        }

        $response = $next($request);
        $response->headers->set('X-Cache-Key', $cacheKey);

        Cache::tags($tags)->put($cacheKey, $response->getContent(), $cacheTime); // Cache for 1 day

        // Store the tags in the $cacheTagMap
        $this->cacheTagMap[$cacheKey] = $tags;

        // add cache generated date to response header
        $response->headers->set('X-Cache-Generated-At', now()->toDateTimeString());
        // add the cache key to response header
        

        return $response;
    }

     /**
     * Set the cache time in seconds.
     * 
     * @param int $seconds
     * @return $this
     */
    public function setCacheTime($seconds)
    {
        $this->cacheTime = $seconds;
        return $this;
    }

    /**
     * Force clean the cache for this request.
     *
     * @return $this
     */
    public function cleanCache()
    {
        request()->merge(['clean-cache' => true]);
        return $this;
    }

    public function getCacheTags(string $cacheKey): array
    {
        return $this->cacheTagMap[$cacheKey] ?? [];
    }

    protected function makePageCacheKey($url)
    {
        return md5($url);
    }
}