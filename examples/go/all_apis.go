package main

import (
	"context"
	"fmt"
	"log"
	"os"
	"time"

	"github.com/yourdudeken/mpesa-sdk/client"
	"github.com/yourdudeken/mpesa-sdk/types"
)

var mpesa *client.Client

func init() {
	mpesa = client.NewClient(types.MpesaConfig{
		ConsumerKey:        os.Getenv("MPESA_CONSUMER_KEY"),
		ConsumerSecret:     os.Getenv("MPESA_CONSUMER_SECRET"),
		Environment:        types.Sandbox,
		Passkey:            os.Getenv("MPESA_PASSKEY"),
		InitiatorName:      os.Getenv("MPESA_INITIATOR_NAME"),
		SecurityCredential: os.Getenv("MPESA_SECURITY_CREDENTIAL"),
	})
}

func stkPush() {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.STKPush(ctx, types.STKPushRequest{
		BusinessShortCode: 174379,
		TransactionType:   types.CustomerPayBillOnline,
		Amount:            1,
		PartyA:            254722000000,
		PartyB:            174379,
		PhoneNumber:       254722000000,
		CallBackURL:       "https://your-domain.com/api/mpesa/callback",
		AccountReference:  "INV-001",
		TransactionDesc:   "Payment for invoice 001",
	})
	if err != nil {
		log.Fatalf("STK Push failed: %v", err)
	}
	fmt.Printf("STK Push: %s\n", resp.CheckoutRequestID)
}

func stkQuery(checkoutID string) {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.STKQuery(ctx, types.STKQueryRequest{
		BusinessShortCode: "174379",
		CheckoutRequestID: checkoutID,
	})
	if err != nil {
		log.Fatalf("STK Query failed: %v", err)
	}
	fmt.Printf("STK Query: %s (code: %s)\n", resp.ResultDesc, resp.ResultCode)
}

func c2bRegisterURL() {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.C2BRegisterURL(ctx, types.C2BRegisterURLRequest{
		ShortCode:       "174379",
		ResponseType:    types.ResponseCompleted,
		ConfirmationURL: "https://your-domain.com/api/c2b/confirmation",
		ValidationURL:   "https://your-domain.com/api/c2b/validation",
	})
	if err != nil {
		log.Fatalf("C2B Register URL failed: %v", err)
	}
	fmt.Printf("C2B Register: %s\n", resp.ResponseDescription)
}

func c2bSimulate() {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.C2BSimulate(ctx, types.C2BSimulateRequest{
		ShortCode:     174379,
		CommandID:     types.C2BPayBill,
		Amount:        100,
		Msisdn:        254708374149,
		BillRefNumber: "ACCNO-001",
	})
	if err != nil {
		log.Fatalf("C2B Simulate failed: %v", err)
	}
	fmt.Printf("C2B Simulate: %s\n", resp.ResponseDescription)
}

func b2cPayment() {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.B2C(ctx, types.B2CRequest{
		InitiatorName:      os.Getenv("MPESA_INITIATOR_NAME"),
		SecurityCredential: os.Getenv("MPESA_SECURITY_CREDENTIAL"),
		CommandID:          types.BusinessPayment,
		Amount:             100,
		PartyA:             174379,
		PartyB:             254705912645,
		Remarks:            "Salary disbursement",
		QueueTimeOutURL:    "https://your-domain.com/api/b2c/queue",
		ResultURL:          "https://your-domain.com/api/b2c/result",
		Occassion:          "Monthly Salary",
	})
	if err != nil {
		log.Fatalf("B2C failed: %v", err)
	}
	fmt.Printf("B2C: %s\n", resp.OriginatorConversationID)
}

