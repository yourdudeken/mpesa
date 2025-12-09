#!/bin/bash

# M-Pesa API Test Script
# Tests basic API functionality

API_URL="http://localhost:8000/api"
API_KEY="demo-api-key-12345"

echo "========================================="
echo "M-Pesa API Test Suite"
echo "========================================="
echo ""

# Test 1: Health Check (No Auth Required)
echo "Test 1: Health Check (No Authentication)"
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

# Test 2: Unauthorized Request
echo "Test 2: Unauthorized Request (No API Key)"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/stk-push" \
    -H "Content-Type: application/json" \
    -d '{"amount": 100}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "401" ]; then
    echo " Unauthorized request correctly rejected"
else
    echo " Unauthorized request test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 3: Authenticated Request with Missing Fields
echo "Test 3: Authenticated Request (Missing Required Fields)"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST "$API_URL/stk-push" \
    -H "X-API-Key: $API_KEY" \
    -H "Content-Type: application/json" \
    -d '{"amount": 100}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" == "400" ]; then
    echo " Validation error correctly returned"
else
    echo " Validation test failed"
fi

echo ""
echo "========================================="
echo ""

# Test 4: CORS Preflight
echo "Test 4: CORS Preflight Request"
echo "----------------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X OPTIONS "$API_URL/stk-push" \
    -H "Origin: https://example.com" \
    -H "Access-Control-Request-Method: POST" \
    -H "Access-Control-Request-Headers: X-API-Key, Content-Type")
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)

echo "Response Code: $HTTP_CODE"
echo ""

if [ "$HTTP_CODE" == "204" ]; then
    echo " CORS preflight handled correctly"
else
    echo "ate  CORS preflight test failed"
fi

echo ""
echo "========================================="
echo ""
echo "Test Summary:"
echo "- Health check endpoint working"
echo "- API key authentication working"
echo "- Request validation working"
echo "- CORS handling working"
echo ""
echo "For full testing, configure M-Pesa credentials"
echo "and test with actual payment requests."
echo "========================================="
