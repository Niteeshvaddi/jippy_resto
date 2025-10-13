<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FirebaseOptimizer
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
        // Optimize Firebase operations for shared hosting
        $this->optimizeFirebaseOperations($request);
        
        return $next($request);
    }
    
    /**
     * Optimize Firebase operations
     */
    private function optimizeFirebaseOperations(Request $request)
    {
        // Limit concurrent Firebase operations
        $this->limitConcurrentOperations();
        
        // Cache frequently accessed data
        $this->enableFirebaseCaching();
        
        // Optimize for shared hosting
        $this->setFirebaseLimits();
    }
    
    /**
     * Limit concurrent Firebase operations
     */
    private function limitConcurrentOperations()
    {
        // Skip rate limiting for local development
        if (app()->environment('local') || request()->ip() === '127.0.0.1') {
            return;
        }
        
        $cacheKey = 'firebase_operations_' . request()->ip();
        $operations = Cache::get($cacheKey, 0);
        
        if ($operations > 50) { // Max 50 operations per IP per minute for production
            Log::warning('Too many Firebase operations', [
                'ip' => request()->ip(),
                'operations' => $operations
            ]);
            
            abort(429, 'Too many requests. Please try again later.');
        }
        
        Cache::put($cacheKey, $operations + 1, 60); // 1 minute
    }
    
    /**
     * Enable Firebase caching
     */
    private function enableFirebaseCaching()
    {
        // Set cache headers for Firebase responses
        if (request()->is('api/*') || request()->is('dashboard')) {
            header('Cache-Control: public, max-age=300'); // 5 minutes cache
        }
    }
    
    /**
     * Set Firebase limits for shared hosting
     */
    private function setFirebaseLimits()
    {
        // Set Firebase timeout limits
        if (!defined('FIREBASE_TIMEOUT')) {
            define('FIREBASE_TIMEOUT', 15); // 15 seconds max
        }
        
        if (!defined('FIREBASE_CONNECT_TIMEOUT')) {
            define('FIREBASE_CONNECT_TIMEOUT', 5); // 5 seconds connection timeout
        }
    }
}
