<?php

namespace Yourdudeken\Mpesa\B2C;

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
        'QueueTimeOutURL:QueueTimeOutURL'       => 'required()({label} is required) | website',
        'ResultURL:ResultURL'                   => 'required()({label} is required) | website',
        'Remarks:Remarks'                       => 'required()({label} is required)',
        'Amount:Amount'                         => 'required()({label} is required)'
    ];

    /**
     * Initiate a B2C Payment request.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode          = $this->engine->getConfig()->get('mpesa.b2c.short_code');
        $successCallback    = $this->engine->getConfig()->get('mpesa.b2c.result_url');
        $timeoutCallback    = $this->engine->getConfig()->get('mpesa.b2c.timeout_url');
        $initiator          = $this->engine->getConfig()->get('mpesa.b2c.initiator_name');
        $initiatorPass      = $this->engine->getConfig()->get('mpesa.b2c.initiator_password');
        $securityCredential = $this->engine->computeSecurityCredential($initiatorPass);
        $commandId          = $this->engine->getConfig()->get('mpesa.b2c.default_command_id');
        $remarks            = $this->engine->getConfig()->get('mpesa.b2c.remarks');
        $occasion           = $this->engine->getConfig()->get('mpesa.b2c.occasion');

        $configParams = [
            'InitiatorName'      => $initiator,
            'SecurityCredential' => $securityCredential,
            'CommandID'          => $commandId,
            'PartyA'             => $shortCode,
            'QueueTimeOutURL'    => $timeoutCallback,
            'ResultURL'          => $successCallback,
            'Remarks'            => $remarks,
            'Occasion'           => $occasion,
        ];

        $body = $this->prepareBody($configParams, $params);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
