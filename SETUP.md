# M-Pesa API Package - Setup Guide

This guide will help you set up and run the M-Pesa API package for development and testing.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Package](#running-the-package)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Troubleshooting](#troubleshooting)

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP**: Version 5.6.0 or higher (PHP 7.0+ recommended)
- **Composer**: PHP dependency manager ([Install Composer](https://getcomposer.org/download/))
- **Git**: For cloning the repository (optional if you already have the code)

### Verify Prerequisites

```bash
# Check PHP version
php -v

# Check Composer version
composer -V
```

## Installation

### Option 1: Using Composer (Recommended)

If you're using this package as a dependency in another project:

```bash
composer require yourdudeken/mpesa
```

### Option 2: Development Setup (For Contributing)

If you want to work on this package directly:

1. **Clone the repository** (if not already done):
   ```bash
   git clone https://github.com/yourdudeken/mpesa.git
   cd mpesa
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

   This will install:
   - PHPUnit (testing framework)
   - Mockery (mocking library for tests)
   - Autoload generator

### Option 3: Without Composer

If you prefer not to use Composer:

1. Download the source code as a ZIP file
2. Extract it to your desired location
3. Include the autoloader in your PHP files:
   ```php
   <?php
   require "{PATH_TO_MPESA_FOLDER}/src/autoload.php";
   
   use yourdudeken\Mpesa\Init as Mpesa;
   ```

## Configuration

The package uses a configuration file to manage API credentials and settings.

### 1. Locate the Configuration File

The main configuration file is located at:
```
src/config/mpesa.php
```

There's also an example configuration at:
```
example/config/mpesa.php
```

### 2. Configure API Credentials

Edit `src/config/mpesa.php` or create your own config file with the following settings:

#### Basic Settings

```php
'apiUrl' => 'https://sandbox.safaricom.co.ke/',  // Sandbox URL
'is_sandbox' => true,  // Set to false for production
```

#### API Credentials

Replace the default credentials with your own from [Safaricom Developer Portal](https://developer.safaricom.co.ke/):

```php
'apps' => [
    'default' => [
        'consumer_key' => 'YOUR_CONSUMER_KEY',
        'consumer_secret' => 'YOUR_CONSUMER_SECRET',
    ],
    'bulk' => [
        'consumer_key' => '',
        'consumer_secret' => '',
    ],
],
```

#### Configure Each API Endpoint

The config file includes settings for:
- **Lipa Na M-Pesa Online (STK Push)**: `lnmo` section
- **C2B (Customer to Business)**: `c2b` section
- **B2C (Business to Customer)**: `b2c` section
- **B2B (Business to Business)**: `b2b` section
- **B2Pochi (Business to Pochi)**: `b2pochi` section
- **Account Balance**: `account_balance` section
- **Transaction Status**: `transaction_status` section
- **Reversal**: `reversal` section

Update each section with your specific credentials and callback URLs.

### 3. Important Configuration Notes

- **Callback URLs**: Replace empty strings with your actual callback endpoints
- **Short Codes**: Use your registered paybill/till numbers
- **Security Credentials**: Update with your actual credentials from Safaricom
- **Passkey**: For STK Push, use the passkey provided by Safaricom

## Running the Package

### Basic Usage Example

Create a PHP file (e.g., `test.php`) in your project:

```php
<?php
require "vendor/autoload.php";  // If using Composer
// OR
// require "src/autoload.php";  // If not using Composer

use yourdudeken\Mpesa\Init as Mpesa;

// Initialize with default config
$mpesa = new Mpesa();

// OR pass custom config
// $mpesa = new Mpesa($customConfig);

try {
    // Example: STK Push
    $response = $mpesa->STKPush([
        'amount' => 10,
        'transactionDesc' => 'Payment for goods',
        'phoneNumber' => '254712345678',
    ]);
    
    echo json_encode($response);
    
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

### Run the Example

```bash
php test.php
```

### Using the Provided Example

The package includes an example file:

```bash
cd example
php mpesa.php
```

**Note**: Make sure to configure the credentials in `example/config/mpesa.php` first.

## Testing

The package uses PHPUnit for testing.

### Running All Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run with verbose output
vendor/bin/phpunit --verbose

# Run with code coverage (requires Xdebug)
vendor/bin/phpunit --coverage-html coverage
```

### Running Specific Tests

```bash
# Run a specific test file
vendor/bin/phpunit tests/Unit/STKPushTest.php

# Run a specific test method
vendor/bin/phpunit --filter testSTKPush
```

### Test Structure

Tests are organized in the `tests/` directory:
```
tests/
├── TestCase.php              # Base test case with mocks
└── Unit/
    ├── AuthenticatorTest.php
    ├── B2BTest.php
    ├── B2CTest.php
    ├── BalanceTest.php
    ├── C2BRegisterTest.php
    ├── CoreTest.php
    ├── ReversalTest.php
    ├── STKPushTest.php
    ├── STKStatusQueryTest.php
    └── TransactionStatusTest.php
```

### Understanding Test Results

- **Green/OK**: All tests passed
- **Red/FAILURES**: Some tests failed - check the output for details
- **Yellow/WARNINGS**: Tests passed but there are warnings

## Project Structure

```
mpesa/
├── .git/                     # Git repository
├── .gitignore               # Git ignore rules
├── .php_cs                  # PHP Code Sniffer config
├── .travis.yml              # Travis CI configuration
├── LICENSE.txt              # MIT License
├── README.md                # Project documentation
├── composer.json            # Composer dependencies
├── phpunit.xml              # PHPUnit configuration
├── docs/                    # API documentation
│   ├── AccountBalance.md
│   ├── B2B.md
│   ├── B2C.md
│   ├── B2Pochi.md
│   ├── C2B.md
│   ├── LipaNaMpesaOnline.md
│   ├── Reversal.md
│   └── TransactionStatus.md
├── example/                 # Example usage
│   ├── config/
│   │   └── mpesa.php       # Example config
│   └── mpesa.php           # Example implementation
├── src/                     # Source code
│   ├── Mpesa/              # Main package code
│   ├── config/
│   │   └── mpesa.php       # Default configuration
│   └── autoload.php        # Autoloader (for non-Composer usage)
└── tests/                   # Test suite
    ├── TestCase.php
    └── Unit/               # Unit tests
```

## Available APIs

The package supports all Safaricom M-Pesa DARAJA API endpoints:

1. **Lipa na M-Pesa Online (STK Push)** - Request payment from customers
   - Method: `$mpesa->STKPush([])`
   - [Documentation](docs/LipaNaMpesaOnline.md)

2. **Lipa na M-Pesa Query** - Check STK Push status
   - Method: `$mpesa->STKStatus([])`
   - [Documentation](docs/LipaNaMpesaOnlineQuery.md)

3. **C2B (Customer to Business)** - Register URLs for C2B callbacks
   - Method: `$mpesa->C2BRegister([])` and `$mpesa->C2BSimulate([])`
   - [Documentation](docs/C2B.md)

4. **B2C (Business to Customer)** - Send money to customers
   - Method: `$mpesa->B2C([])`
   - [Documentation](docs/B2C.md)

5. **B2B (Business to Business)** - Transfer funds between businesses
   - Method: `$mpesa->B2B([])`
   - [Documentation](docs/B2B.md)

6. **B2Pochi (Business to Pochi)** - Send money to customer Pochi savings accounts
   - Method: `$mpesa->B2Pochi([])`
   - [Documentation](docs/B2Pochi.md)

7. **Transaction Status** - Check transaction status
   - Method: `$mpesa->transactionStatus([])`
   - [Documentation](docs/TransactionStatus.md)

8. **Reversal** - Reverse a transaction
   - Method: `$mpesa->reversal([])`
   - [Documentation](docs/Reversal.md)

9. **Account Balance** - Check account balance
   - Method: `$mpesa->accountBalance([])`
   - [Documentation](docs/AccountBalance.md)

## Troubleshooting

### Common Issues

#### 1. Composer Install Fails

**Problem**: `composer install` fails with dependency errors

**Solution**:
```bash
# Update Composer
composer self-update

# Clear cache
composer clear-cache

# Try again
composer install
```

#### 2. PHPUnit Not Found

**Problem**: `vendor/bin/phpunit: No such file or directory`

**Solution**:
```bash
# Reinstall dependencies
rm -rf vendor
composer install
```

#### 3. Autoload Errors

**Problem**: `Class 'yourdudeken\Mpesa\Init' not found`

**Solution**:
```bash
# Regenerate autoload files
composer dump-autoload
```

#### 4. PHP Version Issues

**Problem**: Package requires PHP 5.6.0 or higher

**Solution**:
```bash
# Check your PHP version
php -v

# If needed, update PHP or use a different version
# On Ubuntu/Debian:
sudo apt-get update
sudo apt-get install php7.4
```

#### 5. API Connection Errors

**Problem**: Cannot connect to Safaricom API

**Solution**:
- Verify your internet connection
- Check if you're using the correct API URL (sandbox vs production)
- Verify your API credentials are correct
- Check if your IP is whitelisted (for production)

#### 6. Invalid Credentials

**Problem**: Authentication fails

**Solution**:
- Double-check your consumer key and secret
- Ensure you're using sandbox credentials for sandbox environment
- Verify credentials haven't expired
- Check for extra spaces in configuration

### Getting Help

- **Documentation**: Check the `docs/` folder for API-specific guides
- **Issues**: Report bugs on [GitHub Issues](https://github.com/yourdudeken/mpesa/issues)
- **Telegram Support**: [Contact via Telegram](https://t.me/yourdudeken)
- **Safaricom Developer Portal**: [developer.safaricom.co.ke](https://developer.safaricom.co.ke/)

## Development Workflow

### Making Changes

1. Create a new branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes

3. Run tests to ensure nothing breaks:
   ```bash
   vendor/bin/phpunit
   ```

4. Commit and push your changes:
   ```bash
   git add .
   git commit -m "Description of changes"
   git push origin feature/your-feature-name
   ```

### Code Style

The project uses PHP-CS-Fixer for code styling. Configuration is in `.php_cs`.

## Environment-Specific Notes

### Sandbox Environment
- Use sandbox credentials from Safaricom Developer Portal
- Set `'is_sandbox' => true` in config
- API URL: `https://sandbox.safaricom.co.ke/`

### Production Environment
- Use production credentials
- Set `'is_sandbox' => false` in config
- API URL: `https://api.safaricom.co.ke/`
- Ensure your server IP is whitelisted
- Use HTTPS for all callback URLs

## Next Steps

1.  Install dependencies
2.  Configure API credentials
3.  Run tests to verify setup
4.  Try the example file
5.  Read API-specific documentation in `docs/`
6.  Implement your integration
7.  Test in sandbox environment
8.  Deploy to production

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.txt).

## Credits

- Original inspiration: [SmoDav's M-Pesa Package](https://github.com/SmoDav/mpesa)
- Maintained by: [Kennedy Muthengi](mailto:kenmwendwamuthengi@gmail.com)
