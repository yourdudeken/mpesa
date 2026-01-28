# M-PESA API Package

[![CI](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml)
[![Release](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml)
[![Latest Stable Version](https://poser.pugx.org/yourdudeken/mpesa/v/stable.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![Total Downloads](https://poser.pugx.org/yourdudeken/mpesa/downloads)](https://packagist.org/packages/yourdudeken/mpesa)
[![License](https://poser.pugx.org/yourdudeken/mpesa/license.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![PHP Version](https://img.shields.io/packagist/php-v/yourdudeken/mpesa.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![codecov](https://codecov.io/gh/yourdudeken/mpesa/branch/main/graph/badge.svg)](https://codecov.io/gh/yourdudeken/mpesa)

A comprehensive PHP package for integrating with Safaricom's M-Pesa DARAJA API. This package provides a simple, "Identity-First" interface for all M-Pesa API endpoints including STK Push, B2C, B2B, C2B, and more.

## Features

- **Identity-First Architecture** - Explicit control over credentials and callbacks per request or configuration group.
- **Complete API Coverage** - All 10 M-Pesa API endpoints supported including B2Pochi and STK Status.
- **Service Properties** - Direct, type-hinted access to services (e.g. $mpesa->stk, $mpesa->b2c).
- **Smart Validation** - Automatic metadata sanitization, field trimming, and parameter normalization.
- **Universal Callbacks** - Simplified logic for handling STK, C2B, and result notifications.
- **Modern PHP** - Built for PHP 8.0+ with strict typing and dependency injection.
- **Framework Agnostic** - Works with any PHP project (Laravel, Symfony, or pure PHP).
- **Secure by Default** - SSL peer verification enabled with automatic certificate management.

## Requirements

- PHP 8.0 or higher
- cURL extension enabled
- OpenSSL extension enabled
- JSON extension enabled

## Installation

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

use Yourdudeken\Mpesa\Init as Mpesa;
```

## Quick Start

### Initializing the SDK

```php
<?php
require "vendor/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa([
    'is_sandbox' => true,
    
    // Auth: Required for ALL requests
    'auth' => [
        'consumer_key'    => 'YOUR_CONSUMER_KEY',
        'consumer_secret' => 'YOUR_CONSUMER_SECRET',
    ],

    // Initiator: Required for Business APIs (B2C, B2B, etc.)
    'initiator' => [
        'name'     => 'YOUR_INITIATOR_NAME',
        'password' => 'YOUR_INITIATOR_PASSWORD',
    ],

    // Service Defaults (Optional)
    'stk' => [
        'short_code' => '174379',
        'passkey'    => 'YOUR_LNMO_PASSKEY',
    ]
]);

try {
    // Initiate STK Push
    $response = $mpesa->stk->submit([
        'amount'       => 10,
        'phone'        => '2547XXXXXXXX',
        'reference'    => 'Order-001',
        'description'  => 'Payment for Goods',
        'callback_url' => 'https://example.com/mpesa/callback'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Configuration

The package uses a structured, grouped configuration for better organization.

### Configuration File Location

```
src/config/mpesa.php
```

### Key Configuration Groups

```php
return [
    'is_sandbox' => true,

    // OAuth Credentials
    'auth' => [
        'consumer_key'    => 'your_key',
        'consumer_secret' => 'your_secret',
    ],

    // Business Initiator (B2C, B2B, Reversal)
    'initiator' => [
        'name'     => 'testapi',
        'password' => 'Safaricom123!!',
    ],
    
    // Service Specific Defaults
    'stk' => [
        'short_code'   => '174379',
        'passkey'      => 'bfb27...',
        'callback_url' => 'https://api.com/mpesa/stk',
    ],
];
```

## Available Services

### STK Push (Lipa na M-Pesa Online)
```php
$mpesa->stk->submit([
    'amount'    => 100,
    'phone'     => '2547XXXXXXXX',
    'reference' => 'ORDER-123',
    'description' => 'Payment for order'
]);
```

### STK Status Query
```php
$mpesa->stkStatus->submit([
    'checkoutRequestID' => 'ws_CO_191220191020363925'
]);
```

### B2C (Business to Customer)
```php
$mpesa->b2c->submit([
    'amount'  => 500,
    'phone'   => '2547XXXXXXXX',
    'remarks' => 'Salary Payment'
]);
```

### B2B (Business to Business)
```php
$mpesa->b2b->submit([
    'amount'     => 1000,
    'short_code' => '600000',
    'remarks'    => 'Supplier Payment'
]);
```

### B2Pochi (Business to Pochi)
```php
$mpesa->b2pochi->submit([
    'amount'  => 100,
    'phone'   => '2547XXXXXXXX',
    'remarks' => 'Tip'
]);
```

### C2B Register
```php
$mpesa->c2b->submit([
    'confirmation_url' => 'https://domain.com/confirm',
    'validation_url'   => 'https://domain.com/validate'
]);
```

### C2B Simulate
```php
$mpesa->c2bSimulate->submit([
    'amount'  => 100,
    'phone'   => '2547XXXXXXXX',
    'bill_ref' => 'INV-001'
]);
```

### Account Balance
```php
$mpesa->balance->submit([
    'result_url' => 'https://domain.com/result'
]);
```

### Transaction Status
```php
$mpesa->status->submit([
    'transaction_id' => 'NLJ7RT61SV',
    'result_url'     => 'https://domain.com/result'
]);
```

### Reversal
```php
$mpesa->reversal->submit([
    'transaction_id' => 'NLJ7RT61SV',
    'amount'         => 100,
    'result_url'     => 'https://domain.com/result'
]);
```

## API Documentation

Detailed documentation for each endpoint is available in the docs folder:

1. [Lipa na M-Pesa Online (STK Push)](docs/LipaNaMpesaOnline.md)
2. [C2B (Customer to Business)](docs/C2B.md)
3. [B2C (Business to Customer)](docs/B2C.md)
4. [B2B (Business to Business)](docs/B2B.md)
5. [B2Pochi (Business to Pochi)](docs/B2Pochi.md)
6. [Transaction Status](docs/TransactionStatus.md)
7. [Reversal](docs/Reversal.md)
8. [Account Balance](docs/AccountBalance.md)

## Example Code

### Handling STK Push Callback

```php
<?php
// Get callback data
$callbackData = file_get_contents('php://input');
$callback = json_decode($callbackData, true);

// Extract result
$resultCode = $callback['Body']['stkCallback']['ResultCode'];

if ($resultCode == 0) {
    // Payment successful
    $metadata = $callback['Body']['stkCallback']['CallbackMetadata']['Item'];
    // Process items (Amount, MpesaReceiptNumber, etc.)
}

// Respond to Safaricom
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
```

## Testing

Run unit tests using:

```bash
vendor/bin/phpunit
```

## Security Best Practices

1. **Never commit credentials** - Use environment variables or secure vault.
2. **Use HTTPS** - All callback URLs must use HTTPS in production.
3. **Validate callbacks** - Verify payloads are from Safaricom.
4. **Logic Idempotency** - Handle duplicate callback notifications.
5. **Log Transactions** - Maintain local audit trails.

## Support

- Email: kenmwendwamuthengi@gmail.com
- Telegram: [@yourdudeken](https://t.me/yourdudeken)
- Issues: [GitHub Issues](https://github.com/yourdudeken/mpesa/issues)

## Inspiration
Inspired by the work of [@SmoDav](https://github.com/SmoDav) on the [mpesa](https://github.com/SmoDav/mpesa) project.

## License
Open-sourced under the [MIT license](http://opensource.org/licenses/MIT).

---

**Made in Kenya**
