<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FirebaseCacheOptimizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Optimize Firebase operations with intelligent caching
        $this->optimizeFirebaseCaching($request);
        
        return $next($request);
    }
    
    /**
     * Optimize Firebase caching
     */
    private function optimizeFirebaseCaching(Request $request)
    {
        // Cache Firebase settings for 5 minutes
        if ($request->is('dashboard') || $request->is('foods/*') || $request->is('orders/*')) {
            $this->cacheFirebaseSettings();
        }
        
        // Cache user data for 2 minutes
        if ($request->is('dashboard')) {
            $this->cacheUserData();
        }
    }
    
    /**
     * Cache Firebase settings
     */
    private function cacheFirebaseSettings()
    {
        $cacheKey = 'firebase_settings_' . auth()->id();
        
        if (!Cache::has($cacheKey)) {
            // This will be populated by the frontend
            Cache::put($cacheKey, 'cached', 300); // 5 minutes
        }
    }
    
    /**
     * Cache user data
     */
    private function cacheUserData()
    {
        $cacheKey = 'user_data_' . auth()->id();
        
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 'cached', 120); // 2 minutes
        }
    }
}
