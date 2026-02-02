<?php
header('Content-Type: application/json');
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['transaction_id'])) {
    echo json_encode(['error' => 'Transaction ID is required']);
    exit;
}

try {
    $response = $mpesa->status->submit([
        'transaction_id' => $input['transaction_id'],
        'remarks'        => 'Status Query Demo'
    ]);

    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
