<?php

namespace Mpesa\Tests\Unit;

use Mpesa\Tests\TestCase;
use Mpesa\C2B\Register;
use Mpesa\Auth\Authenticator;
use Mpesa\Engine\Core;
use Mpesa\Contracts\ConfigurationStore;
use Mpesa\Exceptions\ConfigurationException;
use Mpesa\Exceptions\MpesaException;

class C2BRegisterTest extends TestCase{

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanCache();
    }

    private function cleanCache(){
        $file = __DIR__ . '/../../cache/.mpc';
        if (\is_file($file)) {
            \unlink($file);
        }
    }

    /**
     * Test submitting payment request without params throws an error.
     * 
     */
    public function testSubmitWithoutParams(){
        $b2c = new Register($this->engine);
        $this->expectException(ConfigurationException::class);
        $results = $b2c->submit();
    }

    /**
     * Test submitting payment request with appropriate param works.
     * 
     */
    public function testSubmitWithParams(){
        $b2c = new Register($this->engine);
        
        $this->httpClient->method('getInfo')
        ->will($this->returnValue(500));

        // Test should throw ConfigurationException because ResponseType is required but not provided
        $this->expectException(ConfigurationException::class);
        $results = $b2c->submit([
            'confirmationURL' => "https://example.com/v1/payments/callback",
            'validationURL' => "https://example.com/v1/payments/callback"
        ]);
        fwrite(STDERR, print_r($results, TRUE));
    }
}
