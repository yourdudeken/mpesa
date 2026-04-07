package com.yourdudeken.mpesa.services;

import com.yourdudeken.mpesa.config.MpesaConfig;
import com.yourdudeken.mpesa.http.HttpClient;
import com.yourdudeken.mpesa.auth.AuthService;
import java.util.HashMap;
import java.util.Map;

public class C2BService {
    private HttpClient httpClient;
    private AuthService auth;
    private MpesaConfig config;
    private String baseUrl;

    public C2BService(HttpClient httpClient, AuthService auth, MpesaConfig config) {
        this.httpClient = httpClient;
        this.auth = auth;
        this.config = config;
        this.baseUrl = httpClient.getBaseUrl();
    }

    public String registerURLS(String shortcode, String confirmUrl, String validateUrl, String shortCodeType) {
        String url = baseUrl + "/mpesa/c2b/v2/registerurl";
        Map<String, Object> body = new HashMap<>();
        body.put("ShortCode", shortcode);
        body.put("ResponseType", "Completed");
        body.put("ConfirmationURL", confirmUrl != null ? confirmUrl : config.getC2bConfirmationUrl());
        body.put("ValidationURL", validateUrl != null ? validateUrl : config.getC2bValidationUrl());

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, body, token);
    }

    public String simulate(String phonenumber, int amount, String shortcode, String commandId,
                          String accountNumber, String shortCodeType) {
        String url = baseUrl + "/mpesa/c2b/v2/simulate";
        Map<String, Object> data = new HashMap<>();
        data.put("Msisdn", phoneValidator(phonenumber));
        data.put("Amount", amount);
        if (commandId.equals("CustomerPayBillOnline")) {
            data.put("BillRefNumber", accountNumber);
        }
        data.put("CommandID", commandId);
        data.put("ShortCode", shortcode);

        String token = auth.getAccessToken(shortCodeType);
        return httpClient.post(url, data, token);
    }

    private String phoneValidator(String phoneNo) {
        if (phoneNo.startsWith("+")) phoneNo = phoneNo.substring(1);
        if (phoneNo.startsWith("0")) phoneNo = "254" + phoneNo.substring(1);
        else if (phoneNo.startsWith("7")) phoneNo = "254" + phoneNo;
        return phoneNo;
    }
}
