# M-Pesa PHP Package Documentation

## Overview
This package provide a robust and extensible PHP integration for Safaricom's M-Pesa API (Daraja). It follows modern PHP practices, including PSR-based autoloading, decoupled components, and a comprehensive validation engine.

## Core Architecture

### Entry Point: `Init.php`
The `Yourdudeken\Mpesa\Init` class is the main entry point. It initializes all internal components and service classes.
- **Constructor**: Accepts an optional configuration array.
- **Service Access**: Services like `stk`, `b2c`, `b2b`, and `status` are exposed as public properties.

### The Engine (`src/Mpesa/Engine`)
The engine handles the low-level logic of communication with Safaricom's servers.

- **`Core.php`**: The central orchestrator. It manages:
    - Base URL selection (Sandbox vs Production).
    - Authentication via the `Authenticator`.
    - HTTP request execution (POST/GET).
    - Parameter normalization and sanitization (applying length limits for Remarks, AccountReference, etc.).
    - Security credential computation using RSA encryption with Safaricom's public certificates.

- **`Config.php`**: Implements `ConfigurationStore`. It merges internal defaults with user-provided settings and normalizes credential keys.

- **`Cache.php`**: Implements `CacheStore`. A file-based caching system for storing OAuth access tokens to avoid redundant authentication requests.

- **`CurlRequest.php`**: A low-level wrapper for PHP's cURL extension, providing a clean interface for the HTTP client.

- **`AbstractTransaction.php`**: A base class for all transaction types. It enforces validation rules for each transaction and assists in merging configuration defaults with user parameters.

## Service Classes

### Lipa Na M-Pesa Online (STK Push)
- **`STKPush`**: Initiates a payment request directly to the user's phone. Handles password generation using the ShortCode, Passkey, and Timestamp.
- **`STKStatusQuery`**: Queries the status of an STK push transaction using the `CheckoutRequestID`.

### Business Payments
- **`B2C\Pay`**: Handles Business-to-Customer payments (e.g., salary disbursements, promotional payments).
- **`B2B\Pay`**: Handles Business-to-Business payments, allowing for fund transfers between different shortcodes.
- **`B2Pochi\Pay`**: Specifically for Business-to-Pochi (Pochi La Biashara) payments.

### Customer to Business (C2B)
- **`Register`**: Registers the `ConfirmationURL` and `ValidationURL` for a C2B shortcode.
- **`Simulate`**: Allows for simulating a C2B transaction in the sandbox environment.

### Account Utilities
- **`AccountBalance\Balance`**: Queries the current balance of the account's working, utility, or business funds.
- **`TransactionStatus\TransactionStatus`**: Checks the status of a specific transaction using its `TransactionID`.
- **`Reversal\Reversal`**: Initiates a reversal for a specific M-Pesa transaction.

## Validation System (`src/Mpesa/Validation`)
The package includes a sophisticated validation engine to ensure that data sent to Safaricom meets the required schema.

- **Extensibility**: Rules are defined as classes inheriting from `AbstractRule`.
- **Standard Rules**: Includes `Required`, `Number`, `Email`, `Url`, `InList`, `Between`, `MinLength`, `MaxLength`, etc.
- **Complex Rules**:
    - `RequiredWhen`: Field required based on another field's value.
    - `RequiredWith`: Field required if another field is present.
    - `MatchField`: Ensures two fields match (e.g., password confirmation).
- **File & Upload Validation**: Comprehensive rules for validating file types, sizes, image dimensions, and image ratios for both local files and uploaded files.

## Configuration Defaults
Default settings are stored in `src/config/mpesa.php`. This includes:
- Default `CommandID`s for different transaction types.
- Default `IdentifierType`s.
- Automatic selection of Sandbox vs Production certificates based on the environment flag.

## Installation & Support
- **Autoloading**: The package includes a custom `autoload.php` for environments where Composer's autoloader is not used.
- **Installer**: A special `Installer` class handles the copying of default configuration and certificates into the host project upon installation via Composer.

## Security
- **SSL Certificate Verification**: Enabled by default for all API requests.
- **Sensitive Data Encryption**: The package automatically handles the encryption of the `InitiatorPassword` using the Safaricom-provided public certificates for secure credential submission.
