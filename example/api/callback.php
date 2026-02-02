<?php
header('Content-Type: application/json');

// Log the callback query/body for debugging
$logFile = __DIR__ . '/debug.log';
$callbackData = file_get_contents('php://input');

// Append to log
$logEntry = date('Y-m-d H:i:s') . " - Callback received:\n" . $callbackData . "\n\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Always acknowledge recipe to Safaricom
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
