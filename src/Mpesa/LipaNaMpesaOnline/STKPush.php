<?php

namespace Yourdudeken\Mpesa\LipaNaMpesaOnline;

use Yourdudeken\Mpesa\Engine\Core;

class STKPush{

    protected $endpoint = 'mpesa/stkpush/v1/processrequest';

    protected $engine;

    protected $validationRules = [
        'BusinessShortCode:BusinessShortCode' => 'required()({label} is required) | number',
        'Password:Password' => 'required()({label} is required)',
        'Timestamp:Timestamp' => 'required()({label} is required)',
        'TransactionType:TransactionType' => 'required()({label} is required)',
        'Amount:Amount' => 'required()({label} is required) | number()({label} should be a numeric value)',
        'PartyA:Party A' => 'required()({label} is required)',
        'PartyB:PartyB' => 'required()({label} is required)',
        'PhoneNumber:PhoneNumber' => 'required()({label} is required)',
        'CallBackURL:CallBackURL' => 'required()({label} is required) | website',
        'AccountReference:AccountReference' => 'required()({label} is required)',
        'TransactionDesc:TransactionDesc' => 'required()({label} is required)'
    ];

    /**
     * STK constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine)
    {
        $this->engine       = $engine;
        $this->engine->setValidationRules($this->validationRules);
    }
    

    /**
     * Initiate STK push request
     * 
     * @param Array $params
     * 
    */
    public function submit($params = [],$appName='default'){
        $time      = $this->engine->getCurrentRequestTime();
        $shortCode = $this->engine->config->get('mpesa.lnmo.short_code');
        $passkey   = $this->engine->config->get('mpesa.lnmo.passkey');
        $password  = \base64_encode($shortCode . $passkey . $time);
        $accountReference = $this->engine->config->get('mpesa.lnmo.account_reference');
        $callback = $this->engine->config->get('mpesa.lnmo.callback') ?: $this->engine->config->get('mpesa.callback');
        $transactionType = $this->engine->config->get('mpesa.lnmo.default_transaction_type');
        $transactionDesc = $this->engine->config->get('mpesa.lnmo.transaction_desc');

        // Computed and params from config file (pre-normalized for M-Pesa API)
        $configParams = [
            'BusinessShortCode' => $shortCode,
            'CallBackURL'       => $callback,
            'TransactionType'   => $transactionType,
            'Password'          => $password,
            'PartyB'            => $shortCode,
            'Timestamp'         => $time,
            'TransactionDesc'   => $transactionDesc,
            'AccountReference'  => $accountReference,
        ];

        // Normalize user-provided params and merge with config defaults
        $userParams = $this->engine->normalizeParams($params);
        $body = array_merge($configParams, $userParams);

        // Final normalization pass to ensure all merged fields are safe
        $body = $this->engine->normalizeParams($body);
        if(empty($body['PartyA']) && !empty($body['PhoneNumber'])){
            $body['PartyA'] = $body['PhoneNumber'];
        }
        
        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
