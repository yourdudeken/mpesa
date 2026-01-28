<?php

namespace Yourdudeken\Mpesa\B2C;

use Yourdudeken\Mpesa\Engine\Core;

class Pay {

    protected $endpoint = 'mpesa/b2c/v1/paymentrequest';

    protected $engine;

    protected $validationRules = [
        'InitiatorName:InitiatorName' => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID' => 'required()({label} is required)',
        'PartyA:PartyA' => 'required()({label} is required)',
        'PartyB:PartyB' => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL' => 'required()({label} is required) | website',
        'ResultURL:ResultURL' => 'required()({label} is required) | website',
        'Remarks:Remarks' => 'required()({label} is required)',
        'Amount:Amount' => 'required()({label} is required)'
    ];

    /**
     * STK constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine)
    {
        $this->engine = $engine;
        $this->engine->setValidationRules($this->validationRules);
    }

    /**
     * Throw a contextual exception.
     *
     * @param $reason
     *
     * @return ConfigurationException
     */
    private function generateException($reason){
        return new ConfigurationException($reason,422);
    }

    /**
     * Initiate the registration process.
     *
     * @param array [$amount,$partyB,$description]
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function submit($params = [],$appName = 'default'){
        $shortCode       = $this->engine->config->get('mpesa.b2c.short_code');
        $successCallback  = $this->engine->config->get('mpesa.b2c.result_url') ?: $this->engine->config->get('mpesa.callback');
        $timeoutCallback  = $this->engine->config->get('mpesa.b2c.timeout_url') ?: $this->engine->config->get('mpesa.callback');
        $initiator  = $this->engine->config->get('mpesa.b2c.initiator_name');
        $initiatorPass = $this->engine->config->get('mpesa.b2c.initiator_password');
        $securityCredential  = $this->engine->computeSecurityCredential($initiatorPass);
        $commandId  = $this->engine->config->get('mpesa.b2c.default_command_id');
        $remarks    = $this->engine->config->get('mpesa.b2c.remarks');
        $occasion   = $this->engine->config->get('mpesa.b2c.occasion');

        // Params coming from the config file (pre-normalized for M-Pesa API)
        $configParams = [
            'InitiatorName'     => $initiator,
            'SecurityCredential'=> $securityCredential,
            'CommandID'         => $commandId,
            'PartyA'            => $shortCode,
            'QueueTimeOutURL'   => $timeoutCallback,
            'ResultURL'         => $successCallback,
            'Remarks'           => $remarks,
            'Occasion'          => $occasion,
        ];

        // Normalize user-provided params and merge with config defaults
        $userParams = $this->engine->normalizeParams($params);
        $body = array_merge($configParams, $userParams);

        // Final normalization pass to ensure all merged fields are safe
        $body = $this->engine->normalizeParams($body);

        // Send the request to mpesa
        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
