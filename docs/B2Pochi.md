# B2Pochi (Business to Pochi) API

## Overview
The B2Pochi API enables businesses to send money from their M-Pesa paybill account directly to customers' M-Pesa Pochi savings accounts. This is useful for various use cases such as savings deposits, rewards programs, and incentive payments.

## What is M-Pesa Pochi?
M-Pesa Pochi is a savings feature within M-Pesa that allows users to:
- Save money separately from their main M-Pesa wallet
- Earn interest on savings
- Keep funds secure for future use

**B2Pochi vs B2C:**
- **B2C**: Sends money to main M-Pesa wallet (for immediate spending)
- **B2Pochi**: Sends money to Pochi savings account (for saving)

## Prerequisites
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to B2Pochi and taken the necessary steps
3. You have successfully acquired production credentials from Safaricom. If not, you can use the sandbox credentials that come preconfigured on installation
4. You have configured the B2Pochi section in `src/config/mpesa.php`

## Configuration

Update the B2Pochi configuration in `src/config/mpesa.php`:

```php
'b2pochi' => [
    'initiator_name' => 'testapi',
    'default_command_id' => 'BusinessPayToPochi',
    'security_credential' => 'Safaricom999!*!',
    'short_code' => '600000',
    'result_url' => 'https://yourdomain.com/api/mpesa/b2pochi/result',
    'timeout_url' => 'https://yourdomain.com/api/mpesa/b2pochi/timeout'
],
```

### Configuration Parameters

- **initiator_name**: The username of the M-Pesa B2Pochi account API operator
- **default_command_id**: Transaction type (default: `BusinessPayToPochi`)
- **security_credential**: Encrypted password for the initiator
- **short_code**: Your business paybill or till number
- **result_url**: URL to receive successful transaction results
- **timeout_url**: URL to receive timeout notifications

## Payment Flow

1. Your system initiates a B2Pochi payment request to Safaricom
2. Safaricom processes the request and debits your paybill account
3. The customer receives the money in their M-Pesa Pochi savings account
4. Safaricom sends a callback to your `result_url` with transaction details
5. If the request times out, Safaricom sends a callback to your `timeout_url`

## Usage

### Basic Example

```php
<?php
require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->B2Pochi([
        'OriginatorConversationID' => 'B2P_' . uniqid(),
        'amount' => 1000,
        'partyB' => '254712345678',
        'remarks' => 'Monthly savings deposit',
        'occasion' => 'January Savings'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

### Required Parameters

- **OriginatorConversationID**: Unique identifier for the transaction
- **amount**: The amount to send (minimum 10)
- **partyB**: The customer's phone number (format: 254XXXXXXXXX)
- **remarks**: A brief description of the transaction

### Optional Parameters

- **commandID**: Override the default command ID (default: `BusinessPayToPochi`)
- **occasion**: Additional information about the transaction
- **queueTimeOutURL**: Override the default timeout URL
- **resultURL**: Override the default result URL

### Advanced Example with Custom Configuration

```php
$response = $mpesa->B2Pochi([
    'OriginatorConversationID' => 'B2P_' . time(),
    'amount' => 5000,
    'partyB' => '254712345678',
    'commandID' => 'BusinessPayToPochi',
    'remarks' => 'Quarterly savings bonus',
    'occasion' => 'Q1 2024 Performance Bonus',
    'resultURL' => 'https://yourdomain.com/custom/result',
    'queueTimeOutURL' => 'https://yourdomain.com/custom/timeout'
]);
```

## Callback Handling

### Result Callback

When the transaction is successful, Safaricom sends a POST request to your `result_url`:

```php
<?php
// Handle B2Pochi result callback
$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Sample response structure
/*
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "B2P_12345",
        "ConversationID": "AG_20191219_00005797af5d7d75f652",
        "TransactionID": "NLJ7RT61SV",
        "ResultParameters": {
            "ResultParameter": [
                {
                    "Key": "TransactionAmount",
                    "Value": 1000
                },
                {
                    "Key": "TransactionReceipt",
                    "Value": "NLJ7RT61SV"
                },
                {
                    "Key": "ReceiverPartyPublicName",
                    "Value": "254712345678 - John Doe"
                },
                {
                    "Key": "TransactionCompletedDateTime",
                    "Value": "19.12.2019 11:45:50"
                },
                {
                    "Key": "B2CUtilityAccountAvailableFunds",
                    "Value": 10000.00
                },
                {
                    "Key": "B2CWorkingAccountAvailableFunds",
                    "Value": 50000.00
                },
                {
                    "Key": "B2CChargesPaidAccountAvailableFunds",
                    "Value": 0.00
                },
                {
                    "Key": "B2CRecipientIsRegisteredCustomer",
                    "Value": "Y"
                }
            ]
        }
    }
}
*/

