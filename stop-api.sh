#!/bin/bash

# M-Pesa API Stop Script
# Stops the M-Pesa API development server

echo "========================================="
echo "M-Pesa API Server - Stop"
echo "========================================="
echo ""

# Get port from .env file or use default
PORT=$(grep "^APP_PORT=" .env 2>/dev/null | cut -d '=' -f2 || echo "8000")

echo "🔍 Looking for server on port $PORT..."

# Find process using the port
PID=$(lsof -ti:$PORT 2>/dev/null)

if [ -z "$PID" ]; then
    echo "❌ No server found running on port $PORT"
    exit 1
fi

echo "📍 Found server (PID: $PID)"
echo "🛑 Stopping server..."

# Kill the process
kill $PID 2>/dev/null

# Wait a moment
sleep 1

# Check if it's still running
if lsof -ti:$PORT >/dev/null 2>&1; then
    echo "⚠️  Server still running, forcing stop..."
    kill -9 $PID 2>/dev/null
    sleep 1
fi

# Final check
if lsof -ti:$PORT >/dev/null 2>&1; then
    echo "❌ Failed to stop server"
    exit 1
else
    echo "✅ Server stopped successfully"
fi

echo ""
echo "========================================="
