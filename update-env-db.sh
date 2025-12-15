#!/bin/bash
# Update .env with correct database settings

# Backup .env
cp .env .env.backup

# Update database settings
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/^DB_PORT=.*/DB_PORT=3306/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=mpesa_gateway/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=/' .env

# Add DB settings if they don't exist
grep -q "^DB_CONNECTION=" .env || echo "DB_CONNECTION=mysql" >> .env
grep -q "^DB_HOST=" .env || echo "DB_HOST=127.0.0.1" >> .env
grep -q "^DB_PORT=" .env || echo "DB_PORT=3306" >> .env
grep -q "^DB_DATABASE=" .env || echo "DB_DATABASE=mpesa_gateway" >> .env
grep -q "^DB_USERNAME=" .env || echo "DB_USERNAME=root" >> .env
grep -q "^DB_PASSWORD=" .env || echo "DB_PASSWORD=" >> .env

echo "✅ Database settings updated in .env"
