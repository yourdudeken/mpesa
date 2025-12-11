<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\B2Pochi\Pay;
use Yourdudeken\Mpesa\Auth\Authenticator;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;
use Yourdudeken\Mpesa\Exceptions\MpesaException;

class B2PochiTest extends TestCase{

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
        $b2pochi = new Pay($this->engine);
        $this->expectException(ConfigurationException::class);
        $results = $b2pochi->submit();
    }

    /**
     * Test submitting payment request with appropriate param works.
     * 
     */
    public function testSubmitWithParams(){
        $b2pochi = new Pay($this->engine);
        $this->httpClient->method('getInfo')
        ->will($this->returnValue(500));
        
        $this->expectException(MpesaException::class);
        // Test with params should throw an error due to mock response
        $results = $b2pochi->submit([
            'OriginatorConversationID' => 'B2P_TEST_12345',
            'amount' => 1000,
            'partyB' => '254723731241',
            'remarks' => "Pochi savings deposit",
            'occasion' => "Monthly savings",
            'resultURL' => "https://example.com/v1/payments/callback",
            'queueTimeOutURL' => "https://example.com/v1/payments/callback"
        ]);
        fwrite(STDERR, print_r($results, TRUE));
    }
}
