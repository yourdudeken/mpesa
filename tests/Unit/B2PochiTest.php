<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\B2Pochi\Pay;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;

class B2PochiTest extends TestCase
{
    protected $core;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = Mockery::mock(Config::class);
        $this->core = Mockery::mock(Core::class);
        $this->core->shouldReceive('getConfig')->andReturn($this->config);
    }

    public function testSubmitB2PochiSuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.b2pochi.short_code')->andReturn('600000');
        $this->config->shouldReceive('get')->with('mpesa.b2pochi.initiator_name')->andReturn('user');
        $this->config->shouldReceive('get')->with('mpesa.b2pochi.initiator_password')->andReturn('password');
        $this->config->shouldReceive('get')->with('mpesa.b2pochi.command_id')->andReturn('BusinessPayToPochi');
        $this->config->shouldReceive('get')->with('mpesa.b2pochi.result_url')->andReturn('http://result.url');
        $this->config->shouldReceive('get')->with('mpesa.b2pochi.timeout_url')->andReturn('http://timeout.url');
        $this->config->shouldReceive('get')->with('mpesa.b2pochi.remarks')->andReturn('Remarks');

        // Mock methods called during execution
        $this->core->shouldReceive('computeSecurityCredential')->with('password')->andReturn('encrypted_password');
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization
        $this->core->shouldReceive('normalizeParams')
            ->with(['amount' => 100, 'phone' => '254700000000'], [])
            ->andReturn([]);
        
        $expectedBody = [
            'InitiatorName'      => 'user',
            'SecurityCredential' => 'encrypted_password',
            'CommandID'          => 'BusinessPayToPochi',
            'PartyA'             => '600000',
            'PartyB'             => '254700000000',
            'Amount'             => 100,
            'QueueTimeOutURL'    => 'http://timeout.url',
            'ResultURL'          => 'http://result.url',
            'Remarks'            => 'Remarks',
        ];

        // Mock parameter normalization for merged
        $this->core->shouldReceive('normalizeParams')
              ->with(Mockery::on(function ($arg) {
                 return isset($arg['InitiatorName']) && $arg['InitiatorName'] === 'user';
            }), [])
            ->andReturn($expectedBody);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedBody) {
                if ($arg['endpoint'] !== 'mpesa/b2c/v1/paymentrequest') return false;
                foreach ($expectedBody as $k => $v) {
                    if (!isset($arg['body'][$k]) || $arg['body'][$k] !== $v) return false;
                }
                return true;
            }), 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $pochi = new Pay($this->core);
        $response = $pochi->submit([
            'amount' => 100,
            'phone' => '254700000000'
        ]);

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }
}
