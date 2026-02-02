<?php
header('Content-Type: application/json');
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['phone']) || !isset($input['amount'])) {
    echo json_encode(['error' => 'Phone and amount are required']);
    exit;
}

try {
    $response = $mpesa->b2c->submit([
        'amount'  => (int) $input['amount'],
        'phone'   => $input['phone'],
        'remarks' => $input['remarks'] ?? 'B2C Test Payment',
        'occasion' => $input['occasion'] ?? 'B2C Demo'
    ]);

    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
