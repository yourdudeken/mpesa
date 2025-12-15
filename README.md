# M-Pesa API Package

[![Build Status](https://travis-ci.org/yourdudeken/mpesa.svg?branch=master)](https://travis-ci.org/yourdudeken/mpesa)
[![Latest Stable Version](https://poser.pugx.org/yourdudeken/mpesa/v/stable.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![Latest Unstable Version](https://poser.pugx.org/yourdudeken/mpesa/v/unstable.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![License](https://poser.pugx.org/yourdudeken/mpesa/license.svg)](https://packagist.org/packages/yourdudeken/mpesa)

A comprehensive PHP package for integrating with Safaricom's M-Pesa DARAJA API. This package provides a simple, elegant interface for all M-Pesa API endpoints including STK Push, B2C, B2B, C2B, Account Balance, Transaction Status, and Reversals.

## Features

 **Complete API Coverage** - All M-Pesa DARAJA API endpoints including B2Pochi  
 **Easy Configuration** - Simple configuration file setup  
 **Composer Support** - Install via Composer or use standalone  
 **Separate Environments** - Independent production and sandbox directories  
 **Independent Testing** - Test each environment separately  
 **Well Documented** - Comprehensive documentation for each API  
 **Fully Tested** - Includes PHPUnit tests for all APIs  
 **REST API Wrapper** - Optional REST API with authentication and rate limiting  

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Available APIs](#available-apis)
- [Usage Examples](#usage-examples)
- [Documentation](#documentation)
- [Testing](#testing)
- [Support](#support)
- [Contributing](#contributing)
- [License](#license)

## Installation

### Requirements

- PHP 5.6.0 or higher
- Composer (optional)

### Option 1: Using Composer (Recommended)

Production

```bash
cd production

composer require yourdudeken/production/mpesa
```

Sandbox

```bash
cd sandbox

composer require yourdudeken/sandbox/mpesa
```

### Option 2: Without Composer

1. Download the source code as a ZIP file
2. Extract to your project directory
3. Include the autoloader:

```php
<?php
require "{PATH_TO_MPESA_FOLDER}/src/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;
```

## Quick Start

```php
<?php
require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    // Initiate STK Push payment
    $response = $mpesa->STKPush([
        'amount' => 100,
        'phoneNumber' => '254712345678',
        'accountReference' => 'ORDER-001',
        'transactionDesc' => 'Payment for goods'
    ]);
    
    echo json_encode($response);
    
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

## Configuration

### 1. Locate Configuration File

The main configuration file is at `src/config/mpesa.php`. You can also create a custom configuration array.

### 2. Update Credentials

Get your credentials from [Safaricom Developer Portal](https://developer.safaricom.co.ke/):

```php
'apps' => [
    'default' => [
        'consumer_key' => 'YOUR_CONSUMER_KEY',
        'consumer_secret' => 'YOUR_CONSUMER_SECRET',
    ],
],
```

### 3. Configure API Endpoints

Update the configuration for each API you plan to use:

- **Lipa Na M-Pesa Online (STK Push)** - `lnmo` section
- **C2B** - `c2b` section
- **B2C** - `b2c` section
- **B2B** - `b2b` section
- **Account Balance** - `account_balance` section
- **Transaction Status** - `transaction_status` section
- **Reversal** - `reversal` section

### 4. Environment Setup

```php
'apiUrl' => 'https://sandbox.safaricom.co.ke/',  // Sandbox
// 'apiUrl' => 'https://api.safaricom.co.ke/',   // Production

'is_sandbox' => true,  // Set to false for production
```

**For detailed setup instructions, see [SETUP.md](SETUP.md)**

## Available APIs

This package supports all M-Pesa DARAJA API endpoints:

| API | Method | Description | Documentation |
|-----|--------|-------------|---------------|
| **Lipa Na M-Pesa Online** | `STKPush()` | Request payment from customers (STK Push) | [View Docs](docs/LipaNaMpesaOnline.md) |
| **STK Query** | `STKStatus()` | Check status of STK Push request | [View Docs](docs/LipaNaMpesaOnline.md#2-query-stk-push-status) |
| **C2B Register** | `C2BRegister()` | Register validation and confirmation URLs | [View Docs](docs/C2B.md) |
| **C2B Simulate** | `C2BSimulate()` | Simulate C2B payment (testing) | [View Docs](docs/C2B.md#2-simulate-c2b-payment-testing-only) |
| **B2C** | `B2C()` | Send money to customers | [View Docs](docs/B2C.md) |
| **B2B** | `B2B()` | Transfer funds between businesses | [View Docs](docs/B2B.md) |
| **B2Pochi** | `B2Pochi()` | Send money to customer Pochi savings accounts | [View Docs](docs/B2Pochi.md) |
| **Account Balance** | `accountBalance()` | Query account balance | [View Docs](docs/AccountBalance.md) |
| **Transaction Status** | `transactionStatus()` | Check transaction status | [View Docs](docs/TransactionStatus.md) |
| **Reversal** | `reversal()` | Reverse a transaction | [View Docs](docs/Reversal.md) |

## Usage Examples

### 1. STK Push (Lipa Na M-Pesa Online)

Request payment from a customer:

```php
$response = $mpesa->STKPush([
    'amount' => 100,
    'phoneNumber' => '254712345678',
    'accountReference' => 'ORDER-12345',
    'transactionDesc' => 'Payment for Order 12345'
]);

// Save the CheckoutRequestID for status queries
$checkoutRequestID = $response->CheckoutRequestID;
```

### 2. Check STK Push Status

```php
$response = $mpesa->STKStatus([
    'checkoutRequestID' => 'ws_CO_191220191020363925'
]);
```

### 3. B2C Payment

Send money to a customer:

```php
$response = $mpesa->B2C([
    'amount' => 500,
    'partyB' => '254712345678',
    'remarks' => 'Salary payment',
    'occasion' => 'Monthly salary'
]);
```

### 4. B2B Payment

Transfer funds to another business:

```php
$response = $mpesa->B2B([
    'amount' => 1000,
    'partyB' => '600000',  // Receiver's paybill
    'accountReference' => 'INV-2024-001',
    'remarks' => 'Payment for invoice'
]);
```

### 5. B2Pochi Payment

Send money to a customer's Pochi savings account:

```php
$response = $mpesa->B2Pochi([
    'OriginatorConversationID' => 'B2P_' . uniqid(),
    'amount' => 1000,
    'partyB' => '254712345678',
    'remarks' => 'Monthly savings deposit',
    'occasion' => 'Savings program'
]);
```

### 6. C2B Registration

Register your callback URLs:

```php
$response = $mpesa->C2BRegister([
    'confirmationURL' => 'https://yourdomain.com/api/mpesa/c2b/confirmation',
    'validationURL' => 'https://yourdomain.com/api/mpesa/c2b/validation',
    'responseType' => 'Completed'
]);
```

### 7. Account Balance

Check your M-Pesa account balance:

```php
$response = $mpesa->accountBalance([
    'remarks' => 'Daily balance check',
    'identifierType' => 4  // 4 for Paybill
]);
```

### 8. Transaction Status

Query the status of a transaction:

```php
$response = $mpesa->transactionStatus([
    'transactionID' => 'LGR019G3J2',
    'identifierType' => 4,
    'remarks' => 'Checking transaction status'
]);
```

### 9. Reversal

Reverse an erroneous transaction:

```php
$response = $mpesa->reversal([
    'transactionID' => 'LGR019G3J2',
    'amount' => 100,
    'recieverIdentifierType' => 4,
    'remarks' => 'Reversing erroneous payment'
]);
```

## Documentation

Comprehensive documentation is available for each API:

### API Documentation

- **[Lipa Na M-Pesa Online (STK Push)](docs/LipaNaMpesaOnline.md)** - Request payments from customers
- **[C2B (Customer to Business)](docs/C2B.md)** - Receive payments from customers
- **[B2C (Business to Customer)](docs/B2C.md)** - Send money to customers
- **[B2B (Business to Business)](docs/B2B.md)** - Transfer funds between businesses
- **[B2Pochi (Business to Pochi)](docs/B2Pochi.md)** - Send money to customer Pochi savings accounts
- **[Account Balance](docs/AccountBalance.md)** - Query account balance
- **[Transaction Status](docs/TransactionStatus.md)** - Check transaction status
- **[Reversal](docs/Reversal.md)** - Reverse transactions

### Setup & Testing

- **[SETUP.md](SETUP.md)** - Complete setup and installation guide
- **[TESTING.md](TESTING.md)** - Testing quick reference guide
- **[production/README.md](production/README.md)** - Production environment guide
- **[sandbox/README.md](sandbox/README.md)** - Sandbox environment guide

Each documentation file includes:
- Configuration instructions
- Usage examples
- Callback handling
- Response codes
- Best practices
- Common issues and solutions

## Testing

This package includes **separate testing environments** for production and sandbox, each with independent dependencies and configurations.

### Production Environment Testing

```bash
cd production

# Install dependencies
composer install

# Run all tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/Unit/STKPushTest.php
```

### Sandbox Environment Testing

```bash
cd sandbox

# Install dependencies
composer install

# Run all tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/Unit/STKPushTest.php
```

### Test Coverage

Both environments include comprehensive unit tests for:
- **Authentication** - OAuth token generation and validation
- **STK Push** - Lipa Na M-Pesa Online payment requests
- **STK Status Query** - Payment status verification
- **C2B Registration** - Customer to Business URL registration
- **B2C Payments** - Business to Customer transactions
- **B2B Payments** - Business to Business transfers
- **Account Balance** - Balance inquiry
- **Transaction Status** - Transaction verification
- **Reversals** - Transaction reversal operations

### Benefits of Separate Test Environments

-  **Isolation** - Each environment has its own dependencies
-  **Independent Testing** - Test sandbox and production separately
-  **No Conflicts** - Avoid configuration conflicts
-  **Easy Switching** - Simply change directories

For detailed testing instructions, see [TESTING.md](TESTING.md)

## Callback Handling

All asynchronous APIs (STK Push, B2C, B2B, etc.) send callbacks to your registered URLs. Here's a basic callback handler:

```php
<?php
// callback.php
$response = file_get_contents('php://input');
$data = json_decode($response, true);

// Process the callback
if ($data['ResultCode'] == 0) {
    // Success - update your database
    $transactionID = $data['TransactionID'];
    // ... your logic here
} else {
    // Failed - log the error
    $errorMessage = $data['ResultDesc'];
    // ... your error handling
}

// Always acknowledge receipt
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
```

**Important**: Your callback URLs must be:
- Publicly accessible via HTTPS (in production)
- Able to respond within 30 seconds
- Return a proper acknowledgment response

## Environment-Specific Notes

This package provides **separate directories** for production and sandbox environments, each with independent configurations and testing.

### Sandbox Environment (`/sandbox`)

The sandbox directory is a complete, isolated environment for testing:

```bash
cd sandbox
composer install
vendor/bin/phpunit
```

**Configuration:**
- Set `'is_sandbox' => true` in `src/config/mpesa.php`
- Use sandbox credentials from [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
- API URL: `https://sandbox.safaricom.co.ke/`
- Use test phone numbers provided by Safaricom
- See [sandbox/README.md](sandbox/README.md) for details

### Production Environment (`/production`)

The production directory is a complete, isolated environment for live operations:

```bash
cd production
composer install
vendor/bin/phpunit
```

**Configuration:**
- Set `'is_sandbox' => false` in `src/config/mpesa.php`
- Use production credentials
- API URL: `https://api.safaricom.co.ke/`
- Ensure your server IP is whitelisted by Safaricom
- Use HTTPS for all callback URLs
- See [production/README.md](production/README.md) for details

### Why Separate Environments?

- **No Configuration Conflicts** - Each environment has its own settings
- **Independent Dependencies** - Separate vendor directories
- **Easy Testing** - Test each environment independently
- **Production Safety** - Sandbox changes don't affect production

## Best Practices

1. **Security**
   - Never commit credentials to version control
   - Use environment variables for sensitive data
   - Validate all callbacks are from Safaricom IPs

2. **Error Handling**
   - Always wrap API calls in try-catch blocks
   - Log all errors for debugging
   - Implement proper error messages for users

3. **Callbacks**
   - Implement robust callback handling
   - Use database transactions when updating records
   - Always acknowledge callbacks promptly

4. **Testing**
   - Test thoroughly in sandbox before going live
   - Use ngrok for local callback testing
   - Implement comprehensive logging

5. **Reconciliation**
   - Regularly reconcile transactions
   - Store all transaction IDs and receipts
   - Implement automated reconciliation processes

## Common Issues & Solutions

### 1. "Invalid Access Token"
**Solution**: Check your consumer key and secret. Ensure they match your environment (sandbox/production).

### 2. "STK DS Timeout"
**Solution**: Some SIM cards don't support STK Push. This is a Safaricom limitation.

### 3. "Callback Not Received"
**Solution**: 
- Ensure URL is publicly accessible
- Use HTTPS in production
- Check firewall settings
- Verify URL is correctly configured

### 4. "Wrong Credentials"
**Solution**: Ensure you're using sandbox credentials for sandbox and production credentials for production.

For more troubleshooting, see [SETUP.md](SETUP.md#troubleshooting).

## Support

Need help? Here are your options:

-  **Documentation**: Check the [docs](docs/) folder
-  **Issues**: Report bugs on [GitHub Issues](https://github.com/yourdudeken/mpesa/issues)
-  **Telegram**: Join our [Telegram group](https://t.me/yourdudeken)
-  **Email**: [kenmwendwamuthengi@gmail.com](mailto:kenmwendwamuthengi@gmail.com) or [ken@yourdudeken.com](mailto:ken@yourdudeken.com)
-  **Safaricom**: [developer.safaricom.co.ke](https://developer.safaricom.co.ke/)

## Contributing

Contributions are welcome! Here's how you can help:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`vendor/bin/phpunit`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

Please ensure:
- All tests pass
- Code follows PSR standards
- Documentation is updated
- Commit messages are clear

## Inspiration

This package was inspired by [SmoDav's M-Pesa package](https://github.com/SmoDav/mpesa).

## Contributors

Special thanks to all contributors who have helped improve this package!

[<img src="https://avatars1.githubusercontent.com/u/133342792?s=400&u=24e47804b187e651c75c3defc65930ef4e719b79&v=4" width="100px;"/><br /><sub>Kennedy Muthengi</sub>](mailto:kenmwendwamuthengi@gmail.com)

## Changelog

### Version 1.0
- Complete implementation of all M-Pesa DARAJA APIs
- Support for PHP 5.6+
- Comprehensive documentation
- Unit tests
- Sandbox and production support

## Roadmap

- [ ] PHP 8.x support
- [ ] Async/Promise support
- [ ] Webhook signature verification
- [ ] Rate limiting helpers
- [ ] Transaction reconciliation tools
- [ ] CLI tools for testing

## License

The M-Pesa Package is open-sourced software licensed under the [MIT license](LICENSE.txt).

## Disclaimer

This package is not officially affiliated with or endorsed by Safaricom PLC. M-Pesa is a registered trademark of Safaricom PLC.

---

**Made with love in Kenya**

If this package helped you, please star the repository!
