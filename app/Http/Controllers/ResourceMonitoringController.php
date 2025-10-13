<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ResourceOptimizationService;

class ResourceMonitoringController extends Controller
{
    /**
     * Get comprehensive resource monitoring data
     */
    public function getResourceStatus()
    {
        try {
            $optimizationService = new ResourceOptimizationService();
            
            $status = [
                'timestamp' => now()->toISOString(),
                'status' => 'healthy',
                'checks' => []
            ];
            
            // Check memory usage
            $memoryStatus = $this->checkMemoryUsage();
            $status['checks']['memory'] = $memoryStatus;
            
            // Check database performance
            $dbStatus = $this->checkDatabasePerformance();
            $status['checks']['database'] = $dbStatus;
            
            // Check cache performance
            $cacheStatus = $this->checkCachePerformance();
            $status['checks']['cache'] = $cacheStatus;
            
            // Check concurrent operations
            $concurrentStatus = $this->checkConcurrentOperations();
            $status['checks']['concurrent'] = $concurrentStatus;
            
            // Check Firebase operations
            $firebaseStatus = $this->checkFirebaseOperations();
            $status['checks']['firebase'] = $firebaseStatus;
            
            // Determine overall status
            $hasWarnings = collect($status['checks'])->contains('status', 'warning');
            $hasErrors = collect($status['checks'])->contains('status', 'error');
            
            if ($hasErrors) {
                $status['status'] = 'critical';
            } elseif ($hasWarnings) {
                $status['status'] = 'warning';
            }
            
            // Get resource statistics
            $stats = $optimizationService->getResourceStats(24);
            $status['statistics'] = $stats;
            
            return response()->json($status);
            
        } catch (\Exception $e) {
            Log::error('Resource monitoring failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Resource monitoring failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check memory usage
     */
    private function checkMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();
        $memoryPercent = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;
        
        $status = 'ok';
        if ($memoryPercent > 80) {
            $status = 'error';
        } elseif ($memoryPercent > 60) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'usage' => $memoryUsage,
            'peak' => $memoryPeak,
            'limit' => $memoryLimit,
            'percent' => round($memoryPercent, 2),
            'message' => "Memory usage: {$memoryPercent}%"
        ];
    }
    
    /**
     * Check database performance
     */
    private function checkDatabasePerformance()
    {
        try {
            $start = microtime(true);
            \DB::connection()->getPdo();
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            $status = 'ok';
            if ($duration > 2000) {
                $status = 'error';
            } elseif ($duration > 1000) {
                $status = 'warning';
            }
            
            return [
                'status' => $status,
                'duration_ms' => $duration,
                'message' => "Database connection: {$duration}ms"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check cache performance
     */
    private function checkCachePerformance()
    {
        try {
            $testKey = 'cache_test_' . time();
            $testValue = 'test_value';
            
            $start = microtime(true);
            Cache::put($testKey, $testValue, 60);
            $cached = Cache::get($testKey);
            Cache::forget($testKey);
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            $status = 'ok';
            if ($duration > 100) {
                $status = 'warning';
            }
            
            if (!$cached || $cached !== $testValue) {
                $status = 'error';
            }
            
            return [
                'status' => $status,
                'duration_ms' => $duration,
                'message' => "Cache performance: {$duration}ms"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache test failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check concurrent operations
     */
    private function checkConcurrentOperations()
    {
        try {
            $concurrent = Cache::get('concurrent_operations_' . request()->ip(), 0);
            $maxConcurrent = config('optimization.rate_limiting.concurrent_operations', 3);
            
            $status = 'ok';
            if ($concurrent >= $maxConcurrent) {
                $status = 'warning';
            }
            
            return [
                'status' => $status,
                'current' => $concurrent,
                'max' => $maxConcurrent,
                'message' => "Concurrent operations: {$concurrent}/{$maxConcurrent}"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Concurrent operations check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check Firebase operations
     */
    private function checkFirebaseOperations()
    {
        try {
            $firebaseOps = Cache::get('firebase_operations_' . request()->ip(), 0);
            $maxFirebaseOps = config('optimization.rate_limiting.firebase_operations', 5);
            
            $status = 'ok';
            if ($firebaseOps >= $maxFirebaseOps) {
                $status = 'warning';
            }
            
            return [
                'status' => $status,
                'current' => $firebaseOps,
                'max' => $maxFirebaseOps,
                'message' => "Firebase operations: {$firebaseOps}/{$maxFirebaseOps}"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Firebase operations check failed: ' . $e->getMessage()
            ];
        }
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
     * Emergency resource cleanup
     */
    public function emergencyCleanup()
    {
        try {
            $optimizationService = new ResourceOptimizationService();
            
            // Force garbage collection
            gc_collect_cycles();
            
            // Clear all cache
            Cache::flush();
            
            // Clean up old cache entries
            $optimizationService->cleanupOldCacheEntries();
            
            // Clear session data
            session()->flush();
            
            Log::info('Emergency resource cleanup performed', [
                'memory_before' => memory_get_usage(true),
                'memory_after' => memory_get_usage(true)
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Emergency cleanup completed',
                'memory_usage' => memory_get_usage(true)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Emergency cleanup failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Emergency cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get detailed resource statistics
     */
    public function getDetailedStats()
    {
        try {
            $optimizationService = new ResourceOptimizationService();
            
            $stats = $optimizationService->getResourceStats(24);
            $currentMetrics = $optimizationService->monitorResourceUsage('detailed_stats');
            
            return response()->json([
                'historical' => $stats,
                'current' => $currentMetrics,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Detailed stats failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get detailed statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
