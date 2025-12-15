# M-Pesa API Gateway - Quick Start Guide

This guide will help you get started with the M-Pesa API Gateway quickly.

## Table of Contents
1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Running the Gateway](#running-the-gateway)
4. [Making Your First Request](#making-your-first-request)
5. [Common Use Cases](#common-use-cases)
6. [Troubleshooting](#troubleshooting)

## Installation

### Step 1: Install Dependencies

```bash
cd /home/kennedy/vscode/github/yourdudeken/mpesa
composer install
```

### Step 2: Create Storage Directories

```bash
mkdir -p storage/logs storage/cache storage/rate_limits
chmod -R 755 storage
```

### Step 3: Configure Environment

Your `.env` file is already configured. Make sure these values are correct:

```env
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=fduEAZl8XCBAA5dXsoMK4d0EI278jGpcZSDGslWNAuVAGvRP
MPESA_CONSUMER_SECRET=of2dQDr3TaQKT6PWKClb5jpu5ooigb9AIcOLStzF2lR8EMM9SOYzfj4XIS0lbH0o
MPESA_SHORTCODE=174379
MPESA_PASSKEY=bfbo279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
```

## Configuration

### API Keys

Add your API keys to the `.env` file:

```env
API_KEYS=dev_api_key_12345,your_production_key
```

For development, the default key `dev_api_key_12345` is already configured.

### Callback URLs

The gateway automatically configures callback URLs based on `APP_URL`:

```env
APP_URL=http://localhost:8000
```

For production, change this to your public domain:

```env
APP_URL=https://yourdomain.com
```

## Running the Gateway

### Development Server

```bash
cd gateway
php -S localhost:8000
```

The API will be available at `http://localhost:8000`

### Production Server

For production, configure Apache or Nginx to point to the `gateway` directory.

#### Apache Configuration

The `.htaccess` file is already configured. Just ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx Configuration

Add this to your server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/mpesa/gateway;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Making Your First Request

### 1. Test the Health Endpoint

```bash
curl http://localhost:8000/api/v1/health
```

Expected response:
```json
{
  "success": true,
  "message": "Service is healthy",
  "data": {
    "status": "healthy",
    "timestamp": "2024-12-15 20:30:00",
    "environment": "local",
    "mpesa_environment": "sandbox"
  }
}
```

### 2. View API Documentation

```bash
curl http://localhost:8000/api/v1/docs
```

### 3. Initiate an STK Push

```bash
curl -X POST http://localhost:8000/api/v1/stkpush \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "254712345678",
    "amount": 10,
    "account_reference": "TEST001",
    "transaction_desc": "Test payment"
  }'
```

## Common Use Cases

### Use Case 1: Accept Payment via STK Push

**Step 1: Initiate STK Push**

```bash
curl -X POST http://localhost:8000/api/v1/stkpush \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "254712345678",
    "amount": 100,
    "account_reference": "ORDER123",
    "transaction_desc": "Payment for Order #123"
  }'
```

**Step 2: Check Status**

Save the `CheckoutRequestID` from the response, then:

```bash
curl -X POST http://localhost:8000/api/v1/stkpush/query \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "checkout_request_id": "ws_CO_123456789"
  }'
```

**Step 3: Handle Callback**

M-Pesa will send a callback to `/api/v1/callbacks/stkpush` when the customer completes payment. Check `storage/logs/callbacks.log` for the callback data.

### Use Case 2: Send Money to Customer (B2C)

```bash
curl -X POST http://localhost:8000/api/v1/b2c/payment \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "254712345678",
    "amount": 500,
    "remarks": "Salary payment for December",
    "occasion": "Monthly salary"
  }'
```

Check `storage/logs/callbacks.log` for the result.

### Use Case 3: Register C2B URLs

```bash
curl -X POST http://localhost:8000/api/v1/c2b/register \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{}'
```

This registers the default callback URLs from your configuration.

### Use Case 4: Simulate C2B Payment (Sandbox Only)

```bash
curl -X POST http://localhost:8000/api/v1/c2b/simulate \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "254712345678",
    "amount": 100,
    "bill_ref_number": "ACCOUNT123"
  }'
```

### Use Case 5: Check Account Balance

```bash
curl -X POST http://localhost:8000/api/v1/account/balance \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "remarks": "Daily balance check"
  }'
```

Check `storage/logs/callbacks.log` for the balance result.

### Use Case 6: Reverse a Transaction

```bash
curl -X POST http://localhost:8000/api/v1/transaction/reversal \
  -H "Authorization: Bearer dev_api_key_12345" \
  -H "Content-Type: application/json" \
  -d '{
    "transaction_id": "OEI2AK4Q16",
    "amount": 100,
    "remarks": "Customer refund request"
  }'
```

### Use Case 7: View Transaction History

```bash
curl -X GET "http://localhost:8000/api/v1/transactions?page=1&per_page=20&type=STK_PUSH" \
  -H "Authorization: Bearer dev_api_key_12345"
```

## Testing with the Test Script

Run the automated test script:

```bash
cd gateway
./test.sh
```

This will test all major endpoints and display the results.

## Monitoring and Logs

### View Transaction Logs

```bash
tail -f storage/logs/transactions.log
```

### View Callback Logs

```bash
tail -f storage/logs/callbacks.log
```

### View Error Logs

```bash
tail -f storage/logs/errors.log
```

### Parse Logs with jq

```bash
# View last 5 transactions
tail -n 5 storage/logs/transactions.log | jq '.'

# Filter by transaction type
grep "STK_PUSH" storage/logs/transactions.log | jq '.'

# View callbacks from today
grep "$(date +%Y-%m-%d)" storage/logs/callbacks.log | jq '.'
```

## Troubleshooting

### Issue: "Invalid API key"

**Solution:**
- Check that you're sending the API key in the Authorization header
- Verify the API key is in the `API_KEYS` environment variable
- For development, use `dev_api_key_12345`

### Issue: "Route not found"

**Solution:**
- Ensure you're accessing the correct URL
- Check that the development server is running
- Verify `.htaccess` is properly configured

### Issue: M-Pesa authentication failed

**Solution:**
- Verify `MPESA_CONSUMER_KEY` and `MPESA_CONSUMER_SECRET` in `.env`
- Check that `MPESA_ENV` is set to `sandbox` for testing
- Ensure your M-Pesa app credentials are active

### Issue: Callback not received

**Solution:**
- For local development, use ngrok to expose your local server:
  ```bash
  ngrok http 8000
  ```
- Update `APP_URL` in `.env` to the ngrok URL
- Restart the server

### Issue: STK Push fails

**Solution:**
- Verify `MPESA_SHORTCODE` and `MPESA_PASSKEY` are correct
- Ensure phone number is in format `254XXXXXXXXX`
- Check that amount is at least 1 KES
- For sandbox, use test credentials from Safaricom

## Next Steps

1. **Integrate with your application** - Use the API endpoints in your web or mobile app
2. **Set up webhooks** - Process callbacks in real-time
3. **Add database storage** - Store transactions in a database
4. **Implement notifications** - Send SMS/Email notifications on payment
5. **Add monitoring** - Set up error tracking and monitoring
6. **Go to production** - Switch to production credentials when ready

## Support

- **Documentation:** `/api/v1/docs`
- **Email:** kenmwendwamuthengi@gmail.com
- **Logs:** `storage/logs/`

## Security Checklist

Before going to production:

- [ ] Change all API keys
- [ ] Use HTTPS only
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Switch to production M-Pesa credentials
- [ ] Implement IP whitelisting
- [ ] Set up monitoring and alerts
- [ ] Regular backup of logs
- [ ] Implement rate limiting adjustments
- [ ] Review and test all callback handlers

---

**Happy coding!** 🚀
