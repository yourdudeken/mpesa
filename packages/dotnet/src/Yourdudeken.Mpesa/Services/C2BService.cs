using System.Collections.Generic;
using System.Threading.Tasks;
using Yourdudeken.Mpesa.Config;
using Yourdudeken.Mpesa.Http;
using Yourdudeken.Mpesa.Auth;

namespace Yourdudeken.Mpesa.Services
{
    public class C2BService
    {
        private readonly HttpClientWrapper _httpClient;
        private readonly AuthService _auth;
        private readonly string _baseUrl;

        public C2BService(HttpClientWrapper httpClient, AuthService auth, MpesaConfig config)
        {
            _httpClient = httpClient;
            _auth = auth;
            _baseUrl = httpClient.GetBaseUrl();
        }

        public async Task<string> RegisterURLS(string shortcode, string confirmUrl = null, string validateUrl = null, string shortCodeType = "C2B")
        {
            var url = $"{_baseUrl}/mpesa/c2b/v2/registerurl";
            var body = new Dictionary<string, object>
            {
                { "ShortCode", shortcode },
                { "ResponseType", "Completed" },
                { "ConfirmationURL", confirmUrl },
                { "ValidationURL", validateUrl }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, body, token);
        }

        public async Task<string> Simulate(string phonenumber, int amount, string shortcode, string commandId, string accountNumber = null, string shortCodeType = "C2B")
        {
            var url = $"{_baseUrl}/mpesa/c2b/v2/simulate";
            var data = new Dictionary<string, object>
            {
                { "Msisdn", phonenumber },
                { "Amount", amount },
                { "CommandID", commandId },
                { "ShortCode", shortcode }
            };

            if (!string.IsNullOrEmpty(accountNumber))
                data["BillRefNumber"] = accountNumber;

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, data, token);
        }
    }
}
