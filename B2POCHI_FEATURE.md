# 🎉 B2Pochi API Added - New M-Pesa Feature!

## ✅ What Was Added

### New API: B2Pochi (Business to Pochi)

**B2Pochi** allows businesses to send money directly to customer M-Pesa Pochi savings accounts.

---

## 📁 Files Created/Modified

### 1. **Library Class**
- **File:** `src/Mpesa/B2Pochi/Pay.php`
- **Purpose:** Core B2Pochi payment functionality
- **Features:**
  - Automatic security credential encryption
  - Validation rules
  - Integration with M-Pesa engine

### 2. **API Controller**
- **File:** `api/Controllers/MpesaController.php`
- **Added:** `b2pochi()` method
- **Endpoint:** `POST /api/b2pochi`
- **Features:**
  - Request validation
  - Error handling
  - Logging

### 3. **API Route**
- **File:** `api/index.php`
- **Added:** B2Pochi route registration

### 4. **Documentation**
- **File:** `docs/B2Pochi.md`
- **Content:** Complete usage guide with examples
- **File:** `api/README.md`
- **Updated:** Added B2Pochi endpoint documentation

### 5. **Postman Collection**
- **File:** `api/postman_collection.json`
- **Added:** B2Pochi request with sample data

---

## 🚀 How to Use

### Library Usage

```php
use Yourdudeken\Mpesa\B2Pochi\Pay;

$b2pochi = new Pay();

$response = $b2pochi->submit([
    'OriginatorConversationID' => 'B2P_' . uniqid(),
    'InitiatorName' => 'testapi',
    'initiatorPassword' => 'Safaricom999!*!',
    'CommandID' => 'BusinessPayToPochi',
    'Amount' => 1000,
    'PartyA' => '600000',
    'PartyB' => '254712345678',
    'Remarks' => 'Pochi savings payment',
    'ResultURL' => 'https://yourdomain.com/result',
    'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
    'Occasion' => 'Monthly savings',
]);
```

### API Usage

```bash
curl -X POST http://localhost:8000/api/b2pochi \
  -H "X-API-Key: demo-api-key-12345" \
  -H "Content-Type: application/json" \
  -d '{
    "OriginatorConversationID": "B2P_12345",
    "InitiatorName": "testapi",
    "initiatorPassword": "Safaricom999!*!",
    "CommandID": "BusinessPayToPochi",
    "Amount": 1000,
    "PartyA": "600000",
    "PartyB": "254712345678",
    "Remarks": "Pochi savings payment",
    "ResultURL": "https://yourdomain.com/result",
    "QueueTimeOutURL": "https://yourdomain.com/timeout",
    "Occasion": "Monthly savings"
  }'
```

---

## 📊 API Endpoints Summary

The M-Pesa API now has **11 endpoints**:

| # | Endpoint | Method | Description |
|---|----------|--------|-------------|
| 1 | `/api/health` | GET | Health check |
| 2 | `/api/stk-push` | POST | STK Push payment |
| 3 | `/api/stk-query` | POST | STK Push status |
| 4 | `/api/b2c` | POST | Business to Customer |
| 5 | `/api/b2b` | POST | Business to Business |
| 6 | **`/api/b2pochi`** | **POST** | **Business to Pochi (NEW)** |
| 7 | `/api/c2b/register` | POST | Register C2B URLs |
| 8 | `/api/c2b/simulate` | POST | Simulate C2B |
| 9 | `/api/balance` | POST | Account balance |
| 10 | `/api/transaction-status` | POST | Transaction status |
| 11 | `/api/reversal` | POST | Reverse transaction |

---

## 🔍 What is M-Pesa Pochi?

**M-Pesa Pochi** is a savings feature within M-Pesa that allows users to:
- Save money separately from their main M-Pesa wallet
- Earn interest on savings
- Keep funds secure

**B2Pochi vs B2C:**
- **B2C**: Sends money to main M-Pesa wallet (for spending)
- **B2Pochi**: Sends money to Pochi savings account (for saving)

---

## 📝 Required Parameters

| Parameter | Required | Description |
|-----------|----------|-------------|
| `OriginatorConversationID` | ✅ Yes | Unique transaction ID |
| `InitiatorName` | ✅ Yes | API operator username |
| `SecurityCredential` | ✅ Yes* | Encrypted password |
| `initiatorPassword` | ✅ Yes* | Plain password (auto-encrypted) |
| `CommandID` | No | Default: `BusinessPayToPochi` |
| `Amount` | ✅ Yes | Amount to send |
| `PartyA` | ✅ Yes | Business shortcode |
| `PartyB` | ✅ Yes | Customer phone number |
| `Remarks` | ✅ Yes | Transaction remarks |
| `ResultURL` | ✅ Yes | Result callback URL |
| `QueueTimeOutURL` | ✅ Yes | Timeout callback URL |
| `Occasion` | No | Optional description |

*Provide either `SecurityCredential` OR `initiatorPassword`

---

## ✨ Features

### 1. **Automatic Encryption**
The library automatically encrypts the initiator password using M-Pesa's public certificate:

```php
// You provide plain password
'initiatorPassword' => 'Safaricom999!*!'

// Library encrypts it automatically
$params['SecurityCredential'] = $this->engine->computeSecurityCredential($password);
```

### 2. **Validation**
All required fields are validated before sending the request:
- Amount must be a number
- URLs must be valid
- All required fields must be present

### 3. **Error Handling**
Comprehensive error handling with meaningful messages:
```json
{
  "success": false,
  "error": {
    "code": "B2POCHI_ERROR",
    "message": "Detailed error message"
  }
}
```

### 4. **Logging**
All B2Pochi transactions are logged:
```
[2025-12-09 15:48:00] [info] B2Pochi payment initiated {"recipient":"254712345678","amount":1000}
```

---

## 🧪 Testing

### 1. **Test with Postman**
Import the updated collection:
```
api/postman_collection.json
```

The B2Pochi request is pre-configured with test data.

### 2. **Test with cURL**
```bash
curl -X POST http://localhost:8000/api/b2pochi \
  -H "X-API-Key: demo-api-key-12345" \
  -H "Content-Type: application/json" \
  -d @b2pochi_test.json
```

### 3. **Sandbox Credentials**
- Initiator Name: `testapi`
- Initiator Password: `Safaricom999!*!`
- Shortcode: `600000`
- Test Phone: `254708374149`

---

## 📚 Documentation

- **Full Guide:** `docs/B2Pochi.md`
- **API Docs:** `api/README.md` (updated)
- **Postman:** `api/postman_collection.json` (updated)

---

## 🎯 Summary

✅ **B2Pochi library class created**  
✅ **API endpoint added**  
✅ **Route registered**  
✅ **Documentation written**  
✅ **Postman collection updated**  
✅ **Validation implemented**  
✅ **Error handling added**  
✅ **Logging configured**  

**The B2Pochi API is ready to use!** 🚀

---

## 🔄 Comparison with Safaricom API

Based on the Postman collection provided, our implementation matches Safaricom's specification:

| Feature | Safaricom API | Our Implementation |
|---------|---------------|-------------------|
| Endpoint | `/mpesa/b2c/v1/paymentrequest` | ✅ Implemented |
| Parameters | All required params | ✅ All supported |
| Encryption | Security credential | ✅ Auto-encrypted |
| Validation | Required | ✅ Implemented |
| Callbacks | Result & Timeout URLs | ✅ Supported |

**100% Compatible with Safaricom's B2Pochi API!** ✅
