# Laravel API Setup - Complete

## ✅ Setup Complete!

Both production and sandbox environments are now running **Laravel 11 in API-only mode**.

## 📦 What Was Set Up

### 1. Laravel Framework
- **Version**: Laravel 11.47.0
- **Mode**: API-only (no views, blade templates)
- **PHP Version**: 8.1+

### 2. Directory Structure

```
production/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
├── bootstrap/
│   ├── app.php          # Laravel 11 bootstrap
│   └── cache/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── index.php        # Entry point
├── routes/
│   ├── api.php          # API routes
│   └── console.php      # Console commands
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── src/Mpesa/           # M-Pesa package
├── tests/               # PHPUnit tests
├── .env                 # Environment config
├── artisan              # Laravel CLI
├── composer.json
└── phpunit.xml

sandbox/
└── (same structure as production)
```

### 3. Installed Packages

**Core:**
- laravel/framework ^11.0
- guzzlehttp/guzzle ^7.2

**Development:**
- phpunit/phpunit ^10.5
- mockery/mockery ^1.6
- fakerphp/faker ^1.23
- laravel/pint ^1.13
- nunomaduro/collision ^8.1

## 🚀 Running the Servers

### Start Both Servers

```bash
./start-api.sh
```

This will start:
- **Production API** on http://localhost:8000
- **Sandbox API** on http://localhost:8001

### Stop Both Servers

```bash
# Press Ctrl+C in the terminal running start-api.sh
# OR
./stop-api.sh
```

## 📡 API Endpoints

### Health Check

```bash
# Production
curl http://localhost:8000/api/health

# Sandbox
curl http://localhost:8001/api/health
```

### M-Pesa Endpoints

All endpoints are prefixed with `/api/mpesa`:

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/mpesa/stk-push` | Initiate STK Push payment |
| POST | `/api/mpesa/stk-query` | Query STK Push status |
| POST | `/api/mpesa/c2b/register` | Register C2B URLs |
| POST | `/api/mpesa/c2b/simulate` | Simulate C2B payment |
| POST | `/api/mpesa/b2c` | Business to Customer payment |
| POST | `/api/mpesa/b2b` | Business to Business payment |
| POST | `/api/mpesa/balance` | Check account balance |
| POST | `/api/mpesa/transaction-status` | Check transaction status |
| POST | `/api/mpesa/reversal` | Reverse a transaction |

### Callback Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/mpesa/callback/stk` | STK Push callback |
| POST | `/api/mpesa/callback/c2b` | C2B callback |
| POST | `/api/mpesa/callback/b2c` | B2C callback |

## 🛠️ Laravel Commands

### Artisan CLI

```bash
# Production
cd production
php artisan

# Sandbox
cd sandbox
php artisan
```

### Common Commands

```bash
# List all routes
php artisan route:list

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run migrations (when you add them)
php artisan migrate

# Create a controller
php artisan make:controller MpesaController

# Create a model
php artisan make:model Transaction

# Run tests
php artisan test
```

## ⚙️ Configuration

### Environment Files

**Production:** `production/.env`
```env
APP_NAME="M-Pesa Production API"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

MPESA_ENV=production
MPESA_CONSUMER_KEY=your_key
MPESA_CONSUMER_SECRET=your_secret
```

**Sandbox:** `sandbox/.env`
```env
APP_NAME="M-Pesa Sandbox API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=your_sandbox_key
MPESA_CONSUMER_SECRET=your_sandbox_secret
```

### M-Pesa Configuration

The M-Pesa package configuration is in:
- `production/src/config/mpesa.php`
- `sandbox/src/config/mpesa.php`

## 📝 Next Steps

### 1. Implement M-Pesa Controllers

Create controllers to handle M-Pesa operations:

```bash
cd production
php artisan make:controller Api/MpesaController
```

### 2. Update Routes

Edit `routes/api.php` to use controllers instead of closures:

```php
use App\Http\Controllers\Api\MpesaController;

Route::prefix('mpesa')->group(function () {
    Route::post('/stk-push', [MpesaController::class, 'stkPush']);
    Route::post('/stk-query', [MpesaController::class, 'stkQuery']);
    // ... other routes
});
```

### 3. Add Middleware

Add authentication, rate limiting, etc.:

```bash
php artisan make:middleware ValidateApiKey
```

### 4. Create Models

If you need to store transactions:

```bash
php artisan make:model Transaction -m
```

### 5. Add Validation

Create form requests for validation:

```bash
php artisan make:request StkPushRequest
```

## 🧪 Testing

### Run PHPUnit Tests

```bash
# Production
cd production
php artisan test

# Sandbox
cd sandbox
php artisan test
```

### Test API Endpoints

```bash
# Test health endpoint
curl http://localhost:8000/api/health

# Test M-Pesa endpoint
curl -X POST http://localhost:8000/api/mpesa/stk-push \
  -H "Content-Type: application/json" \
  -d '{"phone":"254712345678","amount":100}'
```

## 📚 Documentation

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Laravel API Resources](https://laravel.com/docs/11.x/eloquent-resources)
- [Laravel Routing](https://laravel.com/docs/11.x/routing)
- [M-Pesa Package Documentation](../README.md)

## 🔧 Troubleshooting

### Clear All Caches

```bash
cd production
php artisan optimize:clear
```

### Regenerate Application Key

```bash
php artisan key:generate
```

### Check Logs

```bash
# Production
tail -f production/storage/logs/laravel.log

# Sandbox
tail -f sandbox/storage/logs/laravel.log
```

### Permission Issues

```bash
chmod -R 775 production/storage production/bootstrap/cache
chmod -R 775 sandbox/storage sandbox/bootstrap/cache
```

## 🎯 Features

- ✅ Laravel 11 API-only mode
- ✅ Separate production and sandbox environments
- ✅ M-Pesa package integration
- ✅ RESTful API endpoints
- ✅ Health check endpoint
- ✅ Callback handling
- ✅ Environment-based configuration
- ✅ Artisan CLI support
- ✅ PHPUnit testing ready
- ✅ Auto-discovery of packages

## 📞 Support

For issues or questions:
- Laravel: https://laravel.com/docs
- M-Pesa Package: https://github.com/yourdudeken/mpesa
- Email: kenmwendwamuthengi@gmail.com
