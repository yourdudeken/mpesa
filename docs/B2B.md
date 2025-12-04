# B2B (Business to Business) API

## Overview
The B2B API enables businesses to transfer funds from one business M-Pesa account to another. This is useful for paying suppliers, settling invoices, or transferring funds between business accounts.

## Prerequisites
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to B2B and taken the necessary steps
3. You have successfully acquired production credentials from Safaricom. If not, you can use the sandbox credentials that come preconfigured on installation
4. You have configured the B2B section in `src/config/mpesa.php`

## Configuration

Update the B2B configuration in `src/config/mpesa.php`:

```php
'b2b' => [
    'initiator_name' => 'YOUR_INITIATOR_NAME',
    'default_command_id' => 'BusinessPayBill',
    'security_credential' => 'YOUR_SECURITY_CREDENTIAL',
    'short_code' => 'YOUR_SHORTCODE',
    'test_phone_number' => '254708374149',
    'result_url' => 'https://yourdomain.com/api/mpesa/b2b/result',
    'timeout_url' => 'https://yourdomain.com/api/mpesa/b2b/timeout'
],
```

### Configuration Parameters

- **initiator_name**: The username of the M-Pesa B2B account API operator
- **default_command_id**: Transaction type. Options:
  - `BusinessPayBill` - Transfer to PayBill (default)
  - `BusinessBuyGoods` - Transfer to Till/Buy Goods
  - `DisburseFundsToBusiness` - Disburse funds to business
  - `BusinessToBusinessTransfer` - Generic B2B transfer
  - `MerchantToMerchantTransfer` - Merchant to merchant transfer
- **security_credential**: Encrypted password for the initiator
- **short_code**: Your business paybill or till number (sender)
- **result_url**: URL to receive successful transaction results
- **timeout_url**: URL to receive timeout notifications

## Payment Flow

1. Your system initiates a B2B payment request to Safaricom
2. Safaricom validates both sender and receiver accounts
3. Funds are transferred from sender (PartyA) to receiver (PartyB)
4. Safaricom sends a callback to your `result_url` with transaction details
5. If the request times out, Safaricom sends a callback to your `timeout_url`

## Usage

### Basic Example - PayBill to PayBill

```php
<?php
require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->B2B([
        'amount' => 1000,
        'partyB' => '600000',  // Receiver's paybill number
        'accountReference' => 'INV-2024-001',
        'remarks' => 'Payment for invoice INV-2024-001'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

### Buy Goods (Till Number) Transfer

```php
$response = $mpesa->B2B([
    'amount' => 500,
    'commandID' => 'BusinessBuyGoods',
    'partyB' => '123456',  // Receiver's till number
    'recieverIdentifierType' => 4,  // 4 for Till Number
    'senderIdentifierType' => 4,    // 4 for Paybill
    'accountReference' => 'ORDER-123',
    'remarks' => 'Payment for goods'
]);
```

### Required Parameters

- **amount**: The amount to transfer (minimum 10)
- **partyB**: The receiver's paybill or till number
- **accountReference**: Account reference for the transaction
- **remarks**: A brief description of the transaction

### Optional Parameters

- **commandID**: Override the default command ID
- **recieverIdentifierType**: Type of receiver identifier
  - `1` - MSISDN
  - `2` - Till Number
  - `4` - Paybill Number (default)
- **senderIdentifierType**: Type of sender identifier
  - `1` - MSISDN
  - `2` - Till Number
  - `4` - Paybill Number (default)
- **queueTimeOutURL**: Override the default timeout URL
- **resultURL**: Override the default result URL

### Advanced Example with All Parameters

```php
$response = $mpesa->B2B([
    'amount' => 5000,
    'commandID' => 'BusinessPayBill',
    'partyB' => '600000',
    'recieverIdentifierType' => 4,
    'senderIdentifierType' => 4,
    'accountReference' => 'SUPPLIER-PAY-2024-001',
    'remarks' => 'Monthly supplier payment',
    'resultURL' => 'https://yourdomain.com/custom/b2b/result',
    'queueTimeOutURL' => 'https://yourdomain.com/custom/b2b/timeout'
]);
```

## Callback Handling

### Result Callback

When the transaction is successful, Safaricom sends a POST request to your `result_url`:

```php
<?php
// Handle B2B result callback
$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Sample response structure
/*
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
                    "Key": "InitiatorAccountCurrentBalance",
                    "Value": "{Amount={BasicAmount=49000.00, MinimumAmount=49000.00, CurrencyCode=KES}}"
                },
                {
                    "Key": "DebitAccountCurrentBalance",
                    "Value": "{Amount={BasicAmount=49000.00, MinimumAmount=49000.00, CurrencyCode=KES}}"
                },
                {
                    "Key": "Amount",
                    "Value": 1000
                },
                {
                    "Key": "DebitPartyAffectedAccountBalance",
                    "Value": "Working Account|KES|49000.00|49000.00"
                },
                {
                    "Key": "TransCompletedTime",
                    "Value": 20191219104524
                },
                {
                    "Key": "DebitPartyCharges",
                    "Value": "Business Transfer Charge|KES|0.00"
                },
                {
                    "Key": "ReceiverPartyPublicName",
                    "Value": "600000 - Safaricom"
                },
                {
                    "Key": "Currency",
                    "Value": "KES"
                }
            ]
        },
        "ReferenceData": {
            "ReferenceItem": {
                "Key": "QueueTimeoutURL",
                "Value": "https://yourdomain.com/api/mpesa/b2b/timeout"
            }
        }
    }
}
*/

