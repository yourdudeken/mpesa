<?php

namespace Mpesa;

use Mpesa\Engine\Core;
use Mpesa\Engine\Cache;
use Mpesa\Engine\Config;
use Mpesa\Engine\MpesaTrait;
use Mpesa\Auth\Authenticator;
use Mpesa\Engine\CurlRequest;
/**
 * Class Mpesa
 *
 * @category PHP
 *
 * @author     Kennedy Muthengi <Kenmwendwamuthengi@gmail.com>
 */
class Init
{
    use MpesaTrait;

    /**
     * @var Core
     */
    private $engine;

    /**
     * Mpesa constructor.
     *
     */
    public function __construct($myconfig = []){
        $config = new Config($myconfig);
        $cache = new Cache($config);
        $auth = new Authenticator();
        $httpClient = new CurlRequest();
        $this->engine = new Core($config, $cache,$httpClient,$auth);
    }
}
