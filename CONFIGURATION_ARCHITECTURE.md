# M-Pesa Package Configuration Architecture

## Overview

The M-Pesa package now uses a **hybrid configuration approach**:
- **Internal Config** (`src/config/mpesa.php`): Contains ONLY certificate paths
- **External Config** (user's `config/mpesa.php`): Contains ALL application configuration

This ensures certificates are always available while keeping user configuration external.

## Configuration Structure

### Internal Configuration (Package)
**Location**: `/home/kennedy/vscode/github/yourdudeken/mpesa/src/config/mpesa.php`

**Purpose**: Provides certificate paths only

**Contents**:
```php
<?php
return [
    'certificate_path_sandbox' => __DIR__ . '/SandboxCertificate.cer',
    'certificate_path_production' => __DIR__ . '/ProductionCertificate.cer',
];
```

**Note**: Users should NEVER modify this file.

### External Configuration (User's Project)
**Location**: `your-project/config/mpesa.php`

**Purpose**: Only unique credentials and settings. Transaction boilerplate (CommandIDs, API URLs, etc.) is handled by the package automatically.

**Maximally Simplified Example**:
```php
<?php
return [
    'is_sandbox'         => true,

    // Credentials & Common Fields (Automatically applied to all types)
    'consumer_key'       => 'your-consumer-key',
    'consumer_secret'    => 'your-consumer-secret',
    'short_code'         => '174379',
    'passkey'            => 'your-passkey',
    'callback'           => 'https://your-domain.com/callback',
    'initiator_name'     => 'testapi',
    'initiator_password' => 'Safaricom123!!',
];
```

## How Defaults are Handled

The package now provides built-in defaults for all transaction boilerplate in its internal `src/config/mpesa.php`. This includes:

- **CommandIDs**: `BusinessPayment`, `CustomerPayBillOnline`, `AccountBalance`, `BusinessPayToPochi`, etc.
- **API Settings**: Automatic selection of Sandbox or Production endpoints based on `is_sandbox`.
- **Certificates**: Built-in certificates loaded automatically.

## Configuration Loading Logic

The package uses a three-layer intelligent lookup system when you request a key (e.g., `mpesa.b2c.initiator_name`):

1.  **Direct Match**: Checks for a specific override in your config section (`b2c.initiator_name`).
2.  **Global Fallback**: if not found, it checks for a root-level key in your config (`initiator_name`).
3.  **Package Defaults**: If still not found, it falls back to the package's internal defaults in `src/config/mpesa.php`.

## Configuration Priority

- Passed to constructor -> `new Mpesa($config)`
- Environment Variables -> `.env`
- Project Config -> `config/mpesa.php`
- Project Root Fallback -> `../../../../../../config/mpesa.php`
- Internal Package Config -> `src/config/mpesa.php` (Lowest priority - defaults)

## Environment Variables (.env) Support

## Environment Variables (.env) Support

You can configure the package using environment variables. Create a `.env` file in your project root:

```env
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=your-key
MPESA_CONSUMER_SECRET=your-secret
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your-passkey
MPESA_CALLBACK_URL=https://your-callback.com
MPESA_INITIATOR_NAME=testapi
MPESA_INITIATOR_PASSWORD=Safaricom123!!
```

The package will automatically load these variables if the file exists.

## Certificate Loading

Certificates are loaded automatically based on environment:

### Automatic Loading
```php
// Package automatically selects certificate based on is_sandbox config
$mpesa = new Mpesa();
// Uses SandboxCertificate.cer if is_sandbox = true
// Uses ProductionCertificate.cer if is_sandbox = false
```

### Custom Certificate Path
```php
// Override certificate path in your config
return [
    'certificate_path' => '/path/to/custom/certificate.cer',
    // ... other config
];
```

### Certificate Path Resolution
1. Check for `certificate_path` in config (custom path)
2. Check for `certificate_path_sandbox` or `certificate_path_production` based on `is_sandbox`
3. Fallback to internal certificates in `src/config/`

## Usage Examples

### Basic Usage
```php
<?php
require 'vendor/autoload.php';

use Yourdudeken\Mpesa\Init as Mpesa;

// Config loaded automatically from config/mpesa.php
$mpesa = new Mpesa();

$response = $mpesa->STKPush([
    'amount' => 100,
    'phoneNumber' => '254708374149',
    'accountReference' => 'INV-001',
    'transactionDesc' => 'Payment for invoice'
]);
```

### Passing Config Directly
```php
<?php
$config = [
    'apps' => [
        'default' => [
            'consumer_key' => 'your-key',
            'consumer_secret' => 'your-secret'
        ]
    ],
    'lnmo' => [
        'short_code' => 174379,
        'passkey' => 'your-passkey',
        'callback' => 'https://your-callback.com'
    ],
    'is_sandbox' => true
];

$mpesa = new Mpesa($config);
```

### Multiple Environments
```php
<?php
// Load different configs based on environment
$env = getenv('APP_ENV') ?: 'sandbox';
$configFile = __DIR__ . "/config/mpesa.{$env}.php";

if (file_exists($configFile)) {
    $config = require $configFile;
    $mpesa = new Mpesa($config);
}
```

## File Structure

```
your-project/
├── config/
│   ├── mpesa.php              # Your M-Pesa configuration
│   ├── mpesa.sandbox.php      # Optional: Sandbox config
│   └── mpesa.production.php   # Optional: Production config
│
└── vendor/
    └── yourdudeken/
        └── mpesa/
            └── src/
                └── config/
                    ├── mpesa.php                    # Internal (certs only)
                    ├── SandboxCertificate.cer      # Sandbox certificate
                    └── ProductionCertificate.cer   # Production certificate
```

## Benefits

### 1. Clean Separation
- **Package**: Manages certificates
- **User**: Manages credentials and configuration

### 2. Security
- Credentials stay in user's project
- Not committed to package repository
- Easy to gitignore

### 3. Flexibility
- Support multiple environments
- Override any configuration
- Custom certificate paths if needed

### 4. Simplicity
- Certificates always available
- No manual certificate setup
- Works out of the box

## Migration from Old Configuration

If you were using the old package with internal configuration:

### Before
```php
// Config was in vendor/yourdudeken/mpesa/src/config/mpesa.php
// Had to edit vendor files (bad practice)
```

### After
```php
// Create config/mpesa.php in your project
// Copy template from example/config/mpesa.php
// Update with your credentials
```

## Environment Variables (Future Enhancement)

The architecture supports adding environment variable loading:

```php
// In your config/mpesa.php
return [
    'apps' => [
        'default' => [
            'consumer_key' => getenv('MPESA_CONSUMER_KEY'),
            'consumer_secret' => getenv('MPESA_CONSUMER_SECRET'),
        ]
    ],
    // ... other config
];
```

## Troubleshooting

### Config Not Loading
```bash
# Check working directory
php -r "echo getcwd();"

# Verify config file exists
ls -la config/mpesa.php
```

### Certificate Not Found
```php
// Check certificate paths in config
$mpesa = new Mpesa();
$config = $mpesa->getConfig();
print_r($config['certificate_path_sandbox']);
```

### Testing Configuration
```php
<?php
require 'vendor/autoload.php';

use Yourdudeken\Mpesa\Init as Mpesa;

try {
    $mpesa = new Mpesa();
    echo "✓ Configuration loaded successfully\n";
    
    // Test STK Push
    $response = $mpesa->STKPush([
        'amount' => 1,
        'phoneNumber' => '254708374149',
        'accountReference' => 'TEST',
        'transactionDesc' => 'Config test'
    ]);
    
    echo "✓ STK Push working\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
```

## Summary

✅ **Internal config**: Only certificate paths  
✅ **External config**: All user configuration  
✅ **Automatic certificate loading**: Based on environment  
✅ **Flexible**: Support custom paths and multiple environments  
✅ **Secure**: Credentials stay in user's project  
✅ **Simple**: Works out of the box  

The package now provides a clean, secure, and flexible configuration system!
