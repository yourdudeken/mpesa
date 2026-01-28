<?php

namespace Yourdudeken\Mpesa\B2B;

use Yourdudeken\Mpesa\Engine\Core;

class Pay {

    protected $endpoint = 'mpesa/b2b/v1/paymentrequest';

    protected $engine;

    protected $validationRules = [
        'Initiator:Initiator' => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID' => 'required()({label} is required)',
        'PartyA:PartyA' => 'required()({label} is required)',
        'RecieverIdentifierType:RecieverIdentifierType' => 'required()({label} is required)',
        'PartyB:PartyB' => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL' => 'required()({label} is required) | website',
        'ResultURL:ResultURL' => 'required()({label} is required) | website',
        'SenderIdentifierType:SenderIdentifierType' => 'required()({label} is required)',
        'Remarks:Remarks' => 'required()({label} is required)',
        'AccountReference:AccountReference' => 'required()({label} is required)',
        'Amount:Amount' => 'required()({label} is required)'
    ];

    /**
     * Pay constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine)
    {
        $this->engine       = $engine;
        $this->engine->setValidationRules($this->validationRules);
    }

    /**
     * Initiate the registration process.
     *
     * @param null $amount
     * @param null $number
     * @param null $description
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function submit($params = [],$appName = 'default'){
        $shortCode        = $this->engine->config->get('mpesa.b2b.short_code');
        $successCallback   = $this->engine->config->get('mpesa.b2b.result_url') ?: $this->engine->config->get('mpesa.callback');
        $timeoutCallback   = $this->engine->config->get('mpesa.b2b.timeout_url') ?: $this->engine->config->get('mpesa.callback');
        $initiator         = $this->engine->config->get('mpesa.b2b.initiator_name');
        $initiatorPass     = $this->engine->config->get('mpesa.b2b.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $commandId         = $this->engine->config->get('mpesa.b2b.default_command_id');
        $remarks           = $this->engine->config->get('mpesa.b2b.remarks');
        $accountReference  = $this->engine->config->get('mpesa.b2b.account_reference');
        $senderIdentifierType = $this->engine->config->get('mpesa.b2b.sender_identifier_type');
        $receiverIdentifierType = $this->engine->config->get('mpesa.b2b.reciever_identifier_type');

        $configParams = [
            'Initiator'                 => $initiator,
            'SecurityCredential'        => $securityCredential,
            'CommandID'                 => $commandId,
            'PartyA'                    => $shortCode,
            'RecieverIdentifierType'    => $receiverIdentifierType,
            'SenderIdentifierType'      => $senderIdentifierType,
            'QueueTimeOutURL'           => $timeoutCallback,
            'ResultURL'                 => $successCallback,
            'Remarks'                   => $remarks,
            'AccountReference'          => $accountReference,
        ];

        // Normalize user-provided params and merge with config defaults
        $userParams = $this->engine->normalizeParams($params, [
            'initiator' => 'Initiator',
            'receiver_identifier_type' => 'RecieverIdentifierType',
            'sender_identifier_type' => 'SenderIdentifierType',
        ]);
        $body = array_merge($configParams, $userParams);

        // Final normalization pass to ensure all merged fields are safe
        $body = $this->engine->normalizeParams($body, [
            'InitiatorName' => 'Initiator', // B2B uses 'Initiator' instead of 'InitiatorName'
        ]);
        
        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
