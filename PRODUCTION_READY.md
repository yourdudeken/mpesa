# M-Pesa Gateway API - Production Ready

## Overview

This is a production-ready **M-Pesa Gateway API** built with Laravel 11. It serves as an intermediary between your applications and the official Safaricom M-Pesa API, providing a clean RESTful interface with proper error handling, logging, and validation.

## Architecture

```
Client Application
        ↓
M-Pesa Gateway API (This Service)
        ↓
Safaricom M-Pesa API
```

### Components

1. **Controllers** (`app/Http/Controllers/Api/MpesaController.php`)
   - Handles HTTP requests
   - Validates input data
   - Returns JSON responses

2. **Services** (`app/Services/MpesaService.php`)
   - Business logic layer
   - Interacts with M-Pesa SDK
   - Error handling and logging
   - Response formatting

3. **M-Pesa SDK** (`services/Mpesa/`)
   - Core M-Pesa integration
   - Authentication
   - API communication
   - Encryption/Security

4. **Routes** (`routes/api.php`)
   - API endpoint definitions
   - Route naming
   - Middleware application

## Features

✅ **Production Ready**
- Comprehensive error handling
- Request/response logging
- Input validation
- Security best practices

✅ **All M-Pesa Operations**
- STK Push (Lipa Na M-Pesa)
- STK Query
- C2B (Customer to Business)
- B2C (Business to Customer)
- B2B (Business to Business)
- Account Balance
- Transaction Status
- Reversal

✅ **Developer Friendly**
- Clear API documentation
- Consistent response format
- Detailed error messages
- Easy configuration

## Installation

### 1. Install Dependencies

```bash
composer install
```

### 2. Environment Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Configure your environment:

```env
# Application
APP_NAME="M-Pesa Gateway API"
APP_ENV=production
APP_DEBUG=false
APP_PORT=8000
APP_KEY=  # Will be generated

# M-Pesa Configuration
MPESA_ENV=production
MPESA_CONSUMER_KEY=your_production_consumer_key
MPESA_CONSUMER_SECRET=your_production_consumer_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_INITIATOR_NAME=your_initiator_name
MPESA_INITIATOR_PASSWORD=your_initiator_password

# Callback URLs (must be HTTPS in production)
MPESA_STK_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback/stk
MPESA_C2B_CONFIRMATION_URL=https://yourdomain.com/api/mpesa/callback/c2b
MPESA_C2B_VALIDATION_URL=https://yourdomain.com/api/mpesa/callback/c2b
MPESA_B2C_RESULT_URL=https://yourdomain.com/api/mpesa/callback/b2c
MPESA_B2C_TIMEOUT_URL=https://yourdomain.com/api/mpesa/callback/b2c
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chmod +x artisan start-api.sh stop-api.sh test-api.sh
```

## API Endpoints

Base URL: `http://localhost:8000/api` (development)
Production: `https://yourdomain.com/api`

### Health Check

```http
GET /health
```

**Response:**
```json
{
  "status": "ok",
  "service": "M-Pesa Gateway API",
  "environment": "production",
  "mpesa_env": "production",
  "timestamp": "2025-12-14T20:00:00.000000Z",
  "version": "1.0.0"
}
```

### STK Push

```http
POST /mpesa/stk-push
Content-Type: application/json

{
  "amount": 100,
  "phone_number": "254712345678",
  "account_reference": "INV001",
  "transaction_desc": "Payment"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResponseCode": "0",
    "ResponseDescription": "Success",
    "CustomerMessage": "Success. Request accepted for processing"
  },
  "timestamp": "2025-12-14T20:00:00.000000Z"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "CallBackURL is required",
  "error": ["CallBackURL is required"],
  "code": 422,
  "timestamp": "2025-12-14T20:00:00.000000Z"
}
```

