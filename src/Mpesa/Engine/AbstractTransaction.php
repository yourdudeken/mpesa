<?php

namespace Yourdudeken\Mpesa\Engine;

/**
 * Class AbstractTransaction
 * 
 * Base class for all M-Pesa transaction types.
 */
abstract class AbstractTransaction
{
    /**
     * @var string
     */
    protected string $endpoint;

    /**
     * @var array
     */
    protected array $validationRules = [];

    /**
     * @var Core
     */
    protected Core $engine;

    /**
     * AbstractTransaction constructor.
     *
     * @param Core $engine
     */
    public function __construct(Core $engine)
    {
        $this->engine = $engine;
        if (!empty($this->validationRules)) {
            $this->engine->setValidationRules($this->validationRules);
        }
    }

    /**
     * Submit the transaction request.
     *
     * @param array  $params
     * @param string $appName
     * @return mixed
     */
    abstract public function submit(array $params = [], string $appName = 'default'): mixed;

    /**
     * Help merging config and user parameters safely.
     * 
     * @param array $configParams
     * @param array $userParams
     * @param array $mappings
     * @return array
     */
    protected function prepareBody(array $configParams, array $userParams, array $mappings = []): array
    {
        // Normalize user params first
        $normalizedUser = $this->engine->normalizeParams($userParams, $mappings);
        
        // Merge with config defaults (user overrides config)
        $merged = array_merge($configParams, $normalizedUser);
        
        // Final pass to ensure all fields are normalized and sanitized
        return $this->engine->normalizeParams($merged, $mappings);
    }
}
