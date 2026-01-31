<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\B2C\Pay;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;

class B2CTest extends TestCase
{
    protected $core;
    protected $config;
    protected $b2c;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = Mockery::mock(Config::class);
        $this->core = Mockery::mock(Core::class);
        $this->core->shouldReceive('getConfig')->andReturn($this->config);
    }

    public function testSubmitB2CSuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.b2c.short_code')->andReturn('600000');
        $this->config->shouldReceive('get')->with('mpesa.b2c.initiator_name')->andReturn('user');
        $this->config->shouldReceive('get')->with('mpesa.b2c.initiator_password')->andReturn('password');
        $this->config->shouldReceive('get')->with('mpesa.b2c.command_id')->andReturn('BusinessPayment');
        $this->config->shouldReceive('get')->with('mpesa.b2c.result_url')->andReturn('http://result.url');
        $this->config->shouldReceive('get')->with('mpesa.b2c.timeout_url')->andReturn('http://timeout.url');
        $this->config->shouldReceive('get')->with('mpesa.b2c.remarks')->andReturn('Remarks');
        $this->config->shouldReceive('get')->with('mpesa.b2c.occasion')->andReturn('Occasion');

        // Mock methods called during execution
        $this->core->shouldReceive('computeSecurityCredential')->with('password')->andReturn('encrypted_password');
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization
        // The first call normalizes user params
        $this->core->shouldReceive('normalizeParams')->with(['amount' => 100, 'phone' => '254700000000'], [])->andReturn([]);
        
        $expectedBody = [
            'InitiatorName'      => 'user',
            'SecurityCredential' => 'encrypted_password',
            'CommandID'          => 'BusinessPayment',
            'Amount'             => 100,
            'PartyA'             => '600000',
            'PartyB'             => '254700000000',
            'Remarks'            => 'Remarks',
            'QueueTimeOutURL'    => 'http://timeout.url',
            'ResultURL'          => 'http://result.url',
            'Occasion'           => 'Occasion',
        ];

        // The second call normalizes the merged body
        $this->core->shouldReceive('normalizeParams')
            ->with(Mockery::on(function ($arg) {
                 return isset($arg['InitiatorName']) && $arg['InitiatorName'] === 'user';
            }), [])
            ->andReturn($expectedBody);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with([
                'endpoint' => 'mpesa/b2c/v1/paymentrequest',
                'body'     => $expectedBody
            ], 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $b2c = new Pay($this->core);
        $response = $b2c->submit([
            'amount' => 100,
            'phone' => '254700000000'
        ]);

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }
}
