#!/bin/bash

# Update .env with MySQL password

echo "🔐 MySQL Password Configuration"
echo "================================"
echo ""
echo "Please enter your MySQL root password"
echo "(the same one you used in the previous step)"
echo ""

read -sp "MySQL root password: " MYSQL_PASSWORD
echo ""

# Update the .env file with the password
if [ -f .env ]; then
    # Escape special characters in password for sed
    ESCAPED_PASSWORD=$(printf '%s\n' "$MYSQL_PASSWORD" | sed -e 's/[\/&]/\\&/g')
    
    # Update DB_PASSWORD line
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$ESCAPED_PASSWORD/" .env
    
    echo ""
    echo "✅ MySQL password updated in .env"
    echo ""
    echo "Now running migrations..."
    echo ""
    
    # Run migrations
    php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "🎉 SUCCESS! Database setup is complete!"
        echo ""
        echo "📋 Your merchant portal is ready at:"
        echo "   http://localhost:8000"
        echo ""
    else
        echo ""
        echo "❌ Migration failed. Please check the error above."
        exit 1
    fi
else
    echo "❌ .env file not found"
    exit 1
fi
