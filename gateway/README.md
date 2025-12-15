# M-Pesa API Gateway

A comprehensive RESTful API Gateway for M-Pesa integration in PHP. This gateway provides a clean, modern interface for all M-Pesa services including STK Push, C2B, B2C, B2B, Account Balance, Transaction Status, and Reversals.

## Features

- ✅ **STK Push (Lipa Na M-Pesa Online)** - Initiate and query payment requests
- ✅ **C2B (Customer to Business)** - Register URLs and simulate transactions
- ✅ **B2C (Business to Customer)** - Send money to customers
- ✅ **B2B (Business to Business)** - Transfer funds between businesses
- ✅ **Account Balance** - Query M-Pesa account balance
- ✅ **Transaction Status** - Check status of any transaction
- ✅ **Reversals** - Reverse completed transactions
- ✅ **Transaction History** - View and filter transaction logs
- ✅ **API Key Authentication** - Secure your endpoints
- ✅ **Rate Limiting** - Prevent API abuse
- ✅ **CORS Support** - Enable cross-origin requests
- ✅ **Comprehensive Logging** - Track all transactions and errors
- ✅ **Auto-generated Documentation** - Built-in API docs

## Requirements

- PHP 7.4 or higher
- Composer
- M-Pesa Developer Account (Sandbox or Production)
- Web server (Apache/Nginx)

## Installation

1. **Clone the repository**
   ```bash
   cd /home/kennedy/vscode/github/yourdudeken/mpesa
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment variables**
   
   Update your `.env` file with your M-Pesa credentials:
   ```env
   # Application Configuration
   APP_NAME="M-Pesa Gateway API"
   APP_ENV=local
   APP_DEBUG=true
   APP_PORT=8000
   APP_URL=http://localhost:8000

   # M-Pesa Environment (sandbox or production)
   MPESA_ENV=sandbox

   # M-Pesa API Credentials
   MPESA_CONSUMER_KEY=your_consumer_key
   MPESA_CONSUMER_SECRET=your_consumer_secret

   # M-Pesa Configuration
   MPESA_SHORTCODE=174379
   MPESA_PASSKEY=your_passkey
   MPESA_INITIATOR_NAME=testapi
   MPESA_INITIATOR_PASSWORD=Safaricom123!!

   # API Keys (comma-separated)
   API_KEYS=dev_api_key_12345,prod_api_key_67890
   ```

4. **Create required directories**
   ```bash
   mkdir -p storage/logs storage/cache storage/rate_limits
   chmod -R 755 storage
   ```

5. **Configure web server**

   For Apache, the `.htaccess` file is already configured.
   
   For Nginx, add this to your server block:
   ```nginx
   location / {
       try_files $uri $uri/ /gateway/index.php?$query_string;
   }
   ```

## Usage

### Starting the Development Server

```bash
cd gateway
php -S localhost:8000
```

### API Endpoints

#### Health Check
```bash
GET /api/v1/health
```

#### API Documentation
```bash
GET /api/v1/docs
```

#### STK Push (Lipa Na M-Pesa Online)

**Initiate STK Push:**
```bash
POST /api/v1/stkpush
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "phone_number": "254712345678",
  "amount": 100,
  "account_reference": "INV001",
  "transaction_desc": "Payment for invoice"
}
```

**Query STK Push Status:**
```bash
POST /api/v1/stkpush/query
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "checkout_request_id": "ws_CO_123456789"
}
```

#### C2B (Customer to Business)

**Register URLs:**
```bash
POST /api/v1/c2b/register
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "short_code": "174379",
  "confirmation_url": "https://yourdomain.com/api/v1/callbacks/c2b/confirmation",
  "validation_url": "https://yourdomain.com/api/v1/callbacks/c2b/validation"
}
```

**Simulate C2B (Sandbox only):**
```bash
POST /api/v1/c2b/simulate
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "phone_number": "254712345678",
  "amount": 100,
  "bill_ref_number": "BILL001"
}
```

#### B2C (Business to Customer)

```bash
POST /api/v1/b2c/payment
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "phone_number": "254712345678",
  "amount": 500,
  "remarks": "Salary payment",
  "occasion": "Monthly salary"
}
```

#### B2B (Business to Business)

```bash
POST /api/v1/b2b/payment
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "receiver_shortcode": "600000",
  "amount": 1000,
  "remarks": "Payment for services",
  "account_reference": "ACC001"
}
```

#### Account Balance

```bash
POST /api/v1/account/balance
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "remarks": "Balance inquiry"
}
```

#### Transaction Status

```bash
POST /api/v1/transaction/status
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "transaction_id": "OEI2AK4Q16",
  "remarks": "Status check"
}
```

#### Transaction Reversal

```bash
POST /api/v1/transaction/reversal
Headers:
  Authorization: Bearer your_api_key
  Content-Type: application/json

