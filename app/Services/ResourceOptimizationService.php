<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResourceOptimizationService
{
    /**
     * Optimize image processing to reduce memory usage
     */
    public function optimizeImageProcessing($imageData, $maxWidth = 800, $maxHeight = 600, $quality = 0.8)
    {
        try {
            // Decode base64 image
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = str_replace('data:image/gif;base64,', '', $imageData);
            
            $image = imagecreatefromstring(base64_decode($imageData));
            
            if (!$image) {
                throw new \Exception('Invalid image data');
            }
            
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);
            
            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
            
            // Create optimized image
            $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG/GIF
            if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
                imagealphablending($optimizedImage, false);
                imagesavealpha($optimizedImage, true);
            }
            
            imagecopyresampled($optimizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Output optimized image
            ob_start();
            imagejpeg($optimizedImage, null, (int)($quality * 100));
            $optimizedData = ob_get_contents();
            ob_end_clean();
            
            // Clean up memory
            imagedestroy($image);
            imagedestroy($optimizedImage);
            
            return base64_encode($optimizedData);
            
        } catch (\Exception $e) {
            Log::error('Image optimization failed', ['error' => $e->getMessage()]);
            return $imageData; // Return original if optimization fails
        }
    }
    
    /**
     * Batch process database operations to reduce query count
     */
    public function batchProcessDatabaseOperations($operations, $batchSize = 50)
    {
        $results = [];
        $batches = array_chunk($operations, $batchSize);
        
        foreach ($batches as $batch) {
            try {
                DB::beginTransaction();
                
                foreach ($batch as $operation) {
                    $result = $this->executeDatabaseOperation($operation);
                    $results[] = $result;
                }
                
                DB::commit();
                
                // Force garbage collection after each batch
                gc_collect_cycles();
                
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Batch database operation failed', [
                    'error' => $e->getMessage(),
                    'batch_size' => count($batch)
                ]);
                throw $e;
            }
        }
        
        return $results;
    }
    
    /**
     * Execute individual database operation
     */
    private function executeDatabaseOperation($operation)
    {
        switch ($operation['type']) {
            case 'insert':
                return DB::table($operation['table'])->insert($operation['data']);
            case 'update':
                return DB::table($operation['table'])
                    ->where($operation['where'])
                    ->update($operation['data']);
            case 'delete':
                return DB::table($operation['table'])
                    ->where($operation['where'])
                    ->delete();
            default:
                throw new \Exception('Unknown operation type: ' . $operation['type']);
        }
    }
    
    /**
     * Optimize Firebase operations with caching and rate limiting
     */
    public function optimizeFirebaseOperations($operations)
    {
        $results = [];
        $rateLimitKey = 'firebase_operations_' . request()->ip();
        $operationsCount = Cache::get($rateLimitKey, 0);
        
        // Rate limiting: max 3 operations per minute per IP
        if ($operationsCount >= 3) {
            throw new \Exception('Firebase rate limit exceeded. Please wait before trying again.');
        }
        
        foreach ($operations as $operation) {
            try {
                // Check cache first
                $cacheKey = 'firebase_' . md5(serialize($operation));
                $cached = Cache::get($cacheKey);
                
                if ($cached) {
                    $results[] = $cached;
                    continue;
                }
                
                // Execute Firebase operation
                $result = $this->executeFirebaseOperation($operation);
                
                // Cache result for 5 minutes
                Cache::put($cacheKey, $result, 300);
                
                $results[] = $result;
                
                // Increment rate limit counter
                Cache::put($rateLimitKey, $operationsCount + 1, 60);
                
            } catch (\Exception $e) {
                Log::error('Firebase operation failed', [
                    'operation' => $operation,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
        
        return $results;
    }
    
    /**
     * Execute individual Firebase operation
     */
    private function executeFirebaseOperation($operation)
    {
        // This would contain your Firebase operation logic
        // For now, return a placeholder
        return ['success' => true, 'operation' => $operation];
    }
    
    /**
     * Monitor and log resource usage
     */
    public function monitorResourceUsage($operation = 'unknown')
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();
        
        $metrics = [
            'operation' => $operation,
            'memory_usage' => $memoryUsage,
            'memory_peak' => $memoryPeak,
            'memory_limit' => $memoryLimit,
            'memory_percent' => $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0,
            'timestamp' => now()->toISOString()
        ];
        
        // Log if memory usage is high
        if ($metrics['memory_percent'] > 70) {
            Log::warning('High memory usage detected', $metrics);
        }
        
        // Store metrics for monitoring
        $this->storeResourceMetrics($metrics);
        
        return $metrics;
    }
    
    /**
     * Store resource metrics for monitoring
     */
    private function storeResourceMetrics($metrics)
    {
        $key = 'resource_metrics_' . date('Y-m-d-H');
        $existingMetrics = Cache::get($key, []);
        $existingMetrics[] = $metrics;
        
        // Keep only last 100 metrics per hour
        if (count($existingMetrics) > 100) {
            $existingMetrics = array_slice($existingMetrics, -100);
        }
        
        Cache::put($key, $existingMetrics, 3600);
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
     * Clean up old cache entries to prevent memory leaks
     */
    public function cleanupOldCacheEntries()
    {
        try {
            $patterns = [
                'firebase_operations_*',
                'concurrent_operations_*',
                'validation_*',
                'resource_metrics_*'
            ];
            
            foreach ($patterns as $pattern) {
                // This is a simplified cleanup - in production you'd want more sophisticated cache management
                Cache::forget($pattern);
            }
            
            Log::info('Cache cleanup completed');
            
        } catch (\Exception $e) {
            Log::error('Cache cleanup failed', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get resource usage statistics
     */
    public function getResourceStats($hours = 24)
    {
        $stats = [
            'total_operations' => 0,
            'high_memory_operations' => 0,
            'average_memory_usage' => 0,
            'peak_memory_usage' => 0,
            'cache_hit_rate' => 0
        ];
        
        $totalMemory = 0;
        $peakMemory = 0;
        $highMemoryCount = 0;
        
        for ($i = 0; $i < $hours; $i++) {
            $key = 'resource_metrics_' . date('Y-m-d-H', strtotime("-{$i} hours"));
            $metrics = Cache::get($key, []);
            
            foreach ($metrics as $metric) {
                $stats['total_operations']++;
                $totalMemory += $metric['memory_usage'];
                $peakMemory = max($peakMemory, $metric['memory_peak']);
                
                if ($metric['memory_percent'] > 70) {
                    $highMemoryCount++;
                }
            }
        }
        
        $stats['high_memory_operations'] = $highMemoryCount;
        $stats['average_memory_usage'] = $stats['total_operations'] > 0 ? round($totalMemory / $stats['total_operations'], 2) : 0;
        $stats['peak_memory_usage'] = $peakMemory;
        
        return $stats;
    }
}
