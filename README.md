# Mpesa SDK

Multi-language SDK for Mpesa Daraja API.

[![Node.js](https://img.shields.io/badge/Node.js-16+-339933?style=flat-square&logo=node.js)](https://www.npmjs.com/package/@yourdudeken/mpesa-sdk)
[![Python](https://img.shields.io/badge/Python-3.8+-3776AB?style=flat-square&logo=python)](https://pypi.org/project/yourdudeken-mpesa-sdk/)
[![Go](https://img.shields.io/badge/Go-1.20+-00ADD8?style=flat-square&logo=go)](https://pkg.go.dev/github.com/yourdudeken/mpesa-sdk)

## Supported Languages

| Language | Package | Version |
|----------|---------|---------|
| Node.js/TypeScript | `@yourdudeken/mpesa-sdk` | v1.0.0 |
| Python | `yourdudeken-mpesa-sdk` | v1.0.0 |
| Go | `github.com/yourdudeken/mpesa-sdk` | v1.0.0 |

## Quick Start

### Node.js
```bash
npm install @yourdudeken/mpesa-sdk
```

### Python
```bash
pip install yourdudeken-mpesa-sdk
```

### Go
```bash
go get github.com/yourdudeken/mpesa-sdk
```

## Documentation

- [Node.js SDK](./packages/node/README.md)
- [Python SDK](./packages/python/README.md)
- [Go SDK](./packages/go/README.md)

## API Reference

All SDKs expose a consistent interface:

```typescript
// Node.js
await mpesa.stkpush({ phonenumber, amount, accountNumber });
await mpesa.b2c({ phonenumber, commandId, amount, remarks });
await mpesa.c2bregisterURLS({ shortcode, confirmUrl, validateUrl });
await mpesa.transactionStatus({ shortcode, transactionId, identifierType, remarks });
```

```python
# Python
mpesa.stkpush(phonenumber, amount, account_number)
mpesa.b2c(phonenumber, command_id, amount, remarks)
mpesa.c2bregisterURLS(shortcode, confirm_url, validate_url)
mpesa.transaction_status(shortcode, transaction_id, identifier_type, remarks)
```

```go
// Go
mpesa.Stkpush(map[string]interface{}{
    "phonenumber": "254712345678", "amount": 100, "accountNumber": "12345",
})
mpesa.B2c(map[string]interface{}{
    "phonenumber": "254712345678", "commandId": "BusinessPayment", "amount": 100, "remarks": "Test payment",
})
mpesa.C2bregisterURLS(map[string]interface{}{
    "shortcode": "600000", "confirmUrl": "https://...", "validateUrl": "https://...",
})
mpesa.TransactionStatus(map[string]interface{}{
    "shortcode": "600000", "transactionId": "123456789", "identifierType": 1, "remarks": "Check status",
})
```

## Supported APIs

- **STK Push** - Lipa na Mpesa Online
- **B2C** - Business to Customer
- **B2B** - Business to Business
- **C2B** - Customer to Business
- **B2Pochi** - Business to Pochi
- **Account Balance** - Check till balance
- **Transaction Status** - Query transaction
- **Reversal** - Reverse transaction

## License

MIT License - see [LICENSE.md](./LICENSE.md)
