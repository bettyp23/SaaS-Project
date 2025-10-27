<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Production Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains production-specific configurations for security,
    | performance, and monitoring.
    |
    */

    // Force HTTPS in production
    'force_https' => env('FORCE_HTTPS', true),

    // Trusted proxies (load balancers, CDNs)
    'trusted_proxies' => env('TRUSTED_PROXIES', '*'),

    // Rate limiting for API
    'api_rate_limit' => env('API_RATE_LIMIT', 60), // requests per minute

    // Session security
    'session_secure' => true,
    'session_httponly' => true,
    'session_samesite' => 'strict',

    // Cookies security
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'strict',

    // Error reporting
    'log_level' => env('LOG_LEVEL', 'error'),

    // Caching
    'cache_driver' => env('CACHE_DRIVER', 'redis'),
    'queue_connection' => env('QUEUE_CONNECTION', 'redis'),

    // Monitoring
    'sentry_dsn' => env('SENTRY_DSN', null),
    'bugsnag_api_key' => env('BUGSNAG_API_KEY', null),

    // GDPR settings
    'gdpr_enabled' => true,
    'data_retention_days' => env('DATA_RETENTION_DAYS', 365),

    // Backup configuration
    'backup_driver' => env('BACKUP_DRIVER', 's3'),
    'backup_schedule' => env('BACKUP_SCHEDULE', 'daily'),

    // CDN configuration
    'cdn_url' => env('CDN_URL', null),
    'cdn_enabled' => env('CDN_ENABLED', false),

    // Health check endpoints
    'health_check_enabled' => true,
    'health_check_path' => '/health',

];
