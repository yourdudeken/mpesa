<?php
header('Content-Type: application/json');
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['short_code']) || !isset($input['amount'])) {
    echo json_encode(['error' => 'Receiver Short Code and amount are required']);
    exit;
}

try {
    $response = $mpesa->b2b->submit([
        'amount'     => (int) $input['amount'],
        'short_code' => $input['short_code'],
        'remarks'    => $input['remarks'] ?? 'B2B Test Payment',
        'account_ref' => $input['account_ref'] ?? 'B2B Demo'
    ]);

    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
