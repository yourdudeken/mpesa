# M-Pesa Package Example

This folder contains a fully functional example of how to use the `yourdudeken/mpesa` package in a real-world scenario with a frontend (HTML/JS) and a backend (PHP).

## Structure

- **index.html**: A beautiful, tabbed explorer for all M-Pesa APIs.
- **app.js**: Generic form handler for all API types.
- **api/init.php**: Central initialization for the M-Pesa SDK.
- **api/*.php**: Dedicated endpoints for STK Push, B2C, B2B, B2Pochi, C2B, Balance, Status, and Reversal.

## How to Run

1. **Configure Credentials**
   Open `api/init.php` and provide your Daraja Consumer Key and Secret. Common sandbox defaults for other fields (like Passkey and Initiator) are already pre-filled.

2. **Start PHP Server**
   Run the following from the project root:
   ```bash
   php -S localhost:8000 -t example
   ```

3. **Explore the APIs**
   Navigate to [http://localhost:8000](http://localhost:8000). Use the tabs to switch between:
   - **STK Push**: Customer-to-Business push notifications.
   - **B2C**: Business-to-Customer payouts.
   - **B2B**: Business-to-Business transfers.
   - **C2B Simulation**: simulate customer payments.
   - **Utilities**: Check Balance, Query status, and Reversal.

## Callback Testing

To test callbacks (since localhost isn't accessible publicly), use a tool like [Ngrok](https://ngrok.com/):

```bash
ngrok http 8000
```

Then update the `callback` URL in `api/init.php` to `https://<your-ngrok-id>.ngrok.io/api/callback.php`.
