import { Mpesa } from "mpesa-sdk";

const mpesa = new Mpesa({
  consumerKey: process.env.MPESA_CONSUMER_KEY!,
  consumerSecret: process.env.MPESA_CONSUMER_SECRET!,
  environment: "production",
  passkey: process.env.MPESA_PASSKEY!,
  timeout: 30000,
  retryConfig: {
    maxRetries: 3,
    baseDelayMs: 2000,
    maxDelayMs: 60000,
  },
  logging: {
    onRequest: (req) => console.log("→", req.method, req.url),
    onError: (err) => console.error("✗", err),
  },
});

async function productionWorkflow() {
  // 1. Initiate STK Push
  const stkResponse = await mpesa.stkPush.initiate({
    BusinessShortCode: parseInt(process.env.MPESA_SHORTCODE!),
    TransactionType: "CustomerPayBillOnline",
    Amount: 5000,
    PartyA: 254722000000,
    PartyB: parseInt(process.env.MPESA_SHORTCODE!),
    PhoneNumber: 254722000000,
    CallBackURL: "https://api.yourdomain.com/mpesa/callback",
    AccountReference: "ORDER-12345",
    TransactionDesc: "Order payment",
    Password: "",
    Timestamp: "",
  });
  console.log("STK Push sent:", stkResponse.CheckoutRequestID);

  // 2. Query STK status after delay
  await new Promise((r) => setTimeout(r, 5000));
  const status = await mpesa.stkPush.query({
    BusinessShortCode: process.env.MPESA_SHORTCODE!,
    CheckoutRequestID: stkResponse.CheckoutRequestID,
    Password: "",
    Timestamp: "",
  });
  console.log("Payment status:", status.ResultDesc);

  // 3. Check account balance
  const balance = await mpesa.accountBalance.query({
    Initiator: process.env.MPESA_INITIATOR_NAME!,
    SecurityCredential: process.env.MPESA_SECURITY_CREDENTIAL!,
    CommandID: "AccountBalance",
    PartyA: parseInt(process.env.MPESA_SHORTCODE!),
    IdentifierType: 4,
    Remarks: "Reconcile balance",
    QueueTimeOutURL: "https://api.yourdomain.com/mpesa/queue",
    ResultURL: "https://api.yourdomain.com/mpesa/balance-result",
  });
  console.log("Balance query sent:", balance.OriginatorConversationID);
}

productionWorkflow();
