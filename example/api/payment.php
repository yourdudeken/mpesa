<?php
/**
 * M-Pesa Payment API Handler
 * Handles all M-Pesa transaction types
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require "vendor/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

// Load explicit configuration from the local config file
$configPath = __DIR__ . '/../config/mpesa.php';
$config = is_file($configPath) ? require $configPath : [];

try {
    $input = file_get_contents('php://input');
    $request = json_decode($input, true);

    if (!$request) {
        throw new Exception('Invalid JSON request');
    }

    $action = $request['action'] ?? '';

    switch ($action) {
        // STK Push
        case 'stk_push':
            handleSTKPush($request, $config);
            break;

        case 'stk_status':
            handleSTKStatus($request, $config);
            break;

        // B2C
        case 'b2c_payment':
            handleB2C($request, $config);
            break;

        // B2B
        case 'b2b_payment':
            handleB2B($request, $config);
            break;

        // B2Pochi
        case 'b2pochi_payment':
            handleB2Pochi($request, $config);
            break;

        // C2B
        case 'c2b_register':
            handleC2BRegister($request, $config);
            break;

        case 'c2b_simulate':
            handleC2BSimulate($request, $config);
            break;

        // Account Balance
        case 'account_balance':
            handleAccountBalance($request, $config);
            break;

        // Transaction Status
        case 'transaction_status':
            handleTransactionStatus($request, $config);
            break;

        // Reversal
        case 'reversal':
            handleReversal($request, $config);
            break;

        // Data queries
        case 'get_transactions':
            handleGetTransactions($request);
            break;

        case 'get_stats':
            handleGetStats();
            break;

        default:
            throw new Exception('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * STK Push (Lipa na M-Pesa Online)
 */
function handleSTKPush($request, $config) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['phone_number', 'amount', 'account_reference']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa($config);
    
    $params = [
        'amount'            => (float) $data['amount'],
        'phone'             => $phoneNumber,
        'reference'         => $data['account_reference'],
        'description'       => $data['transaction_desc'] ?? 'Payment',
        'callback_url'      => $data['callback_url'] ?? null
    ];
    
    $response = $mpesa->STKPush($params);

    // Try to save to database, but don't fail if database is unavailable
    $transactionId = null;
    try {
        $transaction = new Transaction();
        $transactionId = $transaction->create([
            'checkout_request_id' => $response->CheckoutRequestID ?? null,
            'merchant_request_id' => $response->MerchantRequestID ?? null,
            'phone_number' => $phoneNumber,
            'amount' => $data['amount'],
            'account_reference' => $data['account_reference'],
            'transaction_desc' => $data['transaction_desc'] ?? 'Payment',
            'status' => 'pending'
        ]);
    } catch (Exception $e) {
        // Database not available, continue without saving
        error_log('Failed to save transaction to database: ' . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => 'STK Push sent successfully',
        'data' => [
            'transaction_id' => $transactionId,
            'checkout_request_id' => $response->CheckoutRequestID ?? null,
            'merchant_request_id' => $response->MerchantRequestID ?? null,
            'response_code' => $response->ResponseCode ?? null,
            'response_description' => $response->ResponseDescription ?? $response->CustomerMessage ?? 'Request sent',
            'customer_message' => $response->CustomerMessage ?? 'Please check your phone to complete payment'
        ]
    ]);
}

/**
 * STK Status Query
 */
function handleSTKStatus($request, $config) {
    $data = $request['data'] ?? [];
    $checkoutRequestId = $data['checkout_request_id'] ?? '';
    
    if (empty($checkoutRequestId)) {
        throw new Exception('Checkout Request ID is required');
    }

    $mpesa = new Mpesa($config);
    $response = $mpesa->STKStatus([
        'checkout_request_id' => $checkoutRequestId
    ]);

    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
}

/**
 * B2C Payment
 */
function handleB2C($request, $config) {
    $data = $request['data'] ?? [];
    validateRequired($data, ['amount', 'phone_number', 'remarks']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa($config);
    
    $params = [
        'amount'      => (float) $data['amount'],
        'party_b'      => $phoneNumber,
        'remarks'     => $data['remarks'],
        'occasion'    => $data['occasion'] ?? null,
        'command_id'  => $data['command_id'] ?? null,
        'result_url'  => $data['result_url'] ?? null,
        'timeout_url' => $data['timeout_url'] ?? null
    ];
    
    $response = $mpesa->B2C($params);

    echo json_encode([
        'success' => true,
        'message' => 'B2C payment initiated successfully',
        'data' => $response
    ]);
}

/**
 * B2B Payment
 */
function handleB2B($request, $config) {
    $data = $request['data'] ?? [];
    validateRequired($data, ['amount', 'party_b', 'account_reference', 'remarks']);
    
    $mpesa = new Mpesa($config);
    
    $params = [
        'amount'            => (float) $data['amount'],
        'party_b'            => $data['party_b'],
        'account_reference' => $data['account_reference'],
        'remarks'           => $data['remarks'],
        'command_id'        => $data['command_id'] ?? null,
        'result_url'        => $data['result_url'] ?? null,
        'timeout_url'       => $data['timeout_url'] ?? null
    ];
    
    $response = $mpesa->B2B($params);

    echo json_encode([
        'success' => true,
        'message' => 'B2B payment initiated successfully',
        'data' => $response
    ]);
}

/**
 * B2Pochi Payment
 */
function handleB2Pochi($request, $config) {
    $data = $request['data'] ?? [];
    validateRequired($data, ['amount', 'phone_number', 'remarks']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa($config);
    
    $params = [
        'amount'      => (float) $data['amount'],
        'party_b'      => $phoneNumber,
        'remarks'     => $data['remarks'],
        'result_url'  => $data['result_url'] ?? null,
        'timeout_url' => $data['timeout_url'] ?? null
    ];
    
    $response = $mpesa->B2Pochi($params);

    echo json_encode([
        'success' => true,
        'message' => 'B2Pochi payment initiated successfully',
        'data' => $response
    ]);
}

/**
 * C2B Register URLs
 */
function handleC2BRegister($request, $config) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['validation_url', 'confirmation_url']);
    
    $mpesa = new Mpesa($config);
    
    $params = [
        'validation_url'   => $data['validation_url'],
        'confirmation_url' => $data['confirmation_url'],
        'response_type'    => $data['response_type'] ?? null
    ];

    $response = $mpesa->C2BRegister($params);

    echo json_encode([
        'success' => true,
        'message' => 'C2B URLs registered successfully',
        'data' => $response
    ]);
}