// Process the callback
if ($data['Result']['ResultCode'] == 0) {
    // Transaction successful
    $transactionID = $data['Result']['TransactionID'];
    $originatorConvID = $data['Result']['OriginatorConversationID'];
    // Update your database, send notifications, etc.
} else {
    // Transaction failed
    $errorMessage = $data['Result']['ResultDesc'];
    // Log the error
}
```

### Timeout Callback

If the transaction times out, Safaricom sends a POST request to your `timeout_url` with similar structure but with a non-zero ResultCode.

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
- Test phone number: `254708374149`
- Initiator name: `testapi`
- Security credential: `Safaricom999!*!`

### Sandbox Example

```php
$response = $mpesa->B2Pochi([
    'OriginatorConversationID' => 'B2P_TEST_' . time(),
    'amount' => 100,
    'partyB' => '254708374149', // Sandbox test number
    'remarks' => 'Test Pochi payment',
    'occasion' => 'Testing'
]);
```

## Known Issues

1. **Minimum Amount**: The minimum amount you can send is KES 10
2. **Phone Number Format**: Always use the format 254XXXXXXXXX (without the + sign)
3. **Insufficient Funds**: Ensure your paybill account has sufficient balance
4. **Security Credential**: Must be properly encrypted using Safaricom's public certificate
5. **Unique Conversation ID**: Each `OriginatorConversationID` must be unique

## Best Practices

1. **Unique Transaction IDs**: Always generate unique `OriginatorConversationID` for each transaction
2. **Validate Phone Numbers**: Always validate phone numbers before making requests
3. **Handle Callbacks**: Implement proper callback handling to track transaction status
4. **Error Logging**: Log all errors for debugging and auditing
5. **Idempotency**: Implement idempotency checks to prevent duplicate payments
6. **Balance Checks**: Regularly check your account balance using the Account Balance API
7. **Rate Limiting**: Implement rate limiting to avoid overwhelming the API
8. **Customer Notification**: Notify customers when money is deposited to their Pochi account

## Use Cases

### 1. Savings Programs
```php
// Monthly savings deposit
$mpesa->B2Pochi([
    'OriginatorConversationID' => 'SAVINGS_' . $customerId . '_' . time(),
    'amount' => 5000,
    'partyB' => $customerPhone,
    'remarks' => 'Monthly savings contribution',
    'occasion' => 'Regular Savings'
]);
```

### 2. Rewards & Incentives
```php
// Performance bonus
$mpesa->B2Pochi([
    'OriginatorConversationID' => 'BONUS_' . $employeeId . '_' . time(),
    'amount' => 10000,
    'partyB' => $employeePhone,
    'remarks' => 'Q1 Performance Bonus',
    'occasion' => 'Quarterly Incentive'
]);
```

### 3. Refunds to Savings
```php
// Refund to Pochi
$mpesa->B2Pochi([
    'OriginatorConversationID' => 'REFUND_' . $orderId,
    'amount' => $refundAmount,
    'partyB' => $customerPhone,
    'remarks' => 'Order refund to savings',
    'occasion' => 'Refund'
]);
```

## Difference from B2C

| Feature | B2C | B2Pochi |
|---------|-----|---------|
| Destination | Main M-Pesa wallet | Pochi savings account |
| Purpose | Immediate spending | Saving for future |
| Interest | No | Yes (on Pochi balance) |
| Withdrawal | Immediate | From Pochi to wallet first |
| Command ID | `BusinessPayment` | `BusinessPayToPochi` |

## Additional Resources

- [Official Safaricom B2C Documentation](https://developer.safaricom.co.ke/b2c/apis/post/paymentrequest)
- [B2C API](B2C.md) - Send to main M-Pesa wallet
- [Account Balance API](AccountBalance.md) - Check your paybill balance
- [Transaction Status API](TransactionStatus.md) - Query transaction status
- [Reversal API](Reversal.md) - Reverse erroneous transactions
