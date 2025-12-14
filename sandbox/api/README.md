# M-Pesa REST API Wrapper

A secure RESTful API wrapper for the M-Pesa library with API key authentication, CORS support, and rate limiting.

## Features

-  **API Key Authentication** - Secure your API with API keys
-  **CORS Support** - Configurable Cross-Origin Resource Sharing
-  **Rate Limiting** - Prevent API abuse with configurable rate limits
-  **Request Logging** - Track all API requests and responses
-  **Error Handling** - Consistent error responses
-  **JSON Responses** - All responses in JSON format
-  **Health Check** - Monitor API status

## Installation

1. Ensure all dependencies are installed:
```bash
composer install
```

2. Configure your M-Pesa credentials in `src/config/mpesa.php`

3. Set up your API key in `api/config/api.php` or use environment variables:
```bash
export MPESA_API_KEY="your-secure-api-key"
```

4. Create necessary directories:
```bash
mkdir -p cache/rate_limit logs
chmod 755 cache logs
```

## Configuration

### API Keys

Edit `api/config/api.php` to add your API keys:

```php
'api_keys' => [
    'your-api-key-here' => [
        'name' => 'Client Name',
        'active' => true,
        'rate_limit' => 100, // requests per minute
    ],
],
```

### CORS Settings

Configure allowed origins in `api/config/api.php`:

```php
'cors' => [
    'allowed_origins' => [
        'https://yourdomain.com',
        'https://app.yourdomain.com',
    ],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
],
```

## Usage

### Authentication

Include your API key in one of the following ways:

**Header (Recommended):**
```bash
curl -H "X-API-Key: your-api-key-here" https://your-domain.com/api/health
```

**Bearer Token:**
```bash
curl -H "Authorization: Bearer your-api-key-here" https://your-domain.com/api/health
```

**Query Parameter (Not recommended for production):**
```bash
curl https://your-domain.com/api/health?api_key=your-api-key-here
```

## API Endpoints

### Health Check
Check API status (no authentication required)

**Endpoint:** `GET /api/health`

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "service": "M-Pesa API",
    "version": "1.0.0"
  },
  "timestamp": "2025-12-06T20:30:00+03:00",
  "request_id": "req_123456"
}
```

### STK Push
Initiate M-Pesa payment request

**Endpoint:** `POST /api/stk-push`

**Request Body:**
```json
{
  "amount": 100,
  "phoneNumber": "254712345678",
  "accountReference": "Order123",
  "transactionDesc": "Payment for Order 123",
  "callBackURL": "https://yourdomain.com/callback"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResponseCode": "0",
    "ResponseDescription": "Success. Request accepted for processing",
    "CustomerMessage": "Success. Request accepted for processing"
  },
  "timestamp": "2025-12-06T20:30:00+03:00",
  "request_id": "req_123456"
}
```

### STK Query
Check STK Push payment status

**Endpoint:** `POST /api/stk-query`

**Request Body:**
```json
{
  "CheckoutRequestID": "ws_CO_191220191020363925"
}
```

### B2C Payment
Business to Customer payment

**Endpoint:** `POST /api/b2c`

**Request Body:**
```json
{
  "amount": 1000,
  "partyB": "254712345678",
  "remarks": "Salary payment",
  "resultURL": "https://yourdomain.com/result",
  "queueTimeOutURL": "https://yourdomain.com/timeout"
}
```

### B2B Payment
Business to Business payment

**Endpoint:** `POST /api/b2b`

**Request Body:**
```json
{
  "amount": 5000,
  "partyB": "600000",
  "accountReference": "Invoice123",
  "remarks": "Payment for services",
  "resultURL": "https://yourdomain.com/result",
  "queueTimeOutURL": "https://yourdomain.com/timeout"
}
```

### B2Pochi Payment
Business to Pochi payment (send money to M-Pesa Pochi savings accounts)

**Endpoint:** `POST /api/b2pochi`

**Request Body:**
```json
{
  "OriginatorConversationID": "B2P_12345",
  "InitiatorName": "testapi",
  "initiatorPassword": "Safaricom999!*!",
  "CommandID": "BusinessPayToPochi",
  "Amount": 1000,
  "PartyA": "600000",
  "PartyB": "254712345678",
  "Remarks": "Pochi savings payment",
  "ResultURL": "https://yourdomain.com/result",
  "QueueTimeOutURL": "https://yourdomain.com/timeout",
  "Occasion": "Monthly savings"
}
```

**Note:** Provide either `SecurityCredential` (pre-encrypted) or `initiatorPassword` (will be encrypted automatically).

### C2B Register
Register C2B validation and confirmation URLs

**Endpoint:** `POST /api/c2b/register`

**Request Body:**
```json
{
  "confirmationURL": "https://yourdomain.com/confirmation",
  "validationURL": "https://yourdomain.com/validation"
}
```

### C2B Simulate
Simulate C2B payment (Sandbox only)

**Endpoint:** `POST /api/c2b/simulate`

**Request Body:**
```json
{
  "amount": 100,
  "phoneNumber": "254712345678",
  "billRefNumber": "INV123"
}
```

### Account Balance
Check M-Pesa account balance

**Endpoint:** `POST /api/balance`

**Request Body:**
```json
{
  "partyB": "600000",
  "remarks": "Balance check",
  "resultURL": "https://yourdomain.com/result",
  "queueTimeOutURL": "https://yourdomain.com/timeout"
}
```

### Transaction Status
Check transaction status

**Endpoint:** `POST /api/transaction-status`

**Request Body:**
```json
{
  "TransactionID": "OEI2AK4Q16",
  "partyB": "600000",
  "remarks": "Status check",
  "resultURL": "https://yourdomain.com/result",
  "queueTimeOutURL": "https://yourdomain.com/timeout"
}
```

### Reversal
Reverse a transaction

**Endpoint:** `POST /api/reversal`

**Request Body:**
```json
{
  "amount": 100,
  "transactionID": "OEI2AK4Q16",
  "remarks": "Reversal reason",
  "resultURL": "https://yourdomain.com/result",
  "queueTimeOutURL": "https://yourdomain.com/timeout"
}
```

## Error Responses

All errors follow this format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message",
    "details": {
      "additional": "error details"
    }
  },
  "timestamp": "2025-12-06T20:30:00+03:00",
  "request_id": "req_123456"
}
```

