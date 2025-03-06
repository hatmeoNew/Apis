<?php

namespace NexaMerchant\Apis\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CacheResponse
{
    protected $cacheTime = 30 * 24 * 3600; // Default: 30 days
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if we should clean the cache
        $cleanCache = $request->get('clean-cache', false);
        
        // Create a normalized URL for cache key (without clean-cache parameter)
        $url = $request->url();
        $queryParams = $request->query();
        unset($queryParams['clean-cache']); // Remove clean-cache parameter
        
        // Rebuild the URL without the clean-cache parameter
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        $cacheKey = $this->makePageCacheKey($url);
        $cacheKey = 'api_cache_' . $cacheKey;

        // add the cache key to response header


        // Clean cache if requested
        if ($cleanCache) {
            Cache::forget($cacheKey);
        }

        if (Cache::has($cacheKey) && ! $cleanCache) {
            $cacheData = Cache::get($cacheKey);
            $cacheData = json_decode($cacheData, true);
            // add the cache key to response header
            return response()->json($cacheData)->header('X-Cache-Key', $cacheKey);
        }

        $response = $next($request);
        $response->headers->set('X-Cache-Key', $cacheKey);

        Cache::put($cacheKey, $response->getContent(), $this->cacheTime); // Cache for 1 day

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

    private function makePageCacheKey($url){
        return Str::slug($url);
    }
}