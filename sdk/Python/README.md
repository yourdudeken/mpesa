# Mpesa SDK for Python

[![PyPI](https://img.shields.io/pypi/v/yourdudeken-mpesa-sdk.svg)](https://pypi.org/project/yourdudeken-mpesa-sdk/)
[![License](https://img.shields.io/github/license/yourdudeken/mpesa.svg)](LICENSE.md)

A Python SDK for the Mpesa Daraja APIs. This SDK allows you to integrate Mpesa Daraja APIs into your Python applications with ease.

## Installation

```bash
pip install yourdudeken-mpesa-sdk
```

## Usage

```python
from yourdudeken_mpesa_sdk import Mpesa, MpesaConfig

config = MpesaConfig(
    environment='sandbox',
    mpesa_consumer_key='your_consumer_key',
    mpesa_consumer_secret='your_consumer_secret',
    passkey='your_passkey',
    shortcode='174379',
    initiator_name='testapi',
    initiator_password='your_password',
    callbacks={
        'callback_url': 'https://your-callback-url.com/callback',
    },
)

mpesa = Mpesa(config)

try:
    response = mpesa.stkpush(
        phonenumber='254712345678',
        amount=10,
        account_number='TEST001',
    )
    print(response)
except Exception as e:
    print(f"Error: {e}")
```

## Supported APIs

- **STK Push** - Lipa na Mpesa Express Online
- **STK Query** - Check transaction status
- **B2C** - Business to Customer
- **B2B** - Business to Business
- **B2Pochi** - Business to Pochi La Biashara
- **C2B** - Customer to Business (Register URL & Simulate)
- **Transaction Status** - Check transaction status
- **Account Balance** - Query account balance
- **Reversal** - Reverse a transaction

## Configuration

| Option | Type | Required | Description |
|--------|------|----------|-------------|
| environment | string | Yes | 'sandbox' or 'production' |
| mpesa_consumer_key | string | Yes | C2B Consumer Key |
| mpesa_consumer_secret | string | Yes | C2B Consumer Secret |
| b2c_consumer_key | string | No | B2C Consumer Key |
| b2c_consumer_secret | string | No | B2C Consumer Secret |
| passkey | string | Yes | Lipa na Mpesa Online Passkey |
| shortcode | string | Yes | Business Shortcode |
| till_number | string | No | Till Number |
| initiator_name | string | Yes | Mpesa Initiator Name |
| initiator_password | string | Yes | Mpesa Initiator Password |
| b2c_shortcode | string | No | B2C Shortcode |
| callbacks | dict | No | Callback URLs |

## License

MIT License - see [LICENSE.md](LICENSE.md) for details.