using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace Yourdudeken.Mpesa.Http
{
    public class HttpClientWrapper
    {
        private readonly string _baseUrl;

        public HttpClientWrapper(string baseUrl)
        {
            _baseUrl = baseUrl;
        }

        public async Task<string> Post(string url, Dictionary<string, object> body, string token)
        {
            using var client = new HttpClient();
            client.DefaultRequestHeaders.Add("Authorization", $"Bearer {token}");
            client.DefaultRequestHeaders.Add("Content-Type", "application/json");

            var json = System.Text.Json.JsonSerializer.Serialize(body);
            var content = new StringContent(json, Encoding.UTF8, "application/json");

            var response = await client.PostAsync(_baseUrl + url, content);
            return await response.Content.ReadAsStringAsync();
        }

        public async Task<string> Get(string url, string token)
        {
            using var client = new HttpClient();
            client.DefaultRequestHeaders.Add("Authorization", $"Bearer {token}");

            var response = await client.GetAsync(_baseUrl + url);
            return await response.Content.ReadAsStringAsync();
        }

        public string GetBaseUrl() => _baseUrl;
    }
}
