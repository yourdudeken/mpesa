<?php

namespace Yourdudeken\Mpesa\Contracts;

/**
 * Interface CacheStore
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
interface CacheStore
{
    /**
     * Get the cache value from the store.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param float|int|null $seconds
     * 
     * @return void
     */
    public function put(string $key, mixed $value, float|int $seconds = null): void;

    /**
     * Check if an item exists in the cache.
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove an item from the cache.
     * 
     * @param string $key
     * @return void
     */
    public function forget(string $key): void;
}
