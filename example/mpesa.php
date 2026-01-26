<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

// Load explicit configuration from the local config file
$configPath = __DIR__ . '/config/mpesa.php';
$config = is_file($configPath) ? require $configPath : [];

$mpesa = new Mpesa($config);
try {
    $response = $mpesa->B2C([
        'amount' => 10,
        'partyB' => '2547XXXXXXXX'
    ]);



    // $mpesa->STKStatus([]);
    
    // $mpesa->C2BRegister([]);
    
    // $mpesa->STKPush([]);
    
    // $mpesa->C2BSimulate([]);
    
    // $mpesa->B2C([])
    
    // $mpesa->B2B([]);

    // $mpesa->B2Pochi([]);
    
    // $mpesa->accountBalance([])
    
    // $mpesa->reversal([]);
    
    // $mpesa->transactionStatus([]);
    
    // $mpesa->reversal([]);
}catch(\Exception $e){
    $response = json_decode($e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
