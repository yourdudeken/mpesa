package com.yourdudeken.mpesa.http;

import java.util.Map;

public class HttpClient {
    private String baseUrl;

    public HttpClient(String baseUrl) {
        this.baseUrl = baseUrl;
    }

    public String post(String url, Map<String, Object> body, String token) {
        try {
            java.net.URL requestUrl = new java.net.URL(baseUrl + url);
            java.net.HttpURLConnection conn = (java.net.HttpURLConnection) requestUrl.openConnection();
            conn.setRequestMethod("POST");
            conn.setRequestProperty("Authorization", "Bearer " + token);
            conn.setRequestProperty("Content-Type", "application/json");
            conn.setDoOutput(true);

            java.io.OutputStream os = conn.getOutputStream();
            os.write(new com.google.gson.Gson().toJson(body).getBytes());
            os.flush();
            os.close();

            java.io.BufferedReader reader = new java.io.BufferedReader(
                new java.io.InputStreamReader(conn.getInputStream())
            );
            StringBuilder response = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                response.append(line);
            }
            reader.close();

            return response.toString();
        } catch (Exception e) {
            throw new RuntimeException("Mpesa request failed: " + e.getMessage());
        }
    }

    public String get(String url, String token) {
        try {
            java.net.URL requestUrl = new java.net.URL(baseUrl + url);
            java.net.HttpURLConnection conn = (java.net.HttpURLConnection) requestUrl.openConnection();
            conn.setRequestMethod("GET");
            conn.setRequestProperty("Authorization", "Bearer " + token);

            java.io.BufferedReader reader = new java.io.BufferedReader(
                new java.io.InputStreamReader(conn.getInputStream())
            );
            StringBuilder response = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                response.append(line);
            }
            reader.close();

            return response.toString();
        } catch (Exception e) {
            throw new RuntimeException("Mpesa request failed: " + e.getMessage());
        }
    }

    public String getBaseUrl() {
        return baseUrl;
    }
}
