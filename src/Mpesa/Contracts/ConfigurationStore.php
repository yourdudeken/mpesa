<?php

namespace Mpesa\Contracts;

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
     * Get the configuration value from the store or a default value to be supplied.
     *
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public function get($key, $default = null);
}
