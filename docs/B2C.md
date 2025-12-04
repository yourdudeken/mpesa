# B2C (Business to Customer) API

## Overview
The B2C API enables businesses to send money from their M-Pesa paybill account to customers' M-Pesa wallets. This is useful for various use cases such as salary payments, promotions, refunds, and rewards.

## Prerequisites
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to B2C and taken the necessary steps
3. You have successfully acquired production credentials from Safaricom. If not, you can use the sandbox credentials that come preconfigured on installation
4. You have configured the B2C section in `src/config/mpesa.php`

## Configuration

Update the B2C configuration in `src/config/mpesa.php`:

```php
'b2c' => [
    'initiator_name' => 'YOUR_INITIATOR_NAME',
    'default_command_id' => 'BusinessPayment',
    'security_credential' => 'YOUR_SECURITY_CREDENTIAL',
    'short_code' => 'YOUR_SHORTCODE',
    'test_phone_number' => '254708374149',
    'result_url' => 'https://yourdomain.com/api/mpesa/b2c/result',
    'timeout_url' => 'https://yourdomain.com/api/mpesa/b2c/timeout'
],
```

### Configuration Parameters

- **initiator_name**: The username of the M-Pesa B2C account API operator
- **default_command_id**: Transaction type. Options:
  - `BusinessPayment` - For business payments (default)
  - `SalaryPayment` - For salary payments
  - `PromotionPayment` - For promotional payments
- **security_credential**: Encrypted password for the initiator
- **short_code**: Your business paybill or till number
- **test_phone_number**: Phone number for sandbox testing
- **result_url**: URL to receive successful transaction results
- **timeout_url**: URL to receive timeout notifications

## Payment Flow

1. Your system initiates a B2C payment request to Safaricom
2. Safaricom processes the request and debits your paybill account
3. The customer receives the money in their M-Pesa wallet
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
    $response = $mpesa->B2C([
        'amount' => 100,
        'partyB' => '254712345678',
        'remarks' => 'Salary payment for January',
        'occasion' => 'Monthly Salary'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

### Required Parameters

- **amount**: The amount to send (minimum 10)
- **partyB**: The customer's phone number (format: 254XXXXXXXXX)
- **remarks**: A brief description of the transaction

### Optional Parameters

- **commandID**: Override the default command ID
- **occasion**: Additional information about the transaction
- **queueTimeOutURL**: Override the default timeout URL
- **resultURL**: Override the default result URL

### Advanced Example with Custom Configuration

```php
$response = $mpesa->B2C([
    'amount' => 500,
    'partyB' => '254712345678',
    'commandID' => 'SalaryPayment',
    'remarks' => 'Employee salary',
    'occasion' => 'January 2024 Salary',
    'resultURL' => 'https://yourdomain.com/custom/result',
    'queueTimeOutURL' => 'https://yourdomain.com/custom/timeout'
]);
```

## Callback Handling

### Result Callback

When the transaction is successful, Safaricom sends a POST request to your `result_url`:

```php
<?php
// Handle B2C result callback
$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Sample response structure
/*
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "AG_20191219_00005797af5d7d75f652",
        "ConversationID": "AG_20191219_00005797af5d7d75f652",
        "TransactionID": "NLJ7RT61SV",
        "ResultParameters": {
            "ResultParameter": [
                {
                    "Key": "TransactionAmount",
                    "Value": 100
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
- The package automatically uses the `test_phone_number` from config
- Use the sandbox credentials provided by Safaricom

## Known Issues

1. **Minimum Amount**: The minimum amount you can send is KES 10
2. **Phone Number Format**: Always use the format 254XXXXXXXXX (without the + sign)
3. **Insufficient Funds**: Ensure your paybill account has sufficient balance
4. **Security Credential**: Must be properly encrypted using Safaricom's public certificate

## Best Practices

1. **Validate Phone Numbers**: Always validate phone numbers before making requests
2. **Handle Callbacks**: Implement proper callback handling to track transaction status
3. **Error Logging**: Log all errors for debugging and auditing
4. **Idempotency**: Implement idempotency checks to prevent duplicate payments
5. **Balance Checks**: Regularly check your account balance using the Account Balance API
6. **Rate Limiting**: Implement rate limiting to avoid overwhelming the API

## Additional Resources

- [Official Safaricom B2C Documentation](https://developer.safaricom.co.ke/b2c/apis/post/paymentrequest)
- [Account Balance API](AccountBalance.md) - Check your paybill balance
- [Transaction Status API](TransactionStatus.md) - Query transaction status
- [Reversal API](Reversal.md) - Reverse erroneous transactions
