### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to Account Balance and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is Account Balance?
The Account Balance API enables businesses to query the balance of their M-Pesa business accounts (PayBill or Till Number) on demand. This is useful for reconciliation, monitoring account activity, and ensuring sufficient funds before initiating transactions.

### How to consume Account Balance endpoint with this package.

#### Query flow involved with this endpoint.
1. Your system initiates a balance query request for your business account. The package automatically computes the `SecurityCredential` using the initiator password and the appropriate Safaricom public certificate.
2. Safaricom processes the request and retrieves the current account balance.
3. The engine uses defaults from `src/config/mpesa.php` if `remarks` or `identifierType` are not provided.
4. Safaricom sends a response to your system with balance details via the ResultURL and QueueTimeOutURL callbacks.

#### Usage
Note this package allows you to override preconfigured parameters for this endpoint. For all supported options check the Safaricom API documentation at https://developer.safaricom.co.ke/docs#account-balance-api

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
    'account_balance' => [
        'short_code' => '600000',
    ]
]);

try {
    $response = $mpesa->balance->submit([
        'result_url'  => 'https://example.com/balance/result',
        'timeout_url' => 'https://example.com/balance/timeout',
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

   public function checkBalance() {
      $mpesa = new Mpesa(config('mpesa'));
      
      $response = $mpesa->balance->submit([
          'result_url'  => route('mpesa.balance.result'),
          'timeout_url' => route('mpesa.balance.timeout'),
      ]); 
      
      return response()->json($response);
   }
}
````

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `balance` section:

- **initiator_name**: The name of the initiator making the request
- **initiator_password**: The encrypted password for the initiator
- **command_id**: Default is 'AccountBalance'
- **short_code**: Your business shortcode
- **result_url** (optional): URL to receive balance query results. Falls back to global callback
- **timeout_url** (optional): URL to receive timeout notifications. Falls back to global callback

### Request Parameters
When calling the balance->submit() method, you can pass the following parameters:

- **identifierType** (optional): Type of organization (default: 4 for organization shortcode)
- **remarks** (optional): Comments sent along with the request. Falls back to config default
- **resultURL** (optional): Overrides the configured result URL. Falls back to global config
- **queueTimeOutURL** (optional): Overrides the configured timeout URL. Falls back to global config
- **commandID** (optional): Overrides the default command ID
- **initiatorName** (optional): Overrides the configured initiator name
- **securityCredential** (optional): Overrides the computed security credential
- **partyA** (optional): The shortcode to query (overrides config short_code)

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

The actual balance information will be sent to your configured `result_url` callback.

### Callback Response Example
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
                    "Key": "AccountBalance",
                    "Value": "Working Account|KES|50000.00|50000.00|0.00|0.00&Float Account|KES|0.00|0.00|0.00|0.00&Utility Account|KES|25000.00|25000.00|0.00|0.00"
                },
                {
                    "Key": "BOCompletedTime",
                    "Value": "20231217230220"
                }
            ]
        },
        "ReferenceData": {
            "ReferenceItem": {
                "Key": "QueueTimeoutURL",
                "Value": "https://example.com/v1/mpesa/balance/timeout"
            }
        }
    }
}
```

### Understanding the Balance Response
The `AccountBalance` value contains information about different account types separated by `&`:

**Working Account**: Main operational account
- Format: `Working Account|Currency|Current Balance|Available Balance|Reserved Amount|Uncleared Balance`

**Float Account**: Float account (if applicable)
- Format: `Float Account|Currency|Current Balance|Available Balance|Reserved Amount|Uncleared Balance`

**Utility Account**: Utility account for charges
- Format: `Utility Account|Currency|Current Balance|Available Balance|Reserved Amount|Uncleared Balance`

Example parsing in PHP:
```php
$accountBalanceString = "Working Account|KES|50000.00|50000.00|0.00|0.00&Float Account|KES|0.00|0.00|0.00|0.00&Utility Account|KES|25000.00|25000.00|0.00|0.00";

$accounts = explode('&', $accountBalanceString);

foreach ($accounts as $account) {
    $parts = explode('|', $account);
    $accountType = $parts[0];
    $currency = $parts[1];
    $currentBalance = $parts[2];
    $availableBalance = $parts[3];
    $reservedAmount = $parts[4];
    $unclearedBalance = $parts[5];
    
    echo "$accountType: $currency $availableBalance available\n";
}
```

### Use Cases
1. **Pre-transaction Validation**: Check if you have sufficient funds before initiating B2C or B2B payments
2. **Reconciliation**: Regular balance checks for accounting and reconciliation purposes
3. **Monitoring**: Set up automated alerts when balance falls below certain thresholds
4. **Reporting**: Generate financial reports with current account balances
5. **Audit Trail**: Maintain historical balance records for auditing

### Known issues and Security
1. **Callback Dependency**: Balance information is only available via the callback URL, not in the immediate response.
2. **Parsing Required**: The balance response requires parsing to extract individual account balances.
3. **Rate Limiting**: Avoid making excessive balance queries. Implement caching if you need frequent balance checks.
4. **Permissions**: Ensure your initiator credentials have permission to query account balances.
5. **Timeout Handling**: Implement proper timeout handling as network issues may delay callbacks.
6. **Automated Security**: Security credentials are computed internally using `openssl_public_encrypt`. The package looks for `.cer` files in the `src/config/` directory.
7. **Validation**: The `AccountBalance\Balance` service enforces validation on `ResultURL` and `QueueTimeOutURL` using the package's internal `Validator`.
