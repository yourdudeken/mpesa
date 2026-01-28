<?php

namespace Yourdudeken\Mpesa\Engine;

use ArrayAccess;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;

class Config implements ArrayAccess, ConfigurationStore
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Create a new configuration repository.
     *
     * @param array $conf
     */
    public function __construct(array $conf = [])
    {
        // Load internal config
        $internalConfigFile = __DIR__ . '/../../config/mpesa.php';
        $internalConfig = is_file($internalConfigFile) ? require $internalConfigFile : [];
        
        // Merge configs: Internal < Constructor
        $this->items = array_merge($internalConfig, $conf);

        // Normalize credentials
        $this->normalizeItems();
    }

    /**
     * Normalize configuration items.
     */
    private function normalizeItems(): void
    {
        // Set API URL based on environment if not explicitly provided
        if (!isset($this->items['apiUrl'])) {
            $isSandbox = $this->items['is_sandbox'] ?? true;
            $this->items['apiUrl'] = $isSandbox 
                ? ($this->items['apiUrlSandbox'] ?? 'https://sandbox.safaricom.co.ke/')
                : ($this->items['apiUrlLive'] ?? 'https://api.safaricom.co.ke/');
        }

        // 1. Consumer Credentials (Auth)
        $consumerKey = $this->items['auth']['consumer_key'] ?? ($this->items['consumer_key'] ?? null);
        $consumerSecret = $this->items['auth']['consumer_secret'] ?? ($this->items['consumer_secret'] ?? null);

        if ($consumerKey && !isset($this->items['apps']['default']['consumer_key'])) {
            $this->items['apps']['default']['consumer_key'] = $consumerKey;
        }
        if ($consumerSecret && !isset($this->items['apps']['default']['consumer_secret'])) {
            $this->items['apps']['default']['consumer_secret'] = $consumerSecret;
        }

        // 2. Business Initiator Credentials
        $initiatorName = $this->items['initiator']['name'] ?? ($this->items['initiator_name'] ?? null);
        $initiatorPass = $this->items['initiator']['password'] ?? ($this->items['initiator_password'] ?? null);

        if ($initiatorName) {
            $this->items['initiator_name'] = $initiatorName;
        }
        if ($initiatorPass) {
            $this->items['initiator_password'] = $initiatorPass;
        }
    }

    /**
     * Set a given configuration value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $items = &$this->items;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($items[$key]) || !is_array($items[$key])) {
                $items[$key] = [];
            }

            $items = &$items[$key];
        }

        $items[array_shift($keys)] = $value;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get a configuration item.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key = str_replace('mpesa.', '', $key);
        $array = $this->items;

        if (empty($key)) {
            return $array;
        }

        // Try to get the specific key first via dot notation
        $value = $this->retrieve($array, $key);
        
        if ($value !== null) {
            return $value;
        }

        // Fallback for nested keys (e.g. b2c.initiator_name -> initiator_name)
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $lastSegment = end($segments);
            
            if (isset($array[$lastSegment])) {
                return $array[$lastSegment];
            }
        }

        return $default;
    }

    /**
     * Retrieve a value from the array using dot notation.
     *
     * @param array  $array
     * @param string $key
     * @return mixed
     */
    private function retrieve(array $array, string $key): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Get all configuration items.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * ArrayAccess implementation.
     */
    public function offsetExists($key): bool
    {
        return $this->has((string) $key);
    }

    public function offsetGet($key): mixed
    {
        return $this->get((string) $key);
    }

    public function offsetSet($key, $value): void
    {
        $this->set((string) $key, $value);
    }

    public function offsetUnset($key): void
    {
        $this->set((string) $key, null);
    }
}
