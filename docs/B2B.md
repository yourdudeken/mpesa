### Prerequisites to implementation
1. Make sure you have installed this package
2. You have read the configuration guidelines specific to B2B and taken the necessary steps.
3. You have successfully acquired production credentials from safaricom. If not you can go ahead and use the sandbox credentials that comes preconfigured on installation.

### What is B2B?
B2B (Business to Business) is an M-Pesa API that enables businesses to transfer funds from one business account to another. This is useful for scenarios such as paying suppliers, settling bills with other businesses, or transferring funds between your own business accounts.

### How to consume B2B endpoint with this package.

#### Payment flow involved with this endpoint.
1. Your system initiates a transfer request from your business account to another business account.
2. Safaricom processes the request and transfers the funds between the business accounts.
3. Safaricom sends a response to your system with details regarding the transaction via the ResultURL and QueueTimeOutURL callbacks.

#### Usage
Note this package allows you to override preconfigured parameters for this endpoint. For all supported options check the Safaricom API documentation at https://developer.safaricom.co.ke/docs#b2b-api

##### Using vanilla php

```php
<?php
require "../src/autoload.php";

use Yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->B2B([
        'amount' => 1000,
        'partyB' => '600000',  // Recipient business shortcode
        'accountReference' => 'INV-2023-001',
        'remarks' => 'Payment for supplies',
        'resultURL' => 'https://example.com/v1/payments/b2b/result',
        'queueTimeOutURL' => 'https://example.com/v1/payments/b2b/timeout'
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    $response = json_decode($e->getMessage());
    echo json_encode($response);
}
```

##### Using Laravel.
```php
use Yourdudeken\Mpesa\Init as Mpesa;

class PaymentController {

   public function payBusiness() {
      $mpesa = new Mpesa();
      
      $response = $mpesa->B2B([
          'amount' => 1000,
          'partyB' => '600000',  // Recipient business shortcode
          'accountReference' => 'INV-2023-001',
          'remarks' => 'Payment for supplies',
          'resultURL' => route('mpesa.b2b.result'),
          'queueTimeOutURL' => route('mpesa.b2b.timeout')
      ]); 
      
      return response()->json($response);
   }
}

```

### Configuration Parameters
The following parameters can be configured in `config/mpesa.php` under the `b2b` section:

- **initiator_name**: The name of the initiator making the request
- **initiator_password**: The encrypted password for the initiator
- **default_command_id**: Default is 'BusinessPayBill'. Other options include 'BusinessBuyGoods', 'DisburseFundsToBusiness', 'BusinessToBusinessTransfer', 'MerchantToMerchantTransfer'
- **short_code**: Your business shortcode (sender)

- **result_url**: URL to receive successful transaction results
- **timeout_url**: URL to receive timeout notifications

### Request Parameters
When calling the B2B method, you can pass the following parameters:

- **amount** (required): The amount to transfer to the business
- **partyB** (required): The shortcode of the recipient business
- **accountReference** (required): Account reference for the transaction
- **remarks** (required): Comments sent along with the transaction
- **resultURL** (optional): Overrides the configured result URL
- **queueTimeOutURL** (optional): Overrides the configured timeout URL
- **commandID** (optional): Overrides the default command ID
- **initiatorName** (optional): Overrides the configured initiator name
- **securityCredential** (optional): Overrides the computed security credential
- **senderIdentifierType** (optional): Type of organization sending the transaction (default: 4)
- **receiverIdentifierType** (optional): Type of organization receiving the transaction (default: 4)

### Command IDs and Their Uses
- **BusinessPayBill**: Transfer funds to a PayBill number
- **BusinessBuyGoods**: Transfer funds to a Till/Buy Goods number
- **DisburseFundsToBusiness**: Disburse funds to a business
- **BusinessToBusinessTransfer**: General business to business transfer
- **MerchantToMerchantTransfer**: Transfer between merchant accounts

### Identifier Types
- **1**: MSISDN (Phone Number)
- **2**: Till Number
- **4**: Organization shortcode (Paybill)

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

The actual transaction result will be sent to your configured `result_url` callback with detailed information including:
- Transaction ID
- Transaction amount
- Recipient details
- Transaction completion time
- Account balances

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
                    "Key": "InitiatorAccountCurrentBalance",
                    "Value": "10000.00"
                },
                {
                    "Key": "DebitAccountCurrentBalance",
                    "Value": "9000.00"
                },
                {
                    "Key": "Amount",
                    "Value": 1000
                },
                {
                    "Key": "DebitPartyAffectedAccountBalance",
                    "Value": "9000.00"
                },
                {
                    "Key": "TransCompletedTime",
                    "Value": "20231217230220"
                },
                {
                    "Key": "DebitPartyCharges",
                    "Value": "0.00"
                },
                {
                    "Key": "ReceiverPartyPublicName",
                    "Value": "600000 - Business Name"
                },
                {
                    "Key": "Currency",
                    "Value": "KES"
                }
            ]
        }
    }
}
```

### Known issues with this endpoint
1. **Recipient Validation**: Ensure the recipient business shortcode is valid and active.
2. **Insufficient Funds**: Ensure your business account has sufficient balance before initiating B2B transactions.
3. **Command ID Selection**: Use the correct command ID based on the recipient type (PayBill vs Till Number).
4. **Daily Limits**: Be aware of daily transaction limits set by Safaricom for your account.
5. **Callback Timeouts**: Implement proper timeout handling as network issues may delay callbacks.
6. **Account Reference**: Some businesses may require specific account reference formats for reconciliation.
