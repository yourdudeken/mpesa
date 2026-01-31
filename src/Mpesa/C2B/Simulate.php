<?php

namespace Yourdudeken\Mpesa\C2B;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

/**
 * Class Simulate.
 *
 * @category PHP
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
class Simulate extends AbstractTransaction
{
    /**
     * @var string
     */
    protected string $endpoint = 'mpesa/c2b/v1/simulate';

    protected array $validationRules = [
        'ShortCode:ShortCode'         => 'required()({label} is required)',
        'CommandID:CommandID'         => 'required()({label} is required)',
        'Msisdn:Msisdn'               => 'required()({label} is required)',
        'Amount:Amount'               => 'required()({label} is required)',
        'BillRefNumber:BillRefNumber' => 'maxlength(20)({label} is too long)',
    ];

    /**
     * Initiate the C2B simulation process.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode = $this->engine->getConfig()->get('mpesa.c2b.short_code');
        $commandId = $this->engine->getConfig()->get('mpesa.c2b.command_id');

        $configParams = [
            'CommandID' => $commandId,
            'ShortCode' => (int) $shortCode,
        ];

        $mappings = [
            'msisdn'          => 'Msisdn',
            'bill_ref_number' => 'BillRefNumber',
        ];

        $body = $this->prepareBody($configParams, $params, $mappings);

        // Safaricom Schema Logic: 
        // For CustomerBuyGoodsOnline (Till Number), BillRefNumber should not be sent
        // For CustomerPayBillOnline, it is mandatory
        if ($body['CommandID'] === 'CustomerBuyGoodsOnline') {
            unset($body['BillRefNumber']);
        } elseif (empty($body['BillRefNumber'])) {
            $body['BillRefNumber'] = (string) $shortCode; // Fallback
        }

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
