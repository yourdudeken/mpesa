using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Yourdudeken.Mpesa.Config;
using Yourdudeken.Mpesa.Http;
using Yourdudeken.Mpesa.Auth;

namespace Yourdudeken.Mpesa.Services
{
    public class B2CService
    {
        private readonly HttpClientWrapper _httpClient;
        private readonly AuthService _auth;
        private readonly MpesaConfig _config;
        private readonly string _baseUrl;

        public B2CService(HttpClientWrapper httpClient, AuthService auth, MpesaConfig config)
        {
            _httpClient = httpClient;
            _auth = auth;
            _config = config;
            _baseUrl = httpClient.GetBaseUrl();
        }

        public async Task<string> Send(string phonenumber, string commandId, int amount, string remarks,
            string resultUrl = null, string timeoutUrl = null, string shortCodeType = "B2C")
        {
            var url = $"{_baseUrl}/mpesa/b2c/v1/paymentrequest";
            var body = new Dictionary<string, object>
            {
                { "InitiatorName", _config.InitiatorName },
                { "SecurityCredential", GenerateSecurityCredential() },
                { "CommandID", commandId },
                { "Amount", amount },
                { "PartyA", _config.B2cShortcode },
                { "PartyB", PhoneValidator(phonenumber) },
                { "Remarks", remarks },
                { "Occassion", "" },
                { "ResultURL", resultUrl ?? _config.B2cResultUrl },
                { "QueueTimeOutURL", timeoutUrl ?? _config.B2cTimeoutUrl }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, body, token);
        }

        private string PhoneValidator(string phoneNo)
        {
            if (phoneNo.StartsWith("+")) phoneNo = phoneNo.Substring(1);
            if (phoneNo.StartsWith("0")) phoneNo = "254" + phoneNo.Substring(1);
            else if (phoneNo.StartsWith("7")) phoneNo = "254" + phoneNo;
            return phoneNo;
        }

        private string GenerateSecurityCredential() => "";
    }
}
