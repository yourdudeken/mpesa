<?php

namespace Yourdudeken\Mpesa\Contracts;

/**
 * Interface Transactable
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */
interface Transactable
{
    /**
     * Generate transaction number for the request
     *
     * @return mixed
     */
    public static function generateTransactionNumber();
}
