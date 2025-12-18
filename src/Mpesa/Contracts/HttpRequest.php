<?php

namespace Yourdudeken\Mpesa\Contracts;
/**
 * Interface HttpRequest
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */

interface HttpRequest
{
    public function setOption($name, $value);
    public function execute();
    public function error();
    public function getInfo($name);
    public function close();
    public function reset();
}
