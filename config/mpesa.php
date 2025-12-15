<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Domain 
    |--------------------------------------------------------------------------
    |
    | This is the url where all the endpoints originates from. 
    */

    'apiUrl' => env('MPESA_ENV', 'sandbox') === 'production' 
        ? 'https://api.safaricom.co.ke/'
        : 'https://sandbox.safaricom.co.ke/',

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    |
    | This determines the state of the package, whether to use in sandbox mode or not.
    |
    */

    'is_sandbox' => env('MPESA_ENV', 'sandbox') !== 'production',

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | These are the credentials to be used to transact with the M-Pesa API
    */

    'apps' => [
        'default' => [
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Cache Location
    |--------------------------------------------------------------------------
    |
    | This will be the location on the disk where the caching will be done.
    |
    */

    'cache_location' => storage_path('framework/cache/mpesa'),

    /*
    |--------------------------------------------------------------------------
    | Callback Method
    |--------------------------------------------------------------------------
    |
    | This is the request method to be used on the Callback URL on communication
    | with your server.
    |
    */

    'callback_method' => 'POST',

    /*
    |--------------------------------------------------------------------------
    | Common Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are shared across all M-Pesa operations
    |
    */

    'shortcode' => env('MPESA_SHORTCODE'),
    'initiator_name' => env('MPESA_INITIATOR_NAME'),
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
    'passkey' => env('MPESA_PASSKEY'),

    /*
    |--------------------------------------------------------------------------
    | LipaNaMpesa API Online Config
    |--------------------------------------------------------------------------
    */
    'lnmo' => [
        'short_code' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'callback' => null, // Passed with request
        'default_transaction_type' => 'CustomerPayBillOnline'
    ],

    /*
    |--------------------------------------------------------------------------
    | C2B Config
    |--------------------------------------------------------------------------
    */

    'c2b' => [
        'confirmation_url' => null, // Passed with request
        'validation_url' => null, // Passed with request
        'responseType' => 'Completed',
        'short_code' => env('MPESA_SHORTCODE'),
        'test_phone_number' => null, // Passed with request
        'default_command_id' => 'CustomerPayBillOnline'
    ],

    /*
    |--------------------------------------------------------------------------
    | B2C Config
    |--------------------------------------------------------------------------
    */

    'b2c' => [
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'default_command_id' => 'BusinessPayment',
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'short_code' => env('MPESA_SHORTCODE'),
        'test_phone_number' => null, // Passed with request
        'result_url' => null, // Passed with request
        'timeout_url' => null // Passed with request
    ],

    /*
    |--------------------------------------------------------------------------
    | B2B API Config
    |--------------------------------------------------------------------------
    */

    'b2b' => [
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'default_command_id' => 'BusinessPayBill',
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'short_code' => env('MPESA_SHORTCODE'),
        'test_phone_number' => null, // Passed with request
        'result_url' => null, // Passed with request
        'timeout_url' => null // Passed with request
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Balance Config
    |--------------------------------------------------------------------------
    */

    'account_balance' => [
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'default_command_id' => 'AccountBalance',
        'short_code' => env('MPESA_SHORTCODE'),
        'result_url' => null, // Passed with request
        'timeout_url' => null // Passed with request
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Status API Config
    |--------------------------------------------------------------------------
    */

    'transaction_status' => [
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'default_command_id' => 'TransactionStatusQuery',
        'short_code' => env('MPESA_SHORTCODE'),
        'result_url' => null, // Passed with request
        'timeout_url' => null // Passed with request
    ],

    /*
    |--------------------------------------------------------------------------
    | Reversal API Config
    |--------------------------------------------------------------------------
    */

    'reversal' => [
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'default_command_id' => 'TransactionReversal',
        'short_code' => env('MPESA_SHORTCODE'),
        'result_url' => null, // Passed with request
        'timeout_url' => null // Passed with request
    ],

    /*
    |--------------------------------------------------------------------------
    | Certificate Path
    |--------------------------------------------------------------------------
    |
    | Path to the certificate file used for encryption
    | Automatically selects based on environment
    |
    */

    'certificate_path' => env('MPESA_ENV', 'sandbox') === 'production'
        ? base_path('config/ProductionCertificate.cer')
        : base_path('config/SandboxCertificate.cer'),
];
