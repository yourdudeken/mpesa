<?php

namespace Yourdudeken\Mpesa\B2Pochi;

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
        'QueueTimeOutURL:QueueTimeOutURL' => 'website',
        'ResultURL:ResultURL' => 'website',
        'Remarks:Remarks' => 'required()({label} is required)',
        'Amount:Amount' => 'required()({label} is required)'
    ];

    /**
     * Pay constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine)
    {
        $this->engine = $engine;
        $this->engine->setValidationRules($this->validationRules);
    }

    /**
     * Initiate the B2Pochi payment process.
     *
     * @param array $params
     * @param string $appName
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function submit($params = [], $appName = 'default'){
        $shortCode        = $this->engine->config->get('mpesa.b2pochi.short_code');
        $successCallback   = $this->engine->config->get('mpesa.b2pochi.result_url') ?: $this->engine->config->get('mpesa.callback');
        $timeoutCallback   = $this->engine->config->get('mpesa.b2pochi.timeout_url') ?: $this->engine->config->get('mpesa.callback');
        $initiator         = $this->engine->config->get('mpesa.b2pochi.initiator_name');
        $initiatorPass     = $this->engine->config->get('mpesa.b2pochi.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $commandId         = $this->engine->config->get('mpesa.b2pochi.default_command_id');
        $remarks           = $this->engine->config->get('mpesa.b2pochi.remarks');
        
        // Params coming from the config file (pre-normalized for M-Pesa API)
        $configParams = [
            'InitiatorName'     => $initiator,
            'SecurityCredential'=> $securityCredential,
            'CommandID'         => $commandId,
            'PartyA'            => $shortCode,
            'QueueTimeOutURL'   => $timeoutCallback,
            'ResultURL'         => $successCallback,
            'Remarks'           => $remarks,
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
        ], $appName);
    }
}
