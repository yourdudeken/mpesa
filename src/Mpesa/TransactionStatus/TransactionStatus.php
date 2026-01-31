<?php

namespace Yourdudeken\Mpesa\TransactionStatus;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

class TransactionStatus extends AbstractTransaction
{
    protected string $endpoint = 'mpesa/transactionstatus/v1/query';
    
    protected array $validationRules = [
        'Initiator:Initiator'                   => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID'                   => 'required()({label} is required)',
        'IdentifierType:IdentifierType'         => 'required()({label} is required)',
        'Remarks:Remarks'                       => 'required()({label} is required)',
        'PartyA:Party A'                        => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL'       => 'required()({label} is required) | website',
        'ResultURL:ResultURL'                   => 'required()({label} is required) | website',
        'TransactionID:TransactionID'           => 'required()({label} is required)',
    ];

    /**
     * Initiate a Transaction Status query.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode          = $this->engine->getConfig()->get('mpesa.status.short_code');
        $successCallback    = $this->engine->getConfig()->get('mpesa.status.result_url');
        $timeoutCallback    = $this->engine->getConfig()->get('mpesa.status.timeout_url');
        $initiator          = $this->engine->getConfig()->get('mpesa.status.initiator_name');
        $commandId          = $this->engine->getConfig()->get('mpesa.status.command_id');
        $initiatorPass      = $this->engine->getConfig()->get('mpesa.status.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $remarks            = $this->engine->getConfig()->get('mpesa.status.remarks');
        $occasion           = $this->engine->getConfig()->get('mpesa.status.occasion');
        $identifierType     = $this->engine->getConfig()->get('mpesa.status.identifier_type');

        $configParams = [
            'Initiator'          => $initiator,
            'SecurityCredential' => $securityCredential,
            'CommandID'          => $commandId,
            'PartyA'             => $shortCode,
            'IdentifierType'     => $identifierType,
            'QueueTimeOutURL'    => $timeoutCallback,
            'ResultURL'          => $successCallback,
            'Remarks'            => $remarks,
            'Occasion'           => $occasion,
        ];

        $mappings = [
            'initiator'       => 'Initiator',
            'transaction_id'  => 'TransactionID',
            'identifier_type' => 'IdentifierType',
        ];

        $body = $this->prepareBody($configParams, $params, $mappings);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
