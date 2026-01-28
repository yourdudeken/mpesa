<?php

namespace Yourdudeken\Mpesa\B2Pochi;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

class Pay extends AbstractTransaction
{
    protected string $endpoint = 'mpesa/b2c/v1/paymentrequest';

    protected array $validationRules = [
        'InitiatorName:InitiatorName'           => 'required()({label} is required)',
        'SecurityCredential:SecurityCredential' => 'required()({label} is required)',
        'CommandID:CommandID'                   => 'required()({label} is required)',
        'PartyA:PartyA'                         => 'required()({label} is required)',
        'PartyB:PartyB'                         => 'required()({label} is required)',
        'QueueTimeOutURL:QueueTimeOutURL'       => 'website',
        'ResultURL:ResultURL'                   => 'website',
        'Remarks:Remarks'                       => 'required()({label} is required)',
        'Amount:Amount'                         => 'required()({label} is required)'
    ];

    /**
     * Initiate a B2Pochi payment request.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode          = $this->engine->getConfig()->get('mpesa.b2pochi.short_code');
        $successCallback    = $this->engine->getConfig()->get('mpesa.b2pochi.result_url');
        $timeoutCallback    = $this->engine->getConfig()->get('mpesa.b2pochi.timeout_url');
        $initiator          = $this->engine->getConfig()->get('mpesa.b2pochi.initiator_name');
        $initiatorPass      = $this->engine->getConfig()->get('mpesa.b2pochi.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $commandId          = $this->engine->getConfig()->get('mpesa.b2pochi.default_command_id');
        $remarks            = $this->engine->getConfig()->get('mpesa.b2pochi.remarks');
        
        $configParams = [
            'InitiatorName'      => $initiator,
            'SecurityCredential' => $securityCredential,
            'CommandID'          => $commandId,
            'PartyA'             => $shortCode,
            'QueueTimeOutURL'    => $timeoutCallback,
            'ResultURL'          => $successCallback,
            'Remarks'            => $remarks,
        ];

        $body = $this->prepareBody($configParams, $params);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