### Common Error Codes

- `UNAUTHORIZED` - Invalid or missing API key
- `RATE_LIMIT_EXCEEDED` - Too many requests
- `VALIDATION_ERROR` - Missing or invalid request parameters
- `NOT_FOUND` - Endpoint not found
- `INTERNAL_ERROR` - Server error

## Rate Limiting

Rate limits are enforced per API key. Default is 60 requests per minute.

**Rate Limit Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1638806400
```

## Testing

### Using cURL

```bash
# Health check
curl -X GET https://your-domain.com/api/health

# STK Push
curl -X POST https://your-domain.com/api/stk-push \
  -H "X-API-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100,
    "phoneNumber": "254712345678",
    "accountReference": "Test123",
    "transactionDesc": "Test payment",
    "callBackURL": "https://yourdomain.com/callback"
  }'
```

### Using Postman

1. Import the API endpoints
2. Set `X-API-Key` header with your API key
3. Send requests to test endpoints

## Development Server

For local development, you can use PHP's built-in server:

```bash
cd api
php -S localhost:8000
```

Then access the API at `http://localhost:8000/api/health`

## Production Deployment

1. **Use HTTPS** - Always use SSL/TLS in production
2. **Secure API Keys** - Store API keys in environment variables
3. **Configure CORS** - Restrict allowed origins
4. **Enable Logging** - Monitor API usage
5. **Set Rate Limits** - Prevent abuse
6. **Use Web Server** - Apache or Nginx for production

### Apache Configuration

Ensure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Nginx Configuration

```nginx
location /api {
    try_files $uri $uri/ /api/index.php?$query_string;
}
```

## Security Best Practices

1.  Use strong, unique API keys
2.  Rotate API keys regularly
3.  Implement IP whitelisting if needed
4.  Monitor API logs for suspicious activity
5.  Use HTTPS only
6.  Validate all input data
7.  Keep dependencies updated

## Logging

API logs are stored in `logs/api.log`. Configure logging in `api/config/api.php`:

```php
'logging' => [
    'enabled' => true,
    'log_file' => __DIR__ . '/../../logs/api.log',
    'log_level' => 'info',
],
```

## Support

For issues and questions:
- Check the main library documentation
- Review API logs
- Contact support

## License

MIT License - See LICENSE.txt for details
