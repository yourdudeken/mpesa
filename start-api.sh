#!/bin/bash

# M-Pesa API Quick Start Script
# This script starts a local development server for testing the API

echo "========================================="
echo "M-Pesa API Development Server"
echo "========================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo " PHP is not installed. Please install PHP 7.4 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo " PHP Version: $PHP_VERSION"

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo " Vendor directory not found. Running composer install..."
    composer install
fi

# Create necessary directories
echo " Creating necessary directories..."
mkdir -p cache/rate_limit logs
chmod 755 cache logs

# Check if .env exists
if [ ! -f "api/.env" ]; then
    echo "  No .env file found. Copying from .env.example..."
    cp api/.env.example api/.env
    echo " Please update api/.env with your configuration"
fi

echo ""
echo "========================================="
echo "Starting development server..."
echo "========================================="
echo ""
echo "API Base URL: http://localhost:8000/api"
echo "Health Check: http://localhost:8000/api/health"
echo ""
echo "Default API Key: demo-api-key-12345"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server
cd api && php -S localhost:8000
