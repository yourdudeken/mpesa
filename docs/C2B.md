### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to C2B and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is C2B?
C2B (Customer to Business) is an M-Pesa API that enables businesses to receive real-time notifications when customers make payments to their Till or PayBill numbers. This is particularly useful for businesses that need to automatically reconcile payments made via the M-Pesa SIM card toolkit.

### How to consume C2B endpoint with this package.

#### Two main operations with C2B:

#### 1. C2B Register - Register your callback URLs
Before you can receive payment notifications, you need to register your validation and confirmation URLs with Safaricom.

##### Using vanilla php

```php
<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->C2BRegister([
        'validationURL' => 'https://example.com/v1/payments/c2b/validate',
        'confirmationURL' => 'https://example.com/v1/payments/c2b/confirm',
        'responseType' => 'Completed',  // or 'Cancelled'
        'shortCode' => '600000'
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

class MpesaController {

   public function registerC2B() {
      $mpesa = new Mpesa();
      
      $response = $mpesa->C2BRegister([
          'validationURL' => route('mpesa.c2b.validate'),
          'confirmationURL' => route('mpesa.c2b.confirm'),
          'responseType' => 'Completed'
      ]); 
      
      return response()->json($response);
   }
}

```

#### 2. C2B Simulate - Test C2B payments (Sandbox only)
In sandbox mode, you can simulate customer payments to test your integration.

##### Using vanilla php

```php
<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->C2BSimulate([
        'amount' => 100,
        'msisdn' => '254722000000',
        'billRefNumber' => 'INV-001',
        'commandID' => 'CustomerPayBillOnline'  // or 'CustomerBuyGoodsOnline'
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

class MpesaController {

   public function simulateC2B() {
      $mpesa = new Mpesa();
      
      $response = $mpesa->C2BSimulate([
          'amount' => 100,
          'msisdn' => '254722000000',
          'billRefNumber' => 'INV-001',
          'commandID' => 'CustomerPayBillOnline'
      ]); 
      
      return response()->json($response);
   }
}

```

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `c2b` section:

- **confirmation_url**: URL to receive payment confirmations
- **validation_url**: URL to validate payments before they are processed
- **on_timeout**: What to do when validation times out ('Completed' or 'Cancelled')
- **short_code**: Your business shortcode (PayBill or Till Number)
- **test_phone_number**: Phone number for testing in sandbox mode
- **default_command_id**: Default is 'CustomerPayBillOnline' or 'CustomerBuyGoodsOnline'

### C2B Register Parameters
When calling the C2BRegister method:

- **validationURL** (optional): URL to validate payments. Overrides config value
- **confirmationURL** (optional): URL to receive payment confirmations. Overrides config value
- **responseType** (optional): 'Completed' or 'Cancelled'. What to do on validation timeout
- **shortCode** (optional): Business shortcode. Overrides config value

### C2B Simulate Parameters
When calling the C2BSimulate method (sandbox only):

- **amount** (required): The amount being paid
- **msisdn** (required): Customer's phone number (format: 254XXXXXXXXX)
- **billRefNumber** (required): Account reference/invoice number
- **commandID** (optional): 'CustomerPayBillOnline' for PayBill or 'CustomerBuyGoodsOnline' for Till Number

### Payment Flow
1. Customer initiates payment via M-Pesa SIM toolkit to your PayBill/Till number
2. Safaricom sends a validation request to your `validation_url` (if configured)
3. Your system responds within 30 seconds to accept or reject the payment
4. If accepted (or validation times out with 'Completed' setting), payment is processed
5. Safaricom sends payment confirmation to your `confirmation_url`
6. Your system processes the payment and updates records

### Validation Request Example
When a customer makes a payment, Safaricom will POST to your validation URL:

```json
{
    "TransactionType": "Pay Bill",
    "TransID": "NLJ7RT61SV",
    "TransTime": "20231217230220",
    "TransAmount": "100.00",
    "BusinessShortCode": "600000",
    "BillRefNumber": "INV-001",
    "InvoiceNumber": "",
    "OrgAccountBalance": "10000.00",
    "ThirdPartyTransID": "",
    "MSISDN": "254722000000",
    "FirstName": "John",
    "MiddleName": "",
    "LastName": "Doe"
}
```

Your validation response should be:

```json
{
    "ResultCode": 0,
    "ResultDesc": "Accepted"
}
```

Or to reject:

```json
{
    "ResultCode": 1,
    "ResultDesc": "Rejected"
}
```

### Confirmation Request Example
After successful payment, Safaricom will POST to your confirmation URL:

```json
{
    "TransactionType": "Pay Bill",
    "TransID": "NLJ7RT61SV",
    "TransTime": "20231217230220",
    "TransAmount": "100.00",
    "BusinessShortCode": "600000",
    "BillRefNumber": "INV-001",
    "InvoiceNumber": "",
    "OrgAccountBalance": "10100.00",
    "ThirdPartyTransID": "",
    "MSISDN": "254722000000",
    "FirstName": "John",
    "MiddleName": "",
    "LastName": "Doe"
}
```

Your confirmation response should be:

```json
{
    "ResultCode": 0,
    "ResultDesc": "Success"
}
```

### Command IDs
- **CustomerPayBillOnline**: For PayBill payments
- **CustomerBuyGoodsOnline**: For Till Number/Buy Goods payments

### Known issues with this endpoint
1. **URL Registration**: You must register your URLs before you can receive payment notifications. Registration is per shortcode.
2. **Validation Timeout**: Your validation endpoint must respond within 30 seconds or the payment will be processed based on your `on_timeout` setting.
3. **HTTPS Required**: Both validation and confirmation URLs must use HTTPS in production.
4. **Simulation Limitations**: C2B Simulate only works in sandbox mode. In production, use actual M-Pesa payments for testing.
5. **Duplicate Notifications**: Implement idempotency checks as you may receive duplicate confirmation requests.
6. **IP Whitelisting**: Consider whitelisting Safaricom's IP addresses for added security.
