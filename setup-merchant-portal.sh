#!/bin/bash

# M-Pesa Gateway - Merchant Portal Setup Script
# This script sets up the merchant management web application

set -e

echo "🚀 Setting up M-Pesa Merchant Portal..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found. Copying from .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✅ .env file created${NC}"
fi

# Check if database configuration is set
echo -e "${YELLOW}📊 Checking database configuration...${NC}"

# Try to create MySQL database if using MySQL
DB_CONNECTION=$(grep DB_CONNECTION .env | cut -d '=' -f2)
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

if [ "$DB_CONNECTION" = "mysql" ]; then
    echo -e "${YELLOW}Creating MySQL database: $DB_DATABASE${NC}"
    
    # Try to create database (ignore error if already exists)
    if [ -z "$DB_PASSWORD" ]; then
        mysql -u"$DB_USERNAME" -e "CREATE DATABASE IF NOT EXISTS $DB_DATABASE;" 2>/dev/null || true
    else
        mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_DATABASE;" 2>/dev/null || true
    fi
    
    echo -e "${GREEN}✅ Database ready${NC}"
elif [ "$DB_CONNECTION" = "sqlite" ]; then
    echo -e "${YELLOW}Creating SQLite database...${NC}"
    touch database/database.sqlite
    echo -e "${GREEN}✅ SQLite database created${NC}"
fi

# Run migrations
echo ""
echo -e "${YELLOW}🔄 Running database migrations...${NC}"
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Migrations completed successfully${NC}"
else
    echo -e "${RED}❌ Migration failed. Please check your database configuration in .env${NC}"
    echo ""
    echo "Database settings:"
    echo "  DB_CONNECTION=$DB_CONNECTION"
    echo "  DB_DATABASE=$DB_DATABASE"
    echo "  DB_USERNAME=$DB_USERNAME"
    echo ""
    echo "Please ensure:"
    echo "  1. MySQL/MariaDB is installed and running (if using MySQL)"
    echo "  2. Database credentials are correct"
    echo "  3. Database user has permission to create databases"
    echo ""
    exit 1
fi

echo ""
echo -e "${GREEN}✨ Setup completed successfully!${NC}"
echo ""
echo "📋 Next steps:"
echo "  1. Start the API server: ./start-api.sh"
echo "  2. Open your browser to: http://localhost:8000"
echo "  3. Create your first merchant account"
echo ""
echo "🔗 Available URLs:"
echo "  • Merchant Registration: http://localhost:8000/"
echo "  • Merchant Management:   http://localhost:8000/merchants"
echo "  • API Health Check:      http://localhost:8000/api/health"
echo ""