Body:
{
  "transaction_id": "OEI2AK4Q16",
  "amount": 100,
  "remarks": "Reversal request"
}
```

#### Transaction History

```bash
GET /api/v1/transactions?page=1&per_page=20&type=STK_PUSH
Headers:
  Authorization: Bearer your_api_key
```

#### Get Specific Transaction

```bash
GET /api/v1/transactions/{transaction_id}
Headers:
  Authorization: Bearer your_api_key
```

## Authentication

The API uses API Key authentication. You can provide the API key in two ways:

1. **Bearer Token in Authorization header:**
   ```
   Authorization: Bearer your_api_key
   ```

2. **X-API-Key header:**
   ```
   X-API-Key: your_api_key
   ```

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // Response data
  },
  "timestamp": "2024-12-15 20:30:00"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // Optional error details
  },
  "timestamp": "2024-12-15 20:30:00"
}
```

## Callbacks

M-Pesa will send callbacks to the following endpoints:

- **STK Push:** `/api/v1/callbacks/stkpush`
- **C2B Validation:** `/api/v1/callbacks/c2b/validation`
- **C2B Confirmation:** `/api/v1/callbacks/c2b/confirmation`
- **B2C Result:** `/api/v1/callbacks/b2c/result`
- **B2C Timeout:** `/api/v1/callbacks/b2c/timeout`
- **B2B Result:** `/api/v1/callbacks/b2b/result`
- **B2B Timeout:** `/api/v1/callbacks/b2b/timeout`
- **Balance Result:** `/api/v1/callbacks/balance/result`
- **Balance Timeout:** `/api/v1/callbacks/balance/timeout`
- **Reversal Result:** `/api/v1/callbacks/reversal/result`
- **Reversal Timeout:** `/api/v1/callbacks/reversal/timeout`
- **Status Result:** `/api/v1/callbacks/status/result`
- **Status Timeout:** `/api/v1/callbacks/status/timeout`

All callbacks are logged in `storage/logs/callbacks.log`.

## Logging

The gateway maintains three log files:

- **transactions.log** - All transaction requests and responses
- **callbacks.log** - All M-Pesa callbacks
- **errors.log** - All errors and exceptions

Logs are stored in `storage/logs/`.

## Rate Limiting

The API implements rate limiting to prevent abuse:
- **Default:** 100 requests per minute per IP address
- Rate limit data is stored in `storage/rate_limits/`

## Security Best Practices

1. **Use HTTPS in production** - Never use HTTP for production deployments
2. **Rotate API keys regularly** - Change your API keys periodically
3. **Whitelist IPs** - Consider IP whitelisting for additional security
4. **Monitor logs** - Regularly check logs for suspicious activity
5. **Use environment variables** - Never hardcode credentials
6. **Validate callbacks** - Implement additional validation for M-Pesa callbacks

## Testing

### Using cURL

```bash
# Health check
curl http://localhost:8000/api/v1/health

# STK Push
curl -X POST http://localhost:8000/api/v1/stkpush \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "254712345678",
    "amount": 100,
    "account_reference": "TEST001",
    "transaction_desc": "Test payment"
  }'
```

### Using Postman

Import the API documentation from `/api/v1/docs` to get started quickly.

## Troubleshooting

### Common Issues

1. **"Invalid API key" error**
   - Check that your API key is correctly set in the Authorization header
   - Verify API_KEYS in .env file

2. **"Route not found" error**
   - Ensure .htaccess is properly configured
   - Check that mod_rewrite is enabled

3. **M-Pesa authentication errors**
   - Verify MPESA_CONSUMER_KEY and MPESA_CONSUMER_SECRET
   - Check that you're using the correct environment (sandbox/production)

4. **Callback not received**
   - Ensure your callback URLs are publicly accessible
   - Check that APP_URL in .env is correct
   - Verify firewall settings

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-source and available under the MIT License.

## Support

For issues and questions:
- Check the logs in `storage/logs/`
- Review the API documentation at `/api/v1/docs`
- Contact: kenmwendwamuthengi@gmail.com

## Author

Kennedy Muthengi
- Email: kenmwendwamuthengi@gmail.com
- GitHub: @yourdudeken

---

**Note:** This is a production-ready API gateway. Always test thoroughly in sandbox mode before deploying to production.
