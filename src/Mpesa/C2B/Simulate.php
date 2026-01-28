<?php

namespace Yourdudeken\Mpesa\C2B;

use InvalidArgumentException;
use Yourdudeken\Mpesa\Engine\Core;

/**
 * Class Simulate.
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
class Simulate
{
    /**
     * @var string
     */
    protected $endpoint = 'mpesa/c2b/v1/simulate';

    /**
     * @var Core
     */
    private $engine;

    protected $validationRules = [
        'ShortCode:ShortCode' => 'required()({label} is required)',
        'CommandID:CommandID' => 'required()({label} is required)',
        'Msisdn:Msisdn' => 'required()({label} is required)',
        'Amount:Amount' => 'required()({label} is required)',
        'BillRefNumber:BillRefNumber' => 'maxlength(20)({label} is too long)',
    ];

    /**
     * Registrar constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine)
    {
        $this->engine   = $engine;
        $this->engine->setValidationRules($this->validationRules);
    }

    /**
     * Initiate the simulation process.
     *
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function submit($params = [],$appName='default'){
        $shortCode = $this->engine->config->get('mpesa.c2b.short_code');
        $commandId = $this->engine->config->get('mpesa.c2b.default_command_id');

        $configParams = [
            'CommandID'         => $commandId,
            'ShortCode'         => intval($shortCode),
        ];

        // Normalize user-provided params and merge with config defaults
        $userParams = $this->engine->normalizeParams($params, [
            'msisdn' => 'Msisdn',
            'bill_ref_number' => 'BillRefNumber',
        ]);
        $body = array_merge($configParams, $userParams);

        // Final normalization pass
        $body = $this->engine->normalizeParams($body);

        // Safaricom Schema Fix: 
        // For CustomerBuyGoodsOnline (Till Number), BillRefNumber should not be sent
        // For CustomerPayBillOnline, it is mandatory
        if ($body['CommandID'] === 'CustomerBuyGoodsOnline') {
            unset($body['BillRefNumber']);
        } elseif (empty($body['BillRefNumber'])) {
            $body['BillRefNumber'] = (string)$shortCode; // Fallback
        }

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
