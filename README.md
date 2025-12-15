# M-Pesa Integration Package & API Gateway

A comprehensive M-Pesa integration solution consisting of a PHP package for M-Pesa operations and a RESTful API Gateway.

## 📦 Project Structure

```
mpesa/
├── Mpesa/                          # Core M-Pesa Package
│   ├── Init.php                    # Main M-Pesa class
│   ├── Engine/                     # Core engine components
│   │   ├── Core.php               # Request handling
│   │   ├── Config.php             # Configuration management
│   │   ├── Cache.php              # Token caching
│   │   ├── CurlRequest.php        # HTTP client
│   │   └── MpesaTrait.php         # M-Pesa methods
│   ├── Auth/                       # Authentication
│   │   └── Authenticator.php      # OAuth handler
│   ├── LipaNaMpesaOnline/         # STK Push
│   ├── C2B/                        # Customer to Business
│   ├── B2C/                        # Business to Customer
│   ├── B2B/                        # Business to Business
│   ├── AccountBalance/            # Balance queries
│   ├── TransactionStatus/         # Status checks
│   ├── Reversal/                  # Transaction reversals
│   ├── Validation/                # Input validation
│   ├── Contracts/                 # Interfaces
│   └── Exceptions/                # Custom exceptions
│
├── gateway/                        # API Gateway
│   ├── index.php                  # Entry point
│   ├── .htaccess                  # Apache config
│   ├── Core/                      # Gateway core
│   │   ├── Router.php            # HTTP routing
│   │   ├── Request.php           # Request handling
│   │   └── Response.php          # Response formatting
│   ├── Middleware/                # Middleware
│   │   ├── AuthMiddleware.php    # API authentication
│   │   ├── CorsMiddleware.php    # CORS handling
│   │   └── RateLimitMiddleware.php # Rate limiting
│   ├── Controllers/               # API Controllers
│   │   ├── BaseController.php    # Base controller
│   │   ├── STKPushController.php # STK Push API
│   │   ├── C2BController.php     # C2B API
│   │   ├── B2CController.php     # B2C API
│   │   ├── B2BController.php     # B2B API
│   │   ├── AccountController.php # Balance API
│   │   ├── TransactionController.php # Transaction API
│   │   ├── CallbackController.php # Callback handler
│   │   ├── HealthController.php  # Health checks
│   │   └── DocsController.php    # Documentation
│   ├── README.md                  # Gateway documentation
│   ├── QUICKSTART.md             # Quick start guide
│   ├── IMPLEMENTATION_SUMMARY.md # Implementation details
│   ├── postman_collection.json   # Postman collection
│   └── test.sh                   # Test script
│
├── storage/                       # Storage directory
│   ├── logs/                     # Application logs
│   │   ├── transactions.log      # Transaction logs
│   │   ├── callbacks.log         # Callback logs
│   │   └── errors.log            # Error logs
│   ├── cache/                    # Cache storage
│   └── rate_limits/              # Rate limit data
│
├── .env                          # Environment configuration
├── composer.json                 # Composer configuration
└── README.md                     # This file
```

## 🎯 Components

### 1. M-Pesa Package (`Mpesa/`)

The core M-Pesa integration package that handles:
- OAuth authentication
- API request/response handling
- Input validation
- Security credential computation
- Token caching

**Key Features:**
- ✅ All M-Pesa APIs supported
- ✅ Automatic token management
- ✅ Comprehensive validation
- ✅ Error handling
- ✅ Multi-app support

### 2. API Gateway (`gateway/`)

A RESTful API Gateway built on top of the M-Pesa package:
- RESTful API endpoints
- API key authentication
- Rate limiting
- CORS support
- Transaction logging
- Comprehensive documentation

**Key Features:**
- ✅ RESTful design
- ✅ Secure authentication
- ✅ Rate limiting
- ✅ Auto-generated docs
- ✅ Transaction history
- ✅ Callback handling

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- Composer
- M-Pesa Developer Account

### Installation

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   
   Your `.env` file is already configured with:
   ```env
   MPESA_ENV=sandbox
   MPESA_CONSUMER_KEY=your_key
   MPESA_CONSUMER_SECRET=your_secret
   MPESA_SHORTCODE=174379
   MPESA_PASSKEY=your_passkey
   ```

3. **Create storage directories:**
   ```bash
   mkdir -p storage/logs storage/cache storage/rate_limits
   chmod -R 755 storage
   ```

4. **Start the API Gateway:**
   ```bash
   cd gateway
   php -S localhost:8000
   ```

5. **Test the API:**
   ```bash
   curl http://localhost:8000/api/v1/health
   ```

## 📖 Usage

### Using the M-Pesa Package Directly

