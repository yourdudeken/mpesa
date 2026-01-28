<?php
/**
 * M-Pesa Callback Handler
 * Receives and processes M-Pesa payment callbacks
 */

header('Content-Type: application/json');

require "vendor/autoload.php";

// Get callback data
$input = file_get_contents('php://input');
$callback = json_decode($input, true);

// Log raw callback if not empty
if (!empty($input)) {
    logCallback('stk_callback', $input);
}

try {
    // 1. STK Push Callback Case
    if (isset($callback['Body']['stkCallback'])) {
        $stkCallback = $callback['Body']['stkCallback'];
        
        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;
        $resultDesc = $stkCallback['ResultDesc'] ?? '';

        if ($checkoutRequestId) {
            $updateData = [
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'status' => $resultCode == 0 ? 'completed' : 'failed'
            ];

            // Extract STK Metadata
            if ($resultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
                foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                    $name = $item['Name'] ?? '';
                    $value = $item['Value'] ?? null;
                    switch ($name) {
                        case 'Amount': $updateData['amount'] = $value; break;
                        case 'MpesaReceiptNumber': $updateData['mpesa_receipt_number'] = $value; break;
                        case 'TransactionDate': $updateData['transaction_date'] = formatMpesaDate($value); break;
                        case 'PhoneNumber': $updateData['phone_number'] = $value; break;
                    }
                }
            }

            $transaction = new Transaction();
            $transaction->updateFromCallback($checkoutRequestId, $updateData);
            error_log("STK Callback processed: $checkoutRequestId");
        }
    } 
    
    // 2. B2C / B2B / Reversal / Account Balance / Transaction Status Case
    elseif (isset($callback['Result'])) {
        $result = $callback['Result'];
        $conversationId = $result['ConversationID'] ?? null;
        $originatorConversationId = $result['OriginatorConversationID'] ?? null;
        $resultCode = $result['ResultCode'] ?? null;
        $resultDesc = $result['ResultDesc'] ?? '';
        
        // Find transaction by conversation IDs (usually stored during initiation)
        // Since we don't store them yet in this example, we log it
        error_log("Transaction Result Received: $resultDesc (Code: $resultCode)");
        
        logCallback('transaction_result', $input);
    }

    // 3. C2B Confirmation Case
    elseif (isset($callback['TransID']) && isset($callback['MSISDN'])) {
        $transId = $callback['TransID'];
        $phoneNumber = $callback['MSISDN'];
        $amount = $callback['TransAmount'];
        $reference = $callback['BillRefNumber'];

        // Save C2B transaction to database
        $transaction = new Transaction();
        $transaction->create([
            'mpesa_receipt_number' => $transId,
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'account_reference' => $reference,
            'transaction_desc' => 'C2B Payment',
            'status' => 'completed'
        ]);

        error_log("C2B Payment Received: $transId from $phoneNumber");
        logCallback('c2b_confirmation', $input);
    }
    
    else {
        error_log('Unknown callback structure received');
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
