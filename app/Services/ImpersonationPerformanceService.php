<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ImpersonationPerformanceService
{
    private $cachePrefix = 'impersonation_perf_';
    private $defaultTTL = 300; // 5 minutes

    /**
     * Cache restaurant data for faster access
     */
    public function cacheRestaurantData($restaurantUid, $data)
    {
        $key = $this->cachePrefix . 'restaurant_' . $restaurantUid;
        Cache::put($key, $data, $this->defaultTTL);
        
        Log::info('Restaurant data cached', [
            'restaurant_uid' => $restaurantUid,
            'cache_key' => $key
        ]);
    }

    /**
     * Get cached restaurant data
     */
    public function getCachedRestaurantData($restaurantUid)
    {
        $key = $this->cachePrefix . 'restaurant_' . $restaurantUid;
        return Cache::get($key);
    }

    /**
     * Cache admin permissions for faster validation
     */
    public function cacheAdminPermissions($adminId, $permissions)
    {
        $key = $this->cachePrefix . 'admin_perms_' . $adminId;
        Cache::put($key, $permissions, 1800); // 30 minutes
        
        Log::info('Admin permissions cached', [
            'admin_id' => $adminId,
            'permissions_count' => count($permissions)
        ]);
    }

    /**
     * Get cached admin permissions
     */
    public function getCachedAdminPermissions($adminId)
    {
        $key = $this->cachePrefix . 'admin_perms_' . $adminId;
        return Cache::get($key);
    }

    /**
     * Optimize database queries for impersonation
     */
    public function getOptimizedRestaurantData($restaurantUid)
    {
        // Use database query optimization
        $restaurant = DB::table('users')
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'status',
                'created_at',
                'updated_at'
            ])
            ->where('firebase_uid', $restaurantUid)
            ->where('status', 'active')
            ->first();

        if ($restaurant) {
            // Cache the result
            $this->cacheRestaurantData($restaurantUid, $restaurant);
        }

        return $restaurant;
    }

    /**
     * Batch process multiple restaurant validations
     */
    public function batchValidateRestaurants($restaurantUids)
    {
        $results = [];
        $uncachedUids = [];

        // Check cache first
        foreach ($restaurantUids as $uid) {
            $cached = $this->getCachedRestaurantData($uid);
            if ($cached) {
                $results[$uid] = $cached;
            } else {
                $uncachedUids[] = $uid;
            }
        }

        // Batch query for uncached restaurants
        if (!empty($uncachedUids)) {
            $restaurants = DB::table('users')
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone',
                    'status',
                    'firebase_uid',
                    'created_at',
                    'updated_at'
                ])
                ->whereIn('firebase_uid', $uncachedUids)
                ->where('status', 'active')
                ->get()
                ->keyBy('firebase_uid');

            foreach ($restaurants as $uid => $restaurant) {
                $results[$uid] = $restaurant;
                $this->cacheRestaurantData($uid, $restaurant);
            }
        }

        return $results;
    }

    /**
     * Monitor performance metrics
     */
    public function recordPerformanceMetric($operation, $duration, $success = true, $metadata = [])
    {
        $metric = [
            'operation' => $operation,
            'duration_ms' => $duration,
            'success' => $success,
            'timestamp' => time(),
            'metadata' => $metadata
        ];

        // Store in cache for real-time monitoring
        $key = $this->cachePrefix . 'metrics_' . date('Y-m-d-H');
        $metrics = Cache::get($key, []);
        $metrics[] = $metric;
        
        // Keep only last 1000 metrics per hour
        if (count($metrics) > 1000) {
            $metrics = array_slice($metrics, -1000);
        }
        
        Cache::put($key, $metrics, 3600); // 1 hour

        // Log performance issues
        if ($duration > 5000) { // 5 seconds
            Log::warning('Slow impersonation operation', $metric);
        }

        if (!$success) {
            Log::error('Failed impersonation operation', $metric);
        }
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStats($hours = 24)
    {
        $stats = [
            'total_operations' => 0,
            'successful_operations' => 0,
            'failed_operations' => 0,
            'average_duration' => 0,
            'slow_operations' => 0,
            'cache_hit_rate' => 0
        ];

        $totalDuration = 0;
        $cacheHits = 0;
        $cacheMisses = 0;

        for ($i = 0; $i < $hours; $i++) {
            $key = $this->cachePrefix . 'metrics_' . date('Y-m-d-H', strtotime("-{$i} hours"));
            $metrics = Cache::get($key, []);

            foreach ($metrics as $metric) {
                $stats['total_operations']++;
                $totalDuration += $metric['duration_ms'];

                if ($metric['success']) {
                    $stats['successful_operations']++;
                } else {
                    $stats['failed_operations']++;
                }

                if ($metric['duration_ms'] > 5000) {
                    $stats['slow_operations']++;
                }

                if (isset($metric['metadata']['cache_hit'])) {
                    if ($metric['metadata']['cache_hit']) {
                        $cacheHits++;
                    } else {
                        $cacheMisses++;
                    }
                }
            }
        }

        if ($stats['total_operations'] > 0) {
            $stats['average_duration'] = round($totalDuration / $stats['total_operations'], 2);
        }

        $totalCacheOps = $cacheHits + $cacheMisses;
        if ($totalCacheOps > 0) {
            $stats['cache_hit_rate'] = round(($cacheHits / $totalCacheOps) * 100, 2);
        }

        return $stats;
    }

    /**
     * Clean up old cache entries
     */
    public function cleanupOldCache()
    {
        $cleaned = 0;
        $keys = Cache::get('impersonation_cache_keys', []);

        foreach ($keys as $key) {
            if (Cache::has($key)) {
                $data = Cache::get($key);
                if (isset($data['created_at']) && (time() - $data['created_at']) > $this->defaultTTL) {
                    Cache::forget($key);
                    $cleaned++;
                }
            }
        }

        Log::info('Impersonation cache cleanup completed', [
            'cleaned_entries' => $cleaned,
            'total_keys' => count($keys)
        ]);

        return $cleaned;
    }

    /**
     * Preload frequently accessed data
     */
    public function preloadFrequentData()
    {
        // Preload active restaurants
        $activeRestaurants = DB::table('users')
            ->select(['firebase_uid', 'name', 'email', 'status'])
            ->where('status', 'active')
            ->whereNotNull('firebase_uid')
            ->limit(100)
            ->get();

        foreach ($activeRestaurants as $restaurant) {
            $this->cacheRestaurantData($restaurant->firebase_uid, $restaurant);
        }

        // Preload admin permissions
        $admins = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.role_id', 1) // Admin role
            ->select(['users.id', 'users.name', 'users.email'])
            ->get();

        foreach ($admins as $admin) {
            $permissions = $this->getAdminPermissions($admin->id);
            $this->cacheAdminPermissions($admin->id, $permissions);
        }

        Log::info('Frequent data preloaded', [
            'restaurants' => $activeRestaurants->count(),
            'admins' => $admins->count()
        ]);
    }

    /**
     * Get admin permissions (optimized)
     */
    private function getAdminPermissions($adminId)
    {
        return DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $adminId)
            ->pluck('permissions.name')
            ->toArray();
    }

    /**
     * Monitor system health
     */
    public function getSystemHealth()
    {
        $health = [
            'status' => 'healthy',
            'issues' => [],
            'metrics' => []
        ];

        // Check cache performance
        $stats = $this->getPerformanceStats(1); // Last hour
        if ($stats['cache_hit_rate'] < 70) {
            $health['issues'][] = 'Low cache hit rate: ' . $stats['cache_hit_rate'] . '%';
        }

        if ($stats['average_duration'] > 2000) {
            $health['issues'][] = 'High average response time: ' . $stats['average_duration'] . 'ms';
        }

        if ($stats['failed_operations'] > 0) {
            $health['issues'][] = 'Failed operations detected: ' . $stats['failed_operations'];
        }

        // Check database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $health['issues'][] = 'Database connection issue: ' . $e->getMessage();
        }

        // Check cache connection
        try {
            Cache::put('health_check', 'ok', 60);
            if (Cache::get('health_check') !== 'ok') {
                $health['issues'][] = 'Cache connection issue';
            }
        } catch (\Exception $e) {
            $health['issues'][] = 'Cache connection issue: ' . $e->getMessage();
        }

        if (!empty($health['issues'])) {
            $health['status'] = 'unhealthy';
        }

        $health['metrics'] = $stats;

        return $health;
    }
}
