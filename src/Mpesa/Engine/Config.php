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
        // Load internal config (ONLY certificate paths)
        $internalConfigFile = __DIR__ . '/../../config/mpesa.php';
        $internalConfig = [];
        if (\is_file($internalConfigFile)) {
            $internalConfig = require $internalConfigFile;
        }
        
        // Load from environment variables if available
        $envConfig = $this->loadFromEnv();
        
        // Check for config in current working directory (user's project)
        $cwdConfig = getcwd() . '/config/mpesa.php';
        $cwdCustom = [];
        if (\is_file($cwdConfig)) {
            $cwdCustom = require $cwdConfig;
        }
        
        // Config after user edits the config file copied by the system
        $userConfig    =  __DIR__ . '/../../../../../../config/mpesa.php';
        $custom        = [];
        if (\is_file($userConfig)) {
            $custom = require $userConfig;
        }
        
        // Merge configs with priority: passed config > cwd config > user config > env config > internal config (certs only)
        $this->items = array_merge(
            $internalConfig, 
            $envConfig,
            $custom, 
            $cwdCustom, 
            $conf
        );
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
        
        // Map environment variables to config
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
            $config['lnmo']['short_code'] = getenv('MPESA_SHORTCODE');
            $config['c2b']['short_code'] = getenv('MPESA_SHORTCODE');
        }
        
        if (getenv('MPESA_PASSKEY') !== false) {
            $config['lnmo']['passkey'] = getenv('MPESA_PASSKEY');
        }
        
        if (getenv('MPESA_CALLBACK_URL') !== false) {
            $config['lnmo']['callback'] = getenv('MPESA_CALLBACK_URL');
        }
        
        if (getenv('MPESA_INITIATOR_NAME') !== false) {
            $initiatorName = getenv('MPESA_INITIATOR_NAME');
            $config['b2c']['initiator_name'] = $initiatorName;
            $config['b2b']['initiator_name'] = $initiatorName;
            $config['account_balance']['initiator_name'] = $initiatorName;
            $config['transaction_status']['initiator_name'] = $initiatorName;
            $config['reversal']['initiator_name'] = $initiatorName;
            $config['b2pochi']['initiator_name'] = $initiatorName;
        }
        
        if (getenv('MPESA_INITIATOR_PASSWORD') !== false) {
            $initiatorPassword = getenv('MPESA_INITIATOR_PASSWORD');
            $config['b2c']['initiator_password'] = $initiatorPassword;
            $config['b2b']['initiator_password'] = $initiatorPassword;
            $config['account_balance']['initiator_password'] = $initiatorPassword;
            $config['transaction_status']['initiator_password'] = $initiatorPassword;
            $config['reversal']['initiator_password'] = $initiatorPassword;
            $config['b2pochi']['initiator_password'] = $initiatorPassword;
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

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : $this->value($default);
        }
 
        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $this->value($default);
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
