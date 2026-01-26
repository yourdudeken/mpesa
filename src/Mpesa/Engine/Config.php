<?php

namespace Yourdudeken\Mpesa\Engine;

use ArrayAccess;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;

class Config implements ArrayAccess,ConfigurationStore
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new configuration repository.
     *
     * @param  array  $items
     * @return void
     */
    /**
     * Create a new configuration repository.
     *
     * @param  array  $conf
     * @return void
     */
    public function __construct($conf = []){
        // Load internal config
        $internalConfigFile = __DIR__ . '/../../config/mpesa.php';
        $internalConfig = [];
        if (\is_file($internalConfigFile)) {
            $internalConfig = require $internalConfigFile;
        }
        
        // Load from environment variables
        $envConfig = $this->loadFromEnv();
        
        // Merge configs: Internal < Env < Constructor
        $this->items = array_merge($internalConfig, $envConfig, $conf);

        // Normalize credentials and generate URLs
        $this->normalizeItems();
    }

    /**
     * Normalize configuration items.
     */
    private function normalizeItems() {
        // Set API URL based on environment if not explicitly provided
        if (!isset($this->items['apiUrl'])) {
            $isSandbox = $this->items['is_sandbox'] ?? true;
            $this->items['apiUrl'] = $isSandbox 
                ? ($this->items['apiUrlSandbox'] ?? 'https://sandbox.safaricom.co.ke/')
                : ($this->items['apiUrlLive'] ?? 'https://api.safaricom.co.ke/');
        }

        // Map root-level consumer credentials to apps structure
        if (isset($this->items['consumer_key']) && !isset($this->items['apps']['default']['consumer_key'])) {
            $this->items['apps']['default']['consumer_key'] = $this->items['consumer_key'];
        }
        if (isset($this->items['consumer_secret']) && !isset($this->items['apps']['default']['consumer_secret'])) {
            $this->items['apps']['default']['consumer_secret'] = $this->items['consumer_secret'];
        }

        // Auto-generate callback URLs if a base callback is provided
        $this->generateCallbackUrls();
    }

    /**
     * Generate specific callback URLs from a base URL if not provided.
     */
    private function generateCallbackUrls() {
        $baseCallback = $this->items['callback'] ?? null;
        if (empty($baseCallback)) {
            return;
        }

        $baseCallback = rtrim($baseCallback, '/');

        // lnmo callback
        if (!isset($this->items['lnmo']['callback'])) {
            $this->items['lnmo']['callback'] = $baseCallback;
        }

        // C2B URLs
        if (!isset($this->items['c2b']['confirmation_url'])) {
            $this->items['c2b']['confirmation_url'] = $baseCallback . '/confirmation';
        }
        if (!isset($this->items['c2b']['validation_url'])) {
            $this->items['c2b']['validation_url'] = $baseCallback . '/validation';
        }

        // Result and Timeout URLs for B2C, B2B, Reversal, AccountBalance, TransactionStatus
        $endpoints = ['b2c', 'b2b', 'reversal', 'account_balance', 'transaction_status', 'b2pochi'];
        foreach ($endpoints as $endpoint) {
            if (!isset($this->items[$endpoint]['result_url'])) {
                $this->items[$endpoint]['result_url'] = $baseCallback . '/result';
            }
            if (!isset($this->items[$endpoint]['timeout_url'])) {
                $this->items[$endpoint]['timeout_url'] = $baseCallback . '/timeout';
            }
        }
    }

    /**
     * Load configuration from environment variables
     * 
     * @return array
     */
    private function loadFromEnv() {
        $config = [];
        
        // Environment variables mapping
        $mapping = [
            'MPESA_ENV'               => 'is_sandbox',
            'MPESA_CONSUMER_KEY'     => 'consumer_key',
            'MPESA_CONSUMER_SECRET'  => 'consumer_secret',
            'MPESA_SHORTCODE'        => 'short_code',
            'MPESA_PASSKEY'          => 'passkey',
            'MPESA_CALLBACK_URL'     => 'callback',
            'MPESA_INITIATOR_NAME'   => 'initiator_name',
            'MPESA_INITIATOR_PASSWORD' => 'initiator_password',
        ];

        foreach ($mapping as $envKey => $configKey) {
            $val = getenv($envKey);
            if ($val !== false) {
                if ($configKey === 'is_sandbox') {
                    $config[$configKey] = ($val === 'sandbox' || $val === 'true' || $val === '1');
                } else {
                    $config[$configKey] = $val;
                }
            }
        }
        
        return $config;
    }

    /**
     * Load environment variables from .env file
     * 
     * @param string $path
     * @return void
     */
    private function loadEnvFile($path) {
        if (!\is_readable($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes
                $value = trim($value, '"\'');
                
                // Set environment variable if not already set
                if (getenv($key) === false) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
         return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
     public static function exists($array, $key)
     {
         if ($array instanceof ArrayAccess) {
             return $array->offsetExists($key);
         }
 
         return array_key_exists($key, $array);
     }

     /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null){
        $key = str_replace("mpesa.","",$key);
        $array = $this->items;
        if (! static::accessible($array)) {
            return $this->value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        // Try to get the specific key first
        $value = $this->retrieve($array, $key);
        
        if ($value !== null) {
            return $value;
        }

        // If not found and is a nested key, try falling back to the last segment
        // e.g. b2c.initiator_name -> initiator_name
        if (strpos($key, '.') !== false) {
            $segments = explode('.', $key);
            $lastSegment = end($segments);
            
            // Check if the last segment exists at the root level
            if (isset($array[$lastSegment])) {
                return $array[$lastSegment];
            }
        }

        return $this->value($default);
    }

    /**
     * Retrieve a value from the array using dot notation.
     */
    private function retrieve($array, $key) {
        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : null;
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    
    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->set($key, null);
    }

     /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function value($value){
        return $value instanceof Closure ? $value() : $value;
    }
}
