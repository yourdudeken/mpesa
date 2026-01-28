<?php

namespace Yourdudeken\Mpesa\Reversal;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

class Reversal extends AbstractTransaction
{
    protected string $endpoint = 'mpesa/reversal/v1/request';

    protected array $validationRules = [
        'Initiator:Initiator'                           => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential'         => 'required()({label} is required)',
        'CommandID:CommandID'                           => 'required()({label} is required)',
        'TransactionID:TransactionID'                   => 'required()({label} is required)',
        'Amount:Amount'                                 => 'required()({label} is required)',
        'ReceiverParty:ReceiverParty'                   => 'required()({label} is required)',
        'RecieverIdentifierType:RecieverIdentifierType' => 'required()({label} is required)',
        'ResultURL:ResultURL'                           => 'required()({label} is required) | website',
        'QueueTimeOutURL:QueueTimeOutURL'               => 'required()({label} is required) | website',
        'Remarks:Remarks'                               => 'required()({label} is required)',
    ];

    /**
     * Initiate the reversal process.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode              = $this->engine->getConfig()->get('mpesa.reversal.short_code');
        $successCallback        = $this->engine->getConfig()->get('mpesa.reversal.result_url');
        $timeoutCallback        = $this->engine->getConfig()->get('mpesa.reversal.timeout_url');
        $initiator              = $this->engine->getConfig()->get('mpesa.reversal.initiator_name');
        $commandId              = $this->engine->getConfig()->get('mpesa.reversal.default_command_id', 'TransactionReversal');
        $initiatorPass          = $this->engine->getConfig()->get('mpesa.reversal.initiator_password');
        $securityCredential     = $this->engine->computeSecurityCredential($initiatorPass);
        $remarks                = $this->engine->getConfig()->get('mpesa.reversal.remarks');
        $occasion               = $this->engine->getConfig()->get('mpesa.reversal.occasion');
        $receiverIdentifierType = $this->engine->getConfig()->get('mpesa.reversal.reciever_identifier_type');

        $configParams = [
            'Initiator'              => $initiator,
            'SecurityCredential'     => $securityCredential,
            'CommandID'              => $commandId,
            'ReceiverParty'          => $shortCode,
            'RecieverIdentifierType' => $receiverIdentifierType,
            'QueueTimeOutURL'        => $timeoutCallback,
            'ResultURL'              => $successCallback,
            'Remarks'                => $remarks,
            'Occasion'               => $occasion,
        ];

        $mappings = [
            'initiator'                => 'Initiator',
            'transaction_id'           => 'TransactionID',
            'receiver_party'           => 'ReceiverParty',
            'receiver_identifier_type' => 'RecieverIdentifierType',
        ];

        $body = $this->prepareBody($configParams, $params, $mappings);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
