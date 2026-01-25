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
    public function __construct($conf = []){
        // Load internal config
        $internalConfigFile = __DIR__ . '/../../config/mpesa.php';
        $internalConfig = [];
        if (\is_file($internalConfigFile)) {
            $internalConfig = require $internalConfigFile;
        }
        
        // Load from environment variables
        $envConfig = $this->loadFromEnv();
        
        // Check for config in user project
        $cwdConfig = getcwd() . '/config/mpesa.php';
        $cwdCustom = [];
        if (\is_file($cwdConfig)) {
            $cwdCustom = require $cwdConfig;
        }
        
        // Final user config from vendor root (fallback)
        $userConfig = __DIR__ . '/../../../../../../config/mpesa.php';
        $custom = [];
        if (\is_file($userConfig)) {
            $custom = require $userConfig;
        }
        
        // Merge all configs
        $this->items = array_merge($internalConfig, $envConfig, $custom, $cwdCustom, $conf);

        // Normalize credentials: Ensure root-level keys are available where package expects them
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
    }

    /**
     * Load configuration from environment variables
     * 
     * @return array
     */
    private function loadFromEnv() {
        $config = [];
        
        // Load .env file if it exists in current directory
        $envFile = getcwd() . '/.env';
        if (\is_file($envFile)) {
            $this->loadEnvFile($envFile);
        }
        
        // Map environment variables to root-level config
        // Hierarchical lookup will handle the fallbacks for nested keys
        if (getenv('MPESA_ENV') !== false) {
            $config['is_sandbox'] = getenv('MPESA_ENV') === 'sandbox';
        }
        
        if (getenv('MPESA_CONSUMER_KEY') !== false) {
            $config['apps']['default']['consumer_key'] = getenv('MPESA_CONSUMER_KEY');
        }
        
        if (getenv('MPESA_CONSUMER_SECRET') !== false) {
            $config['apps']['default']['consumer_secret'] = getenv('MPESA_CONSUMER_SECRET');
        }
        
        if (getenv('MPESA_SHORTCODE') !== false) {
            $config['short_code'] = getenv('MPESA_SHORTCODE');
        }
        
        if (getenv('MPESA_PASSKEY') !== false) {
            $config['passkey'] = getenv('MPESA_PASSKEY');
        }
        
        if (getenv('MPESA_CALLBACK_URL') !== false) {
            $config['callback'] = getenv('MPESA_CALLBACK_URL');
            $config['result_url'] = getenv('MPESA_CALLBACK_URL');
            $config['timeout_url'] = getenv('MPESA_CALLBACK_URL');
        }
        
        if (getenv('MPESA_INITIATOR_NAME') !== false) {
            $config['initiator_name'] = getenv('MPESA_INITIATOR_NAME');
        }
        
        if (getenv('MPESA_INITIATOR_PASSWORD') !== false) {
            $config['initiator_password'] = getenv('MPESA_INITIATOR_PASSWORD');
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
