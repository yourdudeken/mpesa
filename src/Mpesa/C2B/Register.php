<?php

namespace Yourdudeken\Mpesa\C2B;

use InvalidArgumentException;
use Yourdudeken\Mpesa\Engine\Core;

/**
 * Class Register.
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
class Register
{
    /**
     * @var string
     */
    protected $endpoint = 'mpesa/c2b/v2/registerurl';

    /**
     * @var Core
     */
    private $engine;

    protected $validationRules = [
        'ShortCode:ShortCode' => 'required()({label} is required)',
        'ResponseType:ResponseType' => 'required()({label} is required)',
        'ConfirmationURL:ConfirmationURL' => 'required()({label} is required)',
        'ValidationURL:ValidationURL' => 'required()({label} is required)'
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
     * Initiate the registration process.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function submit($params = [],$appName='default'){
        $shortCode       = $this->engine->config->get('mpesa.c2b.short_code');
        $confirmationURL = $this->engine->config->get('mpesa.c2b.confirmation_url') ?: $this->engine->config->get('mpesa.callback');
        $responseType    = $this->engine->config->get('mpesa.c2b.response_type');
        $validationURL   = $this->engine->config->get('mpesa.c2b.validation_url') ?: $this->engine->config->get('mpesa.callback');

        $configParams = [
            'ShortCode'       => $shortCode,
            'ResponseType'    => $responseType,
            'ConfirmationURL' => $confirmationURL,
            'ValidationURL'   => $validationURL
        ];

        // Normalize user-provided params and merge with config defaults
        $userParams = $this->engine->normalizeParams($params);
        $body = array_merge($configParams, $userParams);

        // Final normalization pass
        $body = $this->engine->normalizeParams($body);

        return $this->engine->makePostRequest([
            'endpoint' => $this->endpoint,
            'body' => $body
        ],$appName);
    }
}
