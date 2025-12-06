<?php

namespace Yourdudeken\Mpesa\Tests;

use Mockery;
use PHPUnit\Framework\TestCase as PHPUnit;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Cache;
use Yourdudeken\Mpesa\Engine\Config;
use Yourdudeken\Mpesa\Auth\Authenticator;
use Yourdudeken\Mpesa\Contracts\HttpRequest;

class TestCase extends PHPUnit
{
    /**
     * Engine Core.
     *
     * @var Engine
     **/
    public $engine;

    public $httpClient;

    public $auth;

    /**
     * Set mocks.
     **/
    protected function setUp(): void
    {
        parent::setUp();
        $config       = new Config();
        $cache        = new Cache($config);
        $this->httpClient = $this->createMock(HttpRequest::class);
        $this->auth = $this->createMock(Authenticator::class);
        $this->engine  = new Core($config, $cache,$this->httpClient,$this->auth);
    }

    public function mockAuth(){
    }
}
