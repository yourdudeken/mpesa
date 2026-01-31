<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKPush;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;

class STKPushTest extends TestCase
{
    protected $core;
    protected $config;
    protected $stkPush;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = Mockery::mock(Config::class);
        $this->core = Mockery::mock(Core::class);
        $this->core->shouldReceive('getConfig')->andReturn($this->config);
    }

    public function testSubmitSTKPushSuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.stk.short_code')->andReturn('123456');
        $this->config->shouldReceive('get')->with('mpesa.stk.passkey')->andReturn('passkey');
        $this->config->shouldReceive('get')->with('mpesa.stk.account_reference')->andReturn('ref');
        $this->config->shouldReceive('get')->with('mpesa.stk.callback')->andReturn('http://callback.url');
        $this->config->shouldReceive('get')->with('mpesa.stk.transaction_type')->andReturn('CustomerPayBillOnline');
        $this->config->shouldReceive('get')->with('mpesa.stk.transaction_desc')->andReturn('Payment');

        // Mock methods called during execution
        $this->core->shouldReceive('getCurrentRequestTime')->andReturn('20230101000000');
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization
        // The first call normalizes user params (which are empty in this test case)
        $this->core->shouldReceive('normalizeParams')->with(['amount' => 100, 'phone' => '254700000000'], [])->andReturn([]);
        
        // The second call normalizes the merged body
        $expectedBodyBeforeNormalization = [
            'BusinessShortCode' => '123456',
            'CallBackURL'       => 'http://callback.url',
            'TransactionType'   => 'CustomerPayBillOnline',
            'Password'          => base64_encode('123456passkey20230101000000'),
            'PartyB'            => '123456',
            'Timestamp'         => '20230101000000',
            'TransactionDesc'   => 'Payment',
            'AccountReference'  => 'ref',
            'Amount'            => 100,
            'PhoneNumber'       => '254700000000',
            'PartyA'            => '254700000000'
        ];
        
        $this->core->shouldReceive('normalizeParams')
            ->with(Mockery::on(function ($arg) use ($expectedBodyBeforeNormalization) {
                 // Check if essential keys exist
                 return isset($arg['BusinessShortCode']) && $arg['BusinessShortCode'] === '123456';
            }), [])
            ->andReturn($expectedBodyBeforeNormalization);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with([
                'endpoint' => 'mpesa/stkpush/v1/processrequest',
                'body'     => $expectedBodyBeforeNormalization
            ], 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $stkPush = new STKPush($this->core);
        $response = $stkPush->submit([
            'amount' => 100,
            'phone' => '254700000000'
        ]);

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }
}
