<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ResourceOptimizationService;

class OptimizeResources extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'optimize:resources {--force : Force optimization even if not needed}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize application resources and clean up cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting resource optimization...');
        
        $optimizationService = new ResourceOptimizationService();
        
        // Clean up old cache entries
        $this->info('Cleaning up old cache entries...');
        $optimizationService->cleanupOldCacheEntries();
        
        // Get current resource stats
        $stats = $optimizationService->getResourceStats(24);
        $this->info('Current resource statistics:');
        $this->table(['Metric', 'Value'], [
            ['Total Operations', $stats['total_operations']],
            ['High Memory Operations', $stats['high_memory_operations']],
            ['Average Memory Usage', $this->formatBytes($stats['average_memory_usage'])],
            ['Peak Memory Usage', $this->formatBytes($stats['peak_memory_usage'])],
        ]);
        
        // Force garbage collection
        $this->info('Forcing garbage collection...');
        gc_collect_cycles();
        
        // Clear specific cache patterns
        $this->info('Clearing specific cache patterns...');
        $this->clearCachePatterns();
        
        // Optimize database if needed
        if ($this->option('force') || $stats['high_memory_operations'] > 10) {
            $this->info('Optimizing database...');
            $this->optimizeDatabase();
        }
        
        $this->info('Resource optimization completed successfully!');
        
        return 0;
    }
    
    /**
     * Clear specific cache patterns
     */
    private function clearCachePatterns()
    {
        $patterns = [
            'firebase_operations_*',
            'concurrent_operations_*',
            'validation_*',
            'resource_metrics_*',
            'vendor_user_*'
        ];
        
        foreach ($patterns as $pattern) {
            // In a real implementation, you'd use a more sophisticated cache clearing mechanism
            // For now, we'll just clear some common keys
            Cache::forget($pattern);
        }
    }
    
    /**
     * Optimize database
     */
    private function optimizeDatabase()
    {
        try {
            // Run database optimization queries
            \DB::statement('OPTIMIZE TABLE users');
            \DB::statement('OPTIMIZE TABLE vendor_users');
            \DB::statement('OPTIMIZE TABLE model_has_roles');
            \DB::statement('OPTIMIZE TABLE model_has_permissions');
            \DB::statement('OPTIMIZE TABLE security_audit_logs');
            
            $this->info('Database optimization completed');
        } catch (\Exception $e) {
            $this->error('Database optimization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
