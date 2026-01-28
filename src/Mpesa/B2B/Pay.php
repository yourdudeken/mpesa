<?php

namespace Yourdudeken\Mpesa\B2B;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

class Pay extends AbstractTransaction
{
    protected string $endpoint = 'mpesa/b2b/v1/paymentrequest';

    protected array $validationRules = [
        'Initiator:Initiator'                           => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential'         => 'required()({label} is required)',
        'CommandID:CommandID'                           => 'required()({label} is required)',
        'PartyA:PartyA'                                 => 'required()({label} is required)',
        'RecieverIdentifierType:RecieverIdentifierType' => 'required()({label} is required)',
        'PartyB:PartyB'                                 => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL'               => 'required()({label} is required) | website',
        'ResultURL:ResultURL'                           => 'required()({label} is required) | website',
        'SenderIdentifierType:SenderIdentifierType'     => 'required()({label} is required)',
        'Remarks:Remarks'                               => 'required()({label} is required)',
        'AccountReference:AccountReference'             => 'required()({label} is required)',
        'Amount:Amount'                                 => 'required()({label} is required)'
    ];

    /**
     * Initiate a B2B Payment request.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode              = $this->engine->getConfig()->get('mpesa.b2b.short_code');
        $successCallback        = $this->engine->getConfig()->get('mpesa.b2b.result_url');
        $timeoutCallback        = $this->engine->getConfig()->get('mpesa.b2b.timeout_url');
        $initiator              = $this->engine->getConfig()->get('mpesa.b2b.initiator_name');
        $initiatorPass          = $this->engine->getConfig()->get('mpesa.b2b.initiator_password');
        $securityCredential     = $this->engine->computeSecurityCredential($initiatorPass);
        $commandId              = $this->engine->getConfig()->get('mpesa.b2b.default_command_id');
        $remarks                = $this->engine->getConfig()->get('mpesa.b2b.remarks');
        $accountReference       = $this->engine->getConfig()->get('mpesa.b2b.account_reference');
        $senderIdentifierType   = $this->engine->getConfig()->get('mpesa.b2b.sender_identifier_type');
        $receiverIdentifierType = $this->engine->getConfig()->get('mpesa.b2b.reciever_identifier_type');

        $configParams = [
            'Initiator'              => $initiator,
            'SecurityCredential'     => $securityCredential,
            'CommandID'              => $commandId,
            'PartyA'                 => $shortCode,
            'RecieverIdentifierType' => $receiverIdentifierType,
            'SenderIdentifierType'   => $senderIdentifierType,
            'QueueTimeOutURL'        => $timeoutCallback,
            'ResultURL'              => $successCallback,
            'Remarks'                => $remarks,
            'AccountReference'       => $accountReference,
        ];

        // Specific mappings for B2B which uses 'Initiator' instead of 'InitiatorName'
        $mappings = [
            'initiator'                => 'Initiator',
            'receiver_identifier_type' => 'RecieverIdentifierType',
            'sender_identifier_type'   => 'SenderIdentifierType',
        ];

        $body = $this->prepareBody($configParams, $params, $mappings);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
