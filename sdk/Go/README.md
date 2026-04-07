# Mpesa SDK for Go

[![Go](https://img.shields.io/github/go-mod/go-version/yourdudeken/mpesa.svg)](https://pkg.go.dev/github.com/yourdudeken/mpesa-sdk)
[![License](https://img.shields.io/github/license/yourdudeken/mpesa.svg)](LICENSE.md)

A Go SDK for the Mpesa Daraja APIs. This SDK allows you to integrate Mpesa Daraja APIs into your Go applications with ease.

## Installation

```bash
go get github.com/yourdudeken/mpesa-sdk
```

## Usage

```go
package main

import (
    "fmt"
    mpesa "github.com/yourdudeken/mpesa-sdk/mpesa"
)

func main() {
    config := mpesa.Config{
        Environment:        "sandbox",
        MpesaConsumerKey:   "your_consumer_key",
        MpesaConsumerSecret: "your_consumer_secret",
        Passkey:            "your_passkey",
        Shortcode:          "174379",
        InitiatorName:      "testapi",
        InitiatorPassword:  "your_password",
        Callbacks: map[string]string{
            "callback_url": "https://your-callback-url.com/callback",
        },
    }

    m := mpesa.New(config)

    params := map[string]interface{}{
        "phonenumber":   "254712345678",
        "amount":        10,
        "accountNumber": "TEST001",
    }

    response, err := m.Stkpush(params)
    if err != nil {
        fmt.Println(err)
        return
    }

    fmt.Println(response)
}
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
| Environment | string | Yes | "sandbox" or "production" |
| MpesaConsumerKey | string | Yes | C2B Consumer Key |
| MpesaConsumerSecret | string | Yes | C2B Consumer Secret |
| B2cConsumerKey | string | No | B2C Consumer Key |
| B2cConsumerSecret | string | No | B2C Consumer Secret |
| Passkey | string | Yes | Lipa na Mpesa Online Passkey |
| Shortcode | string | Yes | Business Shortcode |
| TillNumber | string | No | Till Number |
| InitiatorName | string | Yes | Mpesa Initiator Name |
| InitiatorPassword | string | Yes | Mpesa Initiator Password |
| B2cShortcode | string | No | B2C Shortcode |
| Callbacks | map[string]string | No | Callback URLs |

## License

MIT License - see [LICENSE.md](LICENSE.md) for details.