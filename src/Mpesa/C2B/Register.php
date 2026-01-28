<?php

namespace Yourdudeken\Mpesa\C2B;

use Yourdudeken\Mpesa\Engine\AbstractTransaction;

/**
 * Class Register.
 *
 * @category PHP
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
class Register extends AbstractTransaction
{
    /**
     * @var string
     */
    protected string $endpoint = 'mpesa/c2b/v2/registerurl';

    protected array $validationRules = [
        'ShortCode:ShortCode'             => 'required()({label} is required)',
        'ResponseType:ResponseType'       => 'required()({label} is required)',
        'ConfirmationURL:ConfirmationURL' => 'required()({label} is required)',
        'ValidationURL:ValidationURL'     => 'required()({label} is required)'
    ];

    /**
     * Initiate the C2B URL registration process.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     * @throws \Exception
     */
    public function submit(array $params = [], string $appName = 'default'): mixed
    {
        $shortCode       = $this->engine->getConfig()->get('mpesa.c2b.short_code');
        $confirmationURL = $this->engine->getConfig()->get('mpesa.c2b.confirmation_url');
        $responseType    = $this->engine->getConfig()->get('mpesa.c2b.response_type');
        $validationURL   = $this->engine->getConfig()->get('mpesa.c2b.validation_url');

        $configParams = [
            'ShortCode'       => $shortCode,
            'ResponseType'    => $responseType,
            'ConfirmationURL' => $confirmationURL,
            'ValidationURL'   => $validationURL
        ];

        $body = $this->prepareBody($configParams, $params);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body'     => $body
        ], $appName);
    }
}
