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
        'amount'       => (int) $input['amount'],
        'phone'        => $input['phone'],
        'reference'    => 'TEST-' . time(),
        'description'  => 'Test Payment',
        'callback_url' => 'https://example.com/api/callback.php' // In local dev, use ngrok url
    ]);

    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
