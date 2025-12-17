### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to Reversal and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is Reversal?
The Reversal API enables businesses to reverse erroneous M-Pesa transactions (B2B, B2C, or C2B). This is useful for scenarios such as reversing payments made to wrong recipients, duplicate payments, or payments for goods/services that were not delivered.

### How to consume Reversal endpoint with this package.

#### Reversal flow involved with this endpoint.
1. Your system initiates a reversal request for a specific transaction using its Transaction ID.
2. Safaricom processes the reversal request and returns the funds to the original sender.
3. Safaricom sends a response to your system with reversal details via the ResultURL and QueueTimeOutURL callbacks.

#### Usage
Note this package allows you to override preconfigured parameters for this endpoint. For all supported options check the Safaricom API documentation at https://developer.safaricom.co.ke/docs#reversal

##### Using vanilla php

```php
<?php
require "../src/autoload.php";

use Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->reversal([
        'transactionID' => 'NLJ7RT61SV',  // The M-Pesa transaction ID to reverse
        'amount' => 100,  // Amount to reverse
        'receiverParty' => '600000',  // The shortcode that received the original payment
        'receiverIdentifierType' => 4,  // 4 for organization shortcode
        'remarks' => 'Reversing erroneous payment',
        'occasion' => 'Duplicate payment',
        'resultURL' => 'https://example.com/v1/mpesa/reversal/result',
        'queueTimeOutURL' => 'https://example.com/v1/mpesa/reversal/timeout'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

##### Using Laravel.
```php
use Mpesa\Init as Mpesa;

class MpesaController {

   public function reverseTransaction($transactionId) {
      $mpesa = new Mpesa();
      
      $response = $mpesa->reversal([
          'transactionID' => $transactionId,
          'amount' => 100,
          'receiverParty' => '600000',
          'receiverIdentifierType' => 4,
          'remarks' => 'Reversing erroneous payment',
          'occasion' => 'Duplicate payment',
          'resultURL' => route('mpesa.reversal.result'),
          'queueTimeOutURL' => route('mpesa.reversal.timeout')
      ]); 
      
      return response()->json($response);
   }
}

