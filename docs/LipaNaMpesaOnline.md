### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to Lipa na M-Pesa Online and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is Lipa na M-Pesa Online (STK Push)?
Lipa na M-Pesa Online, also known as STK Push or M-Pesa Express, is an API that allows merchants to initiate payment requests directly to customers' phones. When you initiate an STK Push, the customer receives a prompt on their phone asking them to enter their M-Pesa PIN to authorize the payment. This provides a seamless payment experience without requiring customers to navigate through the M-Pesa menu.

### How to consume Lipa na M-Pesa Online (STK Push) endpoint with this package.

#### Payment flow involved with this endpoint.
1. Your system initiates a payment request on behalf of the customer and sends it to Safaricom.
2. Safaricom sends an STK Push prompt to the customer's phone requesting them to authorize the transaction by entering their M-Pesa PIN.
3. Customer enters their M-Pesa PIN to authorize or cancels the request.
4. Safaricom processes the payment (if authorized) and responds to your system with transaction details via the CallBackURL.

#### Usage
Note this package allows you to override preconfigured parameters for this endpoint. For all supported options check the Safaricom API documentation at https://developer.safaricom.co.ke/docs#lipa-na-m-pesa-online-payment

##### Using vanilla php

```php
<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->STKPush([
        'amount' => 100,
        'phoneNumber' => '254722000000',
        'accountReference' => 'INV-001',
        'transactionDesc' => 'Payment for invoice INV-001',
        'callBackURL' => 'https://example.com/v1/payments/stk/callback'
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

class CheckoutController {

   public function initiatePayment() {
      $mpesa = new Mpesa();
      
      $response = $mpesa->STKPush([
          'amount' => 100,
          'phoneNumber' => '254722000000',
          'accountReference' => 'INV-001',
          'transactionDesc' => 'Payment for invoice INV-001',
          'callBackURL' => route('mpesa.stk.callback')
      ]); 
      
      return response()->json($response);
   }
}

```

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `lnmo` section:

- **short_code**: Your business shortcode (Paybill or Till Number)
- **passkey**: The SAG Passkey provided by Safaricom upon registration
- **callback** (optional): Default callback URL to receive payment notifications. Falls back to global callback
- **default_transaction_type**: Default is 'CustomerPayBillOnline' for Paybill, or 'CustomerBuyGoodsOnline' for Till Number

### Request Parameters
When calling the STKPush method, you can pass the following parameters:

- **amount** (required): The amount to charge the customer
- **phoneNumber** (required): Customer's phone number (format: 254XXXXXXXXX)
- **accountReference** (optional): Account reference. Falls back to config default
- **transactionDesc** (optional): Description of the transaction. Falls back to config default
- **callBackURL** (optional): Overrides the configured callback URL. Falls back to global config
- **transactionType** (optional): Overrides the default transaction type
- **passkey** (optional): Overrides the configured passkey
- **shortCode** (optional): Overrides the configured shortcode

### Transaction Types
- **CustomerPayBillOnline**: For Paybill payments (most common)
- **CustomerBuyGoodsOnline**: For Till Number/Buy Goods payments

### Response Handling
The API returns an immediate response with the following structure:

```json
{
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResponseCode": "0",
    "ResponseDescription": "Success. Request accepted for processing",
    "CustomerMessage": "Success. Request accepted for processing"
}
```

**Important**: This response only indicates that the request was accepted, not that payment was successful. The actual payment result will be sent to your callback URL.

### Callback Response Example (Successful Payment)
```json
{
    "Body": {
        "stkCallback": {
            "MerchantRequestID": "29115-34620561-1",
            "CheckoutRequestID": "ws_CO_191220191020363925",
            "ResultCode": 0,
            "ResultDesc": "The service request is processed successfully.",
            "CallbackMetadata": {
                "Item": [
                    {
                        "Name": "Amount",
                        "Value": 100
                    },
                    {
                        "Name": "MpesaReceiptNumber",
                        "Value": "NLJ7RT61SV"
                    },
                    {
                        "Name": "Balance"
                    },
                    {
                        "Name": "TransactionDate",
                        "Value": 20231217230220
                    },
                    {
                        "Name": "PhoneNumber",
                        "Value": 254722000000
                    }
                ]
            }
        }
    }
}
```

### Callback Response Example (Failed/Cancelled Payment)
```json
{
    "Body": {
        "stkCallback": {
            "MerchantRequestID": "29115-34620561-1",
            "CheckoutRequestID": "ws_CO_191220191020363925",
            "ResultCode": 1032,
            "ResultDesc": "Request cancelled by user"
        }
    }
}
```

