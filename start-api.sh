#!/bin/bash

# M-Pesa API Quick Start Script
# Single codebase - environment controlled via .env file

echo "========================================="
echo "M-Pesa API Development Server"
echo "========================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "PHP Version: $PHP_VERSION"
echo ""

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install
    echo ""
fi

# Create necessary directories
echo "Creating necessary directories..."
mkdir -p cache/rate_limit logs storage/app storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        echo "Creating .env file..."
        cp .env.example .env
        echo "Please update .env with your configuration"
        echo ""
    else
        echo "No .env.example file found"
        exit 1
    fi
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate
    echo ""
fi

# Trap to cleanup on exit
cleanup() {
    echo ""
    echo "========================================="
    echo "Stopping server..."
    echo "========================================="
    
    if [ ! -z "$SERVER_PID" ]; then
        echo "Stopping API Server (PID: $SERVER_PID)..."
        kill $SERVER_PID 2>/dev/null
    fi
    
    echo "Perver stopped"
    exit 0
}

trap cleanup SIGINT SIGTERM

echo "========================================="
echo "Starting Development Server..."
echo "========================================="
echo ""

# Get environment from .env file
ENV_NAME=$(grep "^APP_ENV=" .env 2>/dev/null | cut -d '=' -f2 || echo "local")
PORT=$(grep "^APP_PORT=" .env 2>/dev/null | cut -d '=' -f2 || echo "8000")

echo "Starting M-Pesa API Server..."
echo "Environment: $ENV_NAME"
echo "Port: $PORT"

# Start PHP built-in server
(cd public && php -S localhost:$PORT > ../logs/api-server.log 2>&1) &
SERVER_PID=$!
echo "   Server PID: $SERVER_PID"

echo ""
echo "========================================="
echo "Perver Running!"
echo "========================================="
echo ""
echo "   M-Pesa API:"
echo "   Base URL:     http://localhost:$PORT"
echo "   Health Check: http://localhost:$PORT/api/health"
echo "   M-Pesa API:   http://localhost:$PORT/api/mpesa"
echo "   Environment:  $ENV_NAME"
echo "   Logs:         logs/api-server.log"
echo ""
echo "   Tips:"
echo "   - Change environment in .env file (APP_ENV)"
echo "   - Change port in .env file (APP_PORT)"
echo "   - Check logs: tail -f logs/api-server.log"
echo "   - Press Ctrl+C to stop the server"
echo ""
echo "========================================="

# Wait for the process
wait
