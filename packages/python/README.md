# Mpesa Python SDK

[![PyPI Version](https://img.shields.io/pypi/v/yourdudeken-mpesa-sdk)](https://pypi.org/project/yourdudeken-mpesa-sdk/)
[![Python Versions](https://img.shields.io/pypi/pyversions/yourdudeken-mpesa-sdk)](https://pypi.org/project/yourdudeken-mpesa-sdk/)

A Python SDK for Mpesa Daraja API.

## Installation

```bash
pip install yourdudeken-mpesa-sdk
```

## Usage

```python
from mpesa import Mpesa, MpesaConfig

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

# STK Push
response = mpesa.stkpush(
    phonenumber='254712345678',
    amount=100,
    account_number='ORDER123'
)

# B2C
response = mpesa.b2c(
    phonenumber='254712345678',
    command_id='BusinessPayment',
    amount=1000,
    remarks='Salary payment'
)
```

## API Reference

- `stkpush()` - Lipa na Mpesa Online
- `stkquery()` - Query STK Push status
- `b2c()` - Business to Customer
- `validated_b2c()` - B2C with ID validation
- `b2b()` - Business to Business
- `c2bregisterURLS()` - Register C2B URLs
- `c2bsimulate()` - Simulate C2B
- `transactionStatus()` - Query transaction
- `accountBalance()` - Check balance
- `reversal()` - Reverse transaction

## License

MIT License
