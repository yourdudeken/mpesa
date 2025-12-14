# Environment-Based Configuration Guide

## Overview

The M-Pesa Gateway API now uses **environment variables** for all configuration, making it easy to switch between sandbox and production environments without code changes.

## Certificate Selection

The API automatically selects the correct certificate based on the `MPESA_ENV` variable:

- **Sandbox**: Uses `config/SandboxCertificate.cer`
- **Production**: Uses `config/ProductionCertificate.cer`

## Configuration Files

### 1. `.env` File (Your Configuration)

This file contains your actual credentials and is **never committed** to version control.

```env
# Set environment: sandbox or production
MPESA_ENV=sandbox

# Your credentials
MPESA_CONSUMER_KEY=your_actual_key
MPESA_CONSUMER_SECRET=your_actual_secret
```

### 2. `.env.example` (Template)

Template file showing all available configuration options. Copy this to `.env` and fill in your values.

```bash
cp .env.example .env
```

### 3. `config/mpesa.php` (Configuration Logic)

Reads environment variables and provides defaults. **No need to edit this file.**

## Environment Variables

### Core Configuration

| Variable | Description | Example |
|----------|-------------|---------|
| `MPESA_ENV` | Environment mode | `sandbox` or `production` |
| `MPESA_CONSUMER_KEY` | M-Pesa consumer key | `fduEAZl8XCBAA5dX...` |
| `MPESA_CONSUMER_SECRET` | M-Pesa consumer secret | `of2dQDr3TaQKT6PW...` |

### STK Push Configuration

| Variable | Description | Required |
|----------|-------------|----------|
| `MPESA_SHORTCODE` | Paybill/Till number | Yes |
| `MPESA_PASSKEY` | STK Push passkey | Yes |
| `MPESA_STK_CALLBACK_URL` | Callback URL | Yes |

### C2B Configuration

| Variable | Description | Required |
|----------|-------------|----------|
| `MPESA_C2B_SHORTCODE` | C2B shortcode | Yes |
| `MPESA_C2B_CONFIRMATION_URL` | Confirmation URL | Yes |
| `MPESA_C2B_VALIDATION_URL` | Validation URL | Yes |

### B2C Configuration

| Variable | Description | Required |
|----------|-------------|----------|
| `MPESA_B2C_INITIATOR_NAME` | Initiator name | Yes |
| `MPESA_B2C_INITIATOR_PASSWORD` | Initiator password | Yes |
| `MPESA_B2C_SHORTCODE` | B2C shortcode | Yes |
| `MPESA_B2C_RESULT_URL` | Result callback URL | Yes |
| `MPESA_B2C_TIMEOUT_URL` | Timeout callback URL | Yes |

### B2B Configuration

| Variable | Description | Required |
|----------|-------------|----------|
| `MPESA_B2B_INITIATOR_NAME` | Initiator name | Yes |
| `MPESA_B2B_INITIATOR_PASSWORD` | Initiator password | Yes |
| `MPESA_B2B_SHORTCODE` | B2B shortcode | Yes |
| `MPESA_B2B_RESULT_URL` | Result callback URL | Yes |
| `MPESA_B2B_TIMEOUT_URL` | Timeout callback URL | Yes |

## Switching Environments

### Sandbox to Production

1. **Update `.env`:**
   ```env
   MPESA_ENV=production
   MPESA_CONSUMER_KEY=production_key
   MPESA_CONSUMER_SECRET=production_secret
   ```

2. **Update Callback URLs** (must be HTTPS):
   ```env
   MPESA_STK_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback/stk
   MPESA_C2B_CONFIRMATION_URL=https://yourdomain.com/api/mpesa/callback/c2b
   # ... etc
   ```

3. **Restart Server:**
   ```bash
   ./stop-api.sh
   ./start-api.sh
   ```

### Production to Sandbox

1. **Update `.env`:**
   ```env
   MPESA_ENV=sandbox
   MPESA_CONSUMER_KEY=sandbox_key
   MPESA_CONSUMER_SECRET=sandbox_secret
   ```

2. **Restart Server**

## Certificate Management

### Sandbox Certificate

**Location:** `config/SandboxCertificate.cer`

**Used when:** `MPESA_ENV=sandbox`

**Purpose:** Encrypt initiator passwords for sandbox API calls

### Production Certificate

**Location:** `config/ProductionCertificate.cer`

**Used when:** `MPESA_ENV=production`

**Purpose:** Encrypt initiator passwords for production API calls

### How It Works

```php
// In config/mpesa.php
'certificate_path' => env('MPESA_ENV', 'sandbox') === 'production'
    ? base_path('config/ProductionCertificate.cer')
    : base_path('config/SandboxCertificate.cer'),
```

The Core engine automatically:
1. Reads `MPESA_ENV` from `.env`
2. Selects the appropriate certificate
3. Uses it for security credential encryption

