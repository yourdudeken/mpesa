# Transaction Status API

## Overview
The Transaction Status API enables you to query the status of B2B, B2C, and C2B transactions. This is useful when a transaction callback is not received or when you need to verify transaction completion.

## Prerequisites
1. Install this package
2. Configure the Transaction Status section in `src/config/mpesa.php`
3. Have publicly accessible URLs for result and timeout callbacks

## Configuration

```php
'transaction_status' => [
    'initiator_name' => 'YOUR_INITIATOR_NAME',
    'security_credential' => 'YOUR_SECURITY_CREDENTIAL',
    'default_command_id' => 'TransactionStatusQuery',
    'short_code' => 'YOUR_SHORTCODE',
    'result_url' => 'https://yourdomain.com/api/mpesa/status/result',
    'timeout_url' => 'https://yourdomain.com/api/mpesa/status/timeout'
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
    $response = $mpesa->transactionStatus([
        'transactionID' => 'LGR019G3J2',  // M-Pesa transaction ID
        'identifierType' => 4,  // 4 for Paybill, 2 for Till
        'remarks' => 'Checking transaction status',
        'occasion' => 'Status inquiry'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    echo json_encode(json_decode($e->getMessage()));
}
```

### Parameters

**Required:**
- `transactionID` - The M-Pesa transaction ID to query
- `identifierType` - `1` (MSISDN), `2` (Till), `4` (Paybill)
- `remarks` - Description of the query

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
        "TransactionID": "LGR019G3J2",
        "ResultParameters": {
            "ResultParameter": [
                {
                    "Key": "ReceiptNo",
                    "Value": "LGR019G3J2"
                },
                {
                    "Key": "Conversation ID",
                    "Value": "AG_20191219_00004e48cf7e3533f581"
                },
                {
                    "Key": "FinalisedTime",
                    "Value": 20191219104508
                },
                {
                    "Key": "Amount",
                    "Value": 10
                },
                {
                    "Key": "TransactionStatus",
                    "Value": "Completed"
                },
                {
                    "Key": "ReasonType",
                    "Value": "Salary Payment"
                },
                {
                    "Key": "TransactionReason",
                    "Value": ""
                },
                {
                    "Key": "DebitPartyCharges",
                    "Value": ""
                },
                {
                    "Key": "DebitAccountType",
                    "Value": "Utility Account"
                },
                {
                    "Key": "InitiatedTime",
                    "Value": 20191219104508
                },
                {
                    "Key": "OriginatorConversationID",
                    "Value": "29115-34620561-1"
                },
                {
                    "Key": "CreditPartyName",
                    "Value": "254708374149 - John Doe"
                },
                {
                    "Key": "DebitPartyName",
                    "Value": "600256 - Safaricom"
                }
            ]
        }
    }
}
*/

if ($data['Result']['ResultCode'] == 0) {
    $resultParams = $data['Result']['ResultParameters']['ResultParameter'];
    
    $transactionData = [];
    foreach ($resultParams as $param) {
        $transactionData[$param['Key']] = $param['Value'];
    }
    
    // Extract key information
    $status = $transactionData['TransactionStatus'] ?? 'Unknown';
    $amount = $transactionData['Amount'] ?? 0;
    $receiptNo = $transactionData['ReceiptNo'] ?? '';
    
    // Update your database
    updateTransactionStatus($receiptNo, $status, $transactionData);
}
```

## Transaction Statuses

| Status | Description |
|--------|-------------|
| Completed | Transaction completed successfully |
| Failed | Transaction failed |
| Pending | Transaction is still pending |

## Response Codes

| Code | Description |
|------|-------------|
| 0 | Success |
| 1 | Transaction not found |
| 17 | Internal Failure |
| 20 | Unresolved Initiator |

## Use Cases

1. **Missing Callbacks** - Query status when callback is not received
2. **Reconciliation** - Verify transaction completion during reconciliation
3. **Customer Inquiries** - Check status when customers report issues
4. **Automated Monitoring** - Periodically check pending transactions
5. **Dispute Resolution** - Verify transaction details during disputes

## Best Practices

1. **Wait Before Querying** - Wait at least 1 minute after transaction before querying
2. **Store Transaction IDs** - Always store M-Pesa transaction IDs
3. **Handle Callbacks** - Implement proper callback handling
4. **Rate Limiting** - Don't query too frequently
5. **Error Handling** - Handle cases where transaction is not found

## Additional Resources

- [Official Documentation](https://developer.safaricom.co.ke/transaction-status/apis/post/query)
- [B2C API](B2C.md)
- [B2B API](B2B.md)
- [Reversal API](Reversal.md)
