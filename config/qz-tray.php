<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QZ Tray Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for QZ Tray integration.
    |
    */

    'enabled' => env('QZ_TRAY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Smart Print Settings
    |--------------------------------------------------------------------------
    */
    'smart_print' => [
        'enabled' => env('QZ_SMART_PRINT_ENABLED', true),
        'fallback_order' => explode(',', env('QZ_FALLBACK_ORDER', 'qz,browser,download')),
        'auto_attach' => env('QZ_AUTO_ATTACH', true),
        'show_indicator' => env('QZ_SHOW_PRINT_INDICATOR', true),
        'show_errors' => env('QZ_SHOW_PRINT_ERRORS', true),
        'default_type' => env('QZ_DEFAULT_PRINT_TYPE', 'auto'),
        'timeout' => env('QZ_PRINT_TIMEOUT', 30000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'require_https' => env('QZ_REQUIRE_HTTPS', true),
        'allow_localhost' => env('QZ_ALLOW_LOCALHOST', true),
        'signature_algorithm' => env('QZ_SIGNATURE_ALGORITHM', 'SHA512'),
        'private_key' => env('QZ_PRIVATE_KEY', ''),
        'certificate_path' => storage_path(env('QZ_CERTIFICATE_PATH', 'app/certs/')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    */
    'connection' => [
        'auto_connect' => env('QZ_AUTO_CONNECT', true),
        'retry_attempts' => env('QZ_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('QZ_RETRY_DELAY', 1000),
        'timeout' => env('QZ_TIMEOUT', 30000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Printer Settings
    |--------------------------------------------------------------------------
    */
    'printers' => [
        'auto_discovery' => env('QZ_AUTO_DISCOVERY', true),
        'discovery_interval' => env('QZ_DISCOVERY_INTERVAL', 30000),
        'default_printer' => env('QZ_DEFAULT_PRINTER', ''),
        'fallback_strategy' => env('QZ_FALLBACK_STRATEGY', 'browser-print'),
        'persist_selection' => env('QZ_PERSIST_SELECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Print Settings
    |--------------------------------------------------------------------------
    */
    'print' => [
        'default_paper_size' => env('QZ_DEFAULT_PAPER_SIZE', 'A4'),
        'default_orientation' => env('QZ_DEFAULT_ORIENTATION', 'portrait'),
        'default_copies' => env('QZ_DEFAULT_COPIES', 1),
        'queue_enabled' => env('QZ_QUEUE_ENABLED', true),
        'max_queue_size' => env('QZ_MAX_QUEUE_SIZE', 100),
        'job_timeout' => env('QZ_JOB_TIMEOUT', 60000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('QZ_LOGGING_ENABLED', true),
        'level' => env('QZ_LOG_LEVEL', 'info'),
        'log_print_jobs' => env('LOG_QZ_PRINT_JOBS', true),
        'log_errors' => env('LOG_QZ_ERRORS', true),
        'max_log_days' => env('QZ_MAX_LOG_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'dashboard_route' => env('QZ_DASHBOARD_ROUTE', '/qz-tray/dashboard'),
        'enable_diagnostics' => env('QZ_ENABLE_DIAGNOSTICS', true),
        'enable_test_print' => env('QZ_ENABLE_TEST_PRINT', true),
        'theme' => env('QZ_THEME', 'default'), // default, dark, compact
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Settings
    |--------------------------------------------------------------------------
    */
    'tenancy' => [
        'enabled' => env('QZ_TENANCY_ENABLED', false),
        'tenant_column' => env('QZ_TENANT_COLUMN', 'tenant_id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    */
    'advanced' => [
        'enable_debug' => env('QZ_DEBUG_MODE', false),
        'enable_metrics' => env('QZ_ENABLE_METRICS', true),
        'enable_health_check' => env('QZ_ENABLE_HEALTH_CHECK', true),
        'trusted_domains' => explode(',', env('QZ_TRUSTED_DOMAINS', 'localhost,127.0.0.1')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'web',
        'auth',
        'qz-tray.auth',
    ],
];
