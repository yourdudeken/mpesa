<?php

namespace Mpesa\Tests\Unit;

use Mpesa\Tests\TestCase;
use Mpesa\B2C\Pay;
use Mpesa\Auth\Authenticator;
use Mpesa\Engine\Core;
use Mpesa\Contracts\ConfigurationStore;
use Mpesa\Exceptions\ConfigurationException;
use Mpesa\Exceptions\MpesaException;

class B2CTest extends TestCase{

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
        $b2c = new Pay($this->engine);
        $this->expectException(ConfigurationException::class);
        $results = $b2c->submit();
    }

    /**
     * Test submitting payment request with appropriate param works.
     * 
     */
    public function testSubmitWithParams(){
        $b2c = new Pay($this->engine);
        $this->httpClient->method('getInfo')
        ->will($this->returnValue(500));
        
        $this->expectException(MpesaException::class);
        // Test with null params should throw an error.
        $results = $b2c->submit([
            'amount' => 20,
            'partyB' => '254723731241',
            'remarks' => "User X consultation fee",
            'resultURL' => "https://example.com/v1/payments/callback",
            'queueTimeOutURL' => "https://example.com/v1/payments/callback"
        ]);
        fwrite(STDERR, print_r($results, TRUE));
    }
}
