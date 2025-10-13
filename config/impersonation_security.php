<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Impersonation Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security settings for the admin impersonation feature.
    | All settings are designed to prevent unauthorized access and attacks.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for impersonation requests to prevent abuse.
    |
    */
    'rate_limiting' => [
        'enabled' => env('IMPERSONATION_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('IMPERSONATION_MAX_ATTEMPTS', 5),
        'decay_minutes' => env('IMPERSONATION_DECAY_MINUTES', 15),
        'max_attempts_per_hour' => env('IMPERSONATION_MAX_ATTEMPTS_PER_HOUR', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | Restrict impersonation to specific IP addresses for enhanced security.
    | Leave empty to allow all IPs (not recommended for production).
    |
    */
    'allowed_ips' => [
        '127.0.0.1',
        '::1',
        // Add your production admin IPs here
        // '203.0.113.0/24', // Example CIDR range
        // '198.51.100.50',   // Example single IP
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Restrict impersonation requests to specific domains/origins.
    |
    */
    'allowed_origins' => [
        'https://admin.restaurant-system.com',
        'https://restaurant.restaurant-system.com',
        'http://127.0.0.1:8000',
        'http://127.0.0.1:8001',
        'http://localhost:8000',
        'http://localhost:8001',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Security
    |--------------------------------------------------------------------------
    |
    | Configure token security settings.
    |
    */
    'token' => [
        'expiration_seconds' => env('IMPERSONATION_TOKEN_EXPIRY', 300), // 5 minutes
        'max_usage_attempts' => env('IMPERSONATION_MAX_USAGE_ATTEMPTS', 3),
        'encryption_enabled' => env('IMPERSONATION_ENCRYPTION_ENABLED', true),
        'require_csrf' => env('IMPERSONATION_REQUIRE_CSRF', true),
        'require_session_validation' => env('IMPERSONATION_REQUIRE_SESSION_VALIDATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configure session security for impersonation.
    |
    */
    'session' => [
        'timeout_minutes' => env('IMPERSONATION_SESSION_TIMEOUT', 5),
        'require_admin_authentication' => env('IMPERSONATION_REQUIRE_ADMIN_AUTH', true),
        'require_admin_role' => env('IMPERSONATION_REQUIRE_ADMIN_ROLE', true),
        'session_token_validation' => env('IMPERSONATION_SESSION_TOKEN_VALIDATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Configure audit logging for security events.
    |
    */
    'audit' => [
        'enabled' => env('IMPERSONATION_AUDIT_ENABLED', true),
        'log_failed_attempts' => env('IMPERSONATION_LOG_FAILED_ATTEMPTS', true),
        'log_successful_attempts' => env('IMPERSONATION_LOG_SUCCESSFUL_ATTEMPTS', true),
        'log_security_events' => env('IMPERSONATION_LOG_SECURITY_EVENTS', true),
        'retention_days' => env('IMPERSONATION_AUDIT_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure performance monitoring and optimization.
    |
    */
    'performance' => [
        'cache_enabled' => env('IMPERSONATION_CACHE_ENABLED', true),
        'cache_ttl_seconds' => env('IMPERSONATION_CACHE_TTL', 300),
        'preload_frequent_data' => env('IMPERSONATION_PRELOAD_DATA', true),
        'monitor_response_times' => env('IMPERSONATION_MONITOR_PERFORMANCE', true),
        'slow_query_threshold_ms' => env('IMPERSONATION_SLOW_QUERY_THRESHOLD', 5000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Mechanisms
    |--------------------------------------------------------------------------
    |
    | Configure fallback mechanisms for when impersonation fails.
    |
    */
    'fallback' => [
        'enable_manual_login_fallback' => env('IMPERSONATION_ENABLE_FALLBACK', true),
        'fallback_timeout_seconds' => env('IMPERSONATION_FALLBACK_TIMEOUT', 30),
        'show_error_messages' => env('IMPERSONATION_SHOW_ERRORS', true),
        'log_fallback_usage' => env('IMPERSONATION_LOG_FALLBACK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers for impersonation endpoints.
    |
    */
    'headers' => [
        'require_https' => env('IMPERSONATION_REQUIRE_HTTPS', true),
        'add_security_headers' => env('IMPERSONATION_ADD_SECURITY_HEADERS', true),
        'cors_enabled' => env('IMPERSONATION_CORS_ENABLED', true),
        'cors_origins' => [
            'https://admin.restaurant-system.com',
            'https://restaurant.restaurant-system.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Configure validation rules for impersonation requests.
    |
    */
    'validation' => [
        'restaurant_uid' => [
            'required',
            'string',
            'regex:/^[a-zA-Z0-9_-]+$/',
            'max:128'
        ],
        'admin_id' => [
            'required',
            'string',
            'regex:/^[a-zA-Z0-9_-]+$/',
            'max:128'
        ],
        'admin_email' => [
            'required',
            'email',
            'max:255'
        ],
        'admin_name' => [
            'required',
            'string',
            'max:255'
        ],
        'session_token' => [
            'required',
            'string',
            'size:64'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment-Specific Settings
    |--------------------------------------------------------------------------
    |
    | Settings that vary by environment.
    |
    */
    'environment' => [
        'local' => [
            'rate_limiting' => ['enabled' => false],
            'ip_whitelist' => ['enabled' => false],
            'origin_validation' => ['enabled' => false],
            'require_https' => false,
        ],
        'staging' => [
            'rate_limiting' => ['enabled' => true, 'max_attempts' => 10],
            'ip_whitelist' => ['enabled' => true],
            'origin_validation' => ['enabled' => true],
            'require_https' => false,
        ],
        'production' => [
            'rate_limiting' => ['enabled' => true, 'max_attempts' => 5],
            'ip_whitelist' => ['enabled' => true],
            'origin_validation' => ['enabled' => true],
            'require_https' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Alerts
    |--------------------------------------------------------------------------
    |
    | Configure security alert thresholds and notifications.
    |
    */
    'alerts' => [
        'enabled' => env('IMPERSONATION_ALERTS_ENABLED', true),
        'failed_attempts_threshold' => env('IMPERSONATION_FAILED_ATTEMPTS_THRESHOLD', 5),
        'rate_limit_exceeded_threshold' => env('IMPERSONATION_RATE_LIMIT_THRESHOLD', 3),
        'suspicious_activity_threshold' => env('IMPERSONATION_SUSPICIOUS_ACTIVITY_THRESHOLD', 10),
        'notification_email' => env('IMPERSONATION_ALERT_EMAIL', 'security@restaurant-system.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Authentication
    |--------------------------------------------------------------------------
    |
    | Configure backup authentication methods.
    |
    */
    'backup_auth' => [
        'enabled' => env('IMPERSONATION_BACKUP_AUTH_ENABLED', true),
        'methods' => [
            'firebase_custom_token',
            'laravel_session',
            'api_key',
        ],
        'fallback_timeout_seconds' => env('IMPERSONATION_BACKUP_TIMEOUT', 10),
    ],
];
