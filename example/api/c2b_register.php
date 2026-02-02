<?php
header('Content-Type: application/json');
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $response = $mpesa->c2b->submit([
        'short_code'       => $input['short_code'] ?? null,
        'response_type'    => $input['response_type'] ?? 'Completed',
        'confirmation_url' => $input['confirmation_url'] ?? null,
        'validation_url'   => $input['validation_url'] ?? null,
    ]);

    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