```php
<?php
require 'vendor/autoload.php';

use Yourdudeken\Mpesa\Init;

$config = [
    'is_sandbox' => true,
    'apiUrl' => 'https://sandbox.safaricom.co.ke/',
    'apps' => [
        'default' => [
            'consumer_key' => 'your_key',
            'consumer_secret' => 'your_secret'
        ]
    ],
    'lnmo' => [
        'short_code' => '174379',
        'passkey' => 'your_passkey',
        'callback' => 'https://yourdomain.com/callback'
    ]
];

$mpesa = new Init($config);

// Initiate STK Push
$response = $mpesa->STKPush([
    'PhoneNumber' => '254712345678',
    'Amount' => 100,
    'AccountReference' => 'INV001',
    'TransactionDesc' => 'Payment'
]);
```

### Using the API Gateway

```bash
# Initiate STK Push
curl -X POST http://localhost:8000/api/v1/stkpush \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "254712345678",
    "amount": 100,
    "account_reference": "INV001",
    "transaction_desc": "Payment"
  }'
```

## 📚 Documentation

- **API Gateway Documentation:** [gateway/README.md](gateway/README.md)
- **Quick Start Guide:** [gateway/QUICKSTART.md](gateway/QUICKSTART.md)
- **Implementation Summary:** [gateway/IMPLEMENTATION_SUMMARY.md](gateway/IMPLEMENTATION_SUMMARY.md)
- **API Docs Endpoint:** `GET /api/v1/docs`
- **Postman Collection:** [gateway/postman_collection.json](gateway/postman_collection.json)

## 🔌 API Endpoints

### Public Endpoints
- `GET /api/v1/health` - Health check
- `GET /api/v1/docs` - API documentation

### Protected Endpoints (Require API Key)

#### STK Push
- `POST /api/v1/stkpush` - Initiate STK Push
- `POST /api/v1/stkpush/query` - Query STK status

#### C2B
- `POST /api/v1/c2b/register` - Register URLs
- `POST /api/v1/c2b/simulate` - Simulate C2B

#### B2C
- `POST /api/v1/b2c/payment` - Send money to customer

#### B2B
- `POST /api/v1/b2b/payment` - Transfer to business

#### Account
- `POST /api/v1/account/balance` - Query balance

#### Transactions
- `POST /api/v1/transaction/status` - Check status
- `POST /api/v1/transaction/reversal` - Reverse transaction
- `GET /api/v1/transactions` - Transaction history
- `GET /api/v1/transactions/{id}` - Get transaction

## 🧪 Testing

### Run Automated Tests
```bash
cd gateway
./test.sh
```

### Import Postman Collection
Import `gateway/postman_collection.json` into Postman for easy testing.

## 📊 Monitoring

### View Logs
```bash
# Transaction logs
tail -f storage/logs/transactions.log

# Callback logs
tail -f storage/logs/callbacks.log

# Error logs
tail -f storage/logs/errors.log
```

## 🔐 Security

- **API Key Authentication** - Secure your endpoints
- **Rate Limiting** - 100 requests/minute per IP
- **Input Validation** - All inputs validated
- **HTTPS Required** - Use HTTPS in production
- **Environment Variables** - Credentials stored securely

## 🌍 Environment Configuration

### Development
```env
APP_ENV=local
APP_DEBUG=true
MPESA_ENV=sandbox
API_KEYS=dev_api_key_12345
```

### Production
```env
APP_ENV=production
APP_DEBUG=false
MPESA_ENV=production
API_KEYS=your_secure_production_key
```

## 📝 Supported M-Pesa Services

| Service | Package Support | API Gateway | Status |
|---------|----------------|-------------|--------|
| STK Push | ✅ | ✅ | Ready |
| C2B | ✅ | ✅ | Ready |
| B2C | ✅ | ✅ | Ready |
| B2B | ✅ | ✅ | Ready |
| Account Balance | ✅ | ✅ | Ready |
| Transaction Status | ✅ | ✅ | Ready |
| Reversal | ✅ | ✅ | Ready |

## 🛠️ Development

### Composer Scripts
```bash
# Start development server
composer serve

# Run tests
composer test
```

### Directory Permissions
```bash
chmod -R 755 storage
chmod +x gateway/test.sh
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open-source and available under the MIT License.

## 👤 Author

**Kennedy Muthengi**
- Email: kenmwendwamuthengi@gmail.com
- GitHub: @yourdudeken

## 🆘 Support

For issues and questions:
- Check the logs in `storage/logs/`
- Review the documentation in `gateway/`
- Access API docs at `/api/v1/docs`
- Email: kenmwendwamuthengi@gmail.com

## 🎉 Acknowledgments

- Safaricom M-Pesa API
- PHP Community

---

**Note:** Always test in sandbox mode before deploying to production.

**Version:** 1.0.0  
**Last Updated:** December 15, 2024
