# M-Pesa API - Single Codebase Setup

## Overview

This M-Pesa API implementation uses a **single codebase** with environment configuration controlled via the `.env` file. Switch between sandbox and production by updating environment variables.

## Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

Copy the example environment file:uctioy

```bash
cp .env.example .env
```

Edit `.env` and set your environment:

```env
APP_NAME="M-Pesa API"
APP_ENV=local          # local, sandbox, or production
APP_DEBUG=true         # false for production
APP_PORT=8000          # Port to run the server on

# M-Pesa Configuration
MPESA_ENV=sandbox      # sandbox or production
MPESA_CONSUMER_KEY=your_key
MPESA_CONSUMER_SECRET=yoeringur_secret
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Start the Server

```bash
./start-api.sh
```

The server will start on the port specified in `.env` (default: 8000).

## Server Management Scripts

### start-api.sh

Starts the M-Pesa API development server.

```bash
./start-api.sh
```

**Features:**
- ✅ Checks PHP installation
- ✅ Installs dependencies if needed
- ✅ Creates necessary directories
- ✅ Generates app key if missing
- ✅ Reads port from `.env` file
- ✅ Shows environment and configuration
- ✅ Graceful shutdown with Ctrl+C

**Output:**
```
🚀 Starting M-Pesa API Server...
   Environment: local
   Port: 8000
   Server PID: 12345

📍 M-Pesa API:
   Base URL:     http://localhost:8000
   Health Check: http://localhost:8000/api/health
   M-Pesa API:   http://localhost:8000/api/mpesa
```

### stop-api.sh

Stops the running API server.

```bash
./stop-api.sh
```

**Features:**
- ✅ Reads port from `.env` file
- ✅ Finds and stops the server process
- ✅ Force kills if needed
- ✅ Verifies server stopped

### test-api.sh

Tests all API endpoints.

```bash
./test-api.sh
```

**Features:**
- ✅ Reads port from `.env` file
- ✅ Tests all 9 M-Pesa endpoints
- ✅ Validates responses
- ✅ Shows detailed results
- ✅ Provides summary

## Environment Configuration

### Switching Environments

Edit `.env` to switch between environments:

**Sandbox (Testing):**
```env
APP_ENV=local
APP_DEBUG=true
APP_PORT=8000
MPESA_ENV=sandbox
```

**Production (Live):**
```env
APP_ENV=production
APP_DEBUG=false
APP_PORT=8000
MPESA_ENV=production
```

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | M-Pesa API |
| `APP_ENV` | Environment | local, sandbox, production |
| `APP_DEBUG` | Debug mode | true, false |
| `APP_PORT` | Server port | 8000 |
| `MPESA_ENV` | M-Pesa environment | sandbox, production |
| `MPESA_CONSUMER_KEY` | M-Pesa consumer key | your_key |
| `MPESA_CONSUMER_SECRET` | M-Pesa consumer secret | your_secret |

## M-Pesa Configuration

Edit `config/mpesa.php` to configure M-Pesa settings:

```php
return [
    'apiUrl' => env('MPESA_ENV') === 'production' 
        ? 'https://api.safaricom.co.ke/'
        : 'https://sandbox.safaricom.co.ke/',
    
    'is_sandbox' => env('MPESA_ENV') !== 'production',
    
    'apps' => [
        'default' => [
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        ],
    ],
    
    'lnmo' => [
        'short_code' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'callback' => env('MPESA_CALLBACK_URL'),
    ],
    
    // ... other configurations
];
```

## API Endpoints

All endpoints are available at `http://localhost:{PORT}/api/mpesa`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Health check |
| POST | `/mpesa/stk-push` | Initiate STK Push |
| POST | `/mpesa/stk-query` | Query STK status |
| POST | `/mpesa/c2b/register` | Register C2B URLs |
| POST | `/mpesa/c2b/simulate` | Simulate C2B |
| POST | `/mpesa/b2c` | B2C payment |
| POST | `/mpesa/b2b` | B2B payment |
| POST | `/mpesa/balance` | Account balance |
| POST | `/mpesa/transaction-status` | Transaction status |
| POST | `/mpesa/reversal` | Reverse transaction |

## Testing

### Quick Test

```bash
# Test health endpoint
curl http://localhost:8000/api/health

# Test STK Push
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

### Full Test Suite

```bash
./test-api.sh
```

## Directory Structure

```
mpesa/
├── app/                    # Laravel application
│   ├── Http/
│   │   └── Controllers/    # API controllers
│   └── Services/           # Business logic
├── bootstrap/              # Laravel bootstrap
├── config/                 # Configuration files
│   └── mpesa.php          # M-Pesa configuration
├── database/              # Database files
├── public/                # Public entry point
│   └── index.php         # Laravel entry
├── routes/                # Route definitions
│   ├── api.php           # API routes
│   └── console.php       # Console routes
├── services/              # M-Pesa SDK
│   └── Mpesa/            # M-Pesa package
├── storage/               # Storage directory
│   └── logs/             # Log files
├── tests/                 # Tests
├── .env                   # Environment config
├── .env.example          # Example environment
├── artisan               # Laravel CLI
├── composer.json         # Dependencies
├── start-api.sh          # Start server
├── stop-api.sh           # Stop server
└── test-api.sh           # Test endpoints
```

## Logs

View server logs:

```bash
tail -f logs/api-server.log
```

View Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

## Troubleshooting

### Port Already in Use

```bash
# Check what's using the port
lsof -i:8000

# Stop the server
./stop-api.sh

# Or kill manually
kill $(lsof -ti:8000)
```

### Permission Errors

```bash
chmod -R 775 storage bootstrap/cache
chmod +x start-api.sh stop-api.sh test-api.sh
```

### Dependencies Not Installed

```bash
composer install
```

### Application Key Not Set

```bash
php artisan key:generate
```

## Production Deployment

### Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `MPESA_ENV=production`
- [ ] Update M-Pesa credentials
- [ ] Use production API URL
- [ ] Set up HTTPS
- [ ] Configure callback URLs
- [ ] Set up proper logging
- [ ] Enable caching
- [ ] Set up monitoring

### Production Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_PORT=8000
MPESA_ENV=production
MPESA_CONSUMER_KEY=production_key
MPESA_CONSUMER_SECRET=production_secret
```

## Benefits of Single Codebase

✅ **Simplified Management**
- One codebase to maintain
- Easier updates and bug fixes
- Consistent code across environments

✅ **Environment Flexibility**
- Switch environments via `.env`
- No code changes needed
- Easy testing and deployment

✅ **Resource Efficient**
- Single set of dependencies
- Smaller disk footprint
- Faster deployments

✅ **Better Version Control**
- Single Git repository
- Easier branching strategy
- Clearer history

## Support

For issues or questions:
- Email: kenmwendwamuthengi@gmail.com
- GitHub: https://github.com/yourdudeken/mpesa
- Safaricom Developer Portal: https://developer.safaricom.co.ke
