<?php

namespace Yourdudeken\Mpesa\B2Pochi;

use Yourdudeken\Mpesa\Engine\Core;

class Pay {

    protected $endpoint = 'mpesa/b2c/v1/paymentrequest';

    protected $engine;

    protected $validationRules = [
        'OriginatorConversationID:OriginatorConversationID' => 'required()({label} is required)',
        'InitiatorName:InitiatorName' => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID' => 'required()({label} is required)',
        'PartyA:PartyA' => 'required()({label} is required)',
        'PartyB:PartyB' => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL' => 'required()({label} is required)',
        'ResultURL:ResultURL' => 'required()({label} is required)',
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
        // Make sure all the indexes are in Uppercases as shown in docs
        $userParams = [];
        foreach ($params as $key => $value) {
            $userParams[ucwords($key)] = $value;
        }

        $shortCode = $this->engine->config->get('mpesa.b2pochi.short_code', 
            $this->engine->config->get('mpesa.b2c.short_code'));
        $successCallback  = $this->engine->config->get('mpesa.b2pochi.result_url',
            $this->engine->config->get('mpesa.b2c.result_url'));
        $timeoutCallback  = $this->engine->config->get('mpesa.b2pochi.timeout_url',
            $this->engine->config->get('mpesa.b2c.timeout_url'));
        $initiator  = $this->engine->config->get('mpesa.b2pochi.initiator_name',
            $this->engine->config->get('mpesa.b2c.initiator_name'));
        $pass = $this->engine->config->get('mpesa.b2pochi.security_credential',
            $this->engine->config->get('mpesa.b2c.security_credential'));
        $securityCredential  = $this->engine->computeSecurityCredential($pass);
        $commandId  = $this->engine->config->get('mpesa.b2pochi.default_command_id', 'BusinessPayToPochi');
        
        // Params coming from the config file
        $configParams = [
            'InitiatorName'     => $initiator,
            'SecurityCredential'=> $securityCredential,
            'CommandID'         => $commandId,
            'PartyA'            => $shortCode,
            'QueueTimeOutURL'   => $timeoutCallback,
            'ResultURL'         => $successCallback,
        ];

        // This gives precedence to params coming from user allowing them to override config params
        $body = array_merge($configParams, $userParams);

        // Send the request to mpesa
        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ], $appName);
    }
}
