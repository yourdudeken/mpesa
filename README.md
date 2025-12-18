# M-PESA API Package

[![Build Status](https://travis-ci.org/yourdudeken/mpesa.svg?branch=main)](https://travis-ci.org/yourdudeken/mpesa)
[![Latest Stable Version](https://poser.pugx.org/yourdudeken/mpesa/v/stable.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![Latest Unstable Version](https://poser.pugx.org/yourdudeken/mpesa/v/unstable.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![License](https://poser.pugx.org/yourdudeken/mpesa/license.svg)](https://packagist.org/packages/yourdudeken/mpesa)

A comprehensive PHP package for integrating with Safaricom's M-Pesa DARAJA API. This package provides a simple, elegant interface for all M-Pesa API endpoints including STK Push, B2C, B2B, C2B, and more.

## Features

 **Complete API Coverage** - All M-Pesa API endpoints supported  
 **Easy to Use** - Simple, intuitive interface  
 **Well Documented** - Comprehensive documentation for each endpoint  
 **Framework Agnostic** - Works with any PHP project  
 **Laravel Support** - Dedicated Laravel package available  
 **Sandbox & Production** - Easy switching between environments  
 **Automatic Token Management** - Handles authentication automatically  
 **Callback Handling** - Built-in support for M-Pesa callbacks  

## Requirements

- PHP 7.0 or higher
- cURL extension enabled
- OpenSSL extension enabled

## Installation

This package supports both Composer and manual installation.

### Using Composer (Recommended)

```bash
composer require yourdudeken/mpesa
```

### Manual Installation

1. Download the source code as a zip file
2. Extract to your project directory
3. Include the autoloader in your code:

```php
<?php
require "{PATHTOTHISLIBFOLDER}/src/autoload.php";

use Mpesa\Init as Mpesa;
```

### Laravel Users

For Laravel-specific implementation with facades and service providers, check out:  
[Laravel M-Pesa Package](https://github.com/yourdudeken/mpesalaravel)

## Quick Start

### Basic Usage

```php
<?php
require "vendor/autoload.php";

use Mpesa\Init as Mpesa;

// Initialize with default config
$mpesa = new Mpesa();

// Or pass custom configuration
$mpesa = new Mpesa([
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
    'is_sandbox' => true
]);

try {
    // Initiate STK Push
    $response = $mpesa->STKPush([
        'amount' => 100,
        'phoneNumber' => '254722000000',
        'accountReference' => 'INV-001',
        'transactionDesc' => 'Payment for invoice'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Configuration

The package comes with a structured configuration file for easy setup.

### Configuration File Location

```
src/config/mpesa.php
```

### Key Configuration Options

```php
return [
    // API Environment
    'apiUrl' => 'https://sandbox.safaricom.co.ke/',
    'is_sandbox' => true,
    
    // Credentials
    'apps' => [
        'default' => [
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ],
    ],
    
    // STK Push Configuration
    'lnmo' => [
        'short_code' => '174379',
        'passkey' => 'your_passkey',
        'callback' => 'https://yourdomain.com/callback',
    ],
    
    // B2C Configuration
    'b2c' => [
        'initiator_name' => 'testapi',
        'security_credential' => 'your_credential',
        'short_code' => '600000',
        'result_url' => 'https://yourdomain.com/result',
        'timeout_url' => 'https://yourdomain.com/timeout',
    ],
    
    // Additional configurations for B2B, C2B, etc.
];
```

### Environment Setup

1. **Sandbox (Testing)**
   - Set `is_sandbox` to `true`
   - Use sandbox credentials from [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
   - API URL: `https://sandbox.safaricom.co.ke/`

2. **Production (Live)**
   - Set `is_sandbox` to `false`
   - Use production credentials
   - API URL: `https://api.safaricom.co.ke/`

## Available Methods

### STK Push (Lipa na M-Pesa Online)
Initiate payment requests to customer phones:

```php
$mpesa->STKPush([
    'amount' => 100,
    'phoneNumber' => '254722000000',
    'accountReference' => 'ORDER-123',
    'transactionDesc' => 'Payment for order'
]);
```

### STK Status Query
Check the status of an STK Push request:

```php
$mpesa->STKStatus([
    'checkoutRequestID' => 'ws_CO_191220191020363925'
]);
```

### B2C (Business to Customer)
Send money to customer accounts:

```php
$mpesa->B2C([
    'amount' => 100,
    'partyB' => '254722000000',
    'remarks' => 'Salary payment',
    'occasion' => 'Monthly salary'
]);
```

### B2B (Business to Business)
Transfer funds between business accounts:

```php
$mpesa->B2B([
    'amount' => 1000,
    'partyB' => '600000',
    'accountReference' => 'INV-001',
    'remarks' => 'Payment for supplies'
]);
```

### B2Pochi (Business to Pochi)
Send money to customer Pochi savings accounts:

```php
$mpesa->B2Pochi([
    'amount' => 100,
    'partyB' => '254722000000',
    'remarks' => 'Savings deposit'
]);
```

### C2B Register
Register callback URLs for C2B payments:

```php
$mpesa->C2BRegister([
    'validationURL' => 'https://yourdomain.com/validate',
    'confirmationURL' => 'https://yourdomain.com/confirm',
    'responseType' => 'Completed'
]);
```

### C2B Simulate
Simulate C2B payments (sandbox only):

```php
$mpesa->C2BSimulate([
    'amount' => 100,
    'msisdn' => '254722000000',
    'billRefNumber' => 'INV-001'
]);
```

### Account Balance
Query account balance:

```php
$mpesa->accountBalance([
    'remarks' => 'Balance query'
]);
```

### Transaction Status
Check transaction status:

```php
$mpesa->transactionStatus([
    'transactionID' => 'NLJ7RT61SV',
    'remarks' => 'Status check'
]);
```

### Reversal
Reverse erroneous transactions:

```php
$mpesa->reversal([
    'transactionID' => 'NLJ7RT61SV',
    'amount' => 100,
    'receiverParty' => '600000',
    'remarks' => 'Reversing duplicate payment'
]);
```

## API Documentation

Comprehensive documentation for each API endpoint is available in the `docs` folder:

### 1. [Lipa na M-Pesa Online (STK Push)](docs/LipaNaMpesaOnline.md)
Initiate payment requests directly to customer phones. Customers authorize payments by entering their M-Pesa PIN.

**Use Cases:** E-commerce checkout, bill payments, donations, subscriptions

### 2. [Lipa na M-Pesa Online Query](docs/LipaNaMpesaOnlineQuery.md)
Query the status of STK Push requests to verify payment completion.

**Use Cases:** Payment verification, transaction reconciliation

### 3. [C2B (Customer to Business)](docs/C2B.md)
Receive real-time notifications when customers make payments to your Till or Paybill number.

**Use Cases:** Payment notifications, automatic reconciliation, real-time payment processing

### 4. [B2C (Business to Customer)](docs/B2C.md)
Send money from your business account to customer M-Pesa accounts.

**Use Cases:** Salary payments, refunds, promotions, rewards, withdrawals

### 5. [B2B (Business to Business)](docs/B2B.md)
Transfer funds between business accounts (PayBill to PayBill or Till to Till).

**Use Cases:** Supplier payments, business settlements, inter-branch transfers

### 6. [B2Pochi (Business to Pochi)](docs/B2Pochi.md)
Send money directly to customer Pochi savings accounts.

**Use Cases:** Savings programs, rewards, targeted savings deposits

### 7. [Transaction Status](docs/TransactionStatus.md)
Query the status of any M-Pesa transaction for verification and reconciliation.

**Use Cases:** Dispute resolution, transaction verification, reconciliation

### 8. [Reversal](docs/Reversal.md)
Reverse erroneous M-Pesa transactions.

**Use Cases:** Correcting payment errors, duplicate payment reversals, refunds

### 9. [Account Balance](docs/AccountBalance.md)
Query your business account balance on demand.

**Use Cases:** Balance monitoring, pre-transaction validation, financial reporting

## Code Examples

### Complete STK Push Example

```php
<?php
require "vendor/autoload.php";

use Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    // Initiate payment
    $response = $mpesa->STKPush([
        'amount' => 100,
        'phoneNumber' => '254722000000',
        'accountReference' => 'ORDER-12345',
        'transactionDesc' => 'Payment for order #12345',
        'callBackURL' => 'https://yourdomain.com/mpesa/callback'
    ]);
    
    // Store the CheckoutRequestID for status queries
    $checkoutRequestID = $response->CheckoutRequestID;
    
    // Return response to user
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Payment request sent. Please check your phone.',
        'checkoutRequestID' => $checkoutRequestID
    ]);
    
} catch(\Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Payment request failed: ' . $e->getMessage()
    ]);
}
```

### Handling STK Push Callback

```php
<?php
require "vendor/autoload.php";

// Get callback data
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
    
    foreach ($metadata as $item) {
        if ($item['Name'] == 'Amount') {
            $amount = $item['Value'];
        }
        if ($item['Name'] == 'MpesaReceiptNumber') {
            $mpesaReceiptNumber = $item['Value'];
        }
        if ($item['Name'] == 'PhoneNumber') {
            $phoneNumber = $item['Value'];
        }
    }
    
    // Update your database
    // Mark order as paid, send confirmation, etc.
    
    error_log("Payment successful: $mpesaReceiptNumber - Amount: $amount");
    
} else {
    // Payment failed
    $resultDesc = $callback['Body']['stkCallback']['ResultDesc'];
    error_log("Payment failed: $resultDesc");
}

// Always respond to Safaricom
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
```

### B2C Payment Example

```php
<?php
require "vendor/autoload.php";

use Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->B2C([
        'amount' => 500,
        'partyB' => '254722000000',
        'remarks' => 'December salary payment',
        'occasion' => 'Salary',
        'commandID' => 'SalaryPayment',
        'resultURL' => 'https://yourdomain.com/mpesa/b2c/result',
        'queueTimeOutURL' => 'https://yourdomain.com/mpesa/b2c/timeout'
    ]);
    
    echo json_encode($response);
    
} catch(\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Testing

The package includes comprehensive unit tests. Run tests using:

```bash
vendor/bin/phpunit
```

## Troubleshooting

### Common Issues

**Issue:** "Invalid Access Token"  
**Solution:** Check your consumer key and secret. Ensure they match your environment (sandbox/production).

**Issue:** "STK DS timeout"  
**Solution:** Some SIM cards don't support STK Push. This is a Safaricom limitation.

**Issue:** Callback not received  
**Solution:** Ensure your callback URL is publicly accessible via HTTPS. Use tools like ngrok for local testing.

**Issue:** "Insufficient permissions"  
**Solution:** Verify your initiator credentials have the required permissions for the operation.

## Security Best Practices

1. **Never commit credentials** - Use environment variables for sensitive data
2. **Use HTTPS** - All callback URLs must use HTTPS in production
3. **Validate callbacks** - Verify callbacks are from Safaricom (IP whitelisting)
4. **Implement idempotency** - Handle duplicate callback notifications
5. **Secure your endpoints** - Protect callback URLs from unauthorized access
6. **Log everything** - Maintain audit trails of all transactions
7. **Handle errors gracefully** - Don't expose sensitive error details to users

## Support

Need help with integration?

-  Email: kenmwendwamuthengi@gmail.com
-  Telegram: [@yourdudeken](https://t.me/yourdudeken)
-  Documentation: [docs folder](docs/)
-  Issues: [GitHub Issues](https://github.com/yourdudeken/mpesa/issues)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Changelog

### Latest Updates
-  Added B2Pochi payment method support
-  Comprehensive documentation for all endpoints
-  Improved error handling
-  Enhanced callback processing examples
-  Updated configuration structure

## Inspiration

This package was inspired by the excellent work from [@SmoDav](https://github.com/SmoDav) on the [SmoDav/mpesa](https://github.com/SmoDav/mpesa) project.

## Contributors

Special thanks to all contributors who have helped improve this package:

[<img src="https://avatars1.githubusercontent.com/u/133342792?s=400&u=24e47804b187e651c75c3defc65930ef4e719b79&v=4" width="100px;"/><br /><sub>Kennedy Muthengi</sub>](mailto:kenmwendwamuthengi@gmail.com)

## License

The M-Pesa Package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

---

**Made with love in Kenya**
