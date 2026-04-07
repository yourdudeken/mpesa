package com.yourdudeken.mpesa.services;

import com.yourdudeken.mpesa.config.MpesaConfig;
import com.yourdudeken.mpesa.http.HttpClient;
import com.yourdudeken.mpesa.auth.AuthService;
import java.util.HashMap;
import java.util.Map;

public class B2BService {
    private HttpClient httpClient;
    private AuthService auth;
    private MpesaConfig config;
    private String baseUrl;

    public B2BService(HttpClient httpClient, AuthService auth, MpesaConfig config) {
        this.httpClient = httpClient;
        this.auth = auth;
        this.config = config;
        this.baseUrl = httpClient.getBaseUrl();
    }

    public String send(String receiverShortcode, String commandId, int amount, String remarks,
                      String accountNumber, String resultUrl, String timeoutUrl, String shortCodeType) {
        if (commandId.equals("BusinessPayBill") && (accountNumber == null || accountNumber.isEmpty())) {
            throw new IllegalArgumentException("Account Number is required for BusinessPayBill CommandID");
        }

        String url = baseUrl + "/mpesa/b2b/v1/paymentrequest";
        Map<String, Object> body = new HashMap<>();
        body.put("Initiator", config.getInitiatorName());
        body.put("SecurityCredential", generateSecurityCredential());
        body.put("CommandID", commandId);
        body.put("SenderIdentifierType", "4");
        body.put("RecieverIdentifierType", "4");
        body.put("Amount", amount);
        body.put("PartyA", config.getB2cShortcode());
        body.put("PartyB", receiverShortcode);
        body.put("AccountReference", accountNumber);
        body.put("Remarks", remarks);
        body.put("ResultURL", resultUrl != null ? resultUrl : config.getB2bResultUrl());
        body.put("QueueTimeOutURL", timeoutUrl != null ? timeoutUrl : config.getB2bTimeoutUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, body, token);
    }

    private String generateSecurityCredential() {
        return "";
    }
}
