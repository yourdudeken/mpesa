# C2B (Customer to Business) API

## Overview
The C2B API enables businesses to receive real-time notifications when customers make payments to their Till or Paybill numbers via the SIM card toolkit (STK). This is different from Lipa Na M-Pesa Online (STK Push) - C2B is for payments initiated by customers through their M-Pesa menu.

## Prerequisites
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to C2B and taken the necessary steps
3. You have successfully acquired production credentials from Safaricom. If not, you can use the sandbox credentials that come preconfigured on installation
4. You have configured the C2B section in `src/config/mpesa.php`
5. You have publicly accessible URLs for validation and confirmation callbacks

## Configuration

Update the C2B configuration in `src/config/mpesa.php`:

```php
'c2b' => [
    'confirmation_url' => 'https://yourdomain.com/api/mpesa/c2b/confirmation',
    'validation_url' => 'https://yourdomain.com/api/mpesa/c2b/validation',
    'on_timeout' => 'Completed',  // or 'Cancelled'
    'short_code' => 'YOUR_SHORTCODE',
    'test_phone_number' => '254708374149',
    'default_command_id' => 'CustomerPayBillOnline'
],
```

### Configuration Parameters

- **confirmation_url**: URL to receive payment confirmation notifications
- **validation_url**: URL to validate payments before processing (optional)
- **on_timeout**: Action to take when validation times out
  - `Completed` - Accept the payment (default)
  - `Cancelled` - Reject the payment
- **short_code**: Your business paybill or till number
- **test_phone_number**: Phone number for sandbox testing
- **default_command_id**: Transaction type
  - `CustomerPayBillOnline` - For paybill payments (default)
  - `CustomerBuyGoodsOnline` - For till/buy goods payments

## C2B Flow

The C2B API works in two steps:

### Step 1: Register URLs

Before you can receive C2B notifications, you must register your callback URLs with Safaricom.

### Step 2: Receive Callbacks

Once registered, Safaricom will send payment notifications to your URLs whenever a customer pays.

## Usage

### 1. Register C2B URLs

You only need to do this once, or whenever you want to update your callback URLs:

```php
<?php
require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->C2BRegister([
        'confirmationURL' => 'https://yourdomain.com/api/mpesa/c2b/confirmation',
        'validationURL' => 'https://yourdomain.com/api/mpesa/c2b/validation',
        'responseType' => 'Completed'  // or 'Cancelled'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

#### Required Parameters for Registration

- **confirmationURL**: URL to receive payment confirmations
- **validationURL**: URL to validate payments (can be same as confirmation URL)
- **responseType**: What to do when validation times out

#### Optional Parameters

- **shortCode**: Override the default short code from config

### 2. Simulate C2B Payment (Testing Only)

For testing in sandbox, you can simulate a customer payment:

```php
$response = $mpesa->C2BSimulate([
    'amount' => 100,
    'billRefNumber' => 'ACCOUNT-001',  // Customer account number
    'msisdn' => '254712345678'  // Customer phone number (optional in sandbox)
]);
```

#### Required Parameters for Simulation

- **amount**: The amount being paid
- **billRefNumber**: The account number/reference

#### Optional Parameters

- **commandID**: Override the default command ID
- **msisdn**: Customer phone number (auto-filled in sandbox)

## Callback Handling

### Validation Callback

When a customer initiates a payment, Safaricom first calls your `validation_url` to check if you want to accept or reject the payment:

```php
<?php
// validation.php - Handle C2B validation callback

$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Sample validation request
/*
{
    "TransactionType": "Pay Bill",
    "TransID": "LGR019G3J2",
    "TransTime": "20191122063845",
    "TransAmount": "10.00",
    "BusinessShortCode": "600638",
    "BillRefNumber": "account001",
    "InvoiceNumber": "",
    "OrgAccountBalance": "",
    "ThirdPartyTransID": "",
    "MSISDN": "254708374149",
    "FirstName": "John",
    "MiddleName": "",
    "LastName": "Doe"
}
*/

// Validate the payment
$billRefNumber = $data['BillRefNumber'];
$amount = $data['TransAmount'];

// Check if account exists, amount is correct, etc.
$isValid = validateAccount($billRefNumber, $amount);

if ($isValid) {
    // Accept the payment
    $result = [
        'ResultCode' => 0,
        'ResultDesc' => 'Accepted'
    ];
} else {
    // Reject the payment
    $result = [
        'ResultCode' => 'C2B00011',
        'ResultDesc' => 'Invalid Account'
    ];
}

header('Content-Type: application/json');
echo json_encode($result);

function validateAccount($account, $amount) {
    // Your validation logic here
    // Check if account exists in your database
    // Verify amount is correct, etc.
    return true;
}
```

### Confirmation Callback

After validation (or if validation is skipped), Safaricom sends the payment confirmation to your `confirmation_url`:

```php
<?php
// confirmation.php - Handle C2B confirmation callback

