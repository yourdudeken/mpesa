# M-Pesa API - Quick Reference

## 🚀 Start Server
```bash
cd /home/kennedy/vscode/github/yourdudeken/mpesa/api
php -S localhost:8000
```

## 🧪 Quick Tests

### Health Check (No Auth)
```bash
curl http://localhost:8000/api/health
```

### STK Push (With Auth)
```bash
curl -X POST http://localhost:8000/api/stk-push \
  -H "X-API-Key: demo-api-key-12345" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100,
    "phoneNumber": "254712345678",
    "accountReference": "Order123",
    "transactionDesc": "Payment for Order 123",
    "callBackURL": "https://yourdomain.com/callback"
  }'
```

### Test Unauthorized (Should Return 401)
```bash
curl -X POST http://localhost:8000/api/stk-push \
  -H "Content-Type: application/json" \
  -d '{"amount": 100}'
```

## 📍 Key Files

| File | Purpose |
|------|---------|
| `api/index.php` | Main entry point |
| `api/Config/api.php` | API configuration |
| `src/config/mpesa.php` | M-Pesa credentials |
| `api/example.html` | Interactive demo |
| `api/postman_collection.json` | Postman tests |

## 🔑 Default API Key
```
demo-api-key-12345
```

## 📖 Documentation
- Full Docs: `api/README.md`
- Setup Guide: `API_SETUP_COMPLETE.md`
- Summary: `API_SUMMARY.md`

## 🌐 Endpoints
- Health: `GET /api/health` (no auth)
- STK Push: `POST /api/stk-push`
- STK Query: `POST /api/stk-query`
- B2C: `POST /api/b2c`
- B2B: `POST /api/b2b`
- C2B Register: `POST /api/c2b/register`
- C2B Simulate: `POST /api/c2b/simulate`
- Balance: `POST /api/balance`
- Transaction Status: `POST /api/transaction-status`
- Reversal: `POST /api/reversal`

## ✅ Status
**API is fully functional and ready to use!**
