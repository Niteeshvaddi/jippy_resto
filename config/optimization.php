<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Resource Optimization Settings
    |--------------------------------------------------------------------------
    |
    | These settings control various optimization features to reduce
    | resource usage and prevent 503/508 errors on shared hosting.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Memory Management
    |--------------------------------------------------------------------------
    */
    'memory' => [
        'max_usage_percent' => env('MEMORY_MAX_USAGE_PERCENT', 60),
        'emergency_cleanup_threshold' => env('MEMORY_EMERGENCY_THRESHOLD', 80),
        'chunk_size' => env('MEMORY_CHUNK_SIZE', 25),
        'max_memory_limit' => env('MEMORY_MAX_LIMIT', 128), // MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    */
    'database' => [
        'batch_size' => env('DB_BATCH_SIZE', 50),
        'query_timeout' => env('DB_QUERY_TIMEOUT', 10),
        'connection_pool_size' => env('DB_POOL_SIZE', 5),
        'enable_query_caching' => env('DB_QUERY_CACHING', true),
        'cache_ttl' => env('DB_CACHE_TTL', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Optimization
    |--------------------------------------------------------------------------
    */
    'firebase' => [
        'rate_limit_per_minute' => env('FIREBASE_RATE_LIMIT', 3),
        'timeout' => env('FIREBASE_TIMEOUT', 10),
        'retry_attempts' => env('FIREBASE_RETRY_ATTEMPTS', 2),
        'batch_size' => env('FIREBASE_BATCH_SIZE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */
    'images' => [
        'max_width' => env('IMAGE_MAX_WIDTH', 800),
        'max_height' => env('IMAGE_MAX_HEIGHT', 600),
        'quality' => env('IMAGE_QUALITY', 0.8),
        'enable_compression' => env('IMAGE_COMPRESSION', true),
        'max_file_size' => env('IMAGE_MAX_SIZE', 2048), // KB
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Strategy
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'vendor_data_ttl' => env('CACHE_VENDOR_TTL', 300), // 5 minutes
        'admin_permissions_ttl' => env('CACHE_ADMIN_TTL', 1800), // 30 minutes
        'firebase_operations_ttl' => env('CACHE_FIREBASE_TTL', 60), // 1 minute
        'validation_ttl' => env('CACHE_VALIDATION_TTL', 60), // 1 minute
        'cleanup_interval' => env('CACHE_CLEANUP_INTERVAL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'concurrent_operations' => env('RATE_LIMIT_CONCURRENT', 3),
        'firebase_operations' => env('RATE_LIMIT_FIREBASE', 5),
        'import_operations' => env('RATE_LIMIT_IMPORT', 2),
        'timeout' => env('RATE_LIMIT_TIMEOUT', 60), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enable_resource_monitoring' => env('MONITORING_ENABLED', true),
        'log_high_memory_usage' => env('MONITORING_LOG_MEMORY', true),
        'alert_threshold' => env('MONITORING_ALERT_THRESHOLD', 70), // percent
        'metrics_retention_hours' => env('MONITORING_RETENTION', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Emergency Procedures
    |--------------------------------------------------------------------------
    */
    'emergency' => [
        'auto_cleanup' => env('EMERGENCY_AUTO_CLEANUP', true),
        'memory_threshold' => env('EMERGENCY_MEMORY_THRESHOLD', 85), // percent
        'cache_flush' => env('EMERGENCY_CACHE_FLUSH', true),
        'session_cleanup' => env('EMERGENCY_SESSION_CLEANUP', true),
    ],
];
