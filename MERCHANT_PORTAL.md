# 🏪 M-Pesa Gateway - Merchant Portal

A beautiful, secure web application for managing M-Pesa merchant accounts with encrypted credential storage.

## ✨ Features

- 🔐 **Secure Credential Storage** - All M-Pesa credentials are encrypted in the database
- 🔑 **API Key Generation** - Automatic API key generation for each merchant
- 🎨 **Modern UI** - Beautiful, responsive interface with glassmorphism design
- 📊 **Merchant Management** - Full CRUD operations for merchant accounts
- 🔄 **API Key Regeneration** - Regenerate API keys when needed
- ⚡ **Status Management** - Activate/deactivate merchants
- 📈 **Dashboard Statistics** - Overview of all merchants
- 🌓 **Environment Support** - Separate sandbox and production configurations

## 🚀 Quick Start

### Prerequisites

- PHP 8.1 or higher
- MySQL or MariaDB
- Composer (for dependencies)

### Installation

1. **Run the setup script:**
   ```bash
   ./setup-merchant-portal.sh
   ```

2. **Start the API server:**
   ```bash
   ./start-api.sh
   ```

3. **Open your browser:**
   ```
   http://localhost:8000
   ```

## 📱 Usage

### Creating a Merchant

1. Navigate to `http://localhost:8000`
2. Fill in the merchant registration form:
   - **Merchant Name**: Your business name
   - **Environment**: Choose Sandbox (testing) or Production (live)
   - **M-Pesa Shortcode**: Your M-Pesa business shortcode
   - **M-Pesa Passkey**: Your M-Pesa passkey
   - **Initiator Name**: M-Pesa initiator username
   - **Initiator Password**: M-Pesa initiator password
   - **Consumer Key**: M-Pesa API consumer key
   - **Consumer Secret**: M-Pesa API consumer secret

3. Click **Create Merchant Account**
4. **Save your API key** - You won't be able to see it again!

### Managing Merchants

Navigate to `http://localhost:8000/merchants` to:

- View all merchants with statistics
- Activate/deactivate merchants
- Regenerate API keys
- Delete merchants
- View merchant details

## 🔒 Security Features

### Encryption

All sensitive M-Pesa credentials are encrypted using Laravel's encryption:

- M-Pesa Shortcode
- M-Pesa Passkey
- Initiator Name
- Initiator Password
- Consumer Key
- Consumer Secret

### API Key Format

API keys are generated in the format: `mpesa_[64-character-random-string]`

### Best Practices

1. **Never share your API key** - Treat it like a password
2. **Regenerate keys if compromised** - Use the regenerate feature
3. **Use environment-specific credentials** - Keep sandbox and production separate
4. **Deactivate unused merchants** - Prevent unauthorized access

## 🎨 UI Features

### Design Highlights

- **Glassmorphism Effects** - Modern frosted glass design
- **Gradient Accents** - Beautiful color transitions
- **Smooth Animations** - Micro-interactions for better UX
- **Responsive Layout** - Works on all screen sizes
- **Dark Theme** - Easy on the eyes
- **Real-time Feedback** - Instant success/error messages

### Color Scheme

- Primary: Emerald Green (#10b981)
- Secondary: Indigo (#6366f1)
- Background: Dark Slate (#0f172a)
- Accents: Gradient combinations

## 📊 Database Schema

### Merchants Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| merchant_name | string | Unique merchant name |
| api_key | string | Unique API key |
| mpesa_shortcode | text | Encrypted shortcode |
| mpesa_passkey | text | Encrypted passkey |
| mpesa_initiator_name | text | Encrypted initiator name |
| mpesa_initiator_password | text | Encrypted initiator password |
| mpesa_consumer_key | text | Encrypted consumer key |
| mpesa_consumer_secret | text | Encrypted consumer secret |
| is_active | boolean | Merchant status |
| environment | string | sandbox/production |
| last_used_at | timestamp | Last API usage |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update |
| deleted_at | timestamp | Soft delete |

## 🛣️ Routes

### Web Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | / | Merchant registration form |
| GET | /merchants | Merchant management dashboard |
| POST | /merchants | Create new merchant |
| POST | /merchants/{id}/regenerate-key | Regenerate API key |
| POST | /merchants/{id}/toggle-status | Toggle active status |
| DELETE | /merchants/{id} | Delete merchant |

## 🔧 Configuration

### Database Configuration

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mpesa_gateway
DB_USERNAME=root
DB_PASSWORD=
```

### Application Configuration

```env
APP_NAME="M-Pesa Gateway API"
APP_ENV=local
APP_DEBUG=true
APP_PORT=8000
APP_URL=http://localhost:8000
```

## 🧪 Testing

### Manual Testing

1. Create a test merchant with sandbox credentials
2. Verify the API key is generated
3. Test merchant activation/deactivation
4. Test API key regeneration
5. Test merchant deletion

### Database Verification

```bash
# Connect to MySQL
mysql -u root -p mpesa_gateway

# View merchants (encrypted data)
SELECT id, merchant_name, api_key, is_active, environment FROM merchants;
```

## 📝 API Integration

Once you have a merchant API key, you can use it to authenticate API requests:

```bash
# Example: Health check
curl -X GET http://localhost:8000/api/health

# Example: STK Push (with merchant API key in future implementation)
curl -X POST http://localhost:8000/api/mpesa/stk-push \
  -H "Content-Type: application/json" \
  -H "X-API-Key: mpesa_your_api_key_here" \
  -d '{
    "amount": 100,
    "phone_number": "254712345678",
    "account_reference": "INV-001"
  }'
```

## 🎯 Future Enhancements

- [ ] API key authentication middleware
- [ ] Merchant-specific API usage analytics
- [ ] Rate limiting per merchant
- [ ] Webhook management per merchant
- [ ] Transaction history per merchant
- [ ] Email notifications
- [ ] Two-factor authentication
- [ ] API key expiration
- [ ] Merchant user roles

## 🐛 Troubleshooting

### Migration Fails

**Error**: `could not find driver`

**Solution**: Install PHP MySQL extension:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql

# macOS
brew install php
```

### Database Connection Failed

**Solution**: Check MySQL is running:
```bash
sudo systemctl status mysql
# or
sudo service mysql status
```

### Port Already in Use

**Solution**: Change the port in `.env`:
```env
APP_PORT=8001
```

## 📄 License

This project is part of the M-Pesa Gateway API system.

## 🤝 Contributing

Contributions are welcome! Please ensure:

1. Code follows Laravel best practices
2. UI maintains the design system
3. Security features are preserved
4. Documentation is updated

## 📞 Support

For issues or questions:

1. Check the troubleshooting section
2. Review the setup script output
3. Check Laravel logs in `storage/logs/`
4. Verify database credentials

---

**Built with ❤️ using Laravel, Blade, and modern web technologies**
