<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload dependencies
require_once __DIR__ . '/../../vendor/autoload.php';

use Yourdudeken\Mpesa\Init as Mpesa;

// Initialize M-Pesa SDK with sandbox credentials
// In a real app, these should be in .env or a config file
$config = [
    'is_sandbox' => true,
    'auth' => [
        'consumer_key'    => 'YOUR_CONSUMER_KEY', // Replace with your key
        'consumer_secret' => 'YOUR_CONSUMER_SECRET', // Replace with your secret
    ],
    // Initiator details for Business APIs (B2C, B2B, Reversal, Account Balance, Transaction Status)
    'initiator' => [
        'name'     => 'testapi',
        'password' => 'Safaricom123!!',
    ],
    'stk' => [
        'short_code' => '174379',
        'passkey'    => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
        'callback'   => 'https://example.com/api/callback.php',
    ],
    'b2c' => [
        'short_code'  => '600000',
        'result_url'  => 'https://example.com/api/callback.php',
        'timeout_url' => 'https://example.com/api/callback.php',
    ],
    'b2b' => [
        'short_code'  => '600000',
        'result_url'  => 'https://example.com/api/callback.php',
        'timeout_url' => 'https://example.com/api/callback.php',
    ],
    'c2b' => [
        'short_code'       => '600000',
        'confirmation_url' => 'https://example.com/api/callback.php',
        'validation_url'   => 'https://example.com/api/callback.php',
    ],
    'reversal' => [
        'short_code'  => '600000',
        'result_url'  => 'https://example.com/api/callback.php',
        'timeout_url' => 'https://example.com/api/callback.php',
    ],
    'balance' => [
        'short_code'  => '600000',
        'result_url'  => 'https://example.com/api/callback.php',
        'timeout_url' => 'https://example.com/api/callback.php',
    ],
    'status' => [
        'short_code'  => '600000',
        'result_url'  => 'https://example.com/api/callback.php',
        'timeout_url' => 'https://example.com/api/callback.php',
    ],
    'b2pochi' => [
        'short_code' => '600000',
    ],
];

try {
    $mpesa = new Mpesa($config);
} catch (Exception $e) {
    die(json_encode(['error' => 'Failed to initialize M-Pesa: ' . $e->getMessage()]));
}
