# M-Pesa Gateway API - cURL Examples

Complete guide with cURL examples for all M-Pesa Gateway API endpoints.

## Base URL

```
Development: http://localhost:8000/api
Production:  https://yourdomain.com/api
```

## Table of Contents

1. [Health Check](#health-check)
2. [STK Push (Lipa Na M-Pesa)](#stk-push-lipa-na-m-pesa)
3. [STK Query](#stk-query)
4. [C2B Register URLs](#c2b-register-urls)
5. [C2B Simulate](#c2b-simulate)
6. [B2C Payment](#b2c-payment)
7. [B2B Payment](#b2b-payment)
8. [Account Balance](#account-balance)
9. [Transaction Status](#transaction-status)
10. [Reversal](#reversal)

---

## Health Check

Check if the API is running and view configuration.

### Request

```bash
curl -X GET http://localhost:8000/api/health \
  -H "Accept: application/json"
```

### Response

```json
{
  "status": "ok",
  "service": "M-Pesa Gateway API",
  "environment": "local",
  "mpesa_env": "sandbox",
  "timestamp": "2025-12-14T21:06:46.888539Z",
  "version": "1.0.0"
}
```

---

## STK Push (Lipa Na M-Pesa)

Initiate an STK Push to prompt customer to enter M-Pesa PIN.

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/stk-push \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 100,
    "phone_number": "254712345678",
    "account_reference": "ORDER123",
    "transaction_desc": "Payment",
    "callback_url": "https://yourdomain.com/api/mpesa/callback/stk"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `amount` | number | Yes | Amount to charge (min: 1) |
| `phone_number` | string | Yes | Customer phone number (format: 254XXXXXXXXX) |
| `account_reference` | string | No | Reference for the transaction (max: 12 chars) |
| `transaction_desc` | string | No | Description of transaction (max: 13 chars) |
| `callback_url` | string | No | URL to receive payment notification |

### Success Response

```json
{
  "success": true,
  "data": {
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResponseCode": "0",
    "ResponseDescription": "Success. Request accepted for processing",
    "CustomerMessage": "Success. Request accepted for processing"
  },
  "timestamp": "2025-12-14T21:06:46.888539Z"
}
```

### Error Response

```json
{
  "success": false,
  "message": "The phone number must match the format 254XXXXXXXXX",
  "errors": {
    "phone_number": ["The phone number must match the format 254XXXXXXXXX"]
  }
}
```

---

## STK Query

Query the status of an STK Push transaction.

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/stk-query \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "checkout_request_id": "ws_CO_191220191020363925"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `checkout_request_id` | string | Yes | CheckoutRequestID from STK Push response |

### Success Response

```json
{
  "success": true,
  "data": {
    "ResponseCode": "0",
    "ResponseDescription": "The service request has been accepted successfully",
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResultCode": "0",
    "ResultDesc": "The service request is processed successfully."
  },
  "timestamp": "2025-12-14T21:10:00.000000Z"
}
```

---

## C2B Register URLs

Register validation and confirmation URLs for C2B transactions.

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/c2b/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "confirmation_url": "https://yourdomain.com/api/mpesa/callback/c2b",
    "validation_url": "https://yourdomain.com/api/mpesa/callback/c2b",
    "response_type": "Completed"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `confirmation_url` | string | Yes | URL to receive payment confirmation |
| `validation_url` | string | Yes | URL to validate payment before processing |
| `response_type` | string | No | Response type: "Completed" or "Cancelled" |

### Success Response

```json
{
  "success": true,
  "data": {
    "OriginatorCoversationID": "29115-34620561-1",
    "ResponseCode": "0",
    "ResponseDescription": "Success"
  },
  "timestamp": "2025-12-14T21:15:00.000000Z"
}
```

---

## C2B Simulate

Simulate a C2B payment (sandbox only).

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/c2b/simulate \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 100,
    "phone_number": "254712345678",
    "bill_ref_number": "INVOICE123"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `amount` | number | Yes | Amount to simulate (min: 1) |
| `phone_number` | string | Yes | Customer phone number (format: 254XXXXXXXXX) |
| `bill_ref_number` | string | No | Bill reference number |

### Success Response

```json
{
  "success": true,
  "data": {
    "OriginatorCoversationID": "29115-34620561-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
  },
  "timestamp": "2025-12-14T21:20:00.000000Z"
}
```

---

## B2C Payment

Send money from business to customer (Business to Customer).

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/b2c \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 1000,
    "phone_number": "254712345678",
    "remarks": "Salary payment",
    "occasion": "Monthly salary",
    "command_id": "BusinessPayment",
    "result_url": "https://yourdomain.com/api/mpesa/callback/b2c",
    "timeout_url": "https://yourdomain.com/api/mpesa/callback/b2c"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `amount` | number | Yes | Amount to send (min: 1) |
| `phone_number` | string | Yes | Recipient phone number (format: 254XXXXXXXXX) |
| `remarks` | string | No | Comments about the transaction |
| `occasion` | string | No | Occasion for the payment |
| `command_id` | string | No | Type: BusinessPayment, SalaryPayment, PromotionPayment |
| `result_url` | string | No | URL to receive result notification |
| `timeout_url` | string | No | URL to receive timeout notification |

### Success Response

```json
{
  "success": true,
  "data": {
    "ConversationID": "AG_20191219_00005797af5d7d75f652",
    "OriginatorConversationID": "16740-34861180-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
  },
  "timestamp": "2025-12-14T21:25:00.000000Z"
}
```

---

## B2B Payment

Send money from business to business (Business to Business).

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/b2b \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 5000,
    "receiver_shortcode": "600000",
    "account_reference": "SUPPLIER123",
    "remarks": "Payment for goods",
    "command_id": "BusinessPayBill",
    "result_url": "https://yourdomain.com/api/mpesa/callback/b2b",
    "timeout_url": "https://yourdomain.com/api/mpesa/callback/b2b"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `amount` | number | Yes | Amount to send (min: 1) |
| `receiver_shortcode` | string | Yes | Recipient business shortcode |
| `account_reference` | string | No | Account reference |
| `remarks` | string | No | Comments about the transaction |
| `command_id` | string | No | Type: BusinessPayBill, BusinessBuyGoods, etc. |
| `result_url` | string | No | URL to receive result notification |
| `timeout_url` | string | No | URL to receive timeout notification |

### Success Response

```json
{
  "success": true,
  "data": {
    "ConversationID": "AG_20191219_00005797af5d7d75f652",
    "OriginatorConversationID": "16740-34861180-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
  },
  "timestamp": "2025-12-14T21:30:00.000000Z"
}
```

---

## Account Balance

Query the account balance.

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/balance \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "remarks": "Balance inquiry",
    "identifier_type": "4",
    "result_url": "https://yourdomain.com/api/mpesa/callback/balance",
    "timeout_url": "https://yourdomain.com/api/mpesa/callback/balance"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `remarks` | string | No | Comments about the query |
| `identifier_type` | string | No | Type of organization: 1, 2, or 4 (default: 4) |
| `result_url` | string | No | URL to receive result notification |
| `timeout_url` | string | No | URL to receive timeout notification |

### Success Response

```json
{
  "success": true,
  "data": {
    "ConversationID": "AG_20191219_00005797af5d7d75f652",
    "OriginatorConversationID": "16740-34861180-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
  },
  "timestamp": "2025-12-14T21:35:00.000000Z"
}
```

---

## Transaction Status

Query the status of a transaction.

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/transaction-status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "transaction_id": "LGR019G3J2",
    "remarks": "Status check",
    "identifier_type": "4",
    "result_url": "https://yourdomain.com/api/mpesa/callback/status",
    "timeout_url": "https://yourdomain.com/api/mpesa/callback/status"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `transaction_id` | string | Yes | M-Pesa transaction ID |
| `remarks` | string | No | Comments about the query |
| `identifier_type` | string | No | Type of organization: 1, 2, or 4 (default: 4) |
| `result_url` | string | No | URL to receive result notification |
| `timeout_url` | string | No | URL to receive timeout notification |

### Success Response

```json
{
  "success": true,
  "data": {
    "ConversationID": "AG_20191219_00005797af5d7d75f652",
    "OriginatorConversationID": "16740-34861180-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
  },
  "timestamp": "2025-12-14T21:40:00.000000Z"
}
```

---

## Reversal

Reverse a completed M-Pesa transaction.

### Request

```bash
curl -X POST http://localhost:8000/api/mpesa/reversal \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "transaction_id": "LGR019G3J2",
    "amount": 100,
    "remarks": "Reversal for wrong payment",
    "receiver_identifier_type": "4",
    "result_url": "https://yourdomain.com/api/mpesa/callback/reversal",
    "timeout_url": "https://yourdomain.com/api/mpesa/callback/reversal"
  }'
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `transaction_id` | string | Yes | M-Pesa transaction ID to reverse |
| `amount` | number | Yes | Amount to reverse (min: 1) |
| `remarks` | string | No | Reason for reversal |
| `receiver_identifier_type` | string | No | Type: 1, 2, 4, or 11 (default: 4) |
| `result_url` | string | No | URL to receive result notification |
| `timeout_url` | string | No | URL to receive timeout notification |

### Success Response

```json
{
  "success": true,
  "data": {
    "ConversationID": "AG_20191219_00005797af5d7d75f652",
    "OriginatorConversationID": "16740-34861180-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
  },
  "timestamp": "2025-12-14T21:45:00.000000Z"
}
```

---

## Callback Endpoints

These endpoints receive notifications from M-Pesa. They should be publicly accessible via HTTPS.

### STK Push Callback

```
POST /api/mpesa/callback/stk
```

M-Pesa will send payment results to this endpoint.

### C2B Callback

```
POST /api/mpesa/callback/c2b
```

M-Pesa will send C2B payment notifications to this endpoint.

### B2C Callback

```
POST /api/mpesa/callback/b2c
```

M-Pesa will send B2C transaction results to this endpoint.

---

## Error Responses

### Validation Error (422)

```json
{
  "message": "The phone number must match the format 254XXXXXXXXX",
  "errors": {
    "phone_number": ["The phone number must match the format 254XXXXXXXXX"]
  }
}
```

### M-Pesa API Error (400)

```json
{
  "success": false,
  "message": "Invalid Access Token",
  "error": "Invalid Access Token",
  "code": 400,
  "timestamp": "2025-12-14T21:50:00.000000Z"
}
```

### Server Error (500)

```json
{
  "success": false,
  "message": "An error occurred while processing your request",
  "error": "An error occurred while processing your request",
  "code": 500,
  "timestamp": "2025-12-14T21:55:00.000000Z"
}
```

---

## Testing Tips

### 1. Use jq for Pretty Output

```bash
curl -X GET http://localhost:8000/api/health | jq .
```

### 2. Save Response to File

```bash
curl -X POST http://localhost:8000/api/mpesa/stk-push \
  -H "Content-Type: application/json" \
  -d @request.json \
  -o response.json
```

### 3. Include Response Headers

```bash
curl -i -X GET http://localhost:8000/api/health
```

### 4. Verbose Output for Debugging

```bash
curl -v -X POST http://localhost:8000/api/mpesa/stk-push \
  -H "Content-Type: application/json" \
  -d '{...}'
```

### 5. Test with Different Environments

```bash
# Sandbox
curl -X POST http://localhost:8000/api/mpesa/stk-push ...

# Production
curl -X POST https://api.yourdomain.com/api/mpesa/stk-push ...
```

---

## Quick Test Script

Save this as `test-mpesa.sh`:

```bash
#!/bin/bash

API_URL="http://localhost:8000/api"

echo "Testing M-Pesa Gateway API..."
echo ""

# Health Check
echo "1. Health Check"
curl -s "$API_URL/health" | jq .
echo ""

# STK Push
echo "2. STK Push"
curl -s -X POST "$API_URL/mpesa/stk-push" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1,
    "phone_number": "254712345678",
    "account_reference": "TEST",
    "transaction_desc": "Test",
    "callback_url": "https://example.com/callback"
  }' | jq .
echo ""

echo "Tests complete!"
```

Run with:
```bash
chmod +x test-mpesa.sh
./test-mpesa.sh
```

---

## Production Checklist

Before using in production:

- [ ] Update `MPESA_ENV=production` in `.env`
- [ ] Use production M-Pesa credentials
- [ ] Use HTTPS for all callback URLs
- [ ] Whitelist your server IP with Safaricom
- [ ] Test all endpoints thoroughly
- [ ] Monitor logs for errors
- [ ] Set up proper error handling
- [ ] Implement retry logic for failed requests

---

## Support

- **Documentation**: PRODUCTION_READY.md
- **Configuration**: SIMPLIFIED_CONFIG.md
- **Email**: kenmwendwamuthengi@gmail.com
- **Safaricom**: https://developer.safaricom.co.ke

---

**M-Pesa Gateway API v1.0.0**
