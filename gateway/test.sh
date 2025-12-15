#!/bin/bash

# M-Pesa API Gateway Test Script
# This script tests all the API endpoints

BASE_URL="http://localhost:8000"
API_KEY="dev_api_key_12345"

echo "========================================="
echo "M-Pesa API Gateway Test Script"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test function
test_endpoint() {
    local name=$1
    local method=$2
    local endpoint=$3
    local data=$4
    local auth=$5

    echo -e "${YELLOW}Testing: $name${NC}"
    
    if [ "$auth" = "true" ]; then
        if [ "$method" = "GET" ]; then
            response=$(curl -s -w "\n%{http_code}" -X $method \
                -H "Authorization: Bearer $API_KEY" \
                "$BASE_URL$endpoint")
        else
            response=$(curl -s -w "\n%{http_code}" -X $method \
                -H "Authorization: Bearer $API_KEY" \
                -H "Content-Type: application/json" \
                -d "$data" \
                "$BASE_URL$endpoint")
        fi
    else
        response=$(curl -s -w "\n%{http_code}" -X $method "$BASE_URL$endpoint")
    fi
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$http_code" = "200" ] || [ "$http_code" = "201" ]; then
        echo -e "${GREEN}✓ Success (HTTP $http_code)${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    else
        echo -e "${RED}✗ Failed (HTTP $http_code)${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
    echo ""
}

# 1. Health Check
test_endpoint "Health Check" "GET" "/api/v1/health" "" "false"

# 2. API Documentation
test_endpoint "API Documentation" "GET" "/api/v1/docs" "" "false"

# 3. STK Push Initiate
test_endpoint "STK Push Initiate" "POST" "/api/v1/stkpush" \
'{
  "phone_number": "254712345678",
  "amount": 10,
  "account_reference": "TEST001",
  "transaction_desc": "Test payment"
}' "true"

# 4. C2B Register
test_endpoint "C2B Register URLs" "POST" "/api/v1/c2b/register" \
'{
  "short_code": "174379"
}' "true"

# 5. Transaction History
test_endpoint "Transaction History" "GET" "/api/v1/transactions?page=1&per_page=5" "" "true"

echo "========================================="
echo "Test Complete!"
echo "========================================="
echo ""
echo "Note: Some tests may fail if M-Pesa credentials are not configured."
echo "Check the logs in storage/logs/ for more details."
