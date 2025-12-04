# Reversal API

## Overview
The Reversal API enables businesses to reverse erroneous M-Pesa transactions. This is useful for refunding customers, correcting mistakes, or reversing payments for undelivered goods/services.

## Prerequisites
1. Install this package
2. Configure the Reversal section in `src/config/mpesa.php`
3. Have publicly accessible URLs for result and timeout callbacks
4. Have proper authorization to reverse transactions

## Configuration

```php
'reversal' => [
    'initiator_name' => 'YOUR_INITIATOR_NAME',
    'security_credential' => 'YOUR_SECURITY_CREDENTIAL',
    'default_command_id' => 'TransactionReversal',
    'short_code' => 'YOUR_SHORTCODE',
    'result_url' => 'https://yourdomain.com/api/mpesa/reversal/result',
    'timeout_url' => 'https://yourdomain.com/api/mpesa/reversal/timeout'
],
```

## Usage

### Basic Example

```php
<?php
require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->reversal([
        'transactionID' => 'LGR019G3J2',  // Transaction to reverse
        'amount' => 100,  // Amount to reverse
        'recieverIdentifierType' => 4,  // 4 for Paybill, 2 for Till
        'remarks' => 'Reversing erroneous payment',
        'occasion' => 'Customer refund'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    echo json_encode(json_decode($e->getMessage()));
}
```

### Parameters

**Required:**
- `transactionID` - The M-Pesa transaction ID to reverse
- `amount` - Amount to reverse (must match original transaction)
- `recieverIdentifierType` - `1` (MSISDN), `2` (Till), `4` (Paybill)
- `remarks` - Reason for reversal

**Optional:**
- `occasion` - Additional information
- `partyA` - Override default short code
- `resultURL` - Override default result URL
- `queueTimeOutURL` - Override default timeout URL

## Callback Handling

### Result Callback

```php
<?php
$response = file_get_contents('php://input');
$data = json_decode($response, true);

/*
Sample Response:
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "29115-34620561-1",
        "ConversationID": "AG_20191219_00004e48cf7e3533f581",
        "TransactionID": "NLJ41HAY6Q",
        "ResultParameters": {
            "ResultParameter": [
                {
                    "Key": "DebitAccountBalance",
                    "Value": "{Amount={BasicAmount=49000.00, MinimumAmount=49000.00, CurrencyCode=KES}}"
                },
                {
                    "Key": "Amount",
                    "Value": 100
                },
                {
                    "Key": "TransactionReason",
                    "Value": ""
                },
                {
                    "Key": "DebitPartyAffectedAccountBalance",
                    "Value": "Utility Account|KES|49000.00|49000.00|0.00|0.00"
                },
                {
                    "Key": "TransCompletedTime",
                    "Value": 20191219104508
                },
                {
                    "Key": "DebitPartyCharges",
                    "Value": "Fee For Reversal|KES|0.00"
                },
                {
                    "Key": "ReceiverPartyPublicName",
                    "Value": "254708374149 - John Doe"
                },
                {
                    "Key": "Currency",
                    "Value": "KES"
                }
            ]
        }
    }
}
*/

if ($data['Result']['ResultCode'] == 0) {
    // Reversal successful
    $transactionID = $data['Result']['TransactionID'];
    $originalTransactionID = $_SESSION['original_transaction_id'];
    
    // Update database
    updateReversalStatus($originalTransactionID, 'reversed', $transactionID);
    
    // Notify customer
    notifyCustomer("Your payment has been reversed successfully");
} else {
    // Reversal failed
    $errorMessage = $data['Result']['ResultDesc'];
    logError("Reversal failed: $errorMessage");
}
```

## Response Codes

| Code | Description |
|------|-------------|
| 0 | Success |
| 1 | Insufficient Funds |
| 2 | Less Than Minimum Transaction Value |
| 3 | More Than Maximum Transaction Value |
| 4 | Would Exceed Daily Transfer Limit |
| 6 | Unresolved Primary Party |
| 7 | Unresolved Receiver Party |
| 11 | Debit Account Invalid |
| 12 | Credit Account Invalid |
| 15 | Duplicate Detected |
| 17 | Internal Failure |
| 20 | Unresolved Initiator |
| 1032 | Request cancelled by user |
| 1037 | Timeout in completing transaction |

## Important Notes

1. **Time Limit** - Reversals must be done within a specific timeframe (usually same day)
2. **Amount Match** - Amount must exactly match the original transaction
3. **Authorization** - Requires proper authorization and security credentials
4. **One-time Only** - A transaction can only be reversed once
5. **Irreversible** - Once reversed, the reversal itself cannot be reversed

## Use Cases

1. **Customer Refunds** - Refund customers for returned goods
2. **Error Correction** - Reverse payments sent to wrong accounts
3. **Service Cancellation** - Refund for cancelled services
4. **Duplicate Payments** - Reverse duplicate transactions
5. **Dispute Resolution** - Resolve payment disputes

## Best Practices

1. **Verify First** - Use Transaction Status API to verify transaction before reversing
2. **Document Reasons** - Keep detailed records of why reversals were made
3. **Customer Communication** - Inform customers before reversing
4. **Time Awareness** - Reverse as soon as possible (same day preferred)
5. **Exact Amounts** - Ensure amount matches exactly
6. **Audit Trail** - Maintain comprehensive audit logs
7. **Authorization** - Implement proper approval workflows

## Example: Reversal Workflow

```php
<?php
class ReversalManager {
    private $mpesa;
    
    public function __construct() {
        $this->mpesa = new Mpesa();
    }
    
    public function reverseTransaction($transactionID, $amount, $reason) {
        // 1. Verify transaction exists
        $status = $this->checkTransactionStatus($transactionID);
        
        if ($status != 'Completed') {
            throw new Exception("Cannot reverse incomplete transaction");
        }
        
        // 2. Check if already reversed
        if ($this->isAlreadyReversed($transactionID)) {
            throw new Exception("Transaction already reversed");
        }
        
        // 3. Initiate reversal
        try {
            $response = $this->mpesa->reversal([
                'transactionID' => $transactionID,
                'amount' => $amount,
                'recieverIdentifierType' => 4,
                'remarks' => $reason,
                'occasion' => 'Reversal'
            ]);
            
            // 4. Log reversal request
            $this->logReversalRequest($transactionID, $amount, $reason);
            
            return $response;
        } catch(\Exception $e) {
            $this->logError($e->getMessage());
            throw $e;
        }
    }
    
    private function checkTransactionStatus($transactionID) {
        // Use Transaction Status API
        return 'Completed';
    }
    
    private function isAlreadyReversed($transactionID) {
        // Check database
        return false;
    }
    
    private function logReversalRequest($transactionID, $amount, $reason) {
        // Log to database
    }
    
    private function logError($error) {
        error_log("Reversal error: $error");
    }
}
```

## Additional Resources

- [Official Documentation](https://developer.safaricom.co.ke/reversal/apis/post/request)
- [Transaction Status API](TransactionStatus.md) - Verify before reversing
- [B2C API](B2C.md)
- [B2B API](B2B.md)
