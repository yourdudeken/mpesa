<?php

namespace Yourdudeken\Mpesa\Contracts;

/**
 * Interface ConfigurationStore
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
interface ConfigurationStore
{
    /**
     * Get the configuration value from the store.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a configuration value.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void;
}
