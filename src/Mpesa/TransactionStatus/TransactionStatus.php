<?php

namespace Yourdudeken\Mpesa\TransactionStatus;

use Yourdudeken\Mpesa\Engine\Core;

class TransactionStatus {

    protected $endpoint = 'mpesa/transactionstatus/v1/query';
    
    protected $engine;

    protected $validationRules = [
        'Initiator:Initiator' => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID' => 'required()({label} is required)',
        'IdentifierType:IdentifierType' => 'required()({label} is required)',
        'Remarks:Remarks' => 'required()({label} is required)',
        'PartyA:Party A' => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL' => 'required()({label} is required) | website',
        'ResultURL:ResultURL' => 'required()({label} is required) | website',
        'TransactionID:TransactionID' => 'required()({label} is required)',
    ];
    /**
     * TransactionStatus constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine){
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
        $shortCode        = $this->engine->config->get('mpesa.transaction_status.short_code');
        $successCallback   = $this->engine->config->get('mpesa.transaction_status.result_url') ?: $this->engine->config->get('mpesa.callback');
        $timeoutCallback   = $this->engine->config->get('mpesa.transaction_status.timeout_url') ?: $this->engine->config->get('mpesa.callback');
        $initiator  = $this->engine->config->get('mpesa.transaction_status.initiator_name');
        $commandId  = $this->engine->config->get('mpesa.transaction_status.default_command_id');
        $initiatorPass = $this->engine->config->get('mpesa.transaction_status.initiator_password');
        $securityCredential  = $this->engine->computeSecurityCredential($initiatorPass);
        $remarks           = $this->engine->config->get('mpesa.transaction_status.remarks');
        $occasion          = $this->engine->config->get('mpesa.transaction_status.occasion');
        $identifierType    = $this->engine->config->get('mpesa.transaction_status.identifier_type');

        $configParams = [
            'Initiator'         => $initiator,
            'SecurityCredential'=> $securityCredential,
            'CommandID'         => $commandId,
            'PartyA'            => $shortCode,
            'IdentifierType'    => $identifierType,
            'QueueTimeOutURL'   => $timeoutCallback,
            'ResultURL'         => $successCallback,
            'Remarks'           => $remarks,
            'Occasion'          => $occasion,
        ];

        // This gives precedence to params coming from user allowing them to override config params
        $body = array_merge($configParams,$userParams);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
