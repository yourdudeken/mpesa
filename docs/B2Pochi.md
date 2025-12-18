### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to B2Pochi and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is B2Pochi?
B2Pochi (Business to Pochi) is an M-Pesa API that enables businesses to send money directly to customers' M-Pesa Pochi accounts. Pochi is a virtual wallet within M-Pesa that allows users to save money separately from their main M-Pesa balance.

### How to consume B2Pochi endpoint with this package.

#### Payment flow involved with this endpoint.
1. Your system initiates a payment request to send money to a customer's Pochi account.
2. Safaricom processes the request and transfers the funds to the specified Pochi account.
3. Safaricom sends a response to your system with details regarding the transaction via the ResultURL and QueueTimeOutURL callbacks.

#### Usage
Note this package allows you to override preconfigured parameters for this endpoint. For all supported options check the Safaricom API documentation.

##### Using vanilla php

```php
<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->B2Pochi([
        'amount' => 100,
        'partyB' => '254722000000',
        'remarks' => 'Payment to Pochi account',
        'resultURL' => 'https://example.com/v1/payments/b2pochi/result',
        'queueTimeOutURL' => 'https://example.com/v1/payments/b2pochi/timeout'
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

   public function sendToPochi() {
      $mpesa = new Mpesa();
      
      $response = $mpesa->B2Pochi([
          'amount' => 100,
          'partyB' => '254722000000',
          'remarks' => 'Payment to Pochi account',
          'resultURL' => route('mpesa.b2pochi.result'),
          'queueTimeOutURL' => route('mpesa.b2pochi.timeout')
      ]); 
      
      return response()->json($response);
   }
}

```

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `b2pochi` section:

- **initiator_name**: The name of the initiator making the request
- **initiator_password**: The password for the initiator
- **default_command_id**: Default is 'BusinessPayToPochi'
- **short_code**: Your business shortcode
- **test_phone_number**: Phone number for testing in sandbox mode
- **result_url**: URL to receive successful transaction results
- **timeout_url**: URL to receive timeout notifications

### Request Parameters
When calling the B2Pochi method, you can pass the following parameters:

- **amount** (required): The amount to send to the Pochi account
- **partyB** (required): The phone number of the recipient (format: 254XXXXXXXXX)
- **remarks** (required): Comments sent along with the transaction
- **resultURL** (optional): Overrides the configured result URL
- **queueTimeOutURL** (optional): Overrides the configured timeout URL
- **commandID** (optional): Overrides the default command ID
- **initiatorName** (optional): Overrides the configured initiator name
- **securityCredential** (optional): Overrides the computed security credential

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

The actual transaction result will be sent to your configured `result_url` callback.

### Known issues with this endpoint
1. Ensure the recipient's phone number has an active M-Pesa Pochi account.
2. The transaction may fail if the recipient's Pochi account has restrictions.
3. Always implement proper error handling for timeout scenarios.