```

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `reversal` section:

- **initiator_name**: The name of the initiator making the request
- **security_credential**: The encrypted password for the initiator
- **default_command_id**: Default is 'TransactionReversal'
- **short_code**: Your business shortcode
- **result_url**: URL to receive reversal results
- **timeout_url**: URL to receive timeout notifications

### Request Parameters
When calling the reversal method, you can pass the following parameters:

- **transactionID** (required): The M-Pesa transaction ID to reverse (e.g., 'NLJ7RT61SV')
- **amount** (required): The amount to reverse (must match original transaction amount)
- **receiverParty** (required): The party that received the original payment
- **receiverIdentifierType** (required): Type of receiver (1=MSISDN, 2=Till, 4=Shortcode)
- **remarks** (optional): Comments about the reversal
- **occasion** (optional): Reason for the reversal
- **resultURL** (optional): Overrides the configured result URL
- **queueTimeOutURL** (optional): Overrides the configured timeout URL
- **commandID** (optional): Overrides the default command ID
- **initiatorName** (optional): Overrides the configured initiator name
- **securityCredential** (optional): Overrides the computed security credential

### Receiver Identifier Types
- **1**: MSISDN (Phone Number) - For reversing B2C payments
- **2**: Till Number - For reversing payments to Till Numbers
- **4**: Organization shortcode (Paybill) - For reversing B2B payments

### Response Handling
The API returns an immediate response with the following structure:

```json
{
    "ConversationID": "AG_20231217_00004e8f3f7c9b8d1234",
    "OriginatorConversationID": "12345-67890-1",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

The actual reversal result will be sent to your configured `result_url` callback.

### Callback Response Example (Successful Reversal)
```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "12345-67890-1",
        "ConversationID": "AG_20231217_00004e8f3f7c9b8d1234",
        "TransactionID": "NLJ7RT61SV",
        "ResultParameters": {
            "ResultParameter": [
                {
                    "Key": "DebitAccountBalance",
                    "Value": "10100.00"
                },
                {
                    "Key": "Amount",
                    "Value": 100
                },
                {
                    "Key": "TransactionReason",
                    "Value": "Duplicate payment"
                },
                {
                    "Key": "DebitPartyAffectedAccountBalance",
                    "Value": "Working Account|KES|10100.00|10100.00|0.00|0.00"
                },
                {
                    "Key": "TransCompletedTime",
                    "Value": "20231217230220"
                },
                {
                    "Key": "OriginalTransactionID",
                    "Value": "NLJ7RT61SV"
                },
                {
                    "Key": "Charge",
                    "Value": 0
                },
                {
                    "Key": "CreditPartyPublicName",
                    "Value": "254722000000 - John Doe"
                },
                {
                    "Key": "DebitPartyPublicName",
                    "Value": "600000 - Business Name"
                }
            ]
        },
        "ReferenceData": {
            "ReferenceItem": {
                "Key": "QueueTimeoutURL",
                "Value": "https://example.com/v1/mpesa/reversal/timeout"
            }
        }
    }
}
```

### Callback Response Example (Failed Reversal)
```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 1,
        "ResultDesc": "The transaction could not be reversed. Transaction not found.",
        "OriginatorConversationID": "12345-67890-1",
        "ConversationID": "AG_20231217_00004e8f3f7c9b8d1234",
        "TransactionID": "NLJ7RT61SV",
        "ReferenceData": {
            "ReferenceItem": {
                "Key": "QueueTimeoutURL",
                "Value": "https://example.com/v1/mpesa/reversal/timeout"
            }
        }
    }
}
```

### Result Codes
- **0**: Success - Transaction reversed successfully
- **1**: Transaction not found or cannot be reversed
- **2**: System error
- **2001**: Invalid initiator credentials
- **2006**: Insufficient permissions
- **Other codes**: Various error conditions (amount mismatch, already reversed, etc.)

### Use Cases
1. **Erroneous Payments**: Reverse payments made to wrong recipients
2. **Duplicate Payments**: Reverse duplicate transactions
3. **Undelivered Goods/Services**: Reverse payments when goods/services are not delivered
4. **Customer Disputes**: Handle customer complaints about incorrect charges
5. **System Errors**: Reverse transactions caused by system glitches
6. **Refund Processing**: Process refunds for returned goods or cancelled services

### Important Considerations

#### Time Limits
- Reversals must typically be initiated within a specific timeframe (usually within the same day or up to 24 hours)
- Check with Safaricom for exact time limits for your account

#### Amount Matching
- The reversal amount must exactly match the original transaction amount
- Partial reversals are generally not supported

#### Transaction Status
- The original transaction must be completed before it can be reversed
- Already reversed transactions cannot be reversed again

#### Permissions
- Your initiator account must have reversal permissions
- Not all business accounts have reversal rights by default

### Best Practices
1. **Verify Before Reversing**: Always verify transaction details before initiating a reversal
2. **Store Transaction IDs**: Maintain a record of all transaction IDs for potential reversals
3. **Document Reasons**: Keep detailed records of why each reversal was initiated
4. **Customer Communication**: Inform customers before reversing their transactions
5. **Reconciliation**: Update your accounting records after successful reversals
6. **Error Handling**: Handle reversal failures gracefully and notify relevant parties
7. **Approval Workflow**: Implement approval workflows for reversals to prevent unauthorized reversals
8. **Audit Trail**: Log all reversal attempts and their outcomes

### Known issues with this endpoint
1. **Transaction ID Required**: You must have the exact M-Pesa transaction ID to reverse a transaction
2. **Time Constraints**: Reversals may only be possible within a limited time window
3. **Amount Must Match**: The reversal amount must exactly match the original transaction amount
4. **No Partial Reversals**: You cannot reverse part of a transaction
5. **Permissions Required**: Your account must have reversal permissions enabled
6. **Already Reversed**: Transactions that have already been reversed cannot be reversed again
7. **Callback Dependency**: Reversal confirmation is only available via the callback URL
8. **Recipient Balance**: Reversal may fail if the recipient's account has insufficient balance
