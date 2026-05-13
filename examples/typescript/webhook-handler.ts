import { Mpesa, WebhookManager } from "mpesa-sdk";

const mpesa = new Mpesa({
  consumerKey: process.env.MPESA_CONSUMER_KEY!,
  consumerSecret: process.env.MPESA_CONSUMER_SECRET!,
  environment: "sandbox",
  passkey: process.env.MPESA_PASSKEY!,
});

const webhooks = new WebhookManager({ passkey: process.env.MPESA_PASSKEY });

webhooks.on("stk:callback", (event) => {
  const result = webhooks.parseSTKCallback(event.payload);
  if (result.success) {
    console.log(`[STK] Payment received: ${result.receiptNumber} KES ${result.amount}`);
  } else {
    console.log(`[STK] Failed: ${result.resultDescription} (code: ${result.resultCode})`);
  }
});

webhooks.on("b2c:result", (event) => {
  const result = webhooks.parseB2CCallback(event.payload);
  console.log(
    `[B2C] ${result.success ? "Success" : "Failed"}: ${result.transactionId}`,
    result.details,
  );
});

webhooks.on("b2b:result", (event) => {
  const result = webhooks.parseB2BCallback(event.payload);
  console.log(`[B2B] Result: ${result.transactionId}`, result.details);
});

webhooks.on("reversal:result", (event) => {
  const result = webhooks.parseReversalCallback(event.payload);
  console.log(`[Reversal] ${result.success ? "Reversed" : "Failed"}: ${result.transactionId}`);
});

webhooks.on("transaction:status", (event) => {
  const result = webhooks.parseTransactionStatusCallback(event.payload);
  console.log(`[Status] ${result.transactionStatus}: KES ${result.amount}`);
});

webhooks.on("account:balance", (event) => {
  const result = webhooks.parseAccountBalanceCallback(event.payload);
  if (result.balances?.utilityAccount) {
    const acct = result.balances.utilityAccount;
    console.log(`[Balance] Utility: KES ${acct.availableBalance}`);
  }
});

webhooks.on("c2b:validation", (event) => {
  console.log("[C2B] Validation request received:", event.payload);
  return webhooks.createC2BValidationResponse(true);
});

function detectEvent(body: Record<string, any>):
  | { type: "stk:callback"; payload: unknown }
  | { type: "account:balance"; payload: unknown }
  | { type: "transaction:status"; payload: unknown }
  | { type: "b2b:result"; payload: unknown }
  | { type: "reversal:result"; payload: unknown }
  | { type: "b2c:result"; payload: unknown }
  | { type: "c2b:validation"; payload: unknown }
  | null {
  if (body.Body?.stkCallback) {
    return { type: "stk:callback", payload: body };
  }

  if (body.Result?.ResultParameters?.ResultParameter) {
    const params: Array<{ Key: string }> = body.Result.ResultParameters.ResultParameter;
    const keys = new Set(params.map((p: any) => p.Key));

    if (keys.has("AccountBalance")) {
      return { type: "account:balance", payload: body };
    }
    if (keys.has("TransactionStatus")) {
      return { type: "transaction:status", payload: body };
    }
    if (keys.has("B2BRecipientPartyPublicName") || keys.has("B2BSenderPartyPublicName")) {
      return { type: "b2b:result", payload: body };
    }
    if (keys.has("OriginalTransactionID")) {
      return { type: "reversal:result", payload: body };
    }
    return { type: "b2c:result", payload: body };
  }

  if (body.TransactionType) {
    return { type: "c2b:validation", payload: body };
  }

  return null;
}

export async function handleWebhook(body: unknown) {
  const event = detectEvent(body as Record<string, any>);
  if (event) await webhooks.handleEvent(event);
}
