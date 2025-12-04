# Lipa Na M-Pesa Online (STK Push) API

## Overview
Lipa Na M-Pesa Online, also known as STK Push or M-Pesa Express, allows you to initiate payment requests directly to your customers' phones. The customer receives a prompt on their phone to enter their M-Pesa PIN to authorize the payment. This is the most popular payment method for e-commerce and online services in Kenya.

## Prerequisites
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to Lipa Na M-Pesa Online and taken the necessary steps
3. You have successfully acquired production credentials from Safaricom. If not, you can use the sandbox credentials that come preconfigured on installation
4. You have configured the LNMO section in `src/config/mpesa.php`
5. You have a publicly accessible callback URL

## Configuration

Update the Lipa Na M-Pesa Online configuration in `src/config/mpesa.php`:

```php
'lnmo' => [
    'short_code' => 174379,  // Your paybill/till number
    'callback' => 'https://yourdomain.com/api/mpesa/stk/callback',
    'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
    'default_transaction_type' => 'CustomerPayBillOnline'
],
```

### Configuration Parameters

- **short_code**: Your registered paybill or till number
- **callback**: URL to receive payment notifications (must be HTTPS in production)
- **passkey**: SAG Passkey provided by Safaricom during registration
- **default_transaction_type**: Transaction type
  - `CustomerPayBillOnline` - For paybill payments (default)
  - `CustomerBuyGoodsOnline` - For till/buy goods payments

## Payment Flow

1. Your system initiates an STK Push request to Safaricom
2. Safaricom sends an STK prompt to the customer's phone
3. Customer enters their M-Pesa PIN to authorize payment
4. Safaricom processes the payment and debits customer's account
5. Safaricom sends a callback to your URL with transaction details
6. Customer receives an SMS confirmation

## Usage

### 1. Initiate STK Push

```php
<?php
require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->STKPush([
        'amount' => 100,
        'phoneNumber' => '254712345678',
        'accountReference' => 'ORDER-12345',
        'transactionDesc' => 'Payment for Order 12345'
    ]);
    
    // Save the CheckoutRequestID for status queries
    $checkoutRequestID = $response->CheckoutRequestID;
    
    echo json_encode($response);
} catch(\Exception $e) {
    echo json_encode(json_decode($e->getMessage()));
}
```

#### Required Parameters

- **amount**: Amount to charge (minimum 1)
- **phoneNumber**: Customer's phone number (format: 254XXXXXXXXX)
- **accountReference**: Reference for the transaction (max 12 characters)
- **transactionDesc**: Description of the transaction

#### Optional Parameters

- **callBackURL**: Override the default callback URL
- **transactionType**: Override the default transaction type

#### Response

```json
{
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResponseCode": "0",
    "ResponseDescription": "Success. Request accepted for processing",
    "CustomerMessage": "Success. Request accepted for processing"
}
```

**Important**: Save the `CheckoutRequestID` - you'll need it to query the transaction status.

### 2. Query STK Push Status

Use this to check the status of an STK Push request:

```php
<?php
$response = $mpesa->STKStatus([
    'checkoutRequestID' => 'ws_CO_191220191020363925'
]);

echo json_encode($response);
```

#### Status Response

```json
{
    "ResponseCode": "0",
    "ResponseDescription": "The service request has been accepted successfully",
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResultCode": "0",
    "ResultDesc": "The service request is processed successfully."
}
```

## Callback Handling

When the customer completes or cancels the payment, Safaricom sends a POST request to your callback URL:

