# M-Pesa Payment System

A complete, production-ready full-stack web application for M-Pesa payment integration using the M-Pesa DARAJA API.

## Features

### Frontend
- Modern, responsive dark-themed UI
- Real-time dashboard with transaction statistics
- Payment initiation interface with validation
- Transaction history with filtering
- Callback logs viewer
- Auto-refresh functionality
- Toast notifications
- Modal dialogs for transaction details

### Backend
- RESTful API for payment operations
- SQLite database for transaction storage
- Automatic callback handling
- Transaction status tracking
- Phone number formatting
- Error handling and logging

### M-Pesa Integration
- STK Push (Lipa na M-Pesa Online)
- Automatic callback processing
- Transaction status queries
- Real-time payment updates
- Sandbox and production support

## Requirements

- PHP 8.0 or higher
- SQLite3 extension
- cURL extension
- OpenSSL extension
- Modern web browser

## Installation

### 1. Configure M-Pesa Credentials

Edit `/config/mpesa.php` and add your M-Pesa API credentials:

```php
'apps' => [
    'default' => [
        'consumer_key' => 'YOUR_CONSUMER_KEY',
        'consumer_secret' => 'YOUR_CONSUMER_SECRET',
    ],
],

'lnmo' => [
    'short_code' => 'YOUR_SHORTCODE',
    'passkey' => 'YOUR_PASSKEY',
    'callback' => 'https://yourdomain.com/mpesa/example/api/callback.php',
],
```

### 2. Initialize Database

The database will be automatically created when you first access the application. The schema includes:
- `transactions` - Payment transaction records
- `callbacks` - M-Pesa callback logs
- `customers` - Customer information

### 3. Start the Server

```bash
cd /home/kennedy/vscode/github/yourdudeken/mpesa/example
php -S localhost:8000
```

### 4. Access the Application

Open your browser and navigate to:
```
http://localhost:8000/app.html
```

## Application Structure

```
example/
├── app.html                 # Main application entry point
├── api/
│   ├── payment.php         # Payment API handler
│   ├── callback.php        # M-Pesa callback receiver
│   └── logs.php            # Callback logs API
├── static/
│   ├── css/
│   │   └── app.css         # Application styles
│   └── js/
│       └── app.js          # Application logic
├── database/
│   ├── Database.php        # Database connection manager
│   ├── schema.sql          # Database schema
│   └── mpesa.db           # SQLite database (auto-created)
├── models/
│   └── Transaction.php     # Transaction model
└── config/
    └── mpesa.php           # M-Pesa configuration
```

## Usage Guide

### Making a Payment

1. Navigate to **New Payment** from the sidebar
2. Enter the customer's phone number (format: 254722000000)
3. Enter the payment amount
4. Provide an account reference (e.g., invoice number)
5. Optionally add a description
6. Click **Send Payment Request**
7. Customer receives STK Push prompt on their phone
8. Payment status updates automatically via callback

### Viewing Transactions

1. Click **Transactions** in the sidebar
2. View all payment transactions with details
3. Filter by status (Completed, Pending, Failed)
4. Click **View** to see full transaction details

### Monitoring Callbacks

1. Navigate to **Callback Logs**
2. View all incoming M-Pesa callbacks in real-time
3. Inspect callback payloads
4. Clear logs when needed

### Dashboard

The dashboard provides:
- Total successful transactions
- Pending transactions count
- Failed transactions count
- Total amount processed
- Recent transaction list

## API Endpoints

### Payment API (`api/payment.php`)

#### Initiate Payment
```json
POST /api/payment.php
{
    "action": "initiate_payment",
    "data": {
        "phone_number": "254722000000",
        "amount": 100,
        "account_reference": "INV-001",
        "transaction_desc": "Payment for order"
    }
}
```

#### Check Status
```json
POST /api/payment.php
{
    "action": "check_status",
    "checkout_request_id": "ws_CO_191220191020363925"
}
```

#### Get Transactions
```json
POST /api/payment.php
{
    "action": "get_transactions",
    "status": "completed",
    "limit": 50
}
```

#### Get Statistics
```json
POST /api/payment.php
{
    "action": "get_stats"
}
```

