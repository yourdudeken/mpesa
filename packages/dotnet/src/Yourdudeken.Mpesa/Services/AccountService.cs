using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Yourdudeken.Mpesa.Config;
using Yourdudeken.Mpesa.Http;
using Yourdudeken.Mpesa.Auth;

namespace Yourdudeken.Mpesa.Services
{
    public class AccountService
    {
        private readonly HttpClientWrapper _httpClient;
        private readonly AuthService _auth;
        private readonly MpesaConfig _config;
        private readonly string _baseUrl;

        public AccountService(HttpClientWrapper httpClient, AuthService auth, MpesaConfig config)
        {
            _httpClient = httpClient;
            _auth = auth;
            _config = config;
            _baseUrl = httpClient.GetBaseUrl();
        }

        public async Task<string> Balance(string shortcode, int identifierType, string remarks,
            string resultUrl = null, string timeoutUrl = null, string shortCodeType = "C2B")
        {
            var url = $"{_baseUrl}/mpesa/accountbalance/v1/query";
            var body = new Dictionary<string, object>
            {
                { "Initiator", _config.InitiatorName },
                { "SecurityCredential", GenerateSecurityCredential() },
                { "CommandID", "AccountBalance" },
                { "PartyA", shortcode },
                { "IdentifierType", identifierType },
                { "Remarks", remarks },
                { "ResultURL", resultUrl ?? _config.BalanceResultUrl },
                { "QueueTimeOutURL", timeoutUrl ?? _config.BalanceTimeoutUrl }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, body, token);
        }

        public async Task<string> Status(string shortcode, string transactionId, int identifierType, string remarks,
            string resultUrl = null, string timeoutUrl = null, string shortCodeType = "C2B")
        {
            var url = $"{_baseUrl}/mpesa/transactionstatus/v1/query";
            var body = new Dictionary<string, object>
            {
                { "Initiator", _config.InitiatorName },
                { "SecurityCredential", GenerateSecurityCredential() },
                { "CommandID", "TransactionStatusQuery" },
                { "TransactionID", transactionId },
                { "PartyA", shortcode },
                { "IdentifierType", identifierType },
                { "Remarks", remarks },
                { "Occassion", "" },
                { "ResultURL", resultUrl ?? _config.StatusResultUrl },
                { "QueueTimeOutURL", timeoutUrl ?? _config.StatusTimeoutUrl }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, body, token);
        }

        public async Task<string> Reversal(string shortcode, string transactionId, double amount, string remarks,
            string resultUrl = null, string timeoutUrl = null, string shortCodeType = "C2B")
        {
            var url = $"{_baseUrl}/mpesa/reversal/v1/request";
            var body = new Dictionary<string, object>
            {
                { "Initiator", _config.InitiatorName },
                { "SecurityCredential", GenerateSecurityCredential() },
                { "CommandID", "TransactionReversal" },
                { "TransactionID", transactionId },
                { "Amount", amount },
                { "ReceiverParty", shortcode },
                { "RecieverIdentifierType", "11" },
                { "Remarks", remarks },
                { "Occasion", "" },
                { "ResultURL", resultUrl ?? _config.ReversalResultUrl },
                { "QueueTimeOutURL", timeoutUrl ?? _config.ReversalTimeoutUrl }
            };

            var token = await _auth.GetAccessToken(shortCodeType);
            return await _httpClient.Post(url, body, token);
        }

        private string GenerateSecurityCredential() => "";
    }
}
