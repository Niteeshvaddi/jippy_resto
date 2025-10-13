<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    /**
     * Check system health and prevent 503/508 errors
     */
    public function checkHealth()
    {
        try {
            $health = [
                'timestamp' => now()->toISOString(),
                'status' => 'healthy',
                'checks' => []
            ];
            
            // Check memory usage
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->getMemoryLimit();
            $memoryPercent = ($memoryUsage / $memoryLimit) * 100;
            
            $health['checks']['memory'] = [
                'status' => $memoryPercent < 80 ? 'ok' : 'warning',
                'usage' => $memoryUsage,
                'limit' => $memoryLimit,
                'percent' => round($memoryPercent, 2)
            ];
            
            // Check database connection
            $dbHealth = $this->checkDatabaseHealth();
            $health['checks']['database'] = $dbHealth;
            
            // Check Firebase operations
            $firebaseHealth = $this->checkFirebaseHealth();
            $health['checks']['firebase'] = $firebaseHealth;
            
            // Check concurrent operations
            $concurrentHealth = $this->checkConcurrentOperations();
            $health['checks']['concurrent'] = $concurrentHealth;
            
            // Determine overall status
            $hasWarnings = collect($health['checks'])->contains('status', 'warning');
            $hasErrors = collect($health['checks'])->contains('status', 'error');
            
            if ($hasErrors) {
                $health['status'] = 'critical';
            } elseif ($hasWarnings) {
                $health['status'] = 'warning';
            }
            
            return response()->json($health);
            
        } catch (\Exception $e) {
            Log::error('Health check failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Health check failed',
                'error' => $e->getMessage()
            ], 500);
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
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => $duration < 1000 ? 'ok' : 'warning',
                'duration_ms' => $duration,
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check Firebase health
     */
    private function checkFirebaseHealth()
    {
        try {
            $operations = Cache::get('firebase_operations_' . request()->ip(), 0);
            
            return [
                'status' => $operations < 5 ? 'ok' : 'warning',
                'operations' => $operations,
                'message' => 'Firebase operations within limits'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Firebase health check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check concurrent operations
     */
    private function checkConcurrentOperations()
    {
        try {
            $concurrent = Cache::get('concurrent_operations', 0);
            
            return [
                'status' => $concurrent < 10 ? 'ok' : 'warning',
                'count' => $concurrent,
                'message' => 'Concurrent operations within limits'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Concurrent operations check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Emergency cleanup to prevent 503/508 errors
     */
    public function emergencyCleanup()
    {
        try {
            // Force garbage collection
            gc_collect_cycles();
            
            // Clear all cache including rate limiting
            Cache::flush();
            
            // Clear specific rate limiting cache keys
            for ($i = 0; $i < 1000; $i++) {
                Cache::forget('firebase_operations_' . $i);
            }
            Cache::forget('concurrent_operations');
            
            // Clear session data
            session()->flush();
            
            // Log cleanup
            Log::info('Emergency cleanup performed', [
                'memory_before' => memory_get_usage(true),
                'memory_after' => memory_get_usage(true)
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Emergency cleanup completed - All rate limiting cleared',
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
}
