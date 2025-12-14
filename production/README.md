# M-Pesa Production Environment

This directory contains the production environment configuration for the M-Pesa API package.

## Setup

### Install Dependencies

```bash
composer install
```

## Testing

### Run All Tests

```bash
vendor/bin/phpunit
```

### Run Specific Test

```bash
vendor/bin/phpunit tests/Unit/STKPushTest.php
```

### Available Test Files

- `tests/Unit/AuthenticatorTest.php` - Authentication tests
- `tests/Unit/B2BTest.php` - Business to Business tests
- `tests/Unit/B2CTest.php` - Business to Customer tests
- `tests/Unit/BalanceTest.php` - Account Balance tests
- `tests/Unit/C2BRegisterTest.php` - Customer to Business registration tests
- `tests/Unit/CoreTest.php` - Core functionality tests
- `tests/Unit/ReversalTest.php` - Transaction reversal tests
- `tests/Unit/STKPushTest.php` - STK Push tests
- `tests/Unit/STKStatusQueryTest.php` - STK Status Query tests
- `tests/Unit/TransactionStatusTest.php` - Transaction Status tests

## Configuration

The production environment uses production API credentials configured in `src/config/mpesa.php`.

**Important**: 
- Set `'is_sandbox' => false` in the configuration
- Use production API URL: `https://api.safaricom.co.ke/`
- Ensure your server IP is whitelisted by Safaricom
- Use HTTPS for all callback URLs

## Directory Structure

```
production/
├── composer.json          # Composer dependencies
├── phpunit.xml           # PHPUnit configuration
├── vendor/               # Composer dependencies (generated)
├── src/                  # Source code
│   ├── Mpesa/           # M-Pesa package code
│   ├── config/          # Configuration files
│   └── autoload.php     # Autoloader
├── tests/               # Test suite
│   ├── TestCase.php     # Base test case
│   └── Unit/            # Unit tests
└── logs/                # Application logs
```

## See Also

- [Main README](../README.md) - Package overview and usage
- [Setup Guide](../SETUP.md) - Detailed setup instructions
- [API Documentation](../docs/) - API-specific documentation
