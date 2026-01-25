<?php
/**
 * M-Pesa Payment API Handler
 * Handles payment initiation and integrates with database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
        case 'initiate_payment':
            handlePaymentInitiation($request);
            break;

        case 'check_status':
            handleStatusCheck($request);
            break;

        case 'get_transactions':
            handleGetTransactions($request);
            break;

        case 'get_stats':
            handleGetStats();
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Handle payment initiation
 */
function handlePaymentInitiation($request) {
    $data = $request['data'] ?? [];
    
    // Validate required fields
    $required = ['phone_number', 'amount', 'account_reference'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Format phone number
    $phoneNumber = formatPhoneNumber($data['phone_number']);
    
    // Initialize M-Pesa
    $mpesa = new Mpesa();
    
    // Prepare callback URL
    $callbackUrl = getCallbackUrl();
    
    // Initiate STK Push
    $response = $mpesa->STKPush([
        'amount' => (float) $data['amount'],
        'phoneNumber' => $phoneNumber,
        'accountReference' => $data['account_reference'],
        'transactionDesc' => $data['transaction_desc'] ?? 'Payment',
        'CallBackURL' => $callbackUrl
    ]);

    // Save to database
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

    echo json_encode([
        'success' => true,
        'message' => 'Payment request sent successfully',
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
 * Handle status check
 */
function handleStatusCheck($request) {
    $checkoutRequestId = $request['checkout_request_id'] ?? '';
    
    if (empty($checkoutRequestId)) {
        throw new Exception('Checkout Request ID is required');
    }

    // Get from database
    $transaction = new Transaction();
    $record = $transaction->getByCheckoutRequestId($checkoutRequestId);

    if (!$record) {
        throw new Exception('Transaction not found');
    }

    // If still pending, query M-Pesa
    if ($record['status'] === 'pending') {
        try {
            $mpesa = new Mpesa();
            $response = $mpesa->STKStatus([
                'checkoutRequestID' => $checkoutRequestId
            ]);

            // Update status if available
            if (isset($response->ResultCode)) {
                $status = $response->ResultCode == 0 ? 'completed' : 'failed';
                $transaction->updateFromCallback($checkoutRequestId, [
                    'status' => $status,
                    'result_code' => $response->ResultCode,
                    'result_desc' => $response->ResultDesc ?? ''
                ]);
                
                // Refresh record
                $record = $transaction->getByCheckoutRequestId($checkoutRequestId);
            }
        } catch (Exception $e) {
            // Continue with database record if API call fails
            error_log('STK Status query failed: ' . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $record
    ]);
}

/**
 * Handle get transactions
 */
function handleGetTransactions($request) {
    $transaction = new Transaction();
    
    $status = $request['status'] ?? null;
    $phoneNumber = $request['phone_number'] ?? null;
    $limit = $request['limit'] ?? 50;

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
}

/**
 * Handle get statistics
 */
function handleGetStats() {
    $transaction = new Transaction();
    $stats = $transaction->getStats();

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
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

/**
 * Get callback URL
 */
function getCallbackUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    return $protocol . '://' . $host . '/mpesa/example/api/callback.php';
}
