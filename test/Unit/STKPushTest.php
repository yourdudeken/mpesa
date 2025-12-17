<?php

namespace Mpesa\Tests\Unit;

use Mpesa\Tests\TestCase;
use Mpesa\LipaNaMpesaOnline\STKPush;
use Mpesa\Auth\Authenticator;
use Mpesa\Engine\Core;
use Mpesa\Contracts\ConfigurationStore;
use Mpesa\Exceptions\ConfigurationException;
use Mpesa\Exceptions\MpesaException;

class STKPushTest extends TestCase{
    

    public function setUp()
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
        $b2c = new STKPush($this->engine);
        $this->expectException(ConfigurationException::class);
        $results = $b2c->submit();
    }

    /**
     * Test submitting payment request with appropriate param works.
     * 
     */
    public function testSubmitWithParams(){
        $b2c = new STKPush($this->engine);
        
        $this->httpClient->method('getInfo')
        ->will($this->returnValue(500));

        $this->expectException(MpesaException::class);
        // Test with null params should throw an error.
        $results = $b2c->submit([
            'amount' => 20,
            'phoneNumber' => '254723731241',
            'transactionDesc' => 'Hello',
            'accountReference' => "User X consultation fee",
            'callBackURL' => "https://example.com/v1/payments/callback",
            'queueTimeOutURL' => "https://example.com/v1/payments/callback"
        ]);
        fwrite(STDERR, print_r($results, TRUE));
    }
}
