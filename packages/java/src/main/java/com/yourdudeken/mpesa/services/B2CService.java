package com.yourdudeken.mpesa.services;

import com.yourdudeken.mpesa.config.MpesaConfig;
import com.yourdudeken.mpesa.http.HttpClient;
import com.yourdudeken.mpesa.auth.AuthService;
import java.util.HashMap;
import java.util.Map;

public class B2CService {
    private HttpClient httpClient;
    private AuthService auth;
    private MpesaConfig config;
    private String baseUrl;

    public B2CService(HttpClient httpClient, AuthService auth, MpesaConfig config) {
        this.httpClient = httpClient;
        this.auth = auth;
        this.config = config;
        this.baseUrl = httpClient.getBaseUrl();
    }

    public String send(String phonenumber, String commandId, int amount, String remarks,
                      String resultUrl, String timeoutUrl, String shortCodeType) {
        String url = baseUrl + "/mpesa/b2c/v1/paymentrequest";
        Map<String, Object> body = new HashMap<>();
        body.put("InitiatorName", config.getInitiatorName());
        body.put("SecurityCredential", generateSecurityCredential());
        body.put("CommandID", commandId);
        body.put("Amount", amount);
        body.put("PartyA", config.getB2cShortcode());
        body.put("PartyB", phoneValidator(phonenumber));
        body.put("Remarks", remarks);
        body.put("Occassion", "");
        body.put("ResultURL", resultUrl != null ? resultUrl : config.getB2cResultUrl());
        body.put("QueueTimeOutURL", timeoutUrl != null ? timeoutUrl : config.getB2cTimeoutUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, body, token);
    }

    public String validated(String phonenumber, String commandId, int amount, String remarks,
                          String idNumber, String resultUrl, String timeoutUrl, String shortCodeType) {
        String url = baseUrl + "/mpesa/b2cvalidate/v2/paymentrequest";
        Map<String, Object> body = new HashMap<>();
        body.put("InitiatorName", config.getInitiatorName());
        body.put("SecurityCredential", generateSecurityCredential());
        body.put("CommandID", commandId);
        body.put("Amount", amount);
        body.put("PartyA", config.getB2cShortcode());
        body.put("PartyB", phoneValidator(phonenumber));
        body.put("Remarks", remarks);
        body.put("Occassion", "");
        body.put("OriginatorConversationID", getFormattedTimestamp());
        body.put("IDType", "01");
        body.put("IDNumber", idNumber);
        body.put("ResultURL", resultUrl != null ? resultUrl : config.getB2cResultUrl());
        body.put("QueueTimeOutURL", timeoutUrl != null ? timeoutUrl : config.getB2cTimeoutUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, body, token);
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

    private String generateSecurityCredential() {
        return "";
    }
}
