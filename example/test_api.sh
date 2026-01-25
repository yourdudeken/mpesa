#!/bin/bash

# M-Pesa API Endpoint Testing Script
# Tests all M-Pesa transaction types

echo "========================================="
echo "  M-Pesa API Endpoint Testing"
echo "========================================="
echo ""

API_URL="http://localhost:8000/api/payment.php"
PHONE="254708374149"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

test_count=0
pass_count=0
fail_count=0

# Function to test an endpoint
test_endpoint() {
    local name=$1
    local action=$2
    local data=$3
    
    test_count=$((test_count + 1))
    echo ""
    echo "========================================="
    echo "Test #$test_count: $name"
    echo "========================================="
    echo "Action: $action"
    echo "Data: $data"
    echo ""
    
    response=$(curl -s -X POST "$API_URL" \
        -H "Content-Type: application/json" \
        -d "{\"action\":\"$action\",\"data\":$data}")
    
    echo "Response:"
    echo "$response" | jq '.' 2>/dev/null || echo "$response"
    echo ""
    
    # Check if successful
    if echo "$response" | grep -q '"success":true'; then
        echo -e "${GREEN}✓ PASSED${NC}"
        pass_count=$((pass_count + 1))
    else
        echo -e "${RED}✗ FAILED${NC}"
        fail_count=$((fail_count + 1))
        
        # Extract error message
        error=$(echo "$response" | jq -r '.error // .message // "Unknown error"' 2>/dev/null)
        echo -e "${RED}Error: $error${NC}"
    fi
    
    sleep 2
}

echo "Starting API endpoint tests..."
echo ""

# Test 1: STK Push
test_endpoint \
    "STK Push (Lipa na M-Pesa Online)" \
    "stk_push" \
    "{\"phone_number\":\"$PHONE\",\"amount\":10,\"account_reference\":\"TEST001\",\"transaction_desc\":\"Test payment\"}"

# Test 2: C2B Register
test_endpoint \
    "C2B Register URLs" \
    "c2b_register" \
    "{\"validation_url\":\"https://example.com/validate\",\"confirmation_url\":\"https://example.com/confirm\",\"response_type\":\"Completed\"}"

# Test 3: C2B Simulate
test_endpoint \
    "C2B Simulate Payment" \
    "c2b_simulate" \
    "{\"phone_number\":\"$PHONE\",\"amount\":10,\"bill_ref_number\":\"TEST002\"}"

# Test 4: B2C Payment
test_endpoint \
    "B2C Payment (Business to Customer)" \
    "b2c_payment" \
    "{\"phone_number\":\"$PHONE\",\"amount\":10,\"command_id\":\"BusinessPayment\",\"remarks\":\"Test B2C payment\",\"occasion\":\"Testing\"}"

# Test 5: B2B Payment
test_endpoint \
    "B2B Payment (Business to Business)" \
    "b2b_payment" \
    "{\"party_b\":\"600000\",\"amount\":10,\"command_id\":\"BusinessPayBill\",\"account_reference\":\"TEST003\",\"remarks\":\"Test B2B payment\"}"

# Test 6: B2Pochi Payment
test_endpoint \
    "B2Pochi Payment" \
    "b2pochi_payment" \
    "{\"phone_number\":\"$PHONE\",\"amount\":10,\"remarks\":\"Test Pochi payment\"}"

# Test 7: Account Balance
test_endpoint \
    "Account Balance Query" \
    "account_balance" \
    "{\"remarks\":\"Balance check\"}"

# Test 8: Transaction Status
test_endpoint \
    "Transaction Status Query" \
    "transaction_status" \
    "{\"transaction_id\":\"TEST123456\",\"remarks\":\"Status check\"}"

# Test 9: Reversal
test_endpoint \
    "Transaction Reversal" \
    "reversal" \
    "{\"transaction_id\":\"TEST123456\",\"amount\":10,\"receiver_party\":\"600000\",\"remarks\":\"Test reversal\"}"

# Test 10: Get Transactions
test_endpoint \
    "Get Transactions" \
    "get_transactions" \
    "{\"limit\":10}"

# Test 11: Get Statistics
test_endpoint \
    "Get Statistics" \
    "get_stats" \
    "{}"

# Summary
echo ""
echo "========================================="
echo "  Test Summary"
echo "========================================="
echo "Total Tests: $test_count"
echo -e "${GREEN}Passed: $pass_count${NC}"
echo -e "${RED}Failed: $fail_count${NC}"
echo ""

if [ $fail_count -eq 0 ]; then
    echo -e "${GREEN}All tests passed! ✓${NC}"
    exit 0
else
    echo -e "${YELLOW}Some tests failed. Check the output above for details.${NC}"
    exit 1
fi
