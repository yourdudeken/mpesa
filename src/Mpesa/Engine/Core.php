<?php

namespace Yourdudeken\Mpesa\Engine;

use Yourdudeken\Mpesa\Validation\Validator;    
use Yourdudeken\Mpesa\Auth\Authenticator;
use Yourdudeken\Mpesa\Contracts\CacheStore;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;
use Yourdudeken\Mpesa\Exceptions\MpesaException;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;
use Yourdudeken\Mpesa\Contracts\HttpRequest;

/**
 * Class Core.
 *
 * @category PHP
 *
 */
class Core
{
    /**
     * @var ConfigurationStore
     */
    public $config;

    /**
     * @var CacheStore
     */
    public $cache;

    /**
     * @var Core
     */
    public static $instance;

    /**
     * @var Authenticator
     */
    public $auth;

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var Validator
    */
    public $validator;

    /**
     * @var validation rules
     * 
    */
    public $validationRules;

    /**
     * @var HttpRequest curl
     */
    protected $curl;

    /**
     * Core constructor.
     *
     * @param ConfigurationStore $configStore
     * @param CacheStore         $cacheStore
     */
    public function __construct(
        ConfigurationStore $configStore, 
        CacheStore $cacheStore,
        HttpRequest $curl,
        Authenticator $auth
    ){
        $this->config = $configStore;
        $this->cache  = $cacheStore;
        $this->curl = $curl;
        $this->setBaseUrl();
        $this->validator = new Validator();
        self::$instance = $this;
        $this->auth = $auth;
        $this->auth->setEngine($this);
    }

    /**
     * Validate the current package state.
     */
    private function setBaseUrl(){
        $apiRoot = $this->config->get('mpesa.apiUrl', '');
        if (substr($apiRoot, strlen($apiRoot) - 1) !== '/') {
            $apiRoot = $apiRoot . '/';
        }
        $this->baseUrl  = $apiRoot;
    }

    public function setValidationRules($rules){
        $this->validationRules = $rules;
        foreach($this->validationRules as $key => $value){
            $this->validator->add($key,$value);
        }
    }

    private function validateRequestBodyParams($params){
        if ($this->validator->validate($params) == false) {
            $errors = $this->validator->getMessages();
            $finalErrors = [];
            foreach($errors as $err){
                foreach($err as $er){
                    $finalErrors[] = $er->__toString();
                }
            }
            $this->throwApiConfException(\json_encode($finalErrors));
        }
        return true;
    }

    /**
     * Throw an exception that describes a missing param.
     *
     * @param $reason
     *
     * @return ConfigurationException
     */
    public function throwApiConfException($reason){
        throw new ConfigurationException($reason,422);
    }

    /**
     * Get current request time
     * @return 
     */
    public function getCurrentRequestTime(){
        date_default_timezone_set('UTC');
        $date = new \DateTime();
        return $date->format('YmdHis');
    }

    /**
    * Make a post request
    *
    * @param Array $options
    *
    * @return mixed|\Psr\Http\Message\ResponseInterface
    **/
    public function makePostRequest($options = [],$appName = 'default'){
        // Filter out null values to prevent Safaricom schema rejection (400 Bad Request)
        $body = array_filter($options['body'], function($value) {
            return $value !== null;
        });

        $response = $this->request('POST', $options['endpoint'], [
            'headers' => [
                'Authorization: Bearer ' . $this->auth->authenticate($appName),
                'Content-Type: application/json',
            ],
            'body' => $body,
        ]);

        return $response;
    }

    private function request($method,$endpoint,$params){
        // Validate params
        if (!empty($this->validationRules) && isset($params['body'])) {
            $this->validateRequestBodyParams($params['body']);
        }
        $url = $this->baseUrl.$endpoint;
        $this->curl->reset();
        $this->curl->setOption(CURLOPT_URL, $url);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HEADER, false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->setOption(CURLOPT_HTTPHEADER, $params['headers']);

        if($method === 'POST'){
            $this->curl->setOption(CURLOPT_POST, true);
            $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($params['body']));
        }

        $result = $this->curl->execute();
        $httpCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);

        if( $result === false){ 
            $error = $this->curl->error() ?: 'cURL request failed';
            throw new \Exception($error);
        }
        if($httpCode != 200){
            $message = $result ?: 'HTTP request failed with code ' . $httpCode;
            throw new MpesaException($message,$httpCode);
        } 
        return json_decode($result); 
    }

    /**
    * Make a GET request
    *
    * @param Array $options
    *
    * @return mixed|\Psr\Http\Message\ResponseInterface
    **/
    public function makeGetRequest($options = []){
        return $this->request('GET', $options['endpoint'], [
            'headers' => [
                'Authorization: Basic ' . $options['token'],
                'Content-Type: application/json',
            ],
        ]);
    }

    /**
     * Normalize user parameters to match Safaricom's expected keys and sanitize metadata.
     *
     * @param array $params
     * @param array $mappings
     * @return array
     */
    public function normalizeParams($params, $mappings = []) {
        $normalized = [];

        // Define common mappings to Safaricom's PascalCase
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

        // Merge common mappings with provided specific mappings
        $allMappings = array_merge($commonMappings, $mappings);

        foreach ($params as $key => $value) {
            $normalizedKey = $allMappings[$key] ?? str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key)));
            $normalized[$normalizedKey] = $value;
        }

        // Sanitize metadata fields if they exist (Safaricom limits)
        if (isset($normalized['Remarks'])) {
            $normalized['Remarks'] = substr(trim($normalized['Remarks']), 0, 100);
            if (empty($normalized['Remarks'])) {
                $normalized['Remarks'] = 'None';
            }
        }
        if (isset($normalized['TransactionDesc'])) {
            $normalized['TransactionDesc'] = substr(trim($normalized['TransactionDesc']), 0, 100);
        }
        if (isset($normalized['Occasion'])) {
            $normalized['Occasion'] = substr(trim($normalized['Occasion']), 0, 100);
            if (empty($normalized['Occasion'])) {
                $normalized['Occasion'] = null; // Will be pruned by makePostRequest
            }
        }
        if (isset($normalized['AccountReference'])) {
            $normalized['AccountReference'] = substr(trim($normalized['AccountReference']), 0, 20);
        }

        return $normalized;
    }

    /**
     * Compute security credential
     * 
     */
    public function computeSecurityCredential($initiatorPass){
        // Get certificate path from config based on environment
        $isSandbox = $this->config->get('mpesa.is_sandbox', true);
        
        // Try to get custom certificate path first
        $pubKeyFile = $this->config->get('mpesa.certificate_path');
        
        // If no custom path, use environment-specific path from config
        if (empty($pubKeyFile)) {
            if ($isSandbox) {
                $pubKeyFile = $this->config->get('mpesa.certificate_path_sandbox');
            } else {
                $pubKeyFile = $this->config->get('mpesa.certificate_path_production');
            }
        }
        
        // Final fallback to internal certificates (should always work due to internal config)
        if (empty($pubKeyFile)) {
            $pubKeyFile = $isSandbox 
                ? __DIR__ . '/../../config/SandboxCertificate.cer'
                : __DIR__ . '/../../config/ProductionCertificate.cer';
        }
        
        $pubKey = '';
        if(\is_file($pubKeyFile)){
            $pubKey = file_get_contents($pubKeyFile);
        }else{
            throw new \Exception("Certificate file not found at: " . $pubKeyFile);
        }
        openssl_public_encrypt($initiatorPass, $encrypted, $pubKey, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }
}
