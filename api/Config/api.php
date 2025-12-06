<?php

return [
    /**
     * API Keys Configuration
     * Store your API keys here or load from environment variables
     */
    'api_keys' => [
        // Example: 'your-api-key-here' => ['name' => 'Client Name', 'active' => true],
        // Load from environment in production
        getenv('MPESA_API_KEY') ?: 'demo-api-key-12345' => [
            'name' => 'Demo Client',
            'active' => true,
            'rate_limit' => 100, // requests per minute
        ],
    ],

    /**
     * CORS Configuration
     */
    'cors' => [
        'allowed_origins' => [
            '*', // Allow all origins (change in production)
            // 'https://yourdomain.com',
            // 'https://app.yourdomain.com',
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key', 'X-Requested-With'],
        'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining'],
        'max_age' => 86400, // 24 hours
        'supports_credentials' => false,
    ],

    /**
     * Rate Limiting Configuration
     */
    'rate_limit' => [
        'enabled' => true,
        'default_limit' => 60, // requests per minute
        'storage' => __DIR__ . '/../../cache/rate_limit/',
    ],

    /**
     * API Response Configuration
     */
    'response' => [
        'pretty_print' => true,
        'include_timestamp' => true,
        'include_request_id' => true,
    ],

    /**
     * Logging Configuration
     */
    'logging' => [
        'enabled' => true,
        'log_file' => __DIR__ . '/../../logs/api.log',
        'log_level' => 'info', // debug, info, warning, error
    ],
];