$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Sample confirmation request (same structure as validation)
/*
{
    "TransactionType": "Pay Bill",
    "TransID": "LGR019G3J2",
    "TransTime": "20191122063845",
    "TransAmount": "10.00",
    "BusinessShortCode": "600638",
    "BillRefNumber": "account001",
    "InvoiceNumber": "",
    "OrgAccountBalance": "49197.00",
    "ThirdPartyTransID": "",
    "MSISDN": "254708374149",
    "FirstName": "John",
    "MiddleName": "",
    "LastName": "Doe"
}
*/

// Process the payment
$transactionID = $data['TransID'];
$amount = $data['TransAmount'];
$accountNumber = $data['BillRefNumber'];
$phoneNumber = $data['MSISDN'];
$customerName = $data['FirstName'] . ' ' . $data['LastName'];

// Save to database
savePayment([
    'transaction_id' => $transactionID,
    'amount' => $amount,
    'account_number' => $accountNumber,
    'phone_number' => $phoneNumber,
    'customer_name' => $customerName,
    'transaction_time' => $data['TransTime']
]);

// Send confirmation SMS to customer
sendSMS($phoneNumber, "Payment of KES $amount received. Thank you!");

// Return success response
$result = [
    'ResultCode' => 0,
    'ResultDesc' => 'Accepted'
];

header('Content-Type: application/json');
echo json_encode($result);

function savePayment($data) {
    // Save payment to your database
}

function sendSMS($phone, $message) {
    // Send SMS notification
}
```

## Validation Response Codes

When responding to validation requests, use these codes:

| Code | Description |
|------|-------------|
| 0 | Accept the payment |
| C2B00011 | Invalid Account Number |
| C2B00012 | Invalid Amount |
| C2B00013 | Invalid MSISDN |
| C2B00014 | Invalid Bill Reference Number |
| C2B00015 | Invalid Transaction ID |
| C2B00016 | Other Error |

## Transaction Types

| Type | Description |
|------|-------------|
| Pay Bill | Payment to a paybill number |
| Buy Goods | Payment to a till number |

## Testing in Sandbox

When testing in sandbox mode:
1. Set `'is_sandbox' => true` in your config
2. Register your URLs using `C2BRegister()`
3. Use `C2BSimulate()` to simulate customer payments
4. Your callback URLs must be publicly accessible (use ngrok for local testing)

### Using ngrok for Local Testing

```bash
# Install ngrok
# Start your local server on port 8000
php -S localhost:8000

# In another terminal, start ngrok
ngrok http 8000

# Use the ngrok URL in your C2B configuration
# Example: https://abc123.ngrok.io/api/mpesa/c2b/confirmation
```

## Use Cases

1. **Bill Payments**: Customers pay utility bills, school fees, etc.
2. **Account Top-ups**: Customers add money to their accounts
3. **Subscription Payments**: Recurring subscription payments
4. **Donations**: Receive donations from customers
5. **Retail Payments**: Point of sale payments via till numbers

## Known Issues

1. **URL Accessibility**: Your callback URLs must be publicly accessible via HTTPS
2. **Response Time**: Validation must respond within 30 seconds
3. **Duplicate Transactions**: Implement idempotency checks using TransID
4. **Account Reference**: BillRefNumber is limited to 20 characters

## Best Practices

1. **HTTPS Only**: Always use HTTPS for callback URLs
2. **Fast Validation**: Keep validation logic simple and fast (< 30 seconds)
3. **Idempotency**: Check TransID to prevent processing duplicate payments
4. **Error Handling**: Implement proper error handling and logging
5. **Database Logging**: Log all callbacks for audit and reconciliation
6. **Customer Notifications**: Send SMS/email confirmations to customers
7. **Reconciliation**: Regularly reconcile payments with M-Pesa statements
8. **Timeout Handling**: Set appropriate timeout behavior (Completed vs Cancelled)
9. **Account Validation**: Validate account numbers before accepting payments
10. **Security**: Validate that requests are coming from Safaricom IPs

## Difference Between C2B and STK Push

| Feature | C2B | STK Push (Lipa Na M-Pesa Online) |
|---------|-----|----------------------------------|
| Initiated By | Customer | Merchant/Business |
| User Action | Customer goes to M-Pesa menu | Customer enters PIN on popup |
| Use Case | Bill payments, donations | E-commerce, service payments |
| Validation | Optional | Not available |
| Setup | Register URLs once | No registration needed |

## Additional Resources

- [Official Safaricom C2B Documentation](https://developer.safaricom.co.ke/c2b/apis/post/registerurl)
- [Lipa Na M-Pesa Online (STK Push)](LipaNaMpesaOnline.md) - For merchant-initiated payments
- [Transaction Status API](TransactionStatus.md) - Query transaction status
- [Reversal API](Reversal.md) - Reverse erroneous transactions