## API URL Selection

The API URL is also automatically selected:

```php
'apiUrl' => env('MPESA_ENV', 'sandbox') === 'production' 
    ? 'https://api.safaricom.co.ke/'
    : 'https://sandbox.safaricom.co.ke/',
```

- **Sandbox:** `https://sandbox.safaricom.co.ke/`
- **Production:** `https://api.safaricom.co.ke/`

## Configuration Verification

### Check Current Configuration

```bash
curl http://localhost:8000/api/health
```

**Response:**
```json
{
  "status": "ok",
  "service": "M-Pesa Gateway API",
  "environment": "local",
  "mpesa_env": "sandbox",  // ← Shows current M-Pesa environment
  "timestamp": "2025-12-14T20:36:11.025023Z"
}
```

### Verify Certificate Path

```bash
php artisan tinker
>>> config('mpesa.certificate_path')
=> "/path/to/mpesa/config/SandboxCertificate.cer"

>>> config('mpesa.is_sandbox')
=> true
```

## Example Configurations

### Sandbox Configuration

```env
# .env for Sandbox
APP_ENV=local
APP_DEBUG=true
MPESA_ENV=sandbox

MPESA_CONSUMER_KEY=fduEAZl8XCBAA5dXsoMK4d0EI278jGpcZSDGslWNAuVAGvRP
MPESA_CONSUMER_SECRET=of2dQDr3TaQKT6PWKClb5jpu5ooigb9AIcOLStzF2lR8EMM9SOYzfj4XIS0lbH0o
MPESA_SHORTCODE=174379
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919

# Sandbox callback URLs (can be HTTP for testing)
MPESA_STK_CALLBACK_URL=http://localhost:8000/api/mpesa/callback/stk
```

### Production Configuration

```env
# .env for Production
APP_ENV=production
APP_DEBUG=false
MPESA_ENV=production

MPESA_CONSUMER_KEY=your_production_consumer_key
MPESA_CONSUMER_SECRET=your_production_consumer_secret
MPESA_SHORTCODE=your_production_shortcode
MPESA_PASSKEY=your_production_passkey

# Production callback URLs (MUST be HTTPS)
MPESA_STK_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback/stk
MPESA_C2B_CONFIRMATION_URL=https://yourdomain.com/api/mpesa/callback/c2b
MPESA_C2B_VALIDATION_URL=https://yourdomain.com/api/mpesa/callback/c2b
MPESA_B2C_RESULT_URL=https://yourdomain.com/api/mpesa/callback/b2c
MPESA_B2C_TIMEOUT_URL=https://yourdomain.com/api/mpesa/callback/b2c
```

## Security Best Practices

### 1. Never Commit `.env`

The `.env` file is in `.gitignore` and should **never** be committed to version control.

### 2. Use Strong Credentials

- Use unique, strong consumer keys and secrets
- Rotate credentials regularly
- Store production credentials securely

### 3. HTTPS for Production

All callback URLs in production **must** use HTTPS:
```env
# ❌ Wrong for production
MPESA_STK_CALLBACK_URL=http://yourdomain.com/callback

# ✅ Correct for production
MPESA_STK_CALLBACK_URL=https://yourdomain.com/callback
```

### 4. Separate Environments

Use different credentials for sandbox and production:
- Sandbox credentials for testing
- Production credentials for live transactions

## Troubleshooting

### Certificate Not Found

**Error:** `Please provide a valid public key file at: ...`

**Solution:**
1. Check certificate files exist:
   ```bash
   ls -la config/*.cer
   ```

2. Verify paths in config:
   ```bash
   php artisan tinker
   >>> config('mpesa.certificate_path')
   ```

### Wrong Environment

**Issue:** Using sandbox credentials in production

**Solution:**
1. Check `.env` file:
   ```bash
   grep MPESA_ENV .env
   ```

2. Verify via health endpoint:
   ```bash
   curl http://localhost:8000/api/health | jq .mpesa_env
   ```

### Configuration Not Loading

**Solution:**
1. Clear config cache:
   ```bash
   php artisan config:clear
   ```

2. Restart server:
   ```bash
   ./stop-api.sh
   ./start-api.sh
   ```

## Benefits

✅ **Easy Environment Switching**
- Change one variable (`MPESA_ENV`)
- No code changes needed

✅ **Secure**
- Credentials in `.env` (not in code)
- `.env` never committed to Git

✅ **Flexible**
- Override any setting via environment
- Different configs per environment

✅ **Automatic Certificate Selection**
- No manual certificate management
- Correct cert for each environment

## Support

For configuration help:
- **Documentation:** PRODUCTION_READY.md
- **Email:** kenmwendwamuthengi@gmail.com
- **Safaricom:** https://developer.safaricom.co.ke
