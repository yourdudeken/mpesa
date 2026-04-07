using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace Yourdudeken.Mpesa.Auth
{
    public class AuthService
    {
        private readonly Config.MpesaConfig _config;
        private string _baseUrl;
        private string _accessToken;
        private long _tokenExpiry;

        public AuthService(Config.MpesaConfig config)
        {
            _config = config;
            _baseUrl = config.Environment == "sandbox"
                ? "https://sandbox.safaricom.co.ke"
                : "https://api.safaricom.co.ke";
        }

        public async Task<string> GetAccessToken(string shortCodeType = "C2B")
        {
            if (!string.IsNullOrEmpty(_accessToken) && DateTimeOffset.UtcNow.ToUnixTimeMilliseconds() < _tokenExpiry)
            {
                return _accessToken;
            }

            return await GenerateAccessToken(shortCodeType);
        }

        private async Task<string> GenerateAccessToken(string shortCodeType)
        {
            var consumerKey = shortCodeType == "B2C" || shortCodeType == "B2B"
                ? _config.B2cConsumerKey
                : _config.MpesaConsumerKey;
            var consumerSecret = shortCodeType == "B2C" || shortCodeType == "B2B"
                ? _config.B2cConsumerSecret
                : _config.MpesaConsumerSecret;

            var auth = Convert.ToBase64String(Encoding.UTF8.GetBytes($"{consumerKey}:{consumerSecret}"));

            using var client = new HttpClient();
            client.DefaultRequestHeaders.Add("Authorization", $"Basic {auth}");

            var response = await client.GetAsync($"{_baseUrl}/oauth/v1/generate?grant_type=client_credentials");
            var content = await response.Content.ReadAsStringAsync();

            var json = System.Text.Json.JsonDocument.Parse(content);
            _accessToken = json.RootElement.GetProperty("access_token").GetString();
            var expiresIn = json.RootElement.GetProperty("expires_in").GetInt64();
            _tokenExpiry = DateTimeOffset.UtcNow.ToUnixTimeMilliseconds() + (expiresIn - 60) * 1000;

            return _accessToken;
        }

        public void ClearToken()
        {
            _accessToken = null;
            _tokenExpiry = 0;
        }
    }
}
