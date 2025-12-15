#!/bin/bash

# M-Pesa Gateway - Interactive Database Setup Script
# This script creates the MySQL database for the merchant portal

set -e

echo "🗄️  M-Pesa Gateway - Database Setup"
echo "===================================="
echo ""

# Get database name from .env or use default
DB_DATABASE=${1:-mpesa_gateway}

echo "This script will create the MySQL database: $DB_DATABASE"
echo ""
echo "⚠️  You will be prompted for your MySQL root password"
echo ""

# Prompt for MySQL root password
read -sp "Enter MySQL root password: " MYSQL_PASSWORD
echo ""
echo ""

# Create database
echo "Creating database..."
mysql -u root -p"$MYSQL_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_DATABASE;" 2>&1

if [ $? -eq 0 ]; then
    echo "✅ Database '$DB_DATABASE' created successfully!"
    echo ""
    
    # Grant privileges
    echo "Setting up permissions..."
    mysql -u root -p"$MYSQL_PASSWORD" -e "GRANT ALL PRIVILEGES ON $DB_DATABASE.* TO 'root'@'localhost';" 2>&1
    mysql -u root -p"$MYSQL_PASSWORD" -e "FLUSH PRIVILEGES;" 2>&1
    
    echo "✅ Permissions configured!"
    echo ""
    
    # Run migrations
    echo "🔄 Running database migrations..."
    php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Database setup completed successfully!"
        echo ""
        echo "📋 Database Information:"
        echo "  Database Name: $DB_DATABASE"
        echo "  Connection: mysql"
        echo "  Host: localhost"
        echo "  User: root"
        echo ""
        echo "🚀 The merchant portal is ready!"
        echo ""
        echo "  Open: http://localhost:8000"
        echo ""
    else
        echo ""
        echo "❌ Migration failed. Please check the error above."
        exit 1
    fi
else
    echo ""
    echo "❌ Failed to create database."
    echo "   Please check your MySQL root password and try again."
    exit 1
fi
