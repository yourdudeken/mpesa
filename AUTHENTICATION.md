# M-Pesa Merchant Portal - Authentication Documentation

## Overview

The M-Pesa Merchant Portal is protected by HTTP Basic Authentication using the first merchant's credentials.

## Authentication Mechanism

### How It Works

1. **First Access (No Merchants)**
   - When no merchants exist in the database, the portal is accessible without authentication
   - This allows you to create the first merchant account

2. **After First Merchant Creation**
   - Once the first merchant is created, HTTP Basic Authentication is enabled
   - All subsequent access requires authentication

3. **Credentials**
   - **Username**: Consumer Key of the first merchant
   - **Password**: Consumer Secret of the first merchant

## Implementation Details

### Middleware: MerchantAuth

Location: `app/Http/Middleware/MerchantAuth.php`

The middleware:
- Checks if any merchants exist in the database
- If no merchants exist, allows access (to create the first merchant)
- If merchants exist, requires HTTP Basic Authentication
- Validates credentials against the first merchant's consumer key and secret
- Returns 401 Unauthorized if credentials are invalid

### Protected Routes

All web routes are protected by the `merchant.auth` middleware:

- `GET /` - Merchant registration form
- `GET /merchants` - Merchant management dashboard
- `POST /merchants` - Create new merchant
- `POST /merchants/{id}/regenerate-key` - Regenerate API key
- `POST /merchants/{id}/toggle-status` - Toggle merchant status
- `DELETE /merchants/{id}` - Delete merchant

## Usage

### First Time Setup

1. Navigate to `http://localhost:8000`
2. No authentication required (no merchants exist)
3. Fill in the merchant registration form with your M-Pesa credentials
4. Click "Create Merchant Account"
5. Save the generated API key

### Subsequent Access

1. Navigate to `http://localhost:8000`
2. Browser will prompt for authentication
3. Enter credentials:
   - **Username**: The Consumer Key you used for the first merchant
   - **Password**: The Consumer Secret you used for the first merchant
4. Access granted to the portal

## Security Features

### Credential Storage

- All merchant credentials are encrypted in the database
- The middleware decrypts credentials only for authentication
- Credentials are never exposed in logs or responses

### HTTP Basic Authentication

- Standard HTTP Basic Auth protocol
- Credentials sent in Authorization header
- Browser caches credentials for the session
- Logout by closing browser or clearing cache

### Protection Scope

- All web routes are protected
- API routes (`/api/*`) are not affected by this middleware
- Health check endpoint (`/up`) is not protected

## Testing Authentication

### Test Scenario 1: No Merchants

```bash
curl http://localhost:8000
```

Expected: Page loads without authentication

### Test Scenario 2: With Merchants

```bash
curl http://localhost:8000
```

Expected: 401 Unauthorized with WWW-Authenticate header

```bash
curl -u "YOUR_CONSUMER_KEY:YOUR_CONSUMER_SECRET" http://localhost:8000
```

Expected: Page loads successfully

## Troubleshooting

### Issue: Cannot Access Portal After Creating First Merchant

**Solution**: Use the Consumer Key and Consumer Secret from the first merchant you created

### Issue: Forgot First Merchant Credentials

**Solution**: 
1. Access the database directly
2. Query the first merchant:
   ```sql
   SELECT id, merchant_name FROM merchants ORDER BY id ASC LIMIT 1;
   ```
3. The credentials are encrypted, so you'll need to:
   - Delete all merchants and start over, OR
   - Update the middleware to use a different merchant, OR
   - Manually decrypt the credentials using Laravel's decrypt function

### Issue: Want to Change Authentication Credentials

**Solution**:
1. The authentication always uses the first merchant (lowest ID)
2. To change credentials, you can:
   - Delete the first merchant (if you have others)
   - Update the first merchant's consumer key/secret
   - Modify the middleware to use a different merchant

## Code Examples

### Accessing with cURL

```bash
# Without authentication (will fail if merchants exist)
curl http://localhost:8000

# With authentication
curl -u "fduEAZl8XCBAA5dXsoMK4d0EI278jGpcZSDGslWNAuVAGvRP:of2dQDr3TaQKT6PWKClb5jpu5ooigb9AIcOLStzF2lR8EMM9SOYzfj4XIS0lbH0o" \
  http://localhost:8000
```

### Accessing with JavaScript (fetch)

```javascript
// Encode credentials
const username = 'YOUR_CONSUMER_KEY';
const password = 'YOUR_CONSUMER_SECRET';
const credentials = btoa(`${username}:${password}`);

// Make request
fetch('http://localhost:8000', {
    headers: {
        'Authorization': `Basic ${credentials}`
    }
})
.then(response => response.text())
.then(html => console.log(html));
```

### Accessing with Python (requests)

```python
import requests
from requests.auth import HTTPBasicAuth

username = 'YOUR_CONSUMER_KEY'
password = 'YOUR_CONSUMER_SECRET'

response = requests.get(
    'http://localhost:8000',
    auth=HTTPBasicAuth(username, password)
)

print(response.text)
```

## Customization

### Change Authentication Logic

Edit `app/Http/Middleware/MerchantAuth.php`:

```php
// Use a specific merchant instead of the first one
$merchant = Merchant::where('merchant_name', 'Primary Account')->first();

// Use environment variable for credentials
$username = env('PORTAL_USERNAME');
$password = env('PORTAL_PASSWORD');

// Add IP whitelist
$allowedIps = ['127.0.0.1', '192.168.1.100'];
if (!in_array($request->ip(), $allowedIps)) {
    return response('Forbidden', 403);
}
```

### Disable Authentication (Not Recommended)

Remove the middleware from `routes/web.php`:

```php
// Remove the middleware wrapper
Route::get('/', [MerchantController::class, 'index'])->name('merchants.index');
// ... other routes
```

## Best Practices

1. **Secure Credentials**
   - Never share your consumer key and secret
   - Use strong, unique credentials
   - Rotate credentials periodically

2. **HTTPS in Production**
   - Always use HTTPS in production
   - HTTP Basic Auth sends credentials in base64 (not encrypted)
   - HTTPS encrypts the entire communication

3. **Access Control**
   - Limit who has access to the portal
   - Consider adding IP whitelisting
   - Monitor access logs

4. **Credential Management**
   - Document the first merchant credentials securely
   - Store in a password manager
   - Have a recovery plan

## Related Files

- Middleware: `app/Http/Middleware/MerchantAuth.php`
- Routes: `routes/web.php`
- Bootstrap: `bootstrap/app.php`
- Model: `app/Models/Merchant.php`

## Summary

The M-Pesa Merchant Portal uses HTTP Basic Authentication with the first merchant's consumer key and secret. This provides a simple but effective way to protect the portal while using credentials that are already part of the system. The authentication is automatically enabled after the first merchant is created, ensuring the portal remains accessible for initial setup.
