# Account Balance API

## Overview
The Account Balance API enables businesses to query the balance of their M-Pesa paybill or till account on demand.

## Prerequisites
1. Install this package
2. Configure the Account Balance section in `src/config/mpesa.php`
3. Have publicly accessible URLs for result and timeout callbacks

## Configuration

```php
'account_balance' => [
    'initiator_name' => 'YOUR_INITIATOR_NAME',
    'security_credential' => 'YOUR_SECURITY_CREDENTIAL',
    'default_command_id' => 'AccountBalance',
    'short_code' => 'YOUR_SHORTCODE',
    'result_url' => 'https://yourdomain.com/api/mpesa/balance/result',
    'timeout_url' => 'https://yourdomain.com/api/mpesa/balance/timeout'
],
```

## Usage

### Basic Example

```php
<?php
require "vendor/autoload.php";

use yourdudeken\Mpesa\Init as Mpesa;

$mpesa = new Mpesa();

try {
    $response = $mpesa->accountBalance([
        'remarks' => 'Daily balance check',
        'identifierType' => 4  // 4 for Paybill, 2 for Till
    ]);
    
    echo json_encode($response);
} catch(\Exception $e) {
    echo json_encode(json_decode($e->getMessage()));
}
```

### Parameters

**Required:**
- `remarks` - Description of the balance query
- `identifierType` - `1` (MSISDN), `2` (Till), `4` (Paybill)

**Optional:**
- `partyA` - Override default short code
- `resultURL` - Override default result URL
- `queueTimeOutURL` - Override default timeout URL

## Callback Handling

### Result Callback

```php
<?php
$response = file_get_contents('php://input');
$data = json_decode($response, true);

if ($data['Result']['ResultCode'] == 0) {
    $resultParams = $data['Result']['ResultParameters']['ResultParameter'];
    
    foreach ($resultParams as $param) {
        if ($param['Key'] == 'AccountBalance') {
            // Balance format: "Working Account|KES|46713.00|46713.00|0.00|0.00"
            $balanceString = $param['Value'];
            $accounts = explode('&', $balanceString);
            
            foreach ($accounts as $accountString) {
                $parts = explode('|', $accountString);
                // parts[0] = Account Name
                // parts[1] = Currency
                // parts[2] = Current Balance
                // parts[3] = Available Balance
                // parts[4] = Reserved Balance
                // parts[5] = Uncleared Balance
            }
        }
    }
}
```

### Balance String Format

Each account is separated by `&` and contains pipe-separated values:
```
Account Name|Currency|Current|Available|Reserved|Uncleared
```

Example:
```
Working Account|KES|46713.00|46713.00|0.00|0.00&Float Account|KES|0.00|0.00|0.00|0.00
```

## Account Types

- **Working Account** - Main business transactions
- **Float Account** - Float management
- **Utility Account** - Utility payments
- **Charges Paid Account** - M-Pesa charges

## Response Codes

| Code | Description |
|------|-------------|
| 0 | Success |
| 1 | Insufficient Funds |
| 11 | Invalid Debit Account |
| 17 | Internal Failure |
| 20 | Unresolved Initiator |

## Best Practices

1. Check balance regularly for reconciliation
2. Set up alerts for low balance thresholds
3. Log all balance queries for audit trail
4. Validate callbacks are from Safaricom
5. Don't query too frequently (respect API limits)

## Additional Resources

- [Official Documentation](https://developer.safaricom.co.ke/account-balance/apis/post/query)
- [B2C API](B2C.md) - Send money to customers
- [B2B API](B2B.md) - Transfer to businesses
