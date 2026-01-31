<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\TransactionStatus\TransactionStatus;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;

class TransactionStatusTest extends TestCase
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

    public function testSubmitRequestSuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.status.short_code')->andReturn('600000');
        $this->config->shouldReceive('get')->with('mpesa.status.initiator_name')->andReturn('user');
        $this->config->shouldReceive('get')->with('mpesa.status.initiator_password')->andReturn('password');
        $this->config->shouldReceive('get')->with('mpesa.status.command_id')->andReturn('StatusQuery');
        $this->config->shouldReceive('get')->with('mpesa.status.result_url')->andReturn('http://result.url');
        $this->config->shouldReceive('get')->with('mpesa.status.timeout_url')->andReturn('http://timeout.url');
        $this->config->shouldReceive('get')->with('mpesa.status.remarks')->andReturn('Remarks');
        $this->config->shouldReceive('get')->with('mpesa.status.occasion')->andReturn('Occasion');
        $this->config->shouldReceive('get')->with('mpesa.status.identifier_type')->andReturn('4');

        // Mock methods called during execution
        $this->core->shouldReceive('computeSecurityCredential')->with('password')->andReturn('encrypted_password');
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization
        $this->core->shouldReceive('normalizeParams')
            ->with(['transactionID' => 'MX12345678'], Mockery::type('array'))
            ->andReturn([]);
        
        $expectedBody = [
            'Initiator'          => 'user',
            'SecurityCredential' => 'encrypted_password',
            'CommandID'          => 'StatusQuery',
            'PartyA'             => '600000',
            'IdentifierType'     => '4',
            'QueueTimeOutURL'    => 'http://timeout.url',
            'ResultURL'          => 'http://result.url',
            'Remarks'            => 'Remarks',
            'Occasion'           => 'Occasion',
        ];

        // Mock parameter normalization for merged
        $this->core->shouldReceive('normalizeParams')
            ->with(Mockery::any(), Mockery::type('array'))
            ->andReturn($expectedBody);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedBody) {
                if ($arg['endpoint'] !== 'mpesa/transactionstatus/v1/query') return false;
                foreach ($expectedBody as $k => $v) {
                    if (!isset($arg['body'][$k]) || $arg['body'][$k] !== $v) return false;
                }
                return true;
            }), 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $status = new TransactionStatus($this->core);
        $response = $status->submit([
            'transactionID' => 'MX12345678'
        ]);

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }
}
