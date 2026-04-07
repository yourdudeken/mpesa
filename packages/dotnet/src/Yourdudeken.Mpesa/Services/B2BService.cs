using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Yourdudeken.Mpesa.Config;
using Yourdudeken.Mpesa.Http;
using Yourdudeken.Mpesa.Auth;

namespace Yourdudeken.Mpesa.Services
{
    public class B2BService
    {
        private readonly HttpClientWrapper _httpClient;
        private readonly AuthService _auth;
        private readonly MpesaConfig _config;
        private readonly string _baseUrl;

        public B2BService(HttpClientWrapper httpClient, AuthService auth, MpesaConfig config)
        {
            _httpClient = httpClient;
            _auth = auth;
            _config = config;
            _baseUrl = httpClient.GetBaseUrl();
        }

        public async Task<string> Send(string receiverShortcode, string commandId, int amount, string remarks,
            string accountNumber = null, string resultUrl = null, string timeoutUrl = null, string shortCodeType = "B2B")
        {
            if (commandId == "BusinessPayBill" && string.IsNullOrEmpty(accountNumber))
                throw new ArgumentException("Account Number is required for BusinessPayBill CommandID");

            var url = $"{_baseUrl}/mpesa/b2b/v1/paymentrequest";
            var body = new Dictionary<string, object>
            {
                { "Initiator", _config.InitiatorName },
                { "SecurityCredential", GenerateSecurityCredential() },
                { "CommandID", commandId },
                { "SenderIdentifierType", "4" },
                { "RecieverIdentifierType", "4" },
                { "Amount", amount },
                { "PartyA", _config.B2cShortcode },
                { "PartyB", receiverShortcode },
                { "AccountReference", accountNumber },
                { "Remarks", remarks },
                { "ResultURL", resultUrl ?? _config.B2bResultUrl },
                { "QueueTimeOutURL", timeoutUrl ?? _config.B2bTimeoutUrl }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, body, token);
        }

        private string GenerateSecurityCredential() => "";
    }
}
