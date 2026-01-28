<?php

namespace Yourdudeken\Mpesa\Engine;

use Exception;
use Yourdudeken\Mpesa\Auth\Authenticator;
use Yourdudeken\Mpesa\Contracts\CacheStore;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;
use Yourdudeken\Mpesa\Contracts\HttpRequest;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;
use Yourdudeken\Mpesa\Exceptions\MpesaException;
use Yourdudeken\Mpesa\Validation\Validator;

/**
 * Class Core.
 *
 * @category PHP
 */
class Core
{
    /**
     * @var ConfigurationStore
     */
    public ConfigurationStore $config;

    /**
     * @var CacheStore
     */
    public CacheStore $cache;

    /**
     * @var Core
     */
    protected static Core $instance;

    /**
     * @var Authenticator
     */
    public Authenticator $auth;

    /**
     * @var string
     */
    protected string $baseUrl;

    /**
     * @var Validator
     */
    protected Validator $validator;

    /**
     * @var array
     */
    protected array $validationRules = [];

    /**
     * @var HttpRequest
     */
    public HttpRequest $httpClient;

    /**
     * Core constructor.
     *
     * @param ConfigurationStore $config
     * @param CacheStore         $cache
     * @param HttpRequest        $httpClient
     * @param Authenticator      $auth
     */
    public function __construct(
        ConfigurationStore $config,
        CacheStore $cache,
        HttpRequest $httpClient,
        Authenticator $auth
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->auth = $auth;
        
        $this->setBaseUrl();
        $this->validator = new Validator();
        self::$instance = $this;
    }

    /**
     * Set the API base URL from configuration.
     */
    protected function setBaseUrl(): void
    {
        $apiRoot = $this->config->get('mpesa.apiUrl', 'https://sandbox.safaricom.co.ke/');
        $this->baseUrl = rtrim($apiRoot, '/') . '/';
    }

    /**
     * Set validation rules for the current request.
     *
     * @param array $rules
     */
    public function setValidationRules(array $rules): void
    {
        $this->validationRules = $rules;
        foreach ($this->validationRules as $key => $value) {
            $this->validator->add($key, $value);
        }
    }

