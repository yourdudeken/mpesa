<?php
/**
 * M-Pesa API Testing Interface - Backend Handler
 * 
 * This file handles all API requests from the frontend and routes them
 * to the appropriate M-Pesa SDK methods.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed',
        'message' => 'Only POST requests are accepted'
    ]);
    exit();
}

// Load M-Pesa SDK
require "vendor/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

/**
 * Main request handler
 */
try {
    // Get request body
    $input = file_get_contents('php://input');
    $request = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON in request body');
    }
    
    // Validate request structure
    if (!isset($request['endpoint']) || !isset($request['data'])) {
        throw new Exception('Missing required fields: endpoint and data');
    }
    
    $endpoint = $request['endpoint'];
    $data = $request['data'];
    $isSandbox = $request['isSandbox'] ?? true;
    
    // Initialize M-Pesa with environment setting
    $config = [];
    if (!$isSandbox) {
        $config['is_sandbox'] = false;
        $config['apiUrl'] = 'https://api.safaricom.co.ke/';
    }
    
    $mpesa = new Mpesa($config);
    
    // Route to appropriate endpoint
    $response = routeRequest($mpesa, $endpoint, $data);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'endpoint' => $endpoint,
        'environment' => $isSandbox ? 'sandbox' : 'production',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $response
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'endpoint' => $request['endpoint'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Route request to appropriate M-Pesa method
 * 
 * @param Mpesa $mpesa M-Pesa instance
 * @param string $endpoint Endpoint name
 * @param array $data Request data
 * @return mixed API response
 */
function routeRequest($mpesa, $endpoint, $data)
{
    switch ($endpoint) {
        case 'STKPush':
            return handleSTKPush($mpesa, $data);
            
        case 'STKStatus':
            return handleSTKStatus($mpesa, $data);
            
        case 'C2BRegister':
            return handleC2BRegister($mpesa, $data);
            
        case 'C2BSimulate':
            return handleC2BSimulate($mpesa, $data);
            
        case 'B2C':
            return handleB2C($mpesa, $data);
            
        case 'B2B':
            return handleB2B($mpesa, $data);
            
        case 'B2Pochi':
            return handleB2Pochi($mpesa, $data);
            
        case 'accountBalance':
            return handleAccountBalance($mpesa, $data);
            
        case 'transactionStatus':
            return handleTransactionStatus($mpesa, $data);
            
        case 'reversal':
            return handleReversal($mpesa, $data);
            
        default:
            throw new Exception("Unknown endpoint: $endpoint");
    }
}

/**
 * Handle STK Push request
 */
function handleSTKPush($mpesa, $data)
{
    validateRequired($data, ['amount', 'phone_number', 'account_reference']);
    
    $params = [
        'amount'            => (float) $data['amount'],
        'phone'             => $data['phone_number'],
        'reference'         => $data['account_reference'],
        'description'       => $data['transaction_desc'] ?? 'Payment',
        'callback_url'      => $data['callback_url'] ?? null
    ];
    
    return $mpesa->STKPush($params);
}

/**
 * Handle STK Status Query
 */
function handleSTKStatus($mpesa, $data)
{
    validateRequired($data, ['checkout_request_id']);
    
    return $mpesa->STKStatus([
        'checkout_request_id' => $data['checkout_request_id']
    ]);
}

/**
 * Handle C2B Register
 */
function handleC2BRegister($mpesa, $data)
{
    validateRequired($data, ['validation_url', 'confirmation_url']);
    
    return $mpesa->C2BRegister([
        'validation_url' => $data['validation_url'],
        'confirmation_url' => $data['confirmation_url'],
        'response_type' => $data['response_type'] ?? 'Completed'
    ]);
}

/**
 * Handle C2B Simulate
 */
function handleC2BSimulate($mpesa, $data)
{
    validateRequired($data, ['amount', 'msisdn', 'billRefNumber']);
    
    return $mpesa->C2BSimulate([
        'amount' => (float) $data['amount'],
        'msisdn' => $data['msisdn'],
        'billRefNumber' => $data['billRefNumber']
    ]);
}

/**
 * Handle B2C Payment
 */
function handleB2C($mpesa, $data)
{
    validateRequired($data, ['amount', 'phone_number', 'remarks']);
    
    return $mpesa->B2C([
        'amount'      => (float) $data['amount'],
        'party_b'      => $data['phone_number'],
        'remarks'     => $data['remarks'],
        'occasion'    => $data['occasion'] ?? '',
        'command_id'  => $data['command_id'] ?? 'BusinessPayment',
        'result_url'  => $data['result_url'] ?? null,
        'timeout_url' => $data['timeout_url'] ?? null
    ]);
}

/**
 * Handle B2B Transfer
 */
function handleB2B($mpesa, $data)
{
    validateRequired($data, ['amount', 'party_b', 'account_reference', 'remarks']);
    
    return $mpesa->B2B([
        'amount'            => (float) $data['amount'],
        'party_b'            => $data['party_b'],
        'account_reference' => $data['account_reference'],
        'remarks'           => $data['remarks'],
        'command_id'        => $data['command_id'] ?? 'BusinessPayBill',
        'result_url'        => $data['result_url'] ?? null,
        'timeout_url'       => $data['timeout_url'] ?? null
    ]);
}

/**
 * Handle B2Pochi Payment
 */
function handleB2Pochi($mpesa, $data)
{
    validateRequired($data, ['amount', 'phone_number', 'remarks']);
    
    return $mpesa->B2Pochi([
        'amount'      => (float) $data['amount'],
        'phone'       => $data['phone_number'],
        'remarks'     => $data['remarks'],
        'result_url'  => $data['result_url'] ?? null,
        'timeout_url' => $data['timeout_url'] ?? null
    ]);
}

/**
 * Handle Account Balance Query
 */
function handleAccountBalance($mpesa, $data)
{
    return $mpesa->accountBalance([
        'remarks'      => $data['remarks'] ?? 'Balance query',
        'result_url'   => $data['result_url'] ?? null,
        'timeout_url'  => $data['timeout_url'] ?? null
    ]);
}

/**
 * Handle Transaction Status Query
 */
function handleTransactionStatus($mpesa, $data)
{
    validateRequired($data, ['transaction_id']);
    
    return $mpesa->transactionStatus([
        'transaction_id' => $data['transaction_id'],
        'remarks'        => $data['remarks'] ?? 'Status check',
        'result_url'     => $data['result_url'] ?? null,
        'timeout_url'    => $data['timeout_url'] ?? null
    ]);
}

/**
 * Handle Transaction Reversal
 */
function handleReversal($mpesa, $data)
{
    validateRequired($data, ['transaction_id', 'amount', 'receiver_party', 'remarks']);
    
    return $mpesa->reversal([
        'transaction_id'  => $data['transaction_id'],
        'amount'          => (float) $data['amount'],
        'receiver_party'  => $data['receiver_party'],
        'remarks'         => $data['remarks'],
        'result_url'      => $data['result_url'] ?? null,
        'timeout_url'     => $data['timeout_url'] ?? null
    ]);
}

/**
 * Validate required fields
 * 
 * @param array $data Data to validate
 * @param array $required Required field names
 * @throws Exception if any required field is missing
 */
function validateRequired($data, $required)
{
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missing));
    }
}
