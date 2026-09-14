<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monitoring Master Switch
    |--------------------------------------------------------------------------
    |
    | When false, no monitoring data is written to the database.
    | Useful for local development or testing.
    |
    */
    'enabled' => env('MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When true, ALL requests are logged for performance tracking.
    | When false (recommended for local), only slow requests (>1s) are logged.
    |
    */
    'strict' => env('MONITORING_STRICT', false),

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    */
    'log_performance' => env('MONITORING_LOG_PERFORMANCE', true),
    'log_payments'    => env('MONITORING_LOG_PAYMENTS', true),
    'log_sms'         => env('MONITORING_LOG_SMS', true),

    /*
    |--------------------------------------------------------------------------
    | Slow Request Threshold (ms)
    |--------------------------------------------------------------------------
    |
    | Requests slower than this threshold are flagged as "slow" and always
    | logged regardless of strict mode.
    |
    */
    'slow_threshold_ms' => (int) env('MONITORING_SLOW_THRESHOLD_MS', 2000),

    /*
    |--------------------------------------------------------------------------
    | Very Slow Request Threshold (ms)
    |--------------------------------------------------------------------------
    |
    | Requests slower than this trigger a Log::warning for immediate attention.
    |
    */
    'very_slow_threshold_ms' => (int) env('MONITORING_VERY_SLOW_THRESHOLD_MS', 5000),

    /*
    |--------------------------------------------------------------------------
    | Retention Days (for cleanup command)
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'performance_logs' => (int) env('MONITORING_RETENTION_PERFORMANCE', 7),
        'system_errors'    => (int) env('MONITORING_RETENTION_ERRORS', 60),
        'payment_logs'     => (int) env('MONITORING_RETENTION_PAYMENTS', 90),
        'sms_logs'         => (int) env('MONITORING_RETENTION_SMS', 30),
        'request_logs'     => (int) env('MONITORING_RETENTION_REQUESTS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths (performance logging)
    |--------------------------------------------------------------------------
    |
    | Routes matching these patterns will never be logged for performance,
    | even in strict mode.
    |
    */
    'excluded_paths' => [
        'admin/*',
        'health',
        'up',
        'robots.txt',
        'sitemap*.xml',
        'favicon.ico',
        'storage/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds (for check-health command)
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'errors_per_hour'        => (int) env('MONITORING_ALERT_ERRORS_PER_HOUR', 10),
        'payment_failure_rate'   => (int) env('MONITORING_ALERT_PAYMENT_FAIL_RATE', 30),
        'sms_failure_rate'       => (int) env('MONITORING_ALERT_SMS_FAIL_RATE', 50),
        'slow_requests_per_hour' => (int) env('MONITORING_ALERT_SLOW_PER_HOUR', 10),
    ],

];
