<?php

namespace Yourdudeken\Mpesa\Auth;

use Exception;
use Yourdudeken\Mpesa\Contracts\CacheStore;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;
use Yourdudeken\Mpesa\Contracts\HttpRequest;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;
use Yourdudeken\Mpesa\Exceptions\ErrorException;

/**
 * Class Authenticator.
 *
 * @category PHP
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
class Authenticator
{
    /**
     * Cache key suffix.
     */
    protected const AC_TOKEN = 'MP_AC_T';

    /**
     * @var string
     */
    protected string $endpoint = 'oauth/v1/generate?grant_type=client_credentials';

    /**
     * @var ConfigurationStore
     */
    protected ConfigurationStore $config;

    /**
     * @var CacheStore
     */
    protected CacheStore $cache;

    /**
     * @var HttpRequest
     */
    protected HttpRequest $httpClient;

    /**
     * Authenticator constructor.
     *
     * @param ConfigurationStore $config
     * @param CacheStore         $cache
     * @param HttpRequest        $httpClient
     */
    public function __construct(ConfigurationStore $config, CacheStore $cache, HttpRequest $httpClient)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->httpClient = $httpClient;
    }

    /**
     * Get the access token required to transact.
     *
     * @return string
     * @throws ConfigurationException
     * @throws ErrorException
     */
    public function authenticate(): string
    {
        $cacheKey = self::AC_TOKEN;

        if ($token = $this->cache->get($cacheKey)) {
            return $token;
        }

        try {
            $key = $this->config->get('auth.consumer_key');
            $secret = $this->config->get('auth.consumer_secret');

            if (empty($key) || empty($secret)) {
                throw new ConfigurationException('Consumer key or secret missing from configuration.');
            }

            $baseUrl = $this->config->get('mpesa.apiUrl', 'https://sandbox.safaricom.co.ke');
            $url = rtrim($baseUrl, '/') . '/' . $this->endpoint;

            $response = $this->httpClient->request('GET', $url, [
                'auth' => [$key, $secret]
            ]);

            $statusCode = ($response->getStatusCode)();
            $body = ($response->getBody)();

            if ($statusCode !== 200) {
                throw new Exception($body ?: 'Failed to retrieve access token');
            }

            $data = json_decode($body);

            if (!isset($data->access_token)) {
                throw new Exception('Invalid response from Safaricom: access_token missing');
            }

            $this->saveCredentials($data);

            return $data->access_token;

        } catch (Exception $exception) {
            throw $this->generateException($exception->getMessage());
        }
    }

    /**
     * Throw a contextual exception.
     *
     * @param string $reason
     * @return Exception
     */
    private function generateException(string $reason): Exception
    {
        if (str_contains(strtolower($reason), 'invalid credentials')) {
            return new ConfigurationException('Invalid consumer key and secret combination');
        }

        return new ErrorException($reason);
    }

    /**
     * Store the credentials in the cache.
     *
     * @param object $credentials
     */
    private function saveCredentials(object $credentials): void
    {
        $cacheKey = self::AC_TOKEN;
        // Expires in is in seconds. We cache for slightly less than that.
        $seconds = (int) $credentials->expires_in - 60;

        if ($seconds > 0) {
            $this->cache->put($cacheKey, $credentials->access_token, $seconds);
        }
    }
}
