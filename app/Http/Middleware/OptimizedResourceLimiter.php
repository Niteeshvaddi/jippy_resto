<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OptimizedResourceLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Set optimized resource limits
        $this->setOptimizedResourceLimits();
        
        // Check memory usage with more aggressive limits
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->getMemoryLimit();
        $memoryPercent = ($memoryUsage / $memoryLimit) * 100;
        
        // More aggressive memory management
        if ($memoryPercent > 60) { // 60% threshold instead of 80%
            Log::warning('High memory usage detected', [
                'usage' => $memoryUsage,
                'limit' => $memoryLimit,
                'percent' => round($memoryPercent, 2),
                'url' => $request->url()
            ]);
            
            // Force garbage collection
            gc_collect_cycles();
            
            // Clear cache if memory is still high
            if ($memoryPercent > 80) {
                $this->emergencyCacheCleanup();
            }
        }
        
        // Check concurrent operations
        $this->checkConcurrentOperations();
        
        // Set execution time limit
        set_time_limit(25); // Reduced from 30 seconds
        
        return $next($request);
    }
    
    /**
     * Set optimized resource limits for shared hosting
     */
    private function setOptimizedResourceLimits()
    {
        // More conservative memory limit
        $memoryLimit = $this->getMemoryLimit();
        if ($memoryLimit > 0) {
            $safeLimit = min($memoryLimit * 0.5, 128 * 1024 * 1024); // 50% of limit or 128MB max
            ini_set('memory_limit', $safeLimit);
        }
        
        // Shorter execution time limits
        ini_set('max_execution_time', 25);
        ini_set('max_input_time', 20);
        ini_set('default_socket_timeout', 15);
        
        // Optimize PHP settings
        ini_set('opcache.enable', 1);
        ini_set('opcache.memory_consumption', 64);
        ini_set('opcache.max_accelerated_files', 2000);
    }
    
    /**
     * Get memory limit in bytes
     */
    private function getMemoryLimit()
    {
        $limit = ini_get('memory_limit');
        if ($limit == -1) {
            return 0; // No limit
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
    
    /**
     * Check and limit concurrent operations
     */
    private function checkConcurrentOperations()
    {
        $concurrentKey = 'concurrent_operations_' . request()->ip();
        $concurrent = Cache::get($concurrentKey, 0);
        
        // Limit to 3 concurrent operations per IP
        if ($concurrent >= 3) {
            Log::warning('Concurrent operation limit reached', [
                'ip' => request()->ip(),
                'concurrent' => $concurrent
            ]);
            
            // Return 429 Too Many Requests
            return response()->json([
                'error' => 'Too many concurrent operations. Please wait and try again.',
                'retry_after' => 30
            ], 429);
        }
        
        // Increment concurrent operations
        Cache::put($concurrentKey, $concurrent + 1, 60); // 1 minute
        
        // Decrement when request finishes (handled in terminate method)
        $this->concurrentKey = $concurrentKey;
    }
    
    /**
     * Emergency cache cleanup
     */
    private function emergencyCacheCleanup()
    {
        try {
            // Clear old cache entries
            $cacheKeys = [
                'firebase_operations_*',
                'concurrent_operations_*',
                'validation_*',
                'vendor_user_*'
            ];
            
            foreach ($cacheKeys as $pattern) {
                // This is a simplified cleanup - in production you'd want more sophisticated cache management
                Cache::forget($pattern);
            }
            
            Log::info('Emergency cache cleanup performed');
        } catch (\Exception $e) {
            Log::error('Emergency cache cleanup failed', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle request termination
     */
    public function terminate($request, $response)
    {
        // Decrement concurrent operations
        if (isset($this->concurrentKey)) {
            $concurrent = Cache::get($this->concurrentKey, 0);
            if ($concurrent > 0) {
                Cache::put($this->concurrentKey, $concurrent - 1, 60);
            }
        }
    }
}
