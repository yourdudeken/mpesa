# Testing Quick Reference

This guide provides quick commands for testing the M-Pesa package in both production and sandbox environments.

## Production Environment

### Setup
```bash
cd /home/kennedy/vscode/github/yourdudeken/mpesa/production
composer install
```

### Run Tests
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/STKPushTest.php

# Run specific test method
vendor/bin/phpunit --filter testSTKPush
```

## Sandbox Environment

### Setup
```bash
cd /home/kennedy/vscode/github/yourdudeken/mpesa/sandbox
composer install
```

### Run Tests
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/STKPushTest.php

# Run specific test method
vendor/bin/phpunit --filter testSTKPush
```

## Available Test Files

Both environments include the following test files:

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

## Test Results

### Expected Output

When tests pass successfully:
```
PHPUnit 10.5.60 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.6
Configuration: phpunit.xml

......................                                            22 / 22 (100%)

Time: 00:00.305, Memory: 10.00 MB

OK (22 tests, 22 assertions)
```

### Common Issues

1. **Composer not installed**: Run `composer install` first
2. **PHPUnit not found**: Ensure dependencies are installed
3. **Test failures**: Check configuration in `src/config/mpesa.php`

## Directory Structure

```
mpesa/
├── production/              # Production environment
│   ├── composer.json       # Dependencies
│   ├── phpunit.xml        # Test configuration
│   ├── vendor/            # Installed packages
│   ├── src/               # Source code
│   └── tests/             # Test suite
├── sandbox/                # Sandbox environment
│   ├── composer.json       # Dependencies
│   ├── phpunit.xml        # Test configuration
│   ├── vendor/            # Installed packages
│   ├── src/               # Source code
│   └── tests/             # Test suite
└── SETUP.md               # Main setup guide
```

## See Also

- [SETUP.md](SETUP.md) - Complete setup guide
- [production/README.md](production/README.md) - Production environment details
- [sandbox/README.md](sandbox/README.md) - Sandbox environment details
