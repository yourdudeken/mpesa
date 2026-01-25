# M-Pesa API Testing Interface

A professional web interface for testing all M-Pesa DARAJA API endpoints. This testing dashboard provides an intuitive way to interact with the M-Pesa API without writing code.

## Features

- **Modern UI/UX**: Dark theme with smooth animations and responsive layout.
- **Complete Visual Testing**: Test all 10 M-Pesa API endpoints including B2Pochi and STK Status.
- **Callback Logs Dashboard**: Real-time view of incoming Safaricom notifications (STK, C2B, Result).
- **Intelligent Overrides**: Support for custom Response Types and Command IDs directly from the UI.
- **Detailed Error Handling**: Detailed API error reporting with specific error messages from Safaricom.
- **Environment Toggle**: Easy switching between Sandbox and Production modes.
- **Phone Formatting**: Automatic Kenya phone number formatting.

## Quick Start

### Prerequisites

- PHP 8.0 or higher
- Web server (local PHP server is sufficient)
- ngrok (optional, for receiving real callbacks locally)
- M-Pesa API credentials

### Installation

1. Navigate to the example directory:
   ```bash
   cd /path/to/mpesa/example
   ```

2. Configure M-Pesa credentials:
   
   Edit the configuration file at `config/mpesa.php`. For real callbacks, use your ngrok URL.

3. Start the development server:
   ```bash
   php -S localhost:8000
   ```

4. Open in browser:
   `http://localhost:8000`

## Structure

```
example/
├── api/
│   ├── payment.php     # Main API transaction handler
│   ├── callback.php    # Universal M-Pesa callback handler
│   └── logs.php        # JSON log viewer provider
├── static/
│   ├── css/
│   │   └── app.css     # Dashboard stylesheet
│   └── js/
│       └── app.js      # Interactive frontend logic
├── config/
│   └── mpesa.php       # Project-level configuration
├── app.html            # Main dashboard interface
└── README.md           # Example documentation
```

## Available Endpoints

### 1. STK Push (Lipa na M-Pesa Online)
Initiate payment requests directly to customer phones.

**Required Fields:**
- Amount (KES)
- Phone Number (254XXXXXXXXX)
- Account Reference

### 2. STK Status Query
Check the status of an STK Push request.

**Required Fields:**
- Checkout Request ID

### 3. C2B Register URLs
Register validation and confirmation URLs for C2B payments.

### 4. C2B Simulate Payment
Simulate C2B payments (Sandbox only).

### 5. B2C Payment (Business to Customer)
Send money from business account to customer M-Pesa.

### 6. B2B Transfer (Business to Business)
Transfer funds between business accounts.

### 7. B2Pochi Payment
Send money to customer Pochi savings accounts.

### 8. Account Balance Query
Check your business account balance.

### 9. Transaction Status Query
Check the status of any M-Pesa transaction.

### 10. Transaction Reversal
Reverse erroneous M-Pesa transactions.

## Support

Need help with integration?
- Email: kenmwendwamuthengi@gmail.com
- Documentation: Check the main README in the docs folder.
