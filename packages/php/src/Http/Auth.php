<?php

namespace Yourdudeken\Mpesa\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class Auth
{
    private $baseUrl;
    private $cacheKey = 'mpesa_access_token';

    public function __construct()
    {
        $this->baseUrl = config('mpesa.environment') == 'sandbox'
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';
    }

    public function getAccessToken($shortCodeType = 'C2B'): string
    {
        $cached = Cache::get($this->cacheKey);
        if ($cached) {
            return $cached;
        }

        $token = $this->generateAccessToken($shortCodeType);
        Cache::put($this->cacheKey, $token, now()->addMinutes(50));

        return $token;
    }

    private function generateAccessToken($shortCodeType): string
    {
        if ($shortCodeType == 'B2C' || $shortCodeType == 'B2B') {
            $consumerKey = config('mpesa.b2c_consumer_key');
            $consumerSecret = config('mpesa.b2c_consumer_secret');
        } else {
            $consumerKey = config('mpesa.mpesa_consumer_key');
            $consumerSecret = config('mpesa.mpesa_consumer_secret');
        }

        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';

        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get($url);

        $result = json_decode($response);

        return data_get($result, 'access_token');
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
