# M-Pesa Library - API Wrapper Summary

##  What Was Created

A complete RESTful API wrapper for the M-Pesa library with enterprise-grade features:

###  Directory Structure

```
api/
├── config/
│   └── api.php                    # API configuration (keys, CORS, rate limits)
├── controllers/
│   ├── BaseController.php         # Base controller with common methods
│   └── MpesaController.php        # M-Pesa endpoints controller
├── middleware/
│   ├── AuthMiddleware.php         # API key authentication
│   ├── CorsMiddleware.php         # CORS handling
│   └── RateLimitMiddleware.php    # Rate limiting
├── routes/
│   └── Router.php                 # Simple routing system
├── index.php                      # Main API entry point
├── .htaccess                      # Apache URL rewriting
├── .env.example                   # Environment variables template
├── README.md                      # Comprehensive documentation
└── postman_collection.json        # Postman collection for testing
```

##  Security Features

### 1. **API Key Authentication**
- Multiple API keys support
- Per-key rate limits
- Active/inactive status
- Three authentication methods:
  - `X-API-Key` header (recommended)
  - `Authorization: Bearer` header
  - Query parameter (development only)

### 2. **CORS Protection**
- Configurable allowed origins
- Allowed methods and headers
- Preflight request handling
- Credentials support

### 3. **Rate Limiting**
- Per-API-key limits
- Configurable requests per minute
- File-based storage
- Rate limit headers in responses

##  API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/health` | GET | Health check (no auth) |
| `/api/stk-push` | POST | Initiate STK Push payment |
| `/api/stk-query` | POST | Query STK Push status |
| `/api/b2c` | POST | Business to Customer payment |
| `/api/b2b` | POST | Business to Business payment |
| `/api/c2b/register` | POST | Register C2B URLs |
| `/api/c2b/simulate` | POST | Simulate C2B payment |
| `/api/balance` | POST | Check account balance |
| `/api/transaction-status` | POST | Check transaction status |
| `/api/reversal` | POST | Reverse a transaction |

##  Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "timestamp": "2025-12-06T20:30:00+03:00",
  "request_id": "req_123456"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error description",
    "details": { ... }
  },
  "timestamp": "2025-12-06T20:30:00+03:00",
  "request_id": "req_123456"
}
```

##  Quick Start

### 1. Start Development Server
```bash
./start-api.sh
```

### 2. Test the API
```bash
./test-api.sh
```

### 3. Manual Testing
```bash
# Health check
curl http://localhost:8000/api/health

# STK Push (with authentication)
curl -X POST http://localhost:8000/api/stk-push \
  -H "X-API-Key: demo-api-key-12345" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100,
    "phoneNumber": "254712345678",
    "accountReference": "Test123",
    "transactionDesc": "Test payment",
    "callBackURL": "https://yourdomain.com/callback"
  }'
```

##  Features Implemented

 **Authentication & Authorization**
- API key validation
- Multiple authentication methods
- Per-client configuration

 **Request Handling**
- JSON input/output
- Request validation
- Error handling
- Request ID tracking

 **Security**
- CORS protection
- Rate limiting
- Input validation
- Security headers

 **Monitoring & Logging**
- Request/response logging
- Error logging
- Configurable log levels
- File-based logging

 **Developer Experience**
- Comprehensive documentation
- Postman collection
- Example configurations
- Quick start scripts
- Test scripts

##  Configuration

### API Keys (`api/config/api.php`)
```php
'api_keys' => [
    'your-api-key' => [
        'name' => 'Client Name',
        'active' => true,
        'rate_limit' => 100,
    ],
],
```

### CORS Settings
```php
'cors' => [
    'allowed_origins' => ['*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
],
```

### Rate Limiting
```php
'rate_limit' => [
    'enabled' => true,
    'default_limit' => 60, // requests per minute
],
```

##  Dependencies

- PHP 7.4+ (PHP 8.3 compatible)
- M-Pesa library (already installed)
- Apache/Nginx (for production)
- cURL (for testing)

##  Deployment

### Development
```bash
cd api && php -S localhost:8000
```

### Production (Apache)
1. Point document root to `/path/to/mpesa/api`
2. Ensure mod_rewrite is enabled
3. Configure SSL/TLS
4. Set environment variables
5. Update CORS settings

### Production (Nginx)
```nginx
location /api {
    try_files $uri $uri/ /api/index.php?$query_string;
}
```

##  Documentation

- **API README**: `api/README.md` - Complete API documentation
- **Postman Collection**: `api/postman_collection.json` - Import into Postman
- **Environment Template**: `api/.env.example` - Configuration template

##  Testing

### Automated Tests
```bash
./test-api.sh
```

### Manual Testing
1. Import `api/postman_collection.json` into Postman
2. Set variables:
   - `base_url`: `http://localhost:8000/api`
   - `api_key`: `demo-api-key-12345`
3. Test endpoints

##  Security Best Practices

1.  Use strong, unique API keys
2.  Enable HTTPS in production
3.  Restrict CORS origins
4.  Monitor API logs
5.  Rotate API keys regularly
6.  Set appropriate rate limits
7.  Validate all inputs
8.  Keep dependencies updated

##  Next Steps

1. **Configure M-Pesa Credentials**
   - Update `src/config/mpesa.php`
   - Set consumer key, secret, shortcode, passkey

2. **Generate Production API Keys**
   - Create strong random keys
   - Store in environment variables
   - Update `api/config/api.php`

3. **Set Up Callbacks**
   - Create callback endpoints
   - Update callback URLs in requests
   - Handle M-Pesa responses

4. **Deploy to Production**
   - Configure web server
   - Enable SSL/TLS
   - Set up monitoring
   - Configure backups

##  Summary

You now have a production-ready REST API wrapper for the M-Pesa library with:

-  Secure API key authentication
-  CORS protection
-  Rate limiting
-  Request validation
-  Error handling
-  Logging
-  Complete documentation
-  Testing tools

The API is ready to use for development and can be deployed to production with minimal configuration changes.
