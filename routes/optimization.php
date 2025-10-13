<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemHealthController;
use App\Http\Controllers\OptimizedFoodController;
use App\Services\ResourceOptimizationService;

/*
|--------------------------------------------------------------------------
| Optimization Routes
|--------------------------------------------------------------------------
|
| These routes provide optimization and monitoring endpoints to help
| prevent 503/508 errors and manage resource usage.
|
*/

// Health check and monitoring routes
Route::prefix('api/health')->middleware(['throttle:60,1'])->group(function () {
    Route::get('/', [SystemHealthController::class, 'checkHealth'])->name('health.check');
    Route::post('/cleanup', [SystemHealthController::class, 'emergencyCleanup'])->name('health.cleanup');
});

// Optimized food controller routes
Route::prefix('foods')->middleware(['auth', 'throttle:30,1'])->group(function () {
    Route::get('/optimized', [OptimizedFoodController::class, 'index'])->name('foods.optimized.index');
    Route::post('/optimized/import', [OptimizedFoodController::class, 'import'])->name('foods.optimized.import');
    Route::post('/optimized/inline-update/{id}', [OptimizedFoodController::class, 'inlineUpdate'])->name('foods.optimized.inline-update');
});

// Resource optimization routes
Route::prefix('api/optimization')->middleware(['throttle:20,1'])->group(function () {
    Route::get('/stats', function () {
        $service = new ResourceOptimizationService();
        return response()->json($service->getResourceStats(24));
    })->name('optimization.stats');
    
    Route::post('/cleanup', function () {
        $service = new ResourceOptimizationService();
        $service->cleanupOldCacheEntries();
        return response()->json(['status' => 'success', 'message' => 'Cache cleanup completed']);
    })->name('optimization.cleanup');
    
    Route::get('/monitor', function () {
        $service = new ResourceOptimizationService();
        $metrics = $service->monitorResourceUsage('api_monitor');
        return response()->json($metrics);
    })->name('optimization.monitor');
});
