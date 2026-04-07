using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Yourdudeken.Mpesa.Config;
using Yourdudeken.Mpesa.Http;
using Yourdudeken.Mpesa.Auth;

namespace Yourdudeken.Mpesa.Services
{
    public class STKPushService
    {
        private readonly HttpClientWrapper _httpClient;
        private readonly AuthService _auth;
        private readonly MpesaConfig _config;
        private readonly string _baseUrl;

        public STKPushService(HttpClientWrapper httpClient, AuthService auth, MpesaConfig config)
        {
            _httpClient = httpClient;
            _auth = auth;
            _config = config;
            _baseUrl = httpClient.GetBaseUrl();
        }

        public async Task<string> Push(string phonenumber, int amount, string accountNumber,
            string callbackUrl = null, string transactionType = "CustomerPayBillOnline", string shortCodeType = "C2B")
        {
            if (string.IsNullOrEmpty(accountNumber))
                throw new ArgumentException("An Account Reference is required for All transactions.");

            var url = $"{_baseUrl}/mpesa/stkpush/v1/processrequest";
            var data = new Dictionary<string, object>
            {
                { "BusinessShortCode", _config.Shortcode },
                { "Password", LipaNaMpesaPassword() },
                { "Timestamp", GetFormattedTimestamp() },
                { "Amount", amount },
                { "PartyA", PhoneValidator(phonenumber) },
                { "PartyB", transactionType == "CustomerPayBillOnline" ? _config.Shortcode : _config.TillNumber },
                { "TransactionType", transactionType },
                { "PhoneNumber", PhoneValidator(phonenumber) },
                { "TransactionDesc", "Payment" },
                { "AccountReference", accountNumber },
                { "CallBackURL", callbackUrl ?? _config.CallbackUrl }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, data, token);
        }

        public async Task<string> Query(string checkoutRequestId, string shortCodeType = "C2B")
        {
            var url = $"{_baseUrl}/mpesa/stkpushquery/v1/query";
            var data = new Dictionary<string, object>
            {
                { "BusinessShortCode", _config.Shortcode },
                { "Password", LipaNaMpesaPassword() },
                { "Timestamp", GetFormattedTimestamp() },
                { "CheckoutRequestID", checkoutRequestId }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, data, token);
        }

        private string PhoneValidator(string phoneNo)
        {
            if (phoneNo.StartsWith("+")) phoneNo = phoneNo.Substring(1);
            if (phoneNo.StartsWith("0")) phoneNo = "254" + phoneNo.Substring(1);
            else if (phoneNo.StartsWith("7")) phoneNo = "254" + phoneNo;
            return phoneNo;
        }

        private string GetFormattedTimestamp() => DateTime.Now.ToString("yyyyMMddHHmmss");

        private string LipaNaMpesaPassword()
        {
            var timestamp = GetFormattedTimestamp();
            var password = _config.Shortcode + _config.Passkey + timestamp;
            return Convert.ToBase64String(System.Text.Encoding.UTF8.GetBytes(password));
        }
    }
}
