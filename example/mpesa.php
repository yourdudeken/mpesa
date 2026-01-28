<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

// Load explicit configuration from the local config file
$configPath = __DIR__ . '/config/mpesa.php';
$config = is_file($configPath) ? require $configPath : [];

$mpesa = new Mpesa($config);
try {
    // 1. Initiate a B2C Payment
    $response = $mpesa->B2C([
        'amount'      => 10,
        'party_b'      => '2547XXXXXXXX',
        'remarks'     => 'Salary Payment',
        'result_url'  => 'https://example.com/callback/result',
        'timeout_url' => 'https://example.com/callback/timeout',
    ]);

    // 2. Initiate an STK Push (Lipa na M-Pesa Online)
    /*
    $response = $mpesa->STKPush([
        'amount'            => 1,
        'phone'             => '2547XXXXXXXX',
        'reference'         => 'INV-123',
        'description'       => 'Payment for Order #123',
        'callback_url'      => 'https://example.com/callback/stk',
    ]);
    */

    // 3. Register C2B URLs
    /*
    $response = $mpesa->C2BRegister([
        'validation_url'   => 'https://example.com/c2b/validation',
        'confirmation_url' => 'https://example.com/c2b/confirmation',
    ]);
    */

    // 4. Simulate C2B Payment
    /*
    $response = $mpesa->C2BSimulate([
        'amount'  => 100,
        'msisdn'  => '2547XXXXXXXX',
        'bill_ref_number' => 'ACC123'
    ]);
    */
}catch(\Exception $e){
    $response = json_decode($e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
