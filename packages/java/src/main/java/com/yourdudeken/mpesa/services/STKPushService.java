package com.yourdudeken.mpesa.services;

import com.yourdudeken.mpesa.config.MpesaConfig;
import com.yourdudeken.mpesa.http.HttpClient;
import com.yourdudeken.mpesa.auth.AuthService;
import java.util.HashMap;
import java.util.Map;

public class STKPushService {
    private HttpClient httpClient;
    private AuthService auth;
    private MpesaConfig config;
    private String baseUrl;

    public STKPushService(HttpClient httpClient, AuthService auth, MpesaConfig config) {
        this.httpClient = httpClient;
        this.auth = auth;
        this.config = config;
        this.baseUrl = httpClient.getBaseUrl();
    }

    public String push(String phonenumber, int amount, String accountNumber, 
                      String callbackUrl, String transactionType, String shortCodeType) {
        if (accountNumber == null || accountNumber.isEmpty()) {
            throw new IllegalArgumentException("An Account Reference is required for All transactions.");
        }

        String url = baseUrl + "/mpesa/stkpush/v1/processrequest";
        Map<String, Object> data = new HashMap<>();
        data.put("BusinessShortCode", config.getShortcode());
        data.put("Password", lipaNaMpesaPassword());
        data.put("Timestamp", getFormattedTimestamp());
        data.put("Amount", amount);
        data.put("PartyA", phoneValidator(phonenumber));
        data.put("PartyB", transactionType.equals("CustomerPayBillOnline") 
            ? config.getShortcode() : config.getTillNumber());
        data.put("TransactionType", transactionType);
        data.put("PhoneNumber", phoneValidator(phonenumber));
        data.put("TransactionDesc", "Payment");
        data.put("AccountReference", accountNumber);
        data.put("CallBackURL", callbackUrl != null ? callbackUrl : config.getCallbackUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, data, token);
    }

    public String query(String checkoutRequestId, String shortCodeType) {
        String url = baseUrl + "/mpesa/stkpushquery/v1/query";
        Map<String, Object> data = new HashMap<>();
        data.put("BusinessShortCode", config.getShortcode());
        data.put("Password", lipaNaMpesaPassword());
        data.put("Timestamp", getFormattedTimestamp());
        data.put("CheckoutRequestID", checkoutRequestId);

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, data, token);
    }

    private String phoneValidator(String phoneNo) {
        if (phoneNo.startsWith("+")) phoneNo = phoneNo.substring(1);
        if (phoneNo.startsWith("0")) phoneNo = "254" + phoneNo.substring(1);
        else if (phoneNo.startsWith("7")) phoneNo = "254" + phoneNo;
        return phoneNo;
    }

    private String getFormattedTimestamp() {
        java.time.LocalDateTime now = java.time.LocalDateTime.now();
        return String.format("%04d%02d%02d%02d%02d%02d",
            now.getYear(), now.getMonthValue(), now.getDayOfMonth(),
            now.getHour(), now.getMinute(), now.getSecond());
    }

    private String lipaNaMpesaPassword() {
        String timestamp = getFormattedTimestamp();
        String password = config.getShortcode() + config.getPasskey() + timestamp;
        return java.util.Base64.getEncoder().encodeToString(password.getBytes());
    }
}
