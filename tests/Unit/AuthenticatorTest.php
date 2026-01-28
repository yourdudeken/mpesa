<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Yourdudeken\Mpesa\Auth\Authenticator;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;

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
        $response = (object) [
            'getStatusCode' => fn() => 200,
            'getBody' => fn() => '{"access_token":"asdasdsad","expires_in":"3600"}'
        ];

        $this->httpClient->method('request')
            ->willReturn($response);

        $auth   = new Authenticator($this->engine->config, $this->engine->cache, $this->httpClient);
        
        $token  = $auth->authenticate();
        $this->assertIsString($token);
    }
}