```php
<?php
// stk_callback.php - Handle STK Push callback

$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Sample successful callback
/*
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
                        "Value": 20191219102115
                    },
                    {
                        "Name": "PhoneNumber",
                        "Value": 254708374149
                    }
                ]
            }
        }
    }
}
*/

// Extract callback data
$callback = $data['Body']['stkCallback'];
$resultCode = $callback['ResultCode'];
$checkoutRequestID = $callback['CheckoutRequestID'];

if ($resultCode == 0) {
    // Payment successful
    $metadata = $callback['CallbackMetadata']['Item'];
    
    $paymentData = [];
    foreach ($metadata as $item) {
        $paymentData[$item['Name']] = $item['Value'] ?? null;
    }
    
    $amount = $paymentData['Amount'];
    $mpesaReceiptNumber = $paymentData['MpesaReceiptNumber'];
    $transactionDate = $paymentData['TransactionDate'];
    $phoneNumber = $paymentData['PhoneNumber'];
    
    // Update your database
    updatePayment($checkoutRequestID, [
        'status' => 'completed',
        'mpesa_receipt' => $mpesaReceiptNumber,
        'amount' => $amount,
        'phone' => $phoneNumber,
        'transaction_date' => $transactionDate
    ]);
    
    // Send confirmation to customer
    sendConfirmation($phoneNumber, $mpesaReceiptNumber, $amount);
    
} else {
    // Payment failed or cancelled
    $errorMessage = $callback['ResultDesc'];
    
    updatePayment($checkoutRequestID, [
        'status' => 'failed',
        'error_message' => $errorMessage
    ]);
    
    logError("STK Push failed: $errorMessage");
}

// Always return a response
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
```

## Result Codes

| Code | Description |
|------|-------------|
| 0 | Success |
| 1 | Insufficient Funds |
| 1032 | Request cancelled by user |
| 1037 | Timeout - User didn't enter PIN |
| 2001 | Wrong PIN entered |
| 1 | The balance is insufficient for the transaction |
| 17 | System internal error |

## Transaction Date Format

The `TransactionDate` is returned in format: `YYYYMMDDHHmmss`

Example: `20191219102115` = 2019-12-19 10:21:15

```php
function parseTransactionDate($dateString) {
    return DateTime::createFromFormat('YmdHis', $dateString);
}
```

## Complete Implementation Example

```php
<?php
// payment.php - Complete STK Push implementation

require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

class STKPayment {
    private $mpesa;
    private $db;
    
    public function __construct() {
        $this->mpesa = new Mpesa();
        $this->db = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');
    }
    
    public function initiatePayment($orderID, $amount, $phoneNumber) {
        try {
            // Initiate STK Push
            $response = $this->mpesa->STKPush([
                'amount' => $amount,
                'phoneNumber' => $phoneNumber,
                'accountReference' => "ORDER-$orderID",
                'transactionDesc' => "Payment for Order $orderID"
            ]);
            
            // Save to database
            $stmt = $this->db->prepare("
                INSERT INTO payments (
                    order_id, checkout_request_id, merchant_request_id, 
                    amount, phone_number, status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $orderID,
                $response->CheckoutRequestID,
                $response->MerchantRequestID,
                $amount,
                $phoneNumber
            ]);
            
            return [
                'success' => true,
                'checkoutRequestID' => $response->CheckoutRequestID,
                'message' => 'Please enter your M-Pesa PIN on your phone'
            ];
            
        } catch(\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage()
            ];
        }
    }
    
    public function checkStatus($checkoutRequestID) {
        try {
            $response = $this->mpesa->STKStatus([
                'checkoutRequestID' => $checkoutRequestID
            ]);
            
            return $response;
        } catch(\Exception $e) {
            return null;
        }
    }
    
    public function handleCallback($callbackData) {
        $callback = $callbackData['Body']['stkCallback'];
        $checkoutRequestID = $callback['CheckoutRequestID'];
        $resultCode = $callback['ResultCode'];
        
        if ($resultCode == 0) {
            // Success
            $metadata = $callback['CallbackMetadata']['Item'];
            $paymentData = [];
            
            foreach ($metadata as $item) {
                $paymentData[$item['Name']] = $item['Value'] ?? null;
            }
            
            $stmt = $this->db->prepare("
                UPDATE payments 
                SET status = 'completed',
                    mpesa_receipt = ?,
                    transaction_date = ?,
                    updated_at = NOW()
                WHERE checkout_request_id = ?
            ");
            
            $stmt->execute([
                $paymentData['MpesaReceiptNumber'],
                $paymentData['TransactionDate'],
                $checkoutRequestID
            ]);
            
        } else {
            // Failed
            $stmt = $this->db->prepare("
                UPDATE payments 
                SET status = 'failed',
                    error_message = ?,
                    updated_at = NOW()
                WHERE checkout_request_id = ?
            ");
            
            $stmt->execute([
                $callback['ResultDesc'],
                $checkoutRequestID
            ]);
        }
    }
}

// Usage
$payment = new STKPayment();

// Initiate payment
$result = $payment->initiatePayment(12345, 1000, '254712345678');
echo json_encode($result);
```

