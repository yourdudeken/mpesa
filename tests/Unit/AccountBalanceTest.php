<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\AccountBalance\Balance;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;

class AccountBalanceTest extends TestCase
{
    protected $core;
    protected $config;
    protected $balance;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = Mockery::mock(Config::class);
        $this->core = Mockery::mock(Core::class);
        $this->core->shouldReceive('getConfig')->andReturn($this->config);
    }

    public function testSubmitBalanceQuerySuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.balance.short_code')->andReturn('600000');
        $this->config->shouldReceive('get')->with('mpesa.balance.initiator_name')->andReturn('user');
        $this->config->shouldReceive('get')->with('mpesa.balance.initiator_password')->andReturn('password');
        $this->config->shouldReceive('get')->with('mpesa.balance.command_id')->andReturn('AccountBalance');
        $this->config->shouldReceive('get')->with('mpesa.balance.result_url')->andReturn('http://result.url');
        $this->config->shouldReceive('get')->with('mpesa.balance.timeout_url')->andReturn('http://timeout.url');
        $this->config->shouldReceive('get')->with('mpesa.balance.remarks')->andReturn('Remarks');
        $this->config->shouldReceive('get')->with('mpesa.balance.identifier_type')->andReturn('4');

        // Mock methods called during execution
        $this->core->shouldReceive('computeSecurityCredential')->with('password')->andReturn('encrypted_password');
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization call for user params
        $this->core->shouldReceive('normalizeParams')->with([], Mockery::type('array'))->andReturn([]);
        
        $expectedBody = [
            'Initiator'          => 'user',
            'SecurityCredential' => 'encrypted_password',
            'CommandID'          => 'AccountBalance',
            'PartyA'             => '600000',
            'IdentifierType'     => '4',
            'QueueTimeOutURL'    => 'http://timeout.url',
            'ResultURL'          => 'http://result.url',
            'Remarks'            => 'Remarks',
        ];

        // Mock parameter normalization call for merged params
        $this->core->shouldReceive('normalizeParams')
            ->with(Mockery::on(function ($arg) {
                 return isset($arg['Initiator']) && $arg['Initiator'] === 'user';
            }), Mockery::type('array'))
            ->andReturn($expectedBody);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedBody) {
                if ($arg['endpoint'] !== 'mpesa/accountbalance/v1/query') return false;
                
                // key intersection check
                foreach ($expectedBody as $k => $v) {
                    if (!isset($arg['body'][$k]) || $arg['body'][$k] !== $v) return false;
                }
                return true;
            }), 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $balance = new Balance($this->core);
        $response = $balance->submit();

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }
}