func b2bPayment() {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.B2B(ctx, types.B2BRequest{
		Initiator:              os.Getenv("MPESA_INITIATOR_NAME"),
		SecurityCredential:     os.Getenv("MPESA_SECURITY_CREDENTIAL"),
		CommandID:              types.BusinessPayBill,
		SenderIdentifierType:   4,
		RecieverIdentifierType: 4,
		Amount:                 5000,
		PartyA:                 123456,
		PartyB:                 654321,
		Remarks:                "Supplier payment",
		QueueTimeOutURL:        "https://your-domain.com/api/b2b/queue",
		ResultURL:              "https://your-domain.com/api/b2b/result",
		AccountReference:       "SUPP-001",
	})
	if err != nil {
		log.Fatalf("B2B failed: %v", err)
	}
	fmt.Printf("B2B: %s\n", resp.OriginatorConversationID)
}

func reversal(txnID string) {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.Reversal(ctx, types.ReversalRequest{
		Initiator:              os.Getenv("MPESA_INITIATOR_NAME"),
		SecurityCredential:     os.Getenv("MPESA_SECURITY_CREDENTIAL"),
		CommandID:              "TransactionReversal",
		TransactionID:          txnID,
		Amount:                 100,
		ReceiverParty:          174379,
		RecieverIdentifierType: 11,
		QueueTimeOutURL:        "https://your-domain.com/api/reversal/queue",
		ResultURL:              "https://your-domain.com/api/reversal/result",
		Remarks:                "Customer initiated reversal",
	})
	if err != nil {
		log.Fatalf("Reversal failed: %v", err)
	}
	fmt.Printf("Reversal: %s\n", resp.ResponseDescription)
}

func transactionStatus(txnID string) {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.TransactionStatus(ctx, types.TransactionStatusRequest{
		Initiator:          os.Getenv("MPESA_INITIATOR_NAME"),
		SecurityCredential: os.Getenv("MPESA_SECURITY_CREDENTIAL"),
		CommandID:          "TransactionStatusQuery",
		TransactionID:      txnID,
		PartyA:             174379,
		IdentifierType:     4,
		ResultURL:          "https://your-domain.com/api/status/result",
		QueueTimeOutURL:    "https://your-domain.com/api/status/queue",
		Remarks:            "Status check",
	})
	if err != nil {
		log.Fatalf("Transaction Status failed: %v", err)
	}
	fmt.Printf("Status: %s\n", resp.ResponseDescription)
}

func accountBalance() {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.AccountBalance(ctx, types.AccountBalanceRequest{
		Initiator:          os.Getenv("MPESA_INITIATOR_NAME"),
		SecurityCredential: os.Getenv("MPESA_SECURITY_CREDENTIAL"),
		CommandID:          "AccountBalance",
		PartyA:             174379,
		IdentifierType:     4,
		Remarks:            "Daily balance check",
		QueueTimeOutURL:    "https://your-domain.com/api/balance/queue",
		ResultURL:          "https://your-domain.com/api/balance/result",
	})
	if err != nil {
		log.Fatalf("Account Balance failed: %v", err)
	}
	fmt.Printf("Balance: %s\n", resp.OriginatorConversationID)
}

func dynamicQR() {
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()
	resp, err := mpesa.DynamicQR(ctx, types.DynamicQRRequest{
		MerchantName: "Your Business Name",
		RefNo:        "INV-2024-001",
		Amount:       1500,
		TrxCode:      types.TrxBuyGoods,
		CPI:          "174379",
		Size:         "300",
	})
	if err != nil {
		log.Fatalf("Dynamic QR failed: %v", err)
	}
	fmt.Printf("QR Generated: %s (len: %d)\n", resp.ResponseDescription, len(resp.QRCode))
}

func main() {
	fmt.Println("=== M-Pesa All APIs Demo ===\n")

	stkPush()
	stkQuery("ws_CO_0000000000")
	c2bRegisterURL()
	c2bSimulate()
	b2cPayment()
	b2bPayment()
	reversal("NLA12345XX")
	transactionStatus("NLA12345XX")
	accountBalance()
	dynamicQR()
}
