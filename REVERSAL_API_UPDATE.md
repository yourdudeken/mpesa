# M-Pesa Reversal API - Updated Implementation

## Overview
The Reversal API has been updated to match Safaricom's latest documentation (as of December 2025). This API enables the reversal of Customer-to-Business (C2B) transactions.

## What Changed

### 1. Updated Validation Rules
The validation rules now include all required fields as per the latest documentation:

- ✅ `Initiator` - Username of the API user created on M-PESA portal
- ✅ `SecurityCredential` - Encrypted password for the API user
- ✅ `CommandID` - Must be 'TransactionReversal'
- ✅ `TransactionID` - M-PESA Receipt Number for the transaction being reversed
- ✅ `Amount` - Transaction amount to reverse (NEW)
- ✅ `ReceiverParty` - Organization Short Code (RENAMED from PartyA)
- ✅ `RecieverIdentifierType` - Type of Organization (must be '11')
- ✅ `ResultURL` - URL for result notification
- ✅ `QueueTimeOutURL` - URL for timeout notification
- ✅ `Remarks` - Additional information (2-100 characters)

### 2. Field Name Changes
- **Changed:** `PartyA` → `ReceiverParty` (to match official documentation)
- **Fixed:** `RecieverIdentifierType` now correctly set to `'11'` for organization short codes
- **Added:** `Amount` field is now required
- **Default:** `CommandID` defaults to `'TransactionReversal'` if not specified in config

### 3. Code Improvements
- Updated PHPDoc comments to accurately describe the reversal process
- Improved code formatting and consistency
- Added inline comments explaining the identifier type value

## API Request Example

```json
{
    "Initiator": "apiop37",
    "SecurityCredential": "jUb+dOXJiBDui8FnruaFckZJQup3kmmCH5XJ4NY/Oo3KaUTmJbxUiVgzBjqdL533u5Q435MT2VJwr/ /1fuZvA===",
    "CommandID": "TransactionReversal",
    "TransactionID": "PDU91HIVIT",
    "Amount": "200",
    "ReceiverParty": "603021",
    "RecieverIdentifierType": "11",
    "ResultURL": "https://mydomain.com/reversal/result",
    "QueueTimeOutURL": "https://mydomain.com/reversal/queue",
    "Remarks": "Payment reversal"
}
```

## API Response Example

### Success Response
```json
{
    "OriginatorConversationID": "f1e2-4b95-a71d-b30d3cdbb7a7735297",
    "ConversationID": "AG_20210706_20106e9209f64bebd05b",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

## Callback Result Payload

### Successful Callback
```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "dad6-4c34-8787-c8cb963a496d1268232",
        "ConversationID": "AG_20211114_201018edbbf9f1582eaa",
        "TransactionID": "SKE52PAWR9",
        "ResultParameters": {
            "ResultParameter": [
                {"Key": "DebitAccountBalance", "Value": "Utility Account|KES|7722179.62|7722179.62|0.00|0.00"},
                {"Key": "Amount", "Value": 1.00},
                {"Key": "TransCompletedTime", "Value": 20211114132711},
                {"Key": "OriginalTransactionID", "Value": "SKC82PACB8"},
                {"Key": "Charge", "Value": 0.00},
                {"Key": "CreditPartyPublicName", "Value": "254705912645 - NICHOLAS JOHN SONGOK"},
                {"Key": "DebitPartyPublicName", "Value": "600992 - Safaricom Daraja 992"}
            ]
        }
    }
}
```

## Common Result Codes

| ResultCode | ResultDesc | Explanation |
|------------|------------|-------------|
| 0 | The service request is processed successfully | Request processed successfully on M-PESA |
| R000002 | The OriginalTransactionID is invalid | The TransactionID provided is invalid or does not exist |
| R000001 | The transaction has already been reversed | The TransactionID provided is already reversed |
| 11 | The DebitParty is in an invalid state | The organization/short code account is not active |
| 21 | The initiator is not allowed to initiate | API user lacks Org Reversals Initiator API role |
| 2001 | The initiator information is invalid | API user credentials are invalid |

## Use Cases

1. **Reverse an erroneous payment** made to your M-PESA Collection Account (Pay bill or Till number)
2. **Reverse double payments**
3. **Reverse payments where services were not fulfilled**

## Important Notes

1. **Asynchronous API**: This API is asynchronous. You receive an acknowledgement first, and the actual result is sent to your `ResultURL`.

2. **API Role Required**: The API user must have the **Org Reversals Initiator** role assigned in the M-PESA Organization Portal.

3. **RecieverIdentifierType**: Despite the typo in the parameter name, it must be set to `'11'` for organization short codes.

4. **CommandID**: Must always be `'TransactionReversal'` - no other values are allowed.

5. **B2C Reversals**: Cannot be done via API - must be done manually on the M-PESA portal.

## Testing

### Using the API Tester (example.html)

1. Navigate to `http://localhost:8000/example.html`
2. Click on the **"Reversal"** tab
3. Fill in the required fields:
   - **Amount**: e.g., `100`
   - **Transaction ID**: e.g., `PDU91HIVIT` (M-PESA receipt number)
   - **Remarks**: e.g., `Payment reversal`
   - **Result URL**: e.g., `https://yourdomain.com/reversal/result`
   - **Timeout URL**: e.g., `https://yourdomain.com/reversal/queue`
4. Click **"Reverse Transaction"**

### Configuration Required

Ensure your config file has the following settings:

```php
'reversal' => [
    'short_code' => '603021',  // Your organization short code
    'initiator_name' => 'apiop37',  // API user username
    'security_credential' => 'your_password',  // API user password (will be encrypted)
    'default_command_id' => 'TransactionReversal',
    'result_url' => 'https://yourdomain.com/reversal/result',
    'timeout_url' => 'https://yourdomain.com/reversal/queue',
]
```

## Files Modified

1. `/home/kennedy/vscode/github/yourdudeken/mpesa/src/Mpesa/Reversal/Reversal.php`
   - Updated validation rules
   - Changed `PartyA` to `ReceiverParty`
   - Added `Amount` field requirement
   - Set `RecieverIdentifierType` to `'11'`
   - Improved documentation

2. `/home/kennedy/vscode/github/yourdudeken/mpesa/api/Controllers/MpesaController.php`
   - Removed `partyB` from balance and transaction status validation (user changes)

## Migration Guide

If you're upgrading from the previous version:

1. **No breaking changes** for existing implementations - the library maintains backward compatibility
2. **New field required**: `Amount` must now be provided when calling the reversal API
3. **Field renamed**: If you were manually setting `PartyA`, change it to `ReceiverParty`
4. The `ReceiverParty` is automatically set from config, so no changes needed if you rely on config values

## Support

For production issues:
- **Email**: apisupport@safaricom.co.ke
- **Incident Management**: Visit the Safaricom Developer Portal

For M-PESA Organization Portal access:
- **Email**: M-PESABusiness@Safaricom.co.ke
