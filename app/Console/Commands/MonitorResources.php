<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\ResourceOptimizationService;
use App\Http\Controllers\ResourceMonitoringController;

class MonitorResources extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'monitor:resources {--alert : Send alerts for critical issues}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor application resources and alert on issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting resource monitoring...');
        
        try {
            $monitoringController = new ResourceMonitoringController();
            $status = $monitoringController->getResourceStatus();
            
            // Display status
            $this->displayStatus($status);
            
            // Check for critical issues
            if ($status['status'] === 'critical') {
                $this->error('CRITICAL: Resource issues detected!');
                $this->handleCriticalIssues($status);
            } elseif ($status['status'] === 'warning') {
                $this->warn('WARNING: Resource usage is high');
                $this->handleWarningIssues($status);
            } else {
                $this->info('All systems healthy');
            }
            
            // Send alerts if requested
            if ($this->option('alert')) {
                $this->sendAlerts($status);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Monitoring failed: ' . $e->getMessage());
            Log::error('Resource monitoring command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
    
    /**
     * Display resource status
     */
    private function displayStatus($status)
    {
        $this->info("Overall Status: {$status['status']}");
        $this->newLine();
        
        $this->info('Resource Checks:');
        foreach ($status['checks'] as $check => $data) {
            $icon = $this->getStatusIcon($data['status']);
            $this->line("  {$icon} {$check}: {$data['message']}");
        }
        
        if (isset($status['statistics'])) {
            $this->newLine();
            $this->info('Statistics:');
            $stats = $status['statistics'];
            $this->table(['Metric', 'Value'], [
                ['Total Operations', $stats['total_operations']],
                ['High Memory Operations', $stats['high_memory_operations']],
                ['Average Memory Usage', $this->formatBytes($stats['average_memory_usage'])],
                ['Peak Memory Usage', $this->formatBytes($stats['peak_memory_usage'])],
            ]);
        }
    }
    
    /**
     * Get status icon
     */
    private function getStatusIcon($status)
    {
        switch ($status) {
            case 'ok':
                return '✅';
            case 'warning':
                return '⚠️';
            case 'error':
                return '❌';
            default:
                return '❓';
        }
    }
    
    /**
     * Handle critical issues
     */
    private function handleCriticalIssues($status)
    {
        $this->error('Critical issues detected:');
        
        foreach ($status['checks'] as $check => $data) {
            if ($data['status'] === 'error') {
                $this->error("  - {$check}: {$data['message']}");
            }
        }
        
        // Attempt emergency cleanup
        $this->info('Attempting emergency cleanup...');
        try {
            $monitoringController = new ResourceMonitoringController();
            $cleanupResult = $monitoringController->emergencyCleanup();
            
            if ($cleanupResult->getData()->status === 'success') {
                $this->info('Emergency cleanup completed successfully');
            } else {
                $this->error('Emergency cleanup failed');
            }
        } catch (\Exception $e) {
            $this->error('Emergency cleanup failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle warning issues
     */
    private function handleWarningIssues($status)
    {
        $this->warn('Warning issues detected:');
        
        foreach ($status['checks'] as $check => $data) {
            if ($data['status'] === 'warning') {
                $this->warn("  - {$check}: {$data['message']}");
            }
        }
        
        // Suggest optimizations
        $this->info('Recommendations:');
        $this->line('  - Consider running: php artisan optimize:resources');
        $this->line('  - Check for memory leaks in recent operations');
        $this->line('  - Monitor cache usage and clear if necessary');
    }
    
    /**
     * Send alerts
     */
    private function sendAlerts($status)
    {
        if ($status['status'] === 'critical') {
            $this->sendCriticalAlert($status);
        } elseif ($status['status'] === 'warning') {
            $this->sendWarningAlert($status);
        }
    }
    
    /**
     * Send critical alert
     */
    private function sendCriticalAlert($status)
    {
        $message = "CRITICAL ALERT: Resource issues detected!\n";
        $message .= "Status: {$status['status']}\n";
        $message .= "Timestamp: {$status['timestamp']}\n\n";
        
        foreach ($status['checks'] as $check => $data) {
            if ($data['status'] === 'error') {
                $message .= "❌ {$check}: {$data['message']}\n";
            }
        }
        
        Log::critical('Resource monitoring alert', [
            'status' => $status,
            'message' => $message
        ]);
        
        $this->info('Critical alert sent to logs');
    }
    
    /**
     * Send warning alert
     */
    private function sendWarningAlert($status)
    {
        $message = "WARNING: High resource usage detected\n";
        $message .= "Status: {$status['status']}\n";
        $message .= "Timestamp: {$status['timestamp']}\n\n";
        
        foreach ($status['checks'] as $check => $data) {
            if ($data['status'] === 'warning') {
                $message .= "⚠️ {$check}: {$data['message']}\n";
            }
        }
        
        Log::warning('Resource monitoring alert', [
            'status' => $status,
            'message' => $message
        ]);
        
        $this->info('Warning alert sent to logs');
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
