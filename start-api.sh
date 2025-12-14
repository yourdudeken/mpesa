#!/bin/bash

# M-Pesa API Quick Start Script
# This script starts both production and sandbox API servers simultaneously

echo "========================================="
echo "M-Pesa API Development Servers"
echo "========================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 7.4 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "✅ PHP Version: $PHP_VERSION"
echo ""

# Function to setup environment
setup_environment() {
    local ENV_DIR=$1
    local ENV_NAME=$2
    
    echo "Setting up $ENV_NAME environment..."
    
    # Check if vendor directory exists
    if [ ! -d "$ENV_DIR/vendor" ]; then
        echo "  📦 Installing dependencies for $ENV_NAME..."
        (cd "$ENV_DIR" && composer install)
    fi
    
    # Create necessary directories
    mkdir -p "$ENV_DIR/cache/rate_limit" "$ENV_DIR/logs"
    chmod 755 "$ENV_DIR/cache" "$ENV_DIR/logs" 2>/dev/null || true
    
    # Check if .env exists
    if [ ! -f "$ENV_DIR/api/.env" ]; then
        if [ -f "$ENV_DIR/api/.env.example" ]; then
            echo "  📄 Creating .env file for $ENV_NAME..."
            cp "$ENV_DIR/api/.env.example" "$ENV_DIR/api/.env"
            echo "  ⚠️  Please update $ENV_DIR/api/.env with your configuration"
        fi
    fi
    
    echo "✅ $ENV_NAME environment ready"
    echo ""
}

# Setup both environments
setup_environment "production" "Production"
setup_environment "sandbox" "Sandbox"

# Trap to cleanup on exit
cleanup() {
    echo ""
    echo "========================================="
    echo "Stopping servers..."
    echo "========================================="
    
    if [ ! -z "$PROD_PID" ]; then
        echo "Stopping Production API (PID: $PROD_PID)..."
        kill $PROD_PID 2>/dev/null
    fi
    
    if [ ! -z "$SANDBOX_PID" ]; then
        echo "Stopping Sandbox API (PID: $SANDBOX_PID)..."
        kill $SANDBOX_PID 2>/dev/null
    fi
    
    echo "✅ All servers stopped"
    exit 0
}

trap cleanup SIGINT SIGTERM

echo "========================================="
echo "Starting Development Servers..."
echo "========================================="
echo ""

# Start Production API on port 8000
echo "🚀 Starting Production API on port 8000..."
(cd production/public && php -S localhost:8000 > ../logs/api-production.log 2>&1) &
PROD_PID=$!
echo "   Production API PID: $PROD_PID"

# Wait a moment to ensure the first server starts
sleep 1

# Start Sandbox API on port 8001
echo "🚀 Starting Sandbox API on port 8001..."
(cd sandbox/public && php -S localhost:8001 > ../logs/api-sandbox.log 2>&1) &
SANDBOX_PID=$!
echo "   Sandbox API PID: $SANDBOX_PID"

echo ""
echo "========================================="
echo "✅ Both Servers Running!"
echo "========================================="
echo ""
echo "📍 Production API:"
echo "   Base URL:     http://localhost:8000"
echo "   Health Check: http://localhost:8000/api/health"
echo "   M-Pesa API:   http://localhost:8000/api/mpesa"
echo "   Logs:         production/logs/api-production.log"
echo ""
echo "📍 Sandbox API:"
echo "   Base URL:     http://localhost:8001"
echo "   Health Check: http://localhost:8001/api/health"
echo "   M-Pesa API:   http://localhost:8001/api/mpesa"
echo "   Logs:         sandbox/logs/api-sandbox.log"
echo ""
echo "💡 Tips:"
echo "   - Use port 8000 for production testing"
echo "   - Use port 8001 for sandbox testing"
echo "   - Check logs in respective logs/ directories"
echo "   - Press Ctrl+C to stop both servers"
echo ""
echo "========================================="

# Wait for both processes
wait
