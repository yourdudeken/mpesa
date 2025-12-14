#!/bin/bash

# M-Pesa API Stop Script
# This script stops both production and sandbox API servers

echo "========================================="
echo "Stopping M-Pesa API Servers"
echo "========================================="
echo ""

# Find and kill production API server (port 8000)
PROD_PID=$(lsof -ti:8000 2>/dev/null)
if [ ! -z "$PROD_PID" ]; then
    echo "🛑 Stopping Production API (Port 8000, PID: $PROD_PID)..."
    kill $PROD_PID 2>/dev/null
    echo "✅ Production API stopped"
else
    echo "ℹ️  Production API is not running"
fi

echo ""

# Find and kill sandbox API server (port 8001)
SANDBOX_PID=$(lsof -ti:8001 2>/dev/null)
if [ ! -z "$SANDBOX_PID" ]; then
    echo "🛑 Stopping Sandbox API (Port 8001, PID: $SANDBOX_PID)..."
    kill $SANDBOX_PID 2>/dev/null
    echo "✅ Sandbox API stopped"
else
    echo "ℹ️  Sandbox API is not running"
fi

echo ""
echo "========================================="
echo "✅ Done!"
echo "========================================="
