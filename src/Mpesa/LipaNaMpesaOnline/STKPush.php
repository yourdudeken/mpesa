<?php

namespace Yourdudeken\Mpesa\LipaNaMpesaOnline;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

class STKPush extends AbstractTransaction
{
    protected string $endpoint = 'mpesa/stkpush/v1/processrequest';

    protected array $validationRules = [
        'BusinessShortCode:BusinessShortCode' => 'required()({label} is required) | number',
        'Password:Password'                   => 'required()({label} is required)',
        'Timestamp:Timestamp'                 => 'required()({label} is required)',
        'TransactionType:TransactionType'     => 'required()({label} is required)',
        'Amount:Amount'                       => 'required()({label} is required) | number()({label} should be a numeric value)',
        'PartyA:Party A'                      => 'required()({label} is required)',
        'PartyB:PartyB'                       => 'required()({label} is required)',
        'PhoneNumber:PhoneNumber'             => 'required()({label} is required)',
        'CallBackURL:CallBackURL'             => 'required()({label} is required) | website',
        'AccountReference:AccountReference'   => 'required()({label} is required)',
        'TransactionDesc:TransactionDesc'     => 'required()({label} is required)'
    ];

    /**
     * Initiate STK push request.
     * 
     * @param array  $params
     * @param string $appName
     * @return mixed
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $time      = $this->engine->getCurrentRequestTime();
        $shortCode = $this->engine->getConfig()->get('mpesa.stk.short_code');
        $passkey   = $this->engine->getConfig()->get('mpesa.stk.passkey');
        $password  = base64_encode($shortCode . $passkey . $time);
        
        $accountReference = $this->engine->getConfig()->get('mpesa.stk.account_reference');
        $callback         = $this->engine->getConfig()->get('mpesa.stk.callback');
        $transactionType  = $this->engine->getConfig()->get('mpesa.stk.transaction_type');
        $transactionDesc  = $this->engine->getConfig()->get('mpesa.stk.transaction_desc');

        $configParams = [
            'BusinessShortCode' => $shortCode,
            'CallBackURL'       => $callback,
            'TransactionType'   => $transactionType,
            'Password'          => $password,
            'PartyB'            => $shortCode,
            'Timestamp'         => $time,
            'TransactionDesc'   => $transactionDesc,
            'AccountReference'  => $accountReference,
        ];

        $body = $this->prepareBody($configParams, $params);

        // Fallback for PartyA if not explicitly provided
        if (empty($body['PartyA']) && !empty($body['PhoneNumber'])) {
            $body['PartyA'] = $body['PhoneNumber'];
        }
        
        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
