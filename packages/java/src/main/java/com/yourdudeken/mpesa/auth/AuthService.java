package com.yourdudeken.mpesa.auth;

import com.yourdudeken.mpesa.config.MpesaConfig;
import java.util.HashMap;
import java.util.Map;

public class AuthService {
    private MpesaConfig config;
    private String baseUrl;
    private String accessToken;
    private long tokenExpiry;

    public AuthService(MpesaConfig config) {
        this.config = config;
        this.baseUrl = config.getEnvironment().equals("sandbox")
            ? "https://sandbox.safaricom.co.ke"
            : "https://api.safaricom.co.ke";
    }

    public String getAccessToken(String shortCodeType) {
        if (accessToken != null && System.currentTimeMillis() < tokenExpiry) {
            return accessToken;
        }
        return generateAccessToken(shortCodeType);
    }

    private String generateAccessToken(String shortCodeType) {
        String consumerKey = shortCodeType.equals("B2C") || shortCodeType.equals("B2B")
            ? config.getB2cConsumerKey()
            : config.getMpesaConsumerKey();
        String consumerSecret = shortCodeType.equals("B2C") || shortCodeType.equals("B2B")
            ? config.getB2cConsumerSecret()
            : config.getMpesaConsumerSecret();

        String auth = consumerKey + ":" + consumerSecret;
        String encodedAuth = java.util.Base64.getEncoder().encodeToString(auth.getBytes());

        try {
            java.net.URL url = new java.net.URL(baseUrl + "/oauth/v1/generate?grant_type=client_credentials");
            java.net.HttpURLConnection conn = (java.net.HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setRequestProperty("Authorization", "Basic " + encodedAuth);

            java.io.BufferedReader reader = new java.io.BufferedReader(
                new java.io.InputStreamReader(conn.getInputStream())
            );
            StringBuilder response = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                response.append(line);
            }
            reader.close();

            com.google.gson.JsonObject json = new com.google.gson.JsonParser().parse(response.toString()).getAsJsonObject();
            accessToken = json.get("access_token").getAsString();
            tokenExpiry = System.currentTimeMillis() + (json.get("expires_in").getAsLong() - 60) * 1000;

            return accessToken;
        } catch (Exception e) {
            throw new RuntimeException("Failed to generate access token: " + e.getMessage());
        }
    }

    public void clearToken() {
        accessToken = null;
        tokenExpiry = 0;
    }
}
