<?php
/**
 * M-Pesa Callback Handler
 * Receives and processes M-Pesa payment callbacks
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../models/Transaction.php';

// Get callback data
$input = file_get_contents('php://input');
$callback = json_decode($input, true);

// Log raw callback
logCallback('stk_callback', $input);

try {
    // Extract callback data
    if (isset($callback['Body']['stkCallback'])) {
        $stkCallback = $callback['Body']['stkCallback'];
        
        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;
        $resultDesc = $stkCallback['ResultDesc'] ?? '';

        if (!$checkoutRequestId) {
            throw new Exception('Missing CheckoutRequestID in callback');
        }

        // Prepare update data
        $updateData = [
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'status' => $resultCode == 0 ? 'completed' : 'failed'
        ];

        // Extract metadata if payment was successful
        if ($resultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
            $metadata = $stkCallback['CallbackMetadata']['Item'];
            
            foreach ($metadata as $item) {
                $name = $item['Name'] ?? '';
                $value = $item['Value'] ?? null;
                
                switch ($name) {
                    case 'Amount':
                        $updateData['amount'] = $value;
                        break;
                    case 'MpesaReceiptNumber':
                        $updateData['mpesa_receipt_number'] = $value;
                        break;
                    case 'TransactionDate':
                        $updateData['transaction_date'] = formatMpesaDate($value);
                        break;
                    case 'PhoneNumber':
                        $updateData['phone_number'] = $value;
                        break;
                }
            }
        }

        // Update transaction in database
        $transaction = new Transaction();
        $transaction->updateFromCallback($checkoutRequestId, $updateData);

        // Log successful processing
        error_log("Callback processed successfully for: $checkoutRequestId");

    } else {
        error_log('Invalid callback structure: ' . $input);
    }

} catch (Exception $e) {
    error_log('Callback processing error: ' . $e->getMessage());
}

// Always respond with success to Safaricom
echo json_encode([
    'ResultCode' => 0,
    'ResultDesc' => 'Accepted'
]);

/**
 * Log callback to file
 */
function logCallback($type, $data) {
    $logFile = __DIR__ . '/../callback.log';
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'data' => $data
    ];

    // Read existing logs
    $logs = [];
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $logs = json_decode($content, true) ?? [];
    }

    // Prepend new log
    array_unshift($logs, $logEntry);

    // Keep only last 100 logs
    $logs = array_slice($logs, 0, 100);

    // Save logs
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}

/**
 * Format M-Pesa date to SQL datetime
 */
function formatMpesaDate($mpesaDate) {
    // M-Pesa format: 20191219102030 (YYYYMMDDHHmmss)
    if (strlen($mpesaDate) === 14) {
        return substr($mpesaDate, 0, 4) . '-' . 
               substr($mpesaDate, 4, 2) . '-' . 
               substr($mpesaDate, 6, 2) . ' ' . 
               substr($mpesaDate, 8, 2) . ':' . 
               substr($mpesaDate, 10, 2) . ':' . 
               substr($mpesaDate, 12, 2);
    }
    return date('Y-m-d H:i:s');
}
