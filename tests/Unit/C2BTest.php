<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\C2B\Register;
use Yourdudeken\Mpesa\C2B\Simulate;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;

class C2BTest extends TestCase
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

    public function testRegisterC2BSuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.c2b.short_code')->andReturn('600000');
        $this->config->shouldReceive('get')->with('mpesa.c2b.validation_url')->andReturn('http://validation.url');
        $this->config->shouldReceive('get')->with('mpesa.c2b.confirmation_url')->andReturn('http://confirmation.url');
        $this->config->shouldReceive('get')->with('mpesa.c2b.response_type')->andReturn('Completed');

        // Mock methods called during execution
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization
        $this->core->shouldReceive('normalizeParams')->with([], [])->andReturn([]);
        
        $expectedBody = [
            'ShortCode'       => '600000',
            'ResponseType'    => 'Completed',
            'ConfirmationURL' => 'http://confirmation.url',
            'ValidationURL'   => 'http://validation.url',
        ];

        // Mock parameter normalization for merged
        $this->core->shouldReceive('normalizeParams')
            ->with(Mockery::on(function ($arg) {
                 return isset($arg['ShortCode']) && $arg['ShortCode'] === '600000';
            }), [])
            ->andReturn($expectedBody);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with([
                'endpoint' => 'mpesa/c2b/v2/registerurl',
                'body'     => $expectedBody
            ], 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $register = new Register($this->core);
        $response = $register->submit();

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }

    public function testSimulateC2BSuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.c2b.short_code')->andReturn('600000');
        $this->config->shouldReceive('get')->with('mpesa.c2b.command_id')->andReturn('CustomerPayBillOnline');

        // Mock methods called during execution
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization for user params
        $this->core->shouldReceive('normalizeParams')
            ->with(['amount' => 100, 'msisdn' => '254700000000', 'billRefNumber' => 'Ref'], Mockery::type('array'))
            ->andReturn([]); // Mock return empty for simplicity as logic is tested in CoreTest
            
        $expectedBody = [
            'ShortCode'     => '600000',
            'CommandID'     => 'CustomerPayBillOnline',
            'Amount'        => 100,
            'Msisdn'        => '254700000000',
            'BillRefNumber' => 'Ref',
        ];

        // Mock parameter normalization for merged
        $this->core->shouldReceive('normalizeParams')
            ->with(Mockery::on(function ($arg) {
                 return isset($arg['ShortCode']);
            }), Mockery::type('array'))
            ->andReturn($expectedBody);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with([
                'endpoint' => 'mpesa/c2b/v1/simulate',
                'body'     => $expectedBody
            ], 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $simulate = new Simulate($this->core);
        $response = $simulate->submit([
            'amount' => 100,
            'msisdn' => '254700000000',
            'billRefNumber' => 'Ref'
        ]);

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }
}