### Callback Handler (`api/callback.php`)

Automatically receives and processes M-Pesa callbacks:
- Updates transaction status
- Stores M-Pesa receipt numbers
- Logs callback data
- Responds to Safaricom

### Logs API (`api/logs.php`)

#### Get Logs
```
GET /api/logs.php
```

#### Clear Logs
```
GET /api/logs.php?action=clear
```

## Database Schema

### Transactions Table
- `id` - Auto-increment primary key
- `checkout_request_id` - M-Pesa checkout ID
- `merchant_request_id` - M-Pesa merchant ID
- `phone_number` - Customer phone number
- `amount` - Transaction amount
- `account_reference` - Your reference number
- `transaction_desc` - Transaction description
- `mpesa_receipt_number` - M-Pesa receipt (on success)
- `transaction_date` - M-Pesa transaction timestamp
- `status` - Transaction status (pending/completed/failed)
- `result_code` - M-Pesa result code
- `result_desc` - M-Pesa result description
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

## Configuration

### Environment Setup

For **Sandbox** (Testing):
```php
'is_sandbox' => true,
'apiUrl' => 'https://sandbox.safaricom.co.ke/',
```

For **Production** (Live):
```php
'is_sandbox' => false,
'apiUrl' => 'https://api.safaricom.co.ke/',
```

### Callback URL

For local testing, use ngrok or similar:
```bash
ngrok http 8000
```

Then update your callback URL in `config/mpesa.php`:
```php
'callback' => 'https://your-ngrok-url.ngrok-free.app/mpesa/example/api/callback.php'
```

## Security Best Practices

1. **Never commit credentials** - Use environment variables in production
2. **Use HTTPS** - All callback URLs must use HTTPS in production
3. **Validate callbacks** - Verify callbacks are from Safaricom
4. **Sanitize inputs** - All user inputs are validated
5. **Secure database** - Set proper file permissions on SQLite database
6. **Error logging** - Monitor error logs regularly
7. **Rate limiting** - Implement rate limiting in production

## Troubleshooting

### Payment Not Received
- Check phone number format (254XXXXXXXXX)
- Verify M-Pesa credentials are correct
- Ensure callback URL is publicly accessible
- Check callback logs for errors

### Callback Not Working
- Verify callback URL is HTTPS (production)
- Check server error logs
- Ensure callback.php has write permissions
- Test with ngrok for local development

### Database Errors
- Check SQLite extension is enabled
- Verify write permissions on database directory
- Ensure schema.sql has been executed

### STK Push Timeout
- Some SIM cards don't support STK Push
- Customer may have cancelled the request
- Network issues on customer side

## Development

### Adding New Features

1. **New API Endpoint**: Add handler in `api/payment.php`
2. **New Database Table**: Update `database/schema.sql`
3. **New UI Page**: Add to `app.html` and update navigation
4. **New Styles**: Add to `static/css/app.css`
5. **New Logic**: Add to `static/js/app.js`

### Testing

Test payments using Safaricom sandbox credentials:
- Test phone: 254722000000
- Test amount: Any amount
- Expected: STK Push prompt on test phone

## Production Deployment

1. **Update Configuration**
   - Set production credentials
   - Update callback URLs
   - Set `is_sandbox` to `false`

2. **Security Hardening**
   - Use environment variables for secrets
   - Enable HTTPS
   - Set proper file permissions
   - Implement rate limiting

3. **Database**
   - Consider migrating to MySQL/PostgreSQL for production
   - Set up regular backups
   - Implement database connection pooling

4. **Monitoring**
   - Set up error logging
   - Monitor callback success rates
   - Track transaction volumes
   - Set up alerts for failures

## Support

For issues or questions:
- Email: kenmwendwamuthengi@gmail.com
- Telegram: @yourdudeken
- GitHub: https://github.com/yourdudeken/mpesa

## License

MIT License - See main package LICENSE file

## Credits

Built using the [yourdudeken/mpesa](https://github.com/yourdudeken/mpesa) PHP package for M-Pesa DARAJA API integration.

---

**Made with love in Kenya**