### Result Codes
- **0**: Success - Payment completed successfully
- **1**: Insufficient funds in customer's account
- **1032**: Request cancelled by user
- **1037**: Timeout - User didn't enter PIN in time (usually 60 seconds)
- **2001**: Invalid PIN entered
- **Other codes**: Various error conditions

### Handling Callbacks
Your callback endpoint should:

1. **Validate the request** - Ensure it's from Safaricom
2. **Extract transaction details** - Parse the callback data
3. **Update your records** - Mark orders as paid/failed
4. **Respond quickly** - Return a response within 30 seconds
5. **Process asynchronously** - Handle heavy processing in background jobs

Example callback handler in PHP:
```php
<?php
// Get the callback data
$callbackData = file_get_contents('php://input');
$callback = json_decode($callbackData, true);

// Extract result
$resultCode = $callback['Body']['stkCallback']['ResultCode'];

if ($resultCode == 0) {
    // Payment successful
    $metadata = $callback['Body']['stkCallback']['CallbackMetadata']['Item'];
    
    $amount = null;
    $mpesaReceiptNumber = null;
    $phoneNumber = null;
    $transactionDate = null;
    
    foreach ($metadata as $item) {
        switch ($item['Name']) {
            case 'Amount':
                $amount = $item['Value'];
                break;
            case 'MpesaReceiptNumber':
                $mpesaReceiptNumber = $item['Value'];
                break;
            case 'PhoneNumber':
                $phoneNumber = $item['Value'];
                break;
            case 'TransactionDate':
                $transactionDate = $item['Value'];
                break;
        }
    }
    
    // Update your database
    // Mark order as paid, send confirmation email, etc.
    
} else {
    // Payment failed
    $resultDesc = $callback['Body']['stkCallback']['ResultDesc'];
    // Handle failure - notify user, log error, etc.
}

// Always respond to Safaricom
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
```

### STK Status Query
You can also query the status of an STK Push request using the STKStatus method:

```php
$response = $mpesa->STKStatus([
    'checkoutRequestID' => 'ws_CO_191220191020363925'
]);
```

This is useful when:
- You didn't receive a callback
- You want to check status before the callback arrives
- You need to verify a transaction for customer support

### Use Cases
1. **E-commerce Checkout**: Seamless payment during online shopping
2. **Bill Payments**: Utility bills, subscriptions, etc.
3. **Service Payments**: Booking confirmations, service fees
4. **Donations**: Charity and fundraising platforms
5. **Mobile Apps**: In-app purchases and subscriptions
6. **Point of Sale**: Quick payments at physical stores

### Best Practices
1. **Store CheckoutRequestID**: Always save the CheckoutRequestID for status queries
2. **Implement Timeouts**: Handle cases where users don't respond to the prompt
3. **User Feedback**: Show clear instructions to users about the STK prompt
4. **Retry Logic**: Allow users to retry if they cancel or timeout
5. **Idempotency**: Handle duplicate callbacks gracefully
6. **Error Messages**: Provide clear, user-friendly error messages
7. **Testing**: Thoroughly test with different scenarios (success, cancel, timeout)
8. **Callback Security**: Validate that callbacks are from Safaricom (IP whitelisting, etc.)

### Known issues with this endpoint
1. **STK DS Timeout**: Some SIM cards are not yet supported by STK Push. Requests to such SIM cards will fail with `[STK DS timeout]`. This is a Safaricom limitation.

2. **Multiple Requests**: Making multiple subsequent STK Push requests to the same phone number in quick succession can cause the initial request to timeout and the STK prompt not to respond to Safaricom, even if the user enters the correct M-Pesa PIN. Wait at least 60 seconds between requests to the same number.

3. **User Response Time**: Users have approximately 60 seconds to respond to the STK prompt. After this, the request times out.

4. **Network Issues**: Poor network connectivity can cause delays or failures in delivering the STK prompt.

5. **Phone Availability**: The customer's phone must be on and have network connectivity to receive the prompt.

6. **Callback Delays**: In some cases, callbacks may be delayed. Implement proper timeout handling and status query mechanisms.

7. **Amount Limits**: There are minimum and maximum transaction limits. Check with Safaricom for current limits.

8. **Daily Limits**: Customer accounts have daily transaction limits that may cause failures.

### Troubleshooting

**Problem**: User doesn't receive STK prompt
- **Solution**: Verify phone number format (254XXXXXXXXX), check if phone is on and has network

**Problem**: Request times out
- **Solution**: Ensure user responds within 60 seconds, check for network issues

**Problem**: Multiple failed attempts
- **Solution**: Wait at least 60 seconds between retry attempts to the same number

**Problem**: Callback not received
- **Solution**: Use STKStatus to query transaction status, verify callback URL is accessible

**Problem**: "Invalid Access Token"
- **Solution**: Check your consumer key and secret, ensure they're correct for your environment
