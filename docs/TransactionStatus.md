### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to Transaction Status and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is Transaction Status?
The Transaction Status API enables businesses to query the status of M-Pesa transactions (B2B, B2C, or C2B). This is particularly useful when one party in a transaction claims not to have received confirmation, or when you need to verify the status of a transaction for reconciliation purposes.

### How to consume Transaction Status endpoint with this package.

#### Query flow involved with this endpoint.
1. Your system initiates a status query request for a specific transaction using its Transaction ID. The package automatically computes the `SecurityCredential`.
2. Safaricom processes the request and retrieves the transaction status.
3. The engine uses defaults from `src/config/mpesa.php` for `remarks` and `occasion` if they are not provided, and normalizes them for length.
4. Safaricom sends a response to your system with transaction details via the ResultURL and QueueTimeOutURL callbacks.

#### Usage
Note this package allows you to override preconfigured parameters for this endpoint. For all supported options check the Safaricom API documentation at https://developer.safaricom.co.ke/docs#transaction-status

##### Using vanilla php

```php
<?php
require "vendor/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa([
    'auth' => [
        'consumer_key'    => '...',
        'consumer_secret' => '...',
    ],
    'initiator' => [
        'name'     => 'testapi',
        'password' => '...',
    ],
    'transaction_status' => [
        'short_code' => '600000',
    ]
]);

try {
    $response = $mpesa->status->submit([
        'transactionID' => 'NLJ7RT61SV',
        'result_url'    => 'https://example.com/status/result',
        'timeout_url'   => 'https://example.com/status/timeout',
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

##### Using Laravel.
```php
use Yourdudeken\Mpesa\Init as Mpesa;

class MpesaController {

   public function checkTransactionStatus($transactionId) {
      $mpesa = new Mpesa(config('mpesa'));
      
      $response = $mpesa->status->submit([
          'transactionID' => $transactionId,
          'result_url'    => route('mpesa.status.result'),
          'timeout_url'   => route('mpesa.status.timeout'),
      ]); 
      
      return response()->json($response);
   }
}
````

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `status` section:

- **initiator_name**: The name of the initiator making the request
- **initiator_password**: The encrypted password for the initiator
- **command_id**: Default is 'TransactionStatusQuery'
- **short_code**: Your business shortcode
- **result_url** (optional): URL to receive transaction status results. Falls back to global callback
- **timeout_url** (optional): URL to receive timeout notifications. Falls back to global callback

### Request Parameters
When calling the status->submit() method, you can pass the following parameters:

- **transactionID** (required): The M-Pesa transaction ID to query (e.g., 'NLJ7RT61SV')
- **identifierType** (optional): Type of organization (default: 4 for organization shortcode)
- **remarks** (optional): Comments sent along with the request. Falls back to config default
- **occasion** (optional): Additional information about the query. Falls back to config default
- **resultURL** (optional): Overrides the configured result URL. Falls back to global config
- **queueTimeOutURL** (optional): Overrides the configured timeout URL. Falls back to global config
- **commandID** (optional): Overrides the default command ID
- **initiatorName** (optional): Overrides the configured initiator name
- **securityCredential** (optional): Overrides the computed security credential
- **partyA** (optional): The shortcode making the query (overrides config short_code)
- **originatorConversationID** (optional): Your unique conversation ID for tracking

### Identifier Types
- **1**: MSISDN (Phone Number)
- **2**: Till Number
- **4**: Organization shortcode (Paybill) - Most commonly used

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

The actual transaction status will be sent to your configured `result_url` callback.

### Callback Response Example (Successful Transaction)
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
                    "Key": "ReceiptNo",
                    "Value": "NLJ7RT61SV"
                },
                {
                    "Key": "Conversation ID",
                    "Value": "AG_20231217_00004e8f3f7c9b8d1234"
                },
                {
                    "Key": "FinalisedTime",
                    "Value": "20231217230220"
                },
                {
                    "Key": "Amount",
                    "Value": 100
                },
                {
                    "Key": "TransactionStatus",
                    "Value": "Completed"
                },
                {
                    "Key": "ReasonType",
                    "Value": "Salary Payment"
                },
                {
                    "Key": "TransactionReason",
                    "Value": "Payment for services"
                },
                {
                    "Key": "DebitPartyCharges",
                    "Value": "0.00"
                },
                {
                    "Key": "DebitAccountType",
                    "Value": "Working Account"
                },
                {
                    "Key": "InitiatedTime",
                    "Value": "20231217230210"
                },
                {
                    "Key": "Originator Conversation ID",
                    "Value": "12345-67890-1"
                },
                {
                    "Key": "CreditPartyName",
                    "Value": "254722000000 - John Doe"
                },
                {
                    "Key": "DebitPartyName",
                    "Value": "600000 - Business Name"
                }
            ]
        },
        "ReferenceData": {
            "ReferenceItem": {
                "Key": "QueueTimeoutURL",
                "Value": "https://example.com/v1/mpesa/status/timeout"
            }
        }
    }
}
```

### Callback Response Example (Failed/Not Found Transaction)
```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 1,
        "ResultDesc": "The transaction could not be found.",
        "OriginatorConversationID": "12345-67890-1",
        "ConversationID": "AG_20231217_00004e8f3f7c9b8d1234",
        "TransactionID": "NLJ7RT61SV",
        "ReferenceData": {
            "ReferenceItem": {
                "Key": "QueueTimeoutURL",
                "Value": "https://example.com/v1/mpesa/status/timeout"
            }
        }
    }
}
```

### Transaction Status Values
- **Completed**: Transaction was successful
- **Failed**: Transaction failed
- **Pending**: Transaction is still being processed
- **Reversed**: Transaction was reversed

### Result Codes
- **0**: Success - Transaction status retrieved successfully
- **1**: Transaction not found or invalid transaction ID
- **2**: System error
- **Other codes**: Various error conditions

### Use Cases
1. **Dispute Resolution**: Verify transaction status when customers claim payment wasn't received
2. **Reconciliation**: Check status of transactions that didn't receive callbacks
3. **Monitoring**: Track status of critical transactions
4. **Retry Logic**: Determine if a transaction needs to be retried
5. **Audit Trail**: Maintain comprehensive transaction records
6. **Customer Support**: Provide real-time transaction status to customers

### Best Practices
1. **Store Transaction IDs**: Always store M-Pesa transaction IDs from all transactions for future reference
2. **Implement Caching**: Cache transaction status results to avoid redundant queries
3. **Error Handling**: Handle cases where transactions are not found gracefully
4. **Rate Limiting**: Don't query the same transaction repeatedly in short intervals
5. **Logging**: Log all status queries and responses for audit purposes

### Known issues and Security
1. **Transaction ID Required**: You must have the exact M-Pesa transaction ID.
2. **Callback Dependency**: Status details are only available via the callback URL.
3. **Transaction Age**: Very old transactions may not be available.
4. **Permissions**: Ensure your initiator credentials have permission.
5. **Security**: RSA encryption for initiator credentials is handled automatically by the package using certificates in `src/config/`.
6. **Validation**: The `TransactionStatus\TransactionStatus` service runs comprehensive checks using the internal `Validator` before transmission.
