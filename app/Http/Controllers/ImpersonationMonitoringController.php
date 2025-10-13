<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\ImpersonationPerformanceService;

class ImpersonationMonitoringController extends Controller
{
    private $performanceService;

    public function __construct(ImpersonationPerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Get comprehensive system health status
     */
    public function getSystemHealth()
    {
        try {
            $health = $this->performanceService->getSystemHealth();
            $performanceStats = $this->performanceService->getPerformanceStats(24);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'health' => $health,
                    'performance' => $performanceStats,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system health', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system health'
            ], 500);
        }
    }

    /**
     * Get security audit logs
     */
    public function getSecurityAuditLogs(Request $request)
    {
        try {
            $query = DB::table('security_audit_logs')
                ->select(['id', 'event_type', 'ip_address', 'admin_id', 'restaurant_uid', 'created_at'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('event_type')) {
                $query->where('event_type', $request->event_type);
            }

            if ($request->has('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }

            if ($request->has('ip_address')) {
                $query->where('ip_address', $request->ip_address);
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to);
            }

            $logs = $query->paginate($request->get('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get security audit logs', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit logs'
            ], 500);
        }
    }

    /**
     * Get detailed security event
     */
    public function getSecurityEvent($id)
    {
        try {
            $event = DB::table('security_audit_logs')
                ->where('id', $id)
                ->first();

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Security event not found'
                ], 404);
            }

            $event->data = json_decode($event->data, true);

            return response()->json([
                'success' => true,
                'data' => $event
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get security event', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve security event'
            ], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(Request $request)
    {
        try {
            $hours = $request->get('hours', 24);
            $stats = $this->performanceService->getPerformanceStats($hours);

            // Get additional metrics
            $cacheStats = $this->getCacheStatistics();
            $databaseStats = $this->getDatabaseStatistics();

            return response()->json([
                'success' => true,
                'data' => [
                    'performance' => $stats,
                    'cache' => $cacheStats,
                    'database' => $databaseStats,
                    'period_hours' => $hours
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance metrics'
            ], 500);
        }
    }

    /**
     * Get security statistics
     */
    public function getSecurityStatistics(Request $request)
    {
        try {
            $days = $request->get('days', 7);
            $startDate = now()->subDays($days);

            $stats = DB::table('security_audit_logs')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('
                    event_type,
                    COUNT(*) as count,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(DISTINCT admin_id) as unique_admins
                ')
                ->groupBy('event_type')
                ->get();

            $totalEvents = $stats->sum('count');
            $failedAttempts = $stats->where('event_type', 'like', '%failed%')->sum('count');
            $successfulAttempts = $stats->where('event_type', 'like', '%success%')->sum('count');

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_events' => $totalEvents,
                        'failed_attempts' => $failedAttempts,
                        'successful_attempts' => $successfulAttempts,
                        'success_rate' => $totalEvents > 0 ? round(($successfulAttempts / $totalEvents) * 100, 2) : 0
                    ],
                    'by_event_type' => $stats,
                    'period_days' => $days
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get security statistics', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve security statistics'
            ], 500);
        }
    }

    /**
     * Test impersonation system
     */
    public function testImpersonationSystem()
    {
        try {
            $tests = [];
            $overallSuccess = true;

            // Test 1: Database Connection
            $tests['database'] = $this->testDatabaseConnection();

            // Test 2: Cache System
            $tests['cache'] = $this->testCacheSystem();

            // Test 3: Firebase Connection
            $tests['firebase'] = $this->testFirebaseConnection();

            // Test 4: Security Configuration
            $tests['security'] = $this->testSecurityConfiguration();

            // Test 5: Performance
            $tests['performance'] = $this->testPerformance();

            // Determine overall success
            foreach ($tests as $test) {
                if (!$test['success']) {
                    $overallSuccess = false;
                    break;
                }
            }

            return response()->json([
                'success' => $overallSuccess,
                'data' => [
                    'tests' => $tests,
                    'overall_status' => $overallSuccess ? 'healthy' : 'unhealthy',
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to test impersonation system', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to test impersonation system'
            ], 500);
        }
    }

    /**
     * Clean up old data
     */
    public function cleanupOldData()
    {
        try {
            $cleaned = 0;

            // Clean up old audit logs
            $retentionDays = config('impersonation_security.audit.retention_days', 90);
            $cutoffDate = now()->subDays($retentionDays);
            
            $deletedLogs = DB::table('security_audit_logs')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            $cleaned += $deletedLogs;

            // Clean up old cache entries
            $cleanedCache = $this->performanceService->cleanupOldCache();
            $cleaned += $cleanedCache;

            Log::info('Data cleanup completed', [
                'deleted_logs' => $deletedLogs,
                'cleaned_cache_entries' => $cleanedCache,
                'total_cleaned' => $cleaned
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_logs' => $deletedLogs,
                    'cleaned_cache_entries' => $cleanedCache,
                    'total_cleaned' => $cleaned
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cleanup old data', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup old data'
            ], 500);
        }
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection()
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'success' => true,
                'message' => 'Database connection successful',
                'duration_ms' => $duration
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'duration_ms' => 0
            ];
        }
    }

    /**
     * Test cache system
     */
    private function testCacheSystem()
    {
        try {
            $start = microtime(true);
            $testKey = 'test_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($retrieved === $testValue) {
                return [
                    'success' => true,
                    'message' => 'Cache system working correctly',
                    'duration_ms' => $duration
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Cache system not working correctly',
                    'duration_ms' => $duration
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Cache system failed: ' . $e->getMessage(),
                'duration_ms' => 0
            ];
        }
    }

    /**
     * Test Firebase connection
     */
    private function testFirebaseConnection()
    {
        try {
            $start = microtime(true);
            
            // This would test Firebase Admin SDK connection
            // Implementation depends on your Firebase setup
            
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'success' => true,
                'message' => 'Firebase connection successful',
                'duration_ms' => $duration
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Firebase connection failed: ' . $e->getMessage(),
                'duration_ms' => 0
            ];
        }
    }

    /**
     * Test security configuration
     */
    private function testSecurityConfiguration()
    {
        try {
            $config = config('impersonation_security');
            $issues = [];

            // Check required configurations
            if (!isset($config['rate_limiting']['enabled'])) {
                $issues[] = 'Rate limiting not configured';
            }

            if (empty($config['allowed_origins'])) {
                $issues[] = 'No allowed origins configured';
            }

            if (empty($config['allowed_ips'])) {
                $issues[] = 'No allowed IPs configured';
            }

            if (empty($issues)) {
                return [
                    'success' => true,
                    'message' => 'Security configuration is valid',
                    'issues' => []
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Security configuration has issues',
                    'issues' => $issues
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Security configuration test failed: ' . $e->getMessage(),
                'issues' => []
            ];
        }
    }

    /**
     * Test performance
     */
    private function testPerformance()
    {
        try {
            $start = microtime(true);
            
            // Test a simple database query
            DB::table('users')->limit(1)->get();
            
            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($duration < 1000) { // Less than 1 second
                return [
                    'success' => true,
                    'message' => 'Performance is acceptable',
                    'duration_ms' => $duration
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Performance is slow',
                    'duration_ms' => $duration
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Performance test failed: ' . $e->getMessage(),
                'duration_ms' => 0
            ];
        }
    }

    /**
     * Get cache statistics
     */
    private function getCacheStatistics()
    {
        try {
            // This would depend on your cache driver
            return [
                'driver' => config('cache.default'),
                'hit_rate' => 'N/A', // Would need cache driver specific implementation
                'memory_usage' => 'N/A'
            ];
        } catch (\Exception $e) {
            return [
                'driver' => 'unknown',
                'hit_rate' => 'N/A',
                'memory_usage' => 'N/A',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStatistics()
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            
            return [
                'driver' => $connection->getDriverName(),
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'connection_count' => 'N/A' // Would need database specific implementation
            ];
        } catch (\Exception $e) {
            return [
                'driver' => 'unknown',
                'version' => 'unknown',
                'connection_count' => 'N/A',
                'error' => $e->getMessage()
            ];
        }
    }
}
