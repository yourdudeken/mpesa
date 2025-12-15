# Changelog

All notable changes to the M-Pesa Merchant Portal project.

## [2025-12-15] - Complete Implementation

### Added
- **Authentication System**
  - Session-based login/signup (no browser popups)
  - Login page with consumer key/secret authentication
  - Signup page for merchant registration
  - Logout functionality
  - Session security with regeneration

- **Merchant Management**
  - Create new merchants with M-Pesa credentials
  - Edit existing merchants
  - Switch between Sandbox and Production environments
  - Regenerate API keys
  - Activate/deactivate merchants
  - Delete merchants (soft delete)
  - View all merchants with statistics

- **Security Features**
  - Timing-safe credential comparison (hash_equals)
  - Rate limiting (5 attempts per minute on login/signup)
  - Security headers (CSP, X-Frame-Options, X-XSS-Protection, etc.)
  - CSRF protection on all forms
  - Input validation with strict rules
  - Comprehensive logging (login, logout, signup, errors)
  - Data encryption (AES-256-CBC for M-Pesa credentials)
  - Session regeneration to prevent session fixation
  - Generic error messages to prevent user enumeration

- **Database**
  - Merchants table with encrypted credentials
  - Sessions table for session management
  - Cache table for rate limiting
  - Proper indexes for performance

- **UI/UX**
  - Modern glassmorphism design
  - Dark theme with gradient accents
  - Responsive layout
  - Real-time form validation
  - Success/error messages
  - Loading states
  - No emojis in codebase

### Changed
- Switched from HTTP Basic Auth to session-based authentication
- Updated middleware to use sessions instead of HTTP headers
- Changed session and cache drivers to file-based (simpler, no MySQL dependency)
- Enhanced validation rules with min/max length requirements
- Improved error handling with try-catch blocks

### Fixed
- Missing Controller base class import in AuthController
- Missing cache table causing rate limiter errors
- Session driver defaulting to database (changed to file)
- MySQL connection errors by using file-based sessions

### Security
- Implemented timing-safe authentication
- Added rate limiting middleware
- Created security headers middleware
- Enhanced logging for audit trail
- Validated all inputs with strict rules
- Prevented session fixation attacks
- Protected against brute force attacks

### Performance
- Added database indexes
- Optimized query patterns
- Used file-based sessions (no database overhead)
- Minimal external dependencies

### Configuration
- SESSION_DRIVER=file (no database required)
- CACHE_STORE=file (no database required)
- Rate limiting: 5 attempts per minute
- Session lifetime: 120 minutes

### Files Created
- app/Http/Controllers/AuthController.php
- app/Http/Middleware/SecurityHeaders.php
- app/Http/Middleware/ThrottleLogin.php
- resources/views/auth/login.blade.php
- resources/views/auth/signup.blade.php
- resources/views/merchants/edit.blade.php
- database/migrations/*_create_merchants_table.php
- database/migrations/*_create_sessions_table.php
- database/migrations/*_create_cache_table.php

### Files Modified
- app/Http/Middleware/MerchantAuth.php (session-based auth)
- app/Http/Controllers/MerchantController.php (added edit/update)
- routes/web.php (added auth routes, rate limiting)
- bootstrap/app.php (registered middleware)
- resources/views/layout.blade.php (added error-text style)
- resources/views/merchants/index.blade.php (added logout, removed emojis)
- resources/views/merchants/list.blade.php (added edit button, logout, removed emojis)
- .env (added SESSION_DRIVER and CACHE_STORE)
- .env.example (added session/cache config)

### Removed
- HTTP Basic Authentication
- Browser popup authentication
- All emoji characters from codebase
- Multiple documentation files (consolidated to CHANGELOG.md)

## Known Issues
- None currently

## Future Enhancements
- Multi-user support
- Password reset functionality
- Email notifications
- Two-factor authentication (2FA)
- API rate limiting
- Role-based access control
- Audit log viewer
- Dashboard analytics

## Notes
- All M-Pesa credentials are encrypted at rest
- API keys are shown only once after generation
- Sessions expire after 120 minutes of inactivity
- Rate limiting prevents brute force attacks
- File-based sessions/cache for simplicity (no MySQL dependency for auth)

## [2025-12-15 Evening] - Simplified Configuration

### Fixed
- **PERMANENT FIX**: MySQL connection errors during authentication
  - Changed SESSION_DRIVER from database to file
  - Changed CACHE_STORE from database to file
  - Authentication now works without MySQL running
  - Simpler configuration, more reliable

### Changed
- Simplified documentation (removed multiple .md files)
- Created single CHANGELOG.md for all changes
- Updated README.md with essential information only
- Removed complexity from codebase

### Configuration
```env
SESSION_DRIVER=file  # No MySQL needed for sessions
CACHE_STORE=file     # No MySQL needed for cache
```

### Result
- Portal works immediately after server start
- No database setup required for authentication
- Simpler, more reliable system
- All features still functional

## [2025-12-15 - 22:47] - Login Redirect Update

### Changed
- Login now redirects to `/merchants` instead of `/`
- Signup now redirects to `/merchants` instead of `/`
- Already authenticated users redirected to `/merchants`

### User Experience
- Users immediately see merchant list after login
- Better UX - direct access to main functionality
- Consistent navigation flow

## [2025-12-15 - 22:50] - UUID and Slug Implementation

### Added
- UUID column to merchants table for security
- Slug column to merchants table for SEO-friendly URLs
- Automatic UUID generation on merchant creation
- Automatic slug generation from merchant name + random string
- Route model binding using slug instead of ID
- Methods to find merchants by UUID and slug

### Changed
- All routes now use `{merchant}` parameter with slug
- URLs changed from `/merchants/1/edit` to `/merchants/my-business-abc12345/edit`
- Controller methods use route model binding (Merchant type-hinting)
- Removed manual `findOrFail()` calls (handled by Laravel)

### Security
- IDs no longer exposed in URLs (prevents enumeration)
- UUIDs provide additional security layer
- Slugs are SEO-friendly and harder to guess

### Migration
- Added migration to add UUID and slug columns
- Existing merchants automatically get UUID and slug
- Unique constraints and indexes added for performance

### Files Modified
- database/migrations/*_add_uuid_and_slug_to_merchants_table.php
- app/Models/Merchant.php
- app/Http/Controllers/MerchantController.php
- routes/web.php

### Example URLs
Before: `/merchants/1/edit`
After: `/merchants/my-business-xyz12345/edit`
