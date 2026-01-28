<?php

namespace Yourdudeken\Mpesa\LipaNaMpesaOnline;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

class STKStatusQuery extends AbstractTransaction
{
    protected string $endpoint = 'mpesa/stkpushquery/v1/query';

    protected array $validationRules = [
        'BusinessShortCode:BusinessShortCode' => 'required()({label} is required) | number',
        'Password:Password'                   => 'required()({label} is required)',
        'Timestamp:Timestamp'                 => 'required()({label} is required)',
        'CheckoutRequestID:CheckoutRequestID' => 'required()({label} is required)'
    ];

    /**
     * Initiate an STK Status Query request.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $time      = $this->engine->getCurrentRequestTime();
        $shortCode = $this->engine->getConfig()->get('mpesa.lnmo.short_code');
        $passkey   = $this->engine->getConfig()->get('mpesa.lnmo.passkey');
        $password  = base64_encode($shortCode . $passkey . $time);

        $configParams = [
            'BusinessShortCode' => $shortCode,
            'Password'          => $password,
            'Timestamp'         => $time,
        ];

        $mappings = [
            'checkout_request_id' => 'CheckoutRequestID',
        ];

        $body = $this->prepareBody($configParams, $params, $mappings);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
