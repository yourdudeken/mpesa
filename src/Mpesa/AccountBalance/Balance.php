<?php

namespace Yourdudeken\Mpesa\AccountBalance;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

class Balance extends AbstractTransaction
{
    protected string $endpoint = 'mpesa/accountbalance/v1/query';
    
    protected array $validationRules = [
        'Initiator:Initiator'                   => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID'                   => 'required()({label} is required)',
        'PartyA:PartyA'                         => 'required()({label} is required)',
        'IdentifierType:IdentifierType'         => 'required()({label} is required)',
        'Remarks:Remarks'                       => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL'       => 'required()({label} is required) | website',
        'ResultURL:ResultURL'                   => 'required()({label} is required) | website'
    ];

    /**
     * Initiate the balance query process.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode          = $this->engine->getConfig()->get('mpesa.account_balance.short_code');
        $successCallback    = $this->engine->getConfig()->get('mpesa.account_balance.result_url');
        $timeoutCallback    = $this->engine->getConfig()->get('mpesa.account_balance.timeout_url');
        $initiator          = $this->engine->getConfig()->get('mpesa.account_balance.initiator_name');
        $commandId          = $this->engine->getConfig()->get('mpesa.account_balance.default_command_id');
        $initiatorPass      = $this->engine->getConfig()->get('mpesa.account_balance.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $identifierType     = $this->engine->getConfig()->get('mpesa.account_balance.identifier_type');
        $remarks            = $this->engine->getConfig()->get('mpesa.account_balance.remarks');

        $configParams = [
            'Initiator'          => $initiator,
            'SecurityCredential' => $securityCredential,
            'CommandID'          => $commandId,
            'PartyA'             => $shortCode,
            'IdentifierType'     => $identifierType,
            'QueueTimeOutURL'    => $timeoutCallback,
            'ResultURL'          => $successCallback,
            'Remarks'            => $remarks,
        ];

        $mappings = [
            'initiator'       => 'Initiator',
            'identifier_type' => 'IdentifierType',
        ];

        $body = $this->prepareBody($configParams, $params, $mappings);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
