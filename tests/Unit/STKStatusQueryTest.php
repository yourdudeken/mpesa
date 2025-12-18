<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKStatusQuery;
use Yourdudeken\Mpesa\Auth\Authenticator;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;
use Yourdudeken\Mpesa\Exceptions\MpesaException;

class STKStatusQueryTest extends TestCase{

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
        $b2c = new STKStatusQuery($this->engine);
        $this->expectException(ConfigurationException::class);
        $results = $b2c->submit();
    }

    /**
     * Test submitting payment request with appropriate param works.
     * 
     */
    public function testSubmitWithParams(){
        $b2c = new STKStatusQuery($this->engine);
        
        $this->httpClient->method('getInfo')
        ->will($this->returnValue(500));

        $this->expectException(MpesaException::class);
        // Test with null params should throw an error.
        $results = $b2c->submit([
            'CheckoutRequestID' => '254723731241',
        ]);
        fwrite(STDERR, print_r($results, TRUE));
    }
}