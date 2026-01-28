<?php

namespace Yourdudeken\Mpesa\LipaNaMpesaOnline;

use Yourdudeken\Mpesa\Engine\Core;

class STKStatusQuery{

    protected $endpoint = 'mpesa/stkpushquery/v1/query';

    protected $engine;

    protected $validationRules = [
        'BusinessShortCode:BusinessShortCode' => 'required()({label} is required) | number',
        'Password:Password' => 'required()({label} is required)',
        'Timestamp:Timestamp' => 'required()({label} is required)',
        'CheckoutRequestID:CheckoutRequestID' => 'required()({label} is required)'
    ];

    /**
     * STKStatusQuery constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine)
    {
        $this->engine  = $engine;
        $this->engine->setValidationRules($this->validationRules);
    }

    public function submit($params = [],$appName='default'){
        $time      = $this->engine->getCurrentRequestTime();
        $shortCode = $this->engine->config->get('mpesa.lnmo.short_code');
        $passkey   = $this->engine->config->get('mpesa.lnmo.passkey');
        $password  = \base64_encode($shortCode . $passkey . $time);

        // Computed and params from config file (pre-normalized for M-Pesa API)
        $configParams = [
            'BusinessShortCode' => $shortCode,
            'Password'          => $password,
            'Timestamp'         => $time,
        ];

        // Normalize user-provided params and merge with config defaults
        $userParams = $this->engine->normalizeParams($params, [
            'checkout_request_id' => 'CheckoutRequestID',
        ]);
        $body = array_merge($configParams, $userParams);

        // Final normalization pass
        $body = $this->engine->normalizeParams($body);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