/**
 * C2B Simulate Payment
 */
function handleC2BSimulate($request, $config) {
    $data = $request['data'] ?? [];
    validateRequired($data, ['amount', 'phone_number']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa($config);
    
    $response = $mpesa->C2BSimulate([
        'amount'          => (float) $data['amount'],
        'msisdn'          => $phoneNumber,
        'bill_ref_number' => $data['bill_ref_number'] ?? '',
        'command_id'      => $data['command_id'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'C2B payment simulated successfully',
        'data' => $response
    ]);
}

/**
 * Account Balance Query
 */
function handleAccountBalance($request, $config) {
    $data = $request['data'] ?? [];
    $mpesa = new Mpesa($config);
    
    $params = [
        'remarks'     => $data['remarks'] ?? 'Balance query',
        'result_url'  => $data['result_url'] ?? null,
        'timeout_url' => $data['timeout_url'] ?? null
    ];
    
    $response = $mpesa->accountBalance($params);

    echo json_encode([
        'success' => true,
        'message' => 'Balance query initiated successfully',
        'data' => $response
    ]);
}

/**
 * Transaction Status Query
 */
function handleTransactionStatus($request, $config) {
    $data = $request['data'] ?? [];
    validateRequired($data, ['transaction_id']);
    
    $mpesa = new Mpesa($config);
    
    $params = [
        'transaction_id' => $data['transaction_id'],
        'remarks'        => $data['remarks'] ?? 'Status check',
        'result_url'     => $data['result_url'] ?? null,
        'timeout_url'    => $data['timeout_url'] ?? null
    ];
    
    $response = $mpesa->transactionStatus($params);

    echo json_encode([
        'success' => true,
        'message' => 'Transaction status query initiated',
        'data' => $response
    ]);
}

/**
 * Transaction Reversal
 */
function handleReversal($request, $config) {
    $data = $request['data'] ?? [];
    validateRequired($data, ['transaction_id', 'amount', 'receiver_party', 'remarks']);
    
    $mpesa = new Mpesa($config);
    
    $params = [
        'transaction_id' => $data['transaction_id'],
        'amount'         => (float) $data['amount'],
        'receiver_party' => $data['receiver_party'],
        'remarks'        => $data['remarks'],
        'result_url'     => $data['result_url'] ?? null,
        'timeout_url'    => $data['timeout_url'] ?? null
    ];
    
    $response = $mpesa->reversal($params);

    echo json_encode([
        'success' => true,
        'message' => 'Reversal initiated successfully',
        'data' => $response
    ]);
}

/**
 * Get Transactions
 */
function handleGetTransactions($request) {
    try {
        $transaction = new Transaction();
        $data = $request['data'] ?? [];
        
        $status = $data['status'] ?? null;
        $phoneNumber = $data['phone_number'] ?? null;
        $limit = $data['limit'] ?? 50;

        if ($phoneNumber) {
            $transactions = $transaction->getByPhone($phoneNumber, $limit);
        } elseif ($status) {
            $transactions = $transaction->getByStatus($status, $limit);
        } else {
            $transactions = $transaction->getAll($limit);
        }

        echo json_encode([
            'success' => true,
            'data' => $transactions
        ]);
    } catch (Exception $e) {
        // Database not available, return empty data
        echo json_encode([
            'success' => true,
            'data' => [],
            'warning' => 'Database not available: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get Statistics
 */
function handleGetStats() {
    try {
        $transaction = new Transaction();
        $stats = $transaction->getStats();

        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    } catch (Exception $e) {
        // Database not available, return empty stats
        echo json_encode([
            'success' => true,
            'data' => [
                'successful' => 0,
                'pending' => 0,
                'failed' => 0,
                'total_amount' => 0
            ],
            'warning' => 'Database not available: ' . $e->getMessage()
        ]);
    }
}

/**
 * Validate required fields
 */
function validateRequired($data, $required) {
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

/**
 * Format phone number to M-Pesa format
 */
function formatPhoneNumber($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    
    if (strlen($phone) === 9 && $phone[0] === '7') {
        return '254' . $phone;
    }
    
    if (strlen($phone) === 10 && substr($phone, 0, 2) === '07') {
        return '254' . substr($phone, 1);
    }
    
    if (strlen($phone) === 12 && substr($phone, 0, 3) === '254') {
        return $phone;
    }
    
    return $phone;
}
