import os
from mpesa import Mpesa

client = Mpesa({
    "consumer_key": os.environ["MPESA_CONSUMER_KEY"],
    "consumer_secret": os.environ["MPESA_CONSUMER_SECRET"],
    "environment": os.environ.get("MPESA_ENV", "sandbox"),
    "passkey": os.environ.get("MPESA_PASSKEY"),
    "initiator_name": os.environ.get("MPESA_INITIATOR_NAME"),
    "security_credential": os.environ.get("MPESA_SECURITY_CREDENTIAL"),
})

SHORTCODE = int(os.environ.get("MPESA_SHORTCODE", "174379"))


def stk_push():
    response = client.stk_push({
        "BusinessShortCode": SHORTCODE,
        "TransactionType": "CustomerPayBillOnline",
        "Amount": 1,
        "PartyA": 254722000000,
        "PartyB": SHORTCODE,
        "PhoneNumber": 254722000000,
        "CallBackURL": "https://your-domain.com/api/mpesa/callback",
        "AccountReference": "INV-001",
        "TransactionDesc": "Payment for invoice 001",
    })
    print(f"STK Push: {response.CheckoutRequestID}")
    return response


def stk_query(checkout_request_id: str):
    response = client.stk_query({
        "BusinessShortCode": str(SHORTCODE),
        "CheckoutRequestID": checkout_request_id,
    })
    print(f"STK Query: {response.ResultDesc} (code: {response.ResultCode})")
    return response


def c2b_register_url():
    response = client.c2b_register_url({
        "ShortCode": str(SHORTCODE),
        "ResponseType": "Completed",
        "ConfirmationURL": "https://your-domain.com/api/c2b/confirmation",
        "ValidationURL": "https://your-domain.com/api/c2b/validation",
    })
    print(f"C2B Register: {response.ResponseDescription}")
    return response


def c2b_simulate():
    response = client.c2b_simulate({
        "ShortCode": SHORTCODE,
        "CommandID": "CustomerPayBillOnline",
        "Amount": 100,
        "Msisdn": 254708374149,
        "BillRefNumber": "ACCNO-001",
    })
    print(f"C2B Simulate: {response.ResponseDescription}")
    return response


def b2c_payment():
    response = client.b2c({
        "InitiatorName": os.environ["MPESA_INITIATOR_NAME"],
        "SecurityCredential": os.environ["MPESA_SECURITY_CREDENTIAL"],
        "CommandID": "BusinessPayment",
        "Amount": 100,
        "PartyA": SHORTCODE,
        "PartyB": 254705912645,
        "Remarks": "Salary disbursement",
        "QueueTimeOutURL": "https://your-domain.com/api/b2c/queue",
        "ResultURL": "https://your-domain.com/api/b2c/result",
        "Occassion": "Monthly Salary",
    })
    print(f"B2C: {response.OriginatorConversationID}")
    return response


def b2b_payment():
    response = client.b2b({
        "Initiator": os.environ["MPESA_INITIATOR_NAME"],
        "SecurityCredential": os.environ["MPESA_SECURITY_CREDENTIAL"],
        "CommandID": "BusinessPayBill",
        "Amount": 5000,
        "PartyA": 123456,
        "PartyB": 654321,
        "Remarks": "Supplier payment",
        "QueueTimeOutURL": "https://your-domain.com/api/b2b/queue",
        "ResultURL": "https://your-domain.com/api/b2b/result",
        "AccountReference": "SUPP-001",
    })
    print(f"B2B: {response.OriginatorConversationID}")
    return response


def reverse_transaction(transaction_id: str):
    response = client.reversal({
        "Initiator": os.environ["MPESA_INITIATOR_NAME"],
        "SecurityCredential": os.environ["MPESA_SECURITY_CREDENTIAL"],
        "CommandID": "TransactionReversal",
        "TransactionID": transaction_id,
        "Amount": 100,
        "ReceiverParty": SHORTCODE,
        "QueueTimeOutURL": "https://your-domain.com/api/reversal/queue",
        "ResultURL": "https://your-domain.com/api/reversal/result",
        "Remarks": "Customer initiated reversal",
    })
    print(f"Reversal: {response.ResponseDescription}")
    return response


def check_transaction_status(transaction_id: str):
    response = client.transaction_status({
        "Initiator": os.environ["MPESA_INITIATOR_NAME"],
        "SecurityCredential": os.environ["MPESA_SECURITY_CREDENTIAL"],
        "CommandID": "TransactionStatusQuery",
        "TransactionID": transaction_id,
        "PartyA": SHORTCODE,
        "IdentifierType": 4,
        "ResultURL": "https://your-domain.com/api/status/result",
        "QueueTimeOutURL": "https://your-domain.com/api/status/queue",
        "Remarks": "Status check",
    })
    print(f"Status: {response.ResponseDescription}")
    return response


def check_account_balance():
    response = client.account_balance({
        "Initiator": os.environ["MPESA_INITIATOR_NAME"],
        "SecurityCredential": os.environ["MPESA_SECURITY_CREDENTIAL"],
        "CommandID": "AccountBalance",
        "PartyA": SHORTCODE,
        "IdentifierType": 4,
        "Remarks": "Daily balance check",
        "QueueTimeOutURL": "https://your-domain.com/api/balance/queue",
        "ResultURL": "https://your-domain.com/api/balance/result",
    })
    print(f"Balance: {response.OriginatorConversationID}")
    return response


def generate_qr():
    response = client.dynamic_qr({
        "MerchantName": "Your Business Name",
        "RefNo": "INV-2024-001",
        "Amount": 1500,
        "TrxCode": "BG",
        "CPI": str(SHORTCODE),
        "Size": "300",
    })
    print(f"QR Generated: {response.ResponseDescription}")
    return response


if __name__ == "__main__":
    stk = stk_push()
    stk_query(stk.CheckoutRequestID)
    c2b_register_url()
    generate_qr()
