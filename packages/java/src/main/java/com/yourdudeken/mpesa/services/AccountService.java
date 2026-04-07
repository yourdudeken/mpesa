package com.yourdudeken.mpesa.services;

import com.yourdudeken.mpesa.config.MpesaConfig;
import com.yourdudeken.mpesa.http.HttpClient;
import com.yourdudeken.mpesa.auth.AuthService;
import java.util.HashMap;
import java.util.Map;

public class AccountService {
    private HttpClient httpClient;
    private AuthService auth;
    private MpesaConfig config;
    private String baseUrl;

    public AccountService(HttpClient httpClient, AuthService auth, MpesaConfig config) {
        this.httpClient = httpClient;
        this.auth = auth;
        this.config = config;
        this.baseUrl = httpClient.getBaseUrl();
    }

    public String balance(String shortcode, int identifierType, String remarks,
                         String resultUrl, String timeoutUrl, String shortCodeType) {
        String url = baseUrl + "/mpesa/accountbalance/v1/query";
        Map<String, Object> body = new HashMap<>();
        body.put("Initiator", config.getInitiatorName());
        body.put("SecurityCredential", generateSecurityCredential());
        body.put("CommandID", "AccountBalance");
        body.put("PartyA", shortcode);
        body.put("IdentifierType", identifierType);
        body.put("Remarks", remarks);
        body.put("ResultURL", resultUrl != null ? resultUrl : config.getBalanceResultUrl());
        body.put("QueueTimeOutURL", timeoutUrl != null ? timeoutUrl : config.getBalanceTimeoutUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, body, token);
    }

    public String status(String shortcode, String transactionId, int identifierType, String remarks,
                        String resultUrl, String timeoutUrl, String shortCodeType) {
        String url = baseUrl + "/mpesa/transactionstatus/v1/query";
        Map<String, Object> body = new HashMap<>();
        body.put("Initiator", config.getInitiatorName());
        body.put("SecurityCredential", generateSecurityCredential());
        body.put("CommandID", "TransactionStatusQuery");
        body.put("TransactionID", transactionId);
        body.put("PartyA", shortcode);
        body.put("IdentifierType", identifierType);
        body.put("Remarks", remarks);
        body.put("Occassion", "");
        body.put("ResultURL", resultUrl != null ? resultUrl : config.getStatusResultUrl());
        body.put("QueueTimeOutURL", timeoutUrl != null ? timeoutUrl : config.getStatusTimeoutUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, body, token);
    }

    public String reversal(String shortcode, String transactionId, double amount, String remarks,
                           String resultUrl, String timeoutUrl, String shortCodeType) {
        String url = baseUrl + "/mpesa/reversal/v1/request";
        Map<String, Object> body = new HashMap<>();
        body.put("Initiator", config.getInitiatorName());
        body.put("SecurityCredential", generateSecurityCredential());
        body.put("CommandID", "TransactionReversal");
        body.put("TransactionID", transactionId);
        body.put("Amount", amount);
        body.put("ReceiverParty", shortcode);
        body.put("RecieverIdentifierType", "11");
        body.put("Remarks", remarks);
        body.put("Occasion", "");
        body.put("ResultURL", resultUrl != null ? resultUrl : config.getReversalResultUrl());
        body.put("QueueTimeOutURL", timeoutUrl != null ? timeoutUrl : config.getReversalTimeoutUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, body, token);
    }

    private String generateSecurityCredential() {
        return "";
    }
}
