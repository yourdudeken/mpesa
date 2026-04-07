# Mpesa SDK Examples

## PHP (Laravel)

### STK Push (Lipa na Mpesa)
```php
use Yourdudeken\Mpesa\Facades\Mpesa;

$response = Mpesa::stkpush(
    phonenumber: '254712345678',
    amount: 100,
    account_number: 'ORDER123',
    callback_url: 'https://yourdomain.com/stk/callback'
);
```

### B2C (Business to Customer)
```php
$response = Mpesa::b2c(
    phonenumber: '254712345678',
    command_id: 'BusinessPayment',
    amount: 1000,
    remarks: 'Salary payment'
);
```

### C2B Register URLs
```php
$response = Mpesa::c2bregisterURLS(
    shortcode: '600000',
    confirm_url: 'https://yourdomain.com/c2b/confirm',
    validate_url: 'https://yourdomain.com/c2b/validate'
);
```

## Node.js

### STK Push
```typescript
import { Mpesa } from '@yourdudeken/mpesa-sdk';

const mpesa = new Mpesa(config);

const response = await mpesa.stkpush({
  phonenumber: '254712345678',
  amount: 100,
  accountNumber: 'ORDER123',
  callbackUrl: 'https://yourdomain.com/stk/callback'
});
```

### B2C
```typescript
const response = await mpesa.b2c({
  phonenumber: '254712345678',
  commandId: 'BusinessPayment',
  amount: 1000,
  remarks: 'Salary payment'
});
```

## Python

### STK Push
```python
from yourdudeken_mpesa_sdk import Mpesa, MpesaConfig

config = MpesaConfig(
    environment='sandbox',
    mpesa_consumer_key='your_key',
    mpesa_consumer_secret='your_secret',
    passkey='your_passkey',
    shortcode='174379',
    initiator_name='testapi',
    initiator_password='your_password'
)

mpesa = Mpesa(config)

response = mpesa.stkpush(
    phonenumber='254712345678',
    amount=100,
    account_number='ORDER123'
)
```

### B2C
```python
response = mpesa.b2c(
    phonenumber='254712345678',
    command_id='BusinessPayment',
    amount=1000,
    remarks='Salary payment'
)
```

## Java

```java
import com.yourdudeken.mpesa.MpesaClient;
import com.yourdudeken.mpesa.config.MpesaConfig;

MpesaConfig config = new MpesaConfig();
config.setEnvironment("sandbox");
config.setMpesaConsumerKey("your_key");
config.setMpesaConsumerSecret("your_secret");
config.setShortcode("174379");
config.setPasskey("your_passkey");
config.setInitiatorName("testapi");
config.setInitiatorPassword("your_password");

MpesaClient mpesa = new MpesaClient(config);

// STK Push
mpesa.stkPush("254712345678", 100, "ORDER123");
```

## C#

```csharp
using Yourdudeken.Mpesa;

var config = new MpesaConfig
{
    Environment = "sandbox",
    MpesaConsumerKey = "your_key",
    MpesaConsumerSecret = "your_secret",
    Shortcode = "174379",
    Passkey = "your_passkey",
    InitiatorName = "testapi",
    InitiatorPassword = "your_password"
};

var mpesa = new MpesaClient(config);
await mpesa.StkPush("254712345678", 100, "ORDER123");
```

## Go

```go
package main

import (
    "github.com/yourdudeken/mpesa-sdk/mpesa"
)

func main() {
    config := mpesa.Config{
        Environment:       "sandbox",
        MpesaConsumerKey:  "your_key",
        MpesaConsumerSecret: "your_secret",
        Shortcode:         "174379",
        Passkey:           "your_passkey",
        InitiatorName:     "testapi",
        InitiatorPassword: "your_password",
    }

    client := mpesa.NewClient(config)
    client.STKPush("254712345678", 100, "ORDER123")
}
```
