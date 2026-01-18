<?php
/**
 * M-Pesa API Callback Handler
 * 
 * receives callbacks from M-Pesa and logs them to a file.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

// Get request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Get headers
$headers = getallheaders();

// Log entry
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'headers' => $headers,
    'body' => $data ? $data : $input // Store raw input if decoding fails
];

// Log file path
$logFile = __DIR__ . '/../callback.log';

// Read existing logs
$logs = [];
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $logs = json_decode($content, true) ?? [];
}

// Prepend new log (newest first)
array_unshift($logs, $logEntry);

// Keep only last 50 logs
$logs = array_slice($logs, 0, 50);

// Save logs
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));

// Respond to M-Pesa
http_response_code(200);
echo json_encode([
    'ResultCode' => 0,
    'ResultDesc' => 'Accepted'
]);
