<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OptimizeSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize system for shared hosting performance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting system optimization...');
        
        try {
            // Clear old cache entries
            $this->clearOldCache();
            
            // Optimize database
            $this->optimizeDatabase();
            
            // Clear temporary files
            $this->clearTempFiles();
            
            // Log optimization
            Log::info('System optimization completed', [
                'memory_usage' => memory_get_usage(true),
                'timestamp' => now()
            ]);
            
            $this->info('System optimization completed successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('System optimization failed: ' . $e->getMessage());
            Log::error('System optimization failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
    
    /**
     * Clear old cache entries
     */
    private function clearOldCache()
    {
        $this->info('Clearing old cache entries...');
        
        // Clear cache entries older than 1 hour
        $cacheKeys = [
            'firebase_operations_*',
            'concurrent_operations',
            'firebase_settings_*',
            'user_data_*'
        ];
        
        foreach ($cacheKeys as $pattern) {
            if (str_contains($pattern, '*')) {
                // For wildcard patterns, we'll clear specific known keys
                $this->clearSpecificCacheKeys();
            } else {
                Cache::forget($pattern);
            }
        }
        
        $this->info('Cache cleared successfully');
    }
    
    /**
     * Clear specific cache keys
     */
    private function clearSpecificCacheKeys()
    {
        // Clear Firebase operations cache for all IPs (this is a simplified approach)
        for ($i = 0; $i < 100; $i++) {
            Cache::forget('firebase_operations_' . $i);
        }
        
        // Clear other specific keys
        Cache::forget('concurrent_operations');
    }
    
    /**
     * Optimize database
     */
    private function optimizeDatabase()
    {
        $this->info('Optimizing database...');
        
        try {
            // Run database optimization queries
            DB::statement('OPTIMIZE TABLE users');
            DB::statement('OPTIMIZE TABLE vendor_users');
            
            $this->info('Database optimized successfully');
        } catch (\Exception $e) {
            $this->warn('Database optimization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear temporary files
     */
    private function clearTempFiles()
    {
        $this->info('Clearing temporary files...');
        
        try {
            // Clear Laravel cache files
            $cachePath = storage_path('framework/cache');
            if (is_dir($cachePath)) {
                $this->clearDirectory($cachePath);
            }
            
            // Clear session files
            $sessionPath = storage_path('framework/sessions');
            if (is_dir($sessionPath)) {
                $this->clearDirectory($sessionPath);
            }
            
            $this->info('Temporary files cleared successfully');
        } catch (\Exception $e) {
            $this->warn('Failed to clear temporary files: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear directory contents
     */
    private function clearDirectory($path)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
