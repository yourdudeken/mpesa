<?php

namespace Yourdudeken\Mpesa\Engine;

use Yourdudeken\Mpesa\Contracts\CacheStore;

/**
 * Class Cache
 *
 * @category PHP
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
class Cache implements CacheStore
{
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * Cache constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get the cache value.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $location = $this->getCacheFile();

        if (!is_file($location)) {
            return $default;
        }

        $content = file_get_contents($location);
        $cache = $content ? @unserialize($content) : [];
        
        if (!is_array($cache)) {
            $cache = [];
        }

        $cache = $this->cleanCache($cache, $location);

        if (!isset($cache[$key])) {
            return $default;
        }

        return $cache[$key]['v'];
    }

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed  $value
     * @param int|float|null $seconds
     */
    public function put(string $key, mixed $value, int|float|null $seconds = null): void
    {
        $location = $this->getCacheFile();
        $directory = dirname($location);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $initial = [];
        if (is_file($location)) {
            $content = file_get_contents($location);
            $initial = $content ? @unserialize($content) : [];
            if (!is_array($initial)) {
                $initial = [];
            }
            $initial = $this->cleanCache($initial, $location);
        }

        $expiry = $this->computeExpiryTime($seconds);
        $payload = array_merge($initial, [$key => ['v' => $value, 't' => $expiry]]);
        
        file_put_contents($location, serialize($payload));
    }

    /**
     * Check if an item exists in the cache.
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Remove an item from the cache.
     * 
     * @param string $key
     * @return void
     */
    public function forget(string $key): void
    {
        $location = $this->getCacheFile();

        if (!is_file($location)) {
            return;
        }

        $content = file_get_contents($location);
        $cache = $content ? @unserialize($content) : [];
        
        if (is_array($cache) && isset($cache[$key])) {
            unset($cache[$key]);
            file_put_contents($location, serialize($cache));
        }
    }

    /**
     * Compute expiry time.
     * 
     * @param int|float|null $seconds
     * @return string|null
     */
    public function computeExpiryTime(int|float|null $seconds): ?string
    {
        if ($seconds === null) {
            return null;
        }
        
        $date = new \DateTime();
        return $date->modify("+". (int) $seconds ." seconds")->format('Y-m-d H:i:s');
    }

    /**
     * Get path to cache file.
     * 
     * @return string
     */
    protected function getCacheFile(): string
    {
        $directory = trim($this->config->get('mpesa.cache_location') ?: sys_get_temp_dir());
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.mpc';
    }

    /**
     * Clean expired items from cache.
     * 
     * @param array $initial
     * @param string $location
     * @return array
     */
    private function cleanCache(array $initial, string $location): array
    {
        $currentDt = new \DateTime();
        
        $cleaned = array_filter($initial, function ($value) use ($currentDt) {
            if (!isset($value['t']) || $value['t'] === null) {
                return true;
            }
            
            try {
                $expiry = new \DateTime($value['t']);
                return $currentDt <= $expiry;
            } catch (\Exception $e) {
                return false;
            }
        });

        if (count($cleaned) !== count($initial)) {
            file_put_contents($location, serialize($cleaned));
        }

        return $cleaned;
    }
}
