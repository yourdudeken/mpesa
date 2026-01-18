# M-Pesa API Testing Interface

A professional web interface for testing all M-Pesa DARAJA API endpoints. This testing dashboard provides an intuitive way to interact with the M-Pesa API without writing code.

## Features

- **Modern UI/UX**: Dark theme with smooth animations and responsive design.
- **Complete Visual Testing**: Test all 10 M-Pesa API endpoints in one place.
- **Environment Toggle**: Easy switching between Sandbox and Production modes.
- **Quick Actions**: Copy API responses to clipboard.
- **Input Validation**: Form validation with helpful error messages.
- **Test Data Helper**: Auto-fill test data with keyboard shortcut (Ctrl/Cmd + Shift + T).
- **Phone Formatting**: Automatic Kenya phone number formatting.

## Quick Start

### Prerequisites

- PHP 7.0 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- M-Pesa API credentials

### Installation

1. Navigate to the example directory:
   ```bash
   cd /path/to/mpesa/example
   ```

2. Configure M-Pesa credentials:
   
   Edit the configuration file at `config/mpesa.php`.

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
│   └── handler.php     # Backend API handler
├── static/
│   ├── css/
│   │   └── main.css    # Stylesheet
│   └── js/
│       └── main.js     # Frontend logic
├── config/
│   └── mpesa.php       # Configuration
├── index.html          # Main interface
└── README.md           # Documentation
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
