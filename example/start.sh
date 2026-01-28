#!/bin/bash

# M-Pesa Payment System - Quick Start Script

echo "========================================="
echo "  M-Pesa Payment System"
echo "========================================="
echo ""

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;' 2>/dev/null)
if [ $? -ne 0 ]; then
    echo "Error: PHP is not installed"
    exit 1
fi

echo "PHP Version: $PHP_VERSION"

# Check required extensions
echo "Checking required PHP extensions..."

check_extension() {
    php -m | grep -q "$1"
    if [ $? -eq 0 ]; then
        echo "  ✓ $1"
    else
        echo "  ✗ $1 (missing)"
        return 1
    fi
}

MISSING=0
check_extension "pdo_sqlite" || MISSING=1
check_extension "curl" || MISSING=1
check_extension "openssl" || MISSING=1
check_extension "json" || MISSING=1

if [ $MISSING -eq 1 ]; then
    echo ""
    echo "Error: Some required PHP extensions are missing"
    exit 1
fi

echo ""
echo "All requirements met!"
echo ""

# Install dependencies
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install
else
    echo "Composer dependencies already installed."
fi

# Check if database exists
if [ ! -f "database/mpesa.db" ]; then
    echo "Initializing database..."
    mkdir -p database
    echo "Database will be created on first access"
fi

echo ""
echo "Starting PHP development server..."
echo "========================================="
echo ""
echo "  Application URL: http://localhost:8000/app.html"
echo "  API Tester URL:  http://localhost:8000/index.html"
echo ""
echo "  Press Ctrl+C to stop the server"
echo ""
echo "========================================="
echo ""

# Start server
php -S localhost:8000
