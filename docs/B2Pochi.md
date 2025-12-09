# B2Pochi (Business to Pochi)

Send money from your business to customer M-Pesa Pochi accounts.

## Usage

```php
use Yourdudeken\Mpesa\B2Pochi\Pay;

$b2pochi = new Pay();

$response = $b2pochi->submit([
    'OriginatorConversationID' => 'unique-conversation-id-12345',
    'InitiatorName' => 'testapi',
    'initiatorPassword' => 'your-initiator-password', // Will be encrypted automatically
    'CommandID' => 'BusinessPayToPochi', // Default value
    'Amount' => 1000,
    'PartyA' => '600000', // Your business shortcode
    'PartyB' => '254712345678', // Customer phone number
    'Remarks' => 'Payment to Pochi account',
    'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
    'ResultURL' => 'https://yourdomain.com/result',
    'Occasion' => 'Salary payment', // Optional
]);
```

## Parameters

| Parameter | Required | Description |
|-----------|----------|-------------|
| `OriginatorConversationID` | Yes | Unique identifier for the transaction |
| `InitiatorName` | Yes | API operator username |
| `SecurityCredential` | Yes* | Encrypted initiator password |
| `initiatorPassword` | Yes* | Plain initiator password (will be encrypted) |
| `CommandID` | No | Transaction command (default: `BusinessPayToPochi`) |
| `Amount` | Yes | Amount to send |
| `PartyA` | Yes | Organization shortcode |
| `PartyB` | Yes | Customer phone number (254XXXXXXXXX) |
| `Remarks` | Yes | Transaction remarks |
| `QueueTimeOutURL` | Yes | Timeout callback URL |
| `ResultURL` | Yes | Result callback URL |
| `Occasion` | No | Optional occasion description |

*Note: Provide either `SecurityCredential` (pre-encrypted) or `initiatorPassword` (will be encrypted automatically)

## Response

### Success Response
```json
{
    "ConversationID": "AG_20191219_00005797af5d7d75f652",
    "OriginatorConversationID": "unique-conversation-id-12345",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

### Error Response
```json
{
    "requestId": "11728-2929992-1",
    "errorCode": "401.002.01",
    "errorMessage": "Error Occurred - Invalid Access Token - BJGFGOXv5aZnw90KkA4TDtu4Xdyf"
}
```

## Callback Response

M-Pesa will send a callback to your `ResultURL` with the transaction result:

```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "unique-conversation-id-12345",
        "ConversationID": "AG_20191219_00005797af5d7d75f652",
        "TransactionID": "NLJ7RT61SV",
        "ResultParameters": {
            "ResultParameter": [
                {
                    "Key": "TransactionReceipt",
                    "Value": "NLJ7RT61SV"
                },
                {
                    "Key": "TransactionAmount",
                    "Value": 1000
                },
                {
                    "Key": "B2CWorkingAccountAvailableFunds",
                    "Value": 50000.00
                },
                {
                    "Key": "B2CUtilityAccountAvailableFunds",
                    "Value": 120000.00
                },
                {
                    "Key": "TransactionCompletedDateTime",
                    "Value": "19.12.2019 11:45:50"
                },
                {
                    "Key": "ReceiverPartyPublicName",
                    "Value": "254712345678 - John Doe"
                },
                {
                    "Key": "B2CChargesPaidAccountAvailableFunds",
                    "Value": 0.00
                },
                {
                    "Key": "B2CRecipientIsRegisteredCustomer",
                    "Value": "Y"
                }
            ]
        },
        "ReferenceData": {
            "ReferenceItem": {
                "Key": "QueueTimeoutURL",
                "Value": "https://yourdomain.com/timeout"
            }
        }
    }
}
```

## Command IDs

The following command IDs are supported for B2Pochi:

- `BusinessPayToPochi` - Send money to M-Pesa Pochi account (default)

## Difference from B2C

B2Pochi is specifically for sending money to M-Pesa Pochi accounts, which are:
- Savings accounts within M-Pesa
- Separate from the main M-Pesa wallet
- Used for saving money

Regular B2C sends money to the main M-Pesa wallet.

## Example with Error Handling

```php
use Yourdudeken\Mpesa\B2Pochi\Pay;
use Yourdudeken\Mpesa\Exceptions\MpesaException;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;

try {
    $b2pochi = new Pay();
    
    $response = $b2pochi->submit([
        'OriginatorConversationID' => uniqid('B2P_'),
        'InitiatorName' => 'testapi',
        'initiatorPassword' => 'Safaricom999!*!',
        'Amount' => 1000,
        'PartyA' => '600000',
        'PartyB' => '254712345678',
        'Remarks' => 'Pochi savings payment',
        'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
        'ResultURL' => 'https://yourdomain.com/result',
        'Occasion' => 'Monthly savings',
    ]);
    
    if ($response->ResponseCode == '0') {
        echo "Payment initiated successfully!";
        echo "Conversation ID: " . $response->ConversationID;
    } else {
        echo "Payment failed: " . $response->ResponseDescription;
    }
    
} catch (ConfigurationException $e) {
    echo "Configuration error: " . $e->getMessage();
} catch (MpesaException $e) {
    echo "M-Pesa error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Testing

In sandbox environment, use these test credentials:
- Initiator Name: `testapi`
- Initiator Password: `Safaricom999!*!`
- Shortcode: `600000`
- Test Phone: `254708374149`

## Notes

1. Ensure your M-Pesa account has sufficient balance
2. The `OriginatorConversationID` should be unique for each transaction
3. Callback URLs must be publicly accessible HTTPS endpoints
4. The initiator password is automatically encrypted using the M-Pesa public certificate
5. Transaction results are sent asynchronously to your `ResultURL`