// Process the callback
if ($data['Result']['ResultCode'] == 0) {
    // Transaction successful
    $transactionID = $data['Result']['TransactionID'];
    $conversationID = $data['Result']['ConversationID'];
    
    // Extract amount and balance
    $resultParams = $data['Result']['ResultParameters']['ResultParameter'];
    foreach ($resultParams as $param) {
        if ($param['Key'] == 'Amount') {
            $amount = $param['Value'];
        }
        if ($param['Key'] == 'DebitAccountCurrentBalance') {
            $balance = $param['Value'];
        }
    }
    
    // Update your database, send notifications, etc.
} else {
    // Transaction failed
    $errorMessage = $data['Result']['ResultDesc'];
    // Log the error
}
```

### Timeout Callback

If the transaction times out, Safaricom sends a POST request to your `timeout_url` with similar structure but with a non-zero ResultCode.

## Identifier Types

| Type | Value | Description |
|------|-------|-------------|
| MSISDN | 1 | Mobile number |
| Till Number | 2 | Till number for Buy Goods |
| Shortcode | 4 | Paybill number |

## Response Codes

| Code | Description |
|------|-------------|
| 0 | Success |
| 1 | Insufficient Funds |
| 2 | Less Than Minimum Transaction Value |
| 3 | More Than Maximum Transaction Value |
| 4 | Would Exceed Daily Transfer Limit |
| 5 | Would Exceed Minimum Balance |
| 6 | Unresolved Primary Party |
| 7 | Unresolved Receiver Party |
| 8 | Would Exceed Maximum Balance |
| 11 | Debit Account Invalid |
| 12 | Credit Account Invalid |
| 13 | Unresolved Debit Account |
| 14 | Unresolved Credit Account |
| 15 | Duplicate Detected |
| 17 | Internal Failure |
| 20 | Unresolved Initiator |
| 26 | Traffic blocking condition in place |

## Testing in Sandbox

When testing in sandbox mode:
- Set `'is_sandbox' => true` in your config
- Use the sandbox credentials provided by Safaricom
- Use test paybill/till numbers provided in the sandbox environment

## Use Cases

1. **Supplier Payments**: Pay suppliers directly to their paybill accounts
2. **Franchise Payments**: Transfer funds to franchise locations
3. **Inter-branch Transfers**: Move funds between different branches
4. **Bulk Disbursements**: Pay multiple businesses in batch
5. **Commission Payments**: Pay agents or partners their commissions

## Known Issues

1. **Minimum Amount**: The minimum amount you can transfer is KES 10
2. **Account Validation**: Both sender and receiver accounts must be active and valid
3. **Insufficient Funds**: Ensure your paybill account has sufficient balance
4. **Security Credential**: Must be properly encrypted using Safaricom's public certificate

## Best Practices

1. **Validate Accounts**: Verify receiver account details before initiating transfers
2. **Handle Callbacks**: Implement proper callback handling to track transaction status
3. **Error Logging**: Log all errors for debugging and auditing
4. **Idempotency**: Implement idempotency checks to prevent duplicate payments
5. **Balance Checks**: Regularly check your account balance using the Account Balance API
6. **Reconciliation**: Implement proper reconciliation processes
7. **Rate Limiting**: Implement rate limiting to avoid overwhelming the API
8. **Account Reference**: Use unique, meaningful account references for easy tracking

## Additional Resources

- [Official Safaricom B2B Documentation](https://developer.safaricom.co.ke/b2b/apis/post/paymentrequest)
- [Account Balance API](AccountBalance.md) - Check your paybill balance
- [Transaction Status API](TransactionStatus.md) - Query transaction status
- [Reversal API](Reversal.md) - Reverse erroneous transactions
