### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to B2C and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is B2C?
B2C (Business to Customer) is an M-Pesa API that enables businesses to make payments to customers. This is useful for scenarios such as salary payments, promotions, refunds, rewards, and other business-to-customer transactions.

### How to consume B2C endpoint with this package.

#### Payment flow involved with this endpoint.
1. Your system initiates a payment request to send money to a customer's M-Pesa account.
2. Safaricom processes the request and transfers the funds to the specified phone number.
3. Safaricom sends a response to your system with details regarding the transaction via the ResultURL and QueueTimeOutURL callbacks.

#### Usage
Note this package allows you to override preconfigured parameters for this endpoint. For all supported options check the Safaricom API documentation at https://developer.safaricom.co.ke/docs#b2c-api

##### Using vanilla php

```php
<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->B2C([
        'amount' => 100,
        'partyB' => '254722000000',
        'remarks' => 'Salary payment for December',
        'occasion' => 'Monthly Salary',
        'resultURL' => 'https://example.com/v1/payments/b2c/result',
        'queueTimeOutURL' => 'https://example.com/v1/payments/b2c/timeout'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

##### Using Laravel.
```php
use Yourdudeken\Mpesa\Init as Mpesa;

class PaymentController {

   public function payCustomer() {
      $mpesa = new Mpesa();
      
      $response = $mpesa->B2C([
          'amount' => 100,
          'partyB' => '254722000000',
          'remarks' => 'Salary payment for December',
          'occasion' => 'Monthly Salary',
          'resultURL' => route('mpesa.b2c.result'),
          'queueTimeOutURL' => route('mpesa.b2c.timeout')
      ]); 
      
      return response()->json($response);
   }
}

```

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `b2c` section:

- **initiator_name**: The name of the initiator making the request
- **security_credential**: The encrypted password for the initiator
- **default_command_id**: Default is 'BusinessPayment'. Other options include 'SalaryPayment', 'BusinessPayment', 'PromotionPayment'
- **short_code**: Your business shortcode
- **test_phone_number**: Phone number for testing in sandbox mode (automatically used in sandbox)
- **result_url**: URL to receive successful transaction results
- **timeout_url**: URL to receive timeout notifications

### Request Parameters
When calling the B2C method, you can pass the following parameters:

- **amount** (required): The amount to send to the customer
- **partyB** (required): The phone number of the recipient (format: 254XXXXXXXXX)
- **remarks** (required): Comments sent along with the transaction
- **occasion** (optional): Any additional information to be associated with the transaction
- **resultURL** (optional): Overrides the configured result URL
- **queueTimeOutURL** (optional): Overrides the configured timeout URL
- **commandID** (optional): Overrides the default command ID. Options: 'SalaryPayment', 'BusinessPayment', 'PromotionPayment'
- **initiatorName** (optional): Overrides the configured initiator name
- **securityCredential** (optional): Overrides the computed security credential

### Command IDs and Their Uses
- **SalaryPayment**: Use this for salary payments to employees
- **BusinessPayment**: Use this for general business payments to customers
- **PromotionPayment**: Use this for promotional payments or rewards to customers

### Response Handling
The API returns an immediate response with the following structure:

```json
{
    "ConversationID": "AG_20231217_00004e8f3f7c9b8d1234",
    "OriginatorConversationID": "12345-67890-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

The actual transaction result will be sent to your configured `result_url` callback with detailed information including:
- Transaction ID
- Transaction amount
- Recipient details
- Transaction completion time
- Charges applied

### Callback Response Example
```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "12345-67890-1",
        "ConversationID": "AG_20231217_00004e8f3f7c9b8d1234",
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
                    "Value": "254722000000 - John Doe"
                },
                {
                    "Key": "TransactionCompletedDateTime",
                    "Value": "17.12.2023 23:02:20"
                },
                {
                    "Key": "B2CUtilityAccountAvailableFunds",
                    "Value": 5000.00
                },
                {
                    "Key": "B2CWorkingAccountAvailableFunds",
                    "Value": 10000.00
                },
                {
                    "Key": "B2CChargesPaidAccountAvailableFunds",
                    "Value": 0.00
                }
            ]
        }
    }
}
```

### Known issues with this endpoint
1. **Sandbox Mode**: In sandbox mode, the package automatically uses the configured test phone number to ensure requests succeed.
2. **Insufficient Funds**: Ensure your paybill/till account has sufficient balance before initiating B2C transactions.
3. **Daily Limits**: Be aware of daily transaction limits set by Safaricom for your account.
4. **Recipient Validation**: The recipient's phone number must be a valid, registered M-Pesa number.
5. **Callback Timeouts**: Implement proper timeout handling as network issues may delay callbacks.
