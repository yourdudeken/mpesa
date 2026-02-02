<?php
header('Content-Type: application/json');
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['phone']) || !isset($input['amount'])) {
    echo json_encode(['error' => 'Phone and amount are required']);
    exit;
}

try {
    $response = $mpesa->stk->submit([
        'amount'           => (int) $input['amount'],
        'phone'            => $input['phone'],
        'reference'        => $input['reference'] ?? 'TEST-' . time(),
        'description'      => $input['description'] ?? 'Test Payment',
        'transaction_type' => $input['transaction_type'] ?? 'CustomerPayBillOnline',
        'callback_url'     => $input['callback_url'] ?? 'https://example.com/api/callback.php'
    ]);

    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
