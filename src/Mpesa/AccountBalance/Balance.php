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
         // Make sure all the indexes are in Uppercases as shown in docs
        $userParams = [];
        foreach ($params as $key => $value) {
             $userParams[ucwords($key)] = $value;
        }

        $shortCode        = $this->engine->config->get('mpesa.account_balance.short_code');
        $shortCode         = $this->engine->config->get('mpesa.account_balance.short_code');
        $successCallback   = $this->engine->config->get('mpesa.account_balance.result_url') ?: $this->engine->config->get('mpesa.callback');
        $timeoutCallback   = $this->engine->config->get('mpesa.account_balance.timeout_url') ?: $this->engine->config->get('mpesa.callback');
        $initiator         = $this->engine->config->get('mpesa.account_balance.initiator_name');
        $commandId         = $this->engine->config->get('mpesa.account_balance.default_command_id');
        $initiatorPass     = $this->engine->config->get('mpesa.account_balance.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $identifierType    = $this->engine->config->get('mpesa.account_balance.identifier_type');
        $remarks           = trim($this->engine->config->get('mpesa.account_balance.remarks') ?: 'None');

        $remarks = substr($remarks, 0, 100);

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

        // This gives precedence to params coming from user allowing them to override config params
        $body = array_merge($configParams,$userParams);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
