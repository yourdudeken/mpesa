# Old API to Laravel Migration Guide

## Summary

The old custom API implementation in `/api` directories has been **backed up** and can now be **safely deleted**.

## Backup Location

Old API code has been backed up to:
- `production/backup_old_api/`
- `sandbox/backup_old_api/`

## What Was in the Old API

### Controllers
- **BaseController.php** - Base controller with common methods
- **MpesaController.php** - M-Pesa API endpoints implementation

### Middleware
- **AuthMiddleware.php** - API key authentication
- **CorsMiddleware.php** - CORS handling
- **RateLimitMiddleware.php** - Rate limiting

### Other Files
- **Config/** - API configuration
- **Routes/** - Custom routing
- **.env** - Old environment config
- **index.php** - Old entry point

## Migration to Laravel

### 1. Controllers

The old controllers need to be migrated to Laravel controllers.

**Create Laravel Controller:**
```bash
cd production
php artisan make:controller Api/MpesaController
```

**Reference the old implementation:**
- Old: `production/backup_old_api/Controllers/MpesaController.php`
- New: `production/app/Http/Controllers/Api/MpesaController.php`

### 2. Middleware

Laravel has built-in middleware. Migrate custom middleware:

**Auth Middleware:**
```bash
php artisan make:middleware ValidateApiKey
```
Reference: `backup_old_api/Middleware/AuthMiddleware.php`

**CORS Middleware:**
Laravel has built-in CORS support via `fruitcake/laravel-cors` (already installed).
Configure in `config/cors.php`

**Rate Limiting:**
Laravel has built-in rate limiting.
Reference: `backup_old_api/Middleware/RateLimitMiddleware.php`

### 3. Routes

Old routes are in `backup_old_api/Routes/`.
New routes are in `routes/api.php` (already created with M-Pesa endpoints).

### 4. Configuration

Old config in `backup_old_api/Config/`.
New config should go in `config/` directory.

## Safe to Delete

Once you've reviewed the backup, you can delete:

```bash
# Delete old API directories
rm -rf production/api
rm -rf sandbox/api
```

## What You Keep

✅ **M-Pesa Package** - Still in `src/Mpesa/`
✅ **Tests** - Still in `tests/`
✅ **Configuration** - Still in `src/config/mpesa.php`
✅ **Backup** - In `backup_old_api/` for reference

## Laravel Equivalents

| Old API | Laravel Equivalent |
|---------|-------------------|
| `api/index.php` | `public/index.php` |
| `api/Routes/` | `routes/api.php` |
| `api/Controllers/` | `app/Http/Controllers/` |
| `api/Middleware/` | `app/Http/Middleware/` |
| `api/Config/` | `config/` |
| `api/.env` | `.env` (root level) |

## Next Steps

1. ✅ Review backup files in `backup_old_api/`
2. ✅ Migrate any custom logic to Laravel controllers
3. ✅ Migrate middleware if needed
4. ✅ Test the new Laravel API
5. ✅ Delete old `api/` directories when ready

## Command to Delete Old API

When you're ready:

```bash
# Make sure servers are stopped first
./stop-api.sh

# Delete old API directories
rm -rf production/api
rm -rf sandbox/api

# Restart servers with Laravel
./start-api.sh
```

## Verification

After deletion, verify everything works:

```bash
# Test health endpoint
curl http://localhost:8000/api/health
curl http://localhost:8001/api/health

# List routes
cd production && php artisan route:list
cd sandbox && php artisan route:list
```

## Important Notes

- ⚠️ **The servers are currently running from the OLD api/ directory**
- ⚠️ **Stop servers before deleting** (`./stop-api.sh`)
- ⚠️ **Restart with Laravel** (`./start-api.sh`)
- ✅ **Backup is safe** in `backup_old_api/`
- ✅ **Laravel is ready** to take over

## Summary

**YES, you can delete the `/api` directories!**

The old custom API has been replaced by Laravel. All functionality should be reimplemented in Laravel's structure:
- Controllers → `app/Http/Controllers/`
- Middleware → `app/Http/Middleware/`
- Routes → `routes/api.php`
- Config → `config/`

The backup is there for reference when migrating the business logic.
