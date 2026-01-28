<?php

namespace Yourdudeken\Mpesa\AccountBalance;

use Yourdudeken\Mpesa\Engine\Core;

class Balance {

    protected $endpoint = 'mpesa/accountbalance/v1/query';
    
    protected $engine;

    protected $validationRules = [
        'Initiator:Initiator' => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID' => 'required()({label} is required)',
        'PartyA:PartyA' => 'required()({label} is required)',
        'IdentifierType:IdentifierType' => 'required()({label} is required)',
        'Remarks:Remarks' => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL' => 'required()({label} is required) | website',
        'ResultURL:ResultURL' => 'required()({label} is required) | website'
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
     * Initiate the balance query process.
     *
     * @param null $description
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function submit($params = [],$appName='default'){
        $shortCode        = $this->engine->config->get('mpesa.account_balance.short_code');
        $successCallback   = $this->engine->config->get('mpesa.account_balance.result_url') ?: $this->engine->config->get('mpesa.callback');
        $timeoutCallback   = $this->engine->config->get('mpesa.account_balance.timeout_url') ?: $this->engine->config->get('mpesa.callback');
        $initiator         = $this->engine->config->get('mpesa.account_balance.initiator_name');
        $commandId         = $this->engine->config->get('mpesa.account_balance.default_command_id');
        $initiatorPass     = $this->engine->config->get('mpesa.account_balance.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $identifierType    = $this->engine->config->get('mpesa.account_balance.identifier_type');
        $remarks           = $this->engine->config->get('mpesa.account_balance.remarks');

        $configParams = [
            'Initiator'         => $initiator,
            'SecurityCredential'=> $securityCredential,
            'CommandID'         => $commandId,
            'PartyA'            => $shortCode,
            'IdentifierType'    => $identifierType,
            'QueueTimeOutURL'   => $timeoutCallback,
            'ResultURL'         => $successCallback,
            'Remarks'           => $remarks,
        ];

        // Normalize user-provided params and merge with config defaults
        $userParams = $this->engine->normalizeParams($params, [
            'initiator' => 'Initiator',
            'identifier_type' => 'IdentifierType',
        ]);
        $body = array_merge($configParams, $userParams);

        // Final normalization pass to ensure all merged fields are safe
        $body = $this->engine->normalizeParams($body, [
            'InitiatorName' => 'Initiator', // Balance uses 'Initiator'
        ]);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
