# M-Pesa Merchant Portal

A secure web portal for managing M-Pesa merchant accounts with encrypted credential storage and API key management.

## Quick Start

### 1. Start the Server
```bash
./start-api.sh
```

### 2. Access the Portal
Open your browser and go to: `http://localhost:8000`

### 3. Create Your Account
- Click "Sign Up"
- Fill in your M-Pesa credentials
- Choose environment (Sandbox or Production)
- Submit to create your account

### 4. Login
- Use your Consumer Key as username
- Use your Consumer Secret as password

## Features

- **Secure Authentication** - Session-based login (no popups)
- **Merchant Management** - Create, edit, and delete merchants
- **Environment Switching** - Toggle between Sandbox and Production
- **API Key Management** - Generate and regenerate API keys
- **Encrypted Storage** - All credentials encrypted with AES-256
- **Rate Limiting** - Protection against brute force attacks
- **Audit Logging** - Track all authentication events

## Configuration

The portal uses file-based sessions and cache (no MySQL required for authentication).

Key settings in `.env`:
```env
SESSION_DRIVER=file
CACHE_STORE=file
DB_CONNECTION=mysql  # Only for merchant data storage
```

## Security

- Timing-safe authentication
- Rate limiting (5 attempts/minute)
- CSRF protection
- Security headers (CSP, X-Frame-Options, etc.)
- Session regeneration
- Comprehensive logging

## API Testing

Use the included Postman collection:
```
postman_collection.json
```

## Documentation

- `CHANGELOG.md` - All changes and updates
- `README.md` - This file

## Troubleshooting

**Can't login?**
- Verify your Consumer Key and Consumer Secret
- Check that your merchant account is active

**Session expired?**
- Sessions expire after 120 minutes
- Just login again

**Rate limited?**
- Wait 60 seconds and try again

## Support

Check the logs for errors:
```bash
tail -f storage/logs/laravel.log
```

## License

MIT License
