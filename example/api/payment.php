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

// Set working directory to example folder so config is loaded correctly
chdir(__DIR__ . '/..');

require_once __DIR__ . '/../../src/autoload.php';
require_once __DIR__ . '/../models/Transaction.php';

use Yourdudeken\Mpesa\Init as Mpesa;

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
            handleSTKPush($request);
            break;

        case 'stk_status':
            handleSTKStatus($request);
            break;

        // B2C
        case 'b2c_payment':
            handleB2C($request);
            break;

        // B2B
        case 'b2b_payment':
            handleB2B($request);
            break;

        // B2Pochi
        case 'b2pochi_payment':
            handleB2Pochi($request);
            break;

        // C2B
        case 'c2b_register':
            handleC2BRegister($request);
            break;

        case 'c2b_simulate':
            handleC2BSimulate($request);
            break;

        // Account Balance
        case 'account_balance':
            handleAccountBalance($request);
            break;

        // Transaction Status
        case 'transaction_status':
            handleTransactionStatus($request);
            break;

        // Reversal
        case 'reversal':
            handleReversal($request);
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
function handleSTKPush($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['phone_number', 'amount', 'account_reference']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa();
    
    $params = [
        'amount' => (float) $data['amount'],
        'phoneNumber' => $phoneNumber,
        'accountReference' => $data['account_reference'],
        'transactionDesc' => $data['transaction_desc'] ?? 'Payment'
    ];

    if (!empty($data['callback_url'])) {
        $params['CallBackURL'] = $data['callback_url'];
    }
    
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
function handleSTKStatus($request) {
    $data = $request['data'] ?? [];
    $checkoutRequestId = $data['checkout_request_id'] ?? '';
    
    if (empty($checkoutRequestId)) {
        throw new Exception('Checkout Request ID is required');
    }

    $mpesa = new Mpesa();
    $response = $mpesa->STKStatus([
        'checkoutRequestID' => $checkoutRequestId
    ]);

    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
}

/**
 * B2C Payment
 */
function handleB2C($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['amount', 'phone_number', 'remarks']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa();
    
    $params = [
        'amount' => (float) $data['amount'],
        'partyB' => $phoneNumber,
        'remarks' => $data['remarks'],
        'occasion' => $data['occasion'] ?? '',
        'commandID' => $data['command_id'] ?? 'BusinessPayment'
    ];

    if (!empty($data['result_url'])) {
        $params['resultURL'] = $data['result_url'];
    }
    if (!empty($data['timeout_url'])) {
        $params['queueTimeOutURL'] = $data['timeout_url'];
    }
    
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
function handleB2B($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['amount', 'party_b', 'account_reference', 'remarks']);
    
    $mpesa = new Mpesa();
    
    $params = [
        'amount' => (float) $data['amount'],
        'partyB' => $data['party_b'],
        'accountReference' => $data['account_reference'],
        'remarks' => $data['remarks'],
        'commandID' => $data['command_id'] ?? 'BusinessPayBill'
    ];

    if (!empty($data['result_url'])) {
        $params['resultURL'] = $data['result_url'];
    }
    if (!empty($data['timeout_url'])) {
        $params['queueTimeOutURL'] = $data['timeout_url'];
    }
    
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
function handleB2Pochi($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['amount', 'phone_number', 'remarks']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa();
    
    $params = [
        'amount' => (float) $data['amount'],
        'partyB' => $phoneNumber,
        'remarks' => $data['remarks']
    ];

    if (!empty($data['result_url'])) {
        $params['resultURL'] = $data['result_url'];
    }
    if (!empty($data['timeout_url'])) {
        $params['queueTimeOutURL'] = $data['timeout_url'];
    }
    
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
function handleC2BRegister($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['validation_url', 'confirmation_url']);
    
    $mpesa = new Mpesa();
    
    $params = [
        'ValidationURL' => $data['validation_url'],
        'ConfirmationURL' => $data['confirmation_url']
    ];

    if (!empty($data['response_type'])) {
        $params['ResponseType'] = $data['response_type'];
    }

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
function handleC2BSimulate($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['amount', 'phone_number', 'bill_ref_number']);
    
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    $mpesa = new Mpesa();
    
    $response = $mpesa->C2BSimulate([
        'Amount' => (float) $data['amount'],
        'Msisdn' => $phoneNumber,
        'BillRefNumber' => $data['bill_ref_number'],
        'CommandID' => $data['command_id'] ?? null
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
function handleAccountBalance($request) {
    $data = $request['data'] ?? [];
    
    $mpesa = new Mpesa();
    
    $params = [
        'remarks' => $data['remarks'] ?? 'Balance query'
    ];

    if (!empty($data['result_url'])) {
        $params['resultURL'] = $data['result_url'];
    }
    if (!empty($data['timeout_url'])) {
        $params['queueTimeOutURL'] = $data['timeout_url'];
    }
    
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
function handleTransactionStatus($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['transaction_id']);
    
    $mpesa = new Mpesa();
    
    $params = [
        'transactionID' => $data['transaction_id'],
        'remarks' => $data['remarks'] ?? 'Status check'
    ];

    if (!empty($data['result_url'])) {
        $params['resultURL'] = $data['result_url'];
    }
    if (!empty($data['timeout_url'])) {
        $params['queueTimeOutURL'] = $data['timeout_url'];
    }
    
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
function handleReversal($request) {
    $data = $request['data'] ?? [];
    
    validateRequired($data, ['transaction_id', 'amount', 'receiver_party', 'remarks']);
    
    $mpesa = new Mpesa();
    
    $params = [
        'transactionID' => $data['transaction_id'],
        'amount' => (float) $data['amount'],
        'receiverParty' => $data['receiver_party'],
        'remarks' => $data['remarks']
    ];

    if (!empty($data['result_url'])) {
        $params['resultURL'] = $data['result_url'];
    }
    if (!empty($data['timeout_url'])) {
        $params['queueTimeOutURL'] = $data['timeout_url'];
    }
    
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
