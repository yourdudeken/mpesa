<?php
require "vendor/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

/**
 * M-Pesa Integration Quick Start
 * 
 * This example demonstrates the Identity-First architecture where configuration
 * and parameters are explicitly provided.
 */

// 1. Initialize with your merchant profile
$mpesa = new Mpesa([
    'is_sandbox'      => true,
    'consumer_key'    => 'your_key',
    'consumer_secret' => 'your_secret',
    'short_code'     => '174379',
    'passkey'        => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
    'callback'       => 'https://example.com/mpesa' // Optional global callback
]);

try {
    // 2. Initiate STK Push (Lipa na M-Pesa Online)
    // Metadata fields like 'reference' and 'description' are now optional
    $response = $mpesa->STKPush([
        'amount'       => 10,
        'phoneNumber'  => '2547XXXXXXXX',
        'callback_url' => 'https://example.com/callback/stk' // Override global callback
    ]);
    
    // Set headers and output JSON
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    // Standard error handling
    header('Content-Type: application/json', true, 400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

/**
 * Pro Tip:
 * You can also use snake_case for all parameters!
 * 
 * $mpesa->STKPush([
 *    'amount'           => 10,
 *    'phone_number'     => '2547XXXXXXXX',
 *    'account_reference'=> 'INV-001'
 * ]);
 */
