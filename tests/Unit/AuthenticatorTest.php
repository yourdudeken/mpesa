<?php

namespace Mpesa\Tests\Unit;

use Mpesa\Auth\Authenticator;
use Mpesa\Tests\TestCase;
use Mpesa\Engine\Core;
use Mpesa\Exceptions\ConfigurationException;


class AuthenticatorTest extends TestCase{

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanCache();
    }

    private function cleanCache()
    {
        $file = __DIR__ . '/../../cache/.mpc';
        if (\is_file($file)) {
            \unlink($file);
        }
    }

    /**
     * Test that authenticator works.
     *
     * @test
     **/
    public function testAuthentication(){
        $this->httpClient->method('execute')
        ->will($this->returnValue('{"access_token":"asdasdsad"}'));
        
        $this->httpClient->method('getInfo')
        ->will($this->returnValue(200));

        $auth   = new Authenticator();
        $auth->setEngine($this->engine);
        
        $token  = $auth->authenticate();
        $this->assertInternalType('string', $token);
    }
}