### All Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Health check |
| POST | `/mpesa/stk-push` | Initiate STK Push |
| POST | `/mpesa/stk-query` | Query STK status |
| POST | `/mpesa/c2b/register` | Register C2B URLs |
| POST | `/mpesa/c2b/simulate` | Simulate C2B payment |
| POST | `/mpesa/b2c` | B2C payment |
| POST | `/mpesa/b2b` | B2B payment |
| POST | `/mpesa/balance` | Account balance |
| POST | `/mpesa/transaction-status` | Transaction status |
| POST | `/mpesa/reversal` | Reverse transaction |
| POST | `/mpesa/callback/stk` | STK callback |
| POST | `/mpesa/callback/c2b` | C2B callback |
| POST | `/mpesa/callback/b2c` | B2C callback |

## Response Format

### Success Response

```json
{
  "success": true,
  "data": { /* M-Pesa response data */ },
  "timestamp": "ISO 8601 timestamp"
}
```

### Error Response

```json
{
  "success": false,
  "message": "Human readable error message",
  "error": "Detailed error information",
  "code": 400,
  "timestamp": "ISO 8601 timestamp"
}
```

## Error Handling

The API handles three types of errors:

1. **Validation Errors** (422)
   - Invalid input data
   - Missing required fields
   - Format violations

2. **M-Pesa API Errors** (400-500)
   - Configuration errors
   - M-Pesa service errors
   - Authentication failures

3. **Server Errors** (500)
   - Unexpected errors
   - System failures

## Logging

All requests and responses are logged:

```bash
# View application logs
tail -f storage/logs/laravel.log

# View server logs
tail -f logs/api-server.log
```

**Log Levels:**
- `INFO`: Normal operations
- `ERROR`: Errors and exceptions
- `DEBUG`: Detailed debugging (only in development)

## Security

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Use HTTPS for all callbacks
- [ ] Secure your `.env` file (chmod 600)
- [ ] Use strong APP_KEY
- [ ] Whitelist your IP with Safaricom
- [ ] Implement rate limiting
- [ ] Add API authentication
- [ ] Enable CORS properly
- [ ] Monitor logs regularly

### Recommended Middleware

Add to `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi('60,1'); // Rate limiting
    $middleware->validateCsrfTokens(except: [
        'api/mpesa/callback/*' // Exclude M-Pesa callbacks
    ]);
})
```

## Testing

### Start Server

```bash
./start-api.sh
```

### Run Tests

```bash
./test-api.sh
```

### Manual Testing

```bash
# Health check
curl http://localhost:8000/api/health

# STK Push
curl -X POST http://localhost:8000/api/mpesa/stk-push \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 100,
    "phone_number": "254712345678",
    "account_reference": "TEST001",
    "transaction_desc": "Test"
  }'
```

## Deployment

### Requirements

- PHP 8.1+
- Composer
- Web server (Nginx/Apache)
- SSL certificate
- Whitelisted IP address

### Deployment Steps

1. **Clone Repository**
   ```bash
   git clone your-repo
   cd mpesa
   ```

2. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with production values
   php artisan key:generate
   ```

4. **Set Permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

5. **Configure Web Server**
   - Point document root to `public/`
   - Enable HTTPS
   - Configure PHP-FPM

6. **Optimize**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Monitoring

### Health Checks

Monitor the `/health` endpoint:

```bash
curl https://yourdomain.com/api/health
```

### Log Monitoring

Use log aggregation tools:
- Sentry
- Papertrail
- Loggly
- ELK Stack

### Performance Monitoring

- New Relic
- Datadog
- Application Insights

## Troubleshooting

### Common Issues

**1. "Class not found" errors**
```bash
composer dump-autoload
```

**2. Permission denied**
```bash
chmod -R 775 storage bootstrap/cache
```

**3. "APP_KEY not set"**
```bash
php artisan key:generate
```

**4. M-Pesa callback not working**
- Ensure HTTPS is enabled
- Verify callback URLs in M-Pesa portal
- Check firewall rules
- Verify IP whitelisting

## Support

- **Email**: kenmwendwamuthengi@gmail.com
- **GitHub**: https://github.com/yourdudeken/mpesa
- **Safaricom**: https://developer.safaricom.co.ke

## License

MIT License - See LICENSE.txt for details
