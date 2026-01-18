<?php
/**
 * M-Pesa API Log Viewer
 * 
 * Returns contents of callback.log or clears it.
 */

header('Content-Type: application/json');

$logFile = __DIR__ . '/../callback.log';

// Handle Clear Request
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    file_put_contents($logFile, '[]');
    echo json_encode(['success' => true]);
    exit;
}

// Read Logs
if (file_exists($logFile)) {
    echo file_get_contents($logFile);
} else {
    echo json_encode([]);
}
