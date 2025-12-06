<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\TransactionStatus\TransactionStatus;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;
use Yourdudeken\Mpesa\Exceptions\MpesaException;

class TransactionStatusTest extends TestCase{

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
        $b2c = new TransactionStatus($this->engine);
        $this->expectException(ConfigurationException::class);
        $results = $b2c->submit();
    }

    /**
     * Test submitting payment request with appropriate param works.
     * 
     */
    public function testSubmitWithParams(){
        $b2c = new TransactionStatus($this->engine);

        $this->httpClient->method('getInfo')
        ->will($this->returnValue(500));

        $this->expectException(MpesaException::class);
        // Test with null params should throw an error.
        $results = $b2c->submit([
            'TransactionID' => 20,
            'partyB' => '254723731241',
            'remarks' => "User X consultation fee",
            'resultURL' => "https://example.com/v1/payments/callback",
            'queueTimeOutURL' => "https://example.com/v1/payments/callback"
        ]);
        fwrite(STDERR, print_r($results, TRUE));
    }
}
