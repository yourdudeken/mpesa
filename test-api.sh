#!/bin/bash

# M-Pesa API Test Script
# Tests all API endpoints with dynamic port from .env

# Get port from .env file or use default
PORT=$(grep "^APP_PORT=" .env 2>/dev/null | cut -d '=' -f2 || echo "8000")
API_URL="http://localhost:$PORT/api"

echo "========================================="
echo "M-Pesa API Complete Test Suite"
echo "========================================="
echo "Testing on port: $PORT"
echo "API URL: $API_URL"
echo ""

# Test 1: Health Check
echo "Test 1: Health Check"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" "$API_URL/health")
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ]; then
    echo " Health check passed"
else
    echo " Health check failed"
fi

echo ""
echo "========================================="
echo ""

# Test 2: STK Push with Validation Error
echo "Test 2: STK Push (Validation Test)"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/stk-push" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"amount": 100}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "422" ]; then
    echo " Validation working correctly"
else
    echo " Validation test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 3: STK Push with Valid Data
echo "Test 3: STK Push (Valid Request)"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/stk-push" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "amount": 100,
        "phone_number": "254712345678",
        "account_reference": "TEST001",
        "transaction_desc": "Test"
    }')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " STK Push endpoint working (may need M-Pesa config)"
else
    echo " STK Push test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 4: STK Query
echo "Test 4: STK Query"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/stk-query" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"checkout_request_id": "ws_CO_191220191020363925"}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " STK Query endpoint working"
else
    echo " STK Query test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 5: B2C Payment
echo "Test 5: B2C Payment"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/b2c" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "amount": 100,
        "phone_number": "254712345678",
        "remarks": "Test payment"
    }')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " B2C endpoint working"
else
    echo " B2C test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 6: B2B Payment
echo "Test 6: B2B Payment"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/b2b" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "amount": 1000,
        "receiver_shortcode": "600000",
        "account_reference": "TEST"
    }')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " B2B endpoint working"
else
    echo " B2B test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 7: Account Balance
echo "Test 7: Account Balance"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/balance" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"remarks": "Balance check"}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " Balance endpoint working"
else
    echo " Balance test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 8: Transaction Status
echo "Test 8: Transaction Status"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/transaction-status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"transaction_id": "LGR019G3J2"}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " Transaction Status endpoint working"
else
    echo " Transaction Status test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 9: Reversal
echo "Test 9: Reversal"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/reversal" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "transaction_id": "LGR019G3J2",
        "amount": 100
    }')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " Reversal endpoint working"
else
    echo " Reversal test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 10: C2B Register
echo "Test 10: C2B Register"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/mpesa/c2b/register" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "confirmation_url": "https://example.com/callback",
        "validation_url": "https://example.com/callback"
    }')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "400" ]; then
    echo " C2B Register endpoint working"
else
    echo " C2B Register test failed"
fi

echo ""
echo "========================================="
echo ""
echo " Test Summary"
echo "========================================="
echo ""
echo " Endpoints Tested:"
echo "   1. GET  /api/health"
echo "   2. POST /api/mpesa/stk-push"
echo "   3. POST /api/mpesa/stk-query"
echo "   4. POST /api/mpesa/b2c"
echo "   5. POST /api/mpesa/b2b"
echo "   6. POST /api/mpesa/balance"
echo "   7. POST /api/mpesa/transaction-status"
echo "   8. POST /api/mpesa/reversal"
echo "   9. POST /api/mpesa/c2b/register"
echo ""
echo " Notes:"
echo "   - 200: Success"
echo "   - 400: M-Pesa SDK validation (config needed)"
echo "   - 422: Laravel validation error"
echo ""
echo "   To test with real M-Pesa API:"
echo "   1. Update config/mpesa.php with credentials"
echo "   2. Set callback URLs"
echo "   3. Run tests again"
echo ""
echo "========================================="