## Testing in Sandbox

When testing in sandbox mode:
- Set `'is_sandbox' => true` in your config
- Use the test credentials provided by Safaricom
- Use test phone numbers from Safaricom sandbox
- Your callback URL must be publicly accessible (use ngrok for local testing)

### Using ngrok for Local Testing

```bash
# Start your local server
php -S localhost:8000

# In another terminal, start ngrok
ngrok http 8000

# Update your callback URL to use the ngrok URL
# Example: https://abc123.ngrok.io/stk_callback.php
```

## Known Issues

1. **STK DS Timeout**: Some SIM cards are not yet supported. Requests to these numbers will fail with "STK DS timeout"
2. **Multiple Requests**: Making multiple subsequent requests to the same phone number can cause the initial request to timeout
3. **PIN Entry Time**: Users have approximately 60 seconds to enter their PIN
4. **Callback Delays**: Callbacks may take 5-30 seconds to arrive
5. **Account Reference Limit**: Maximum 12 characters for account reference

## Best Practices

1. **Save CheckoutRequestID**: Always save this for status queries and reconciliation
2. **Implement Callbacks**: Don't rely solely on status queries - implement proper callback handling
3. **User Feedback**: Show clear instructions to users about entering their PIN
4. **Timeout Handling**: Implement timeout handling (60-90 seconds)
5. **Status Polling**: If callback doesn't arrive, poll status every 5-10 seconds
6. **Idempotency**: Check for duplicate requests before initiating new STK Push
7. **Error Messages**: Show user-friendly error messages
8. **Phone Validation**: Validate phone numbers before initiating payment
9. **Amount Validation**: Ensure amount is within acceptable limits
10. **Reconciliation**: Regularly reconcile payments using M-Pesa receipts

## Difference Between STK Push and C2B

| Feature | STK Push (LNMO) | C2B |
|---------|-----------------|-----|
| Initiated By | Merchant | Customer |
| User Experience | Popup on phone | Customer goes to M-Pesa menu |
| Best For | E-commerce, online payments | Bill payments, donations |
| Setup Complexity | Simple | Requires URL registration |
| Validation | Not available | Optional validation |

## Use Cases

1. **E-commerce**: Online shopping checkout
2. **Service Payments**: Subscription payments, utility bills
3. **Event Tickets**: Concert, movie, or event ticket purchases
4. **Donations**: Charity and fundraising
5. **Mobile Apps**: In-app purchases
6. **Ride Hailing**: Taxi and delivery services
7. **Food Delivery**: Restaurant orders

## Additional Resources

- [Official Safaricom Documentation](https://developer.safaricom.co.ke/lipa-na-m-pesa-online/apis/post/stkpush/v1/processrequest)
- [STK Query Documentation](https://developer.safaricom.co.ke/lipa-na-m-pesa-online/apis/post/stkpushquery/v1/query)
- [C2B API](C2B.md) - For customer-initiated payments
- [Transaction Status API](TransactionStatus.md) - Query transaction status
- [Reversal API](Reversal.md) - Reverse erroneous transactions
