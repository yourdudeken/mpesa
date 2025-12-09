<?php

namespace Yourdudeken\Mpesa\B2Pochi;

use Yourdudeken\Mpesa\Contracts\Transactable;
use Yourdudeken\Mpesa\Engine\MpesaTrait;

/**
 * Class Pay
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
class Pay implements Transactable
{
    use MpesaTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    protected $validationRules = [
        'OriginatorConversationID' => 'required',
        'InitiatorName' => 'required',
        'SecurityCredential' => 'required',
        'CommandID' => 'required',
        'Amount' => 'required|number',
        'PartyA' => 'required',
        'PartyB' => 'required',
        'Remarks' => 'required',
        'QueueTimeOutURL' => 'required|url',
        'ResultURL' => 'required|url',
        'Occasion' => '',
    ];

    /**
     * Endpoint to hit when submitting a B2Pochi request
     *
     * @var string
     */
    protected $endpoint = 'mpesa/b2c/v1/paymentrequest';

    /**
     * Prepare B2Pochi request
     *
     * @param array $params
     *
     * @return mixed
     */
    public function submit($params = [])
    {
        // Set default values if not provided
        $defaults = [
            'CommandID' => 'BusinessPayToPochi',
        ];

        $params = array_merge($defaults, $params);

        // Compute security credential if initiator password is provided
        if (isset($params['initiatorPassword'])) {
            $params['SecurityCredential'] = $this->engine->computeSecurityCredential($params['initiatorPassword']);
            unset($params['initiatorPassword']);
        }

        return $this->makePayment($params);
    }

    /**
     * Make the payment request
     *
     * @param array $body
     *
     * @return mixed
     */
    private function makePayment($body)
    {
        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body,
        ]);
    }
}