    /**
     * Validate request body parameters.
     *
     * @param array $params
     * @return bool
     * @throws ConfigurationException
     */
    protected function validateRequestBodyParams(array $params): bool
    {
        if (!$this->validator->validate($params)) {
            $errors = $this->validator->getMessages();
            $messages = [];
            foreach ($errors as $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $messages[] = (string) $error;
                }
            }
            throw new ConfigurationException(json_encode($messages), 422);
        }
        return true;
    }

    /**
     * Get current request time in YmdHis format.
     *
     * @return string
     */
    public function getCurrentRequestTime(): string
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        return $date->format('YmdHis');
    }

    /**
     * Make a POST request.
     *
     * @param array  $options
     * @param string $appName
     * @return mixed
     * @throws Exception
     */
    public function makePostRequest(array $options = [], string $appName = 'default'): mixed
    {
        $body = array_filter($options['body'], fn($v) => $v !== null);

        return $this->request('POST', $options['endpoint'], [
            'headers' => [
                'Authorization: Bearer ' . $this->auth->authenticate($appName),
                'Content-Type: application/json',
            ],
            'body' => $body,
        ]);
    }

    /**
     * Make a GET request.
     *
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function makeGetRequest(array $options = []): mixed
    {
        return $this->request('GET', $options['endpoint'], [
            'headers' => [
                'Authorization: Basic ' . ($options['token'] ?? ''),
                'Content-Type: application/json',
            ],
        ]);
    }

    /**
     * Execute HTTP request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     * @return mixed
     * @throws Exception
     */
    protected function request(string $method, string $endpoint, array $params): mixed
    {
        if (!empty($this->validationRules) && isset($params['body'])) {
            $this->validateRequestBodyParams($params['body']);
        }

        $url = $this->baseUrl . ltrim($endpoint, '/');
        
        $this->httpClient->reset();
        $this->httpClient->setOption(CURLOPT_URL, $url);
        $this->httpClient->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->httpClient->setOption(CURLOPT_HTTPHEADER, $params['headers']);
        
        // Security: Enable SSL verification by default
        $this->httpClient->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $this->httpClient->setOption(CURLOPT_SSL_VERIFYHOST, 2);

        // Allow custom CA bundle if provided
        if ($caPath = $this->config->get('mpesa.ca_bundle')) {
            $this->httpClient->setOption(CURLOPT_CAINFO, $caPath);
        }

        if ($method === 'POST') {
            $this->httpClient->setOption(CURLOPT_POST, true);
            $this->httpClient->setOption(CURLOPT_POSTFIELDS, json_encode($params['body']));
        }

        $result = $this->httpClient->execute();
        $httpCode = (int) $this->httpClient->getInfo(CURLINFO_HTTP_CODE);

        if ($result === false) {
            throw new Exception($this->httpClient->error() ?: 'HTTP request failed');
        }

        if ($httpCode >= 400) {
            throw new MpesaException($result ?: "Request failed with status {$httpCode}", $httpCode);
        }

        return json_decode($result);
    }

    /**
     * Normalize user parameters and sanitize metadata for Safaricom.
     *
     * @param array $params
     * @param array $mappings
     * @return array
     */
    public function normalizeParams(array $params, array $mappings = []): array
    {
        $normalized = [];
        $commonMappings = [
            'amount'            => 'Amount',
            'phone'             => 'PhoneNumber',
            'phoneNumber'       => 'PhoneNumber',
            'callback_url'      => 'CallBackURL',
            'result_url'        => 'ResultURL',
            'timeout_url'       => 'QueueTimeOutURL',
            'remarks'           => 'Remarks',
            'description'       => 'TransactionDesc',
            'transaction_desc'  => 'TransactionDesc',
            'account_reference' => 'AccountReference',
            'reference'         => 'AccountReference',
            'occasion'          => 'Occasion',
            'short_code'        => 'ShortCode',
            'response_type'     => 'ResponseType',
            'confirmation_url'  => 'ConfirmationURL',
            'validation_url'    => 'ValidationURL',
            'initiator'         => 'InitiatorName',
            'initiator_name'    => 'InitiatorName',
            'command_id'        => 'CommandID',
            'party_a'           => 'PartyA',
            'party_b'           => 'PartyB',
        ];

        $allMappings = array_merge($commonMappings, $mappings);

        foreach ($params as $key => $value) {
            $normalizedKey = $allMappings[$key] ?? str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key)));
            $normalized[$normalizedKey] = $value;
        }

        // Apply Safaricom length limits
        if (isset($normalized['Remarks'])) {
            $normalized['Remarks'] = substr(trim($normalized['Remarks']), 0, 100) ?: 'None';
        }
        if (isset($normalized['TransactionDesc'])) {
            $normalized['TransactionDesc'] = substr(trim($normalized['TransactionDesc']), 0, 100);
        }
        if (isset($normalized['Occasion'])) {
            $normalized['Occasion'] = substr(trim($normalized['Occasion']), 0, 100) ?: null;
        }
        if (isset($normalized['AccountReference'])) {
            $normalized['AccountReference'] = substr(trim($normalized['AccountReference']), 0, 20);
        }

        return $normalized;
    }

    /**
     * Compute security credential using Safaricom's public certificate.
     *
     * @param string|null $initiatorPass
     * @return string
     * @throws ConfigurationException
     */
    public function computeSecurityCredential(?string $initiatorPass): string
    {
        if (empty($initiatorPass)) {
            throw new ConfigurationException('Initiator password is required for this transaction type. Please set [initiator.password] in your configuration.');
        }

        $isSandbox = $this->config->get('mpesa.is_sandbox', true);
        
        $pubKeyFile = $this->config->get('mpesa.certificate_path')
            ?? ($isSandbox 
                ? $this->config->get('mpesa.certificate_path_sandbox')
                : $this->config->get('mpesa.certificate_path_production'));

        if (empty($pubKeyFile)) {
            $pubKeyFile = $isSandbox 
                ? __DIR__ . '/../../config/SandboxCertificate.cer'
                : __DIR__ . '/../../config/ProductionCertificate.cer';
        }

        if (!is_file($pubKeyFile)) {
            throw new Exception("Certificate file not found: {$pubKeyFile}");
        }

        $pubKey = file_get_contents($pubKeyFile);
        
        if (!openssl_public_encrypt($initiatorPass, $encrypted, $pubKey, OPENSSL_PKCS1_PADDING)) {
            throw new Exception('Failed to encrypt initiator password: ' . openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    /**
     * Get configuration store.
     *
     * @return ConfigurationStore
     */
    public function getConfig(): ConfigurationStore
    {
        return $this->config;
    }
}
