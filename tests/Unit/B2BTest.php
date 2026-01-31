<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\B2B\Pay;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;

class B2BTest extends TestCase
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

    public function testSubmitB2BSuccessfully()
    {
        // Define expected configuration values
        $this->config->shouldReceive('get')->with('mpesa.b2b.short_code')->andReturn('600000');
        $this->config->shouldReceive('get')->with('mpesa.b2b.initiator_name')->andReturn('user');
        $this->config->shouldReceive('get')->with('mpesa.b2b.initiator_password')->andReturn('password');
        $this->config->shouldReceive('get')->with('mpesa.b2b.command_id')->andReturn('BusinessPayBill');
        $this->config->shouldReceive('get')->with('mpesa.b2b.result_url')->andReturn('http://result.url');
        $this->config->shouldReceive('get')->with('mpesa.b2b.timeout_url')->andReturn('http://timeout.url');
        $this->config->shouldReceive('get')->with('mpesa.b2b.remarks')->andReturn('Remarks');
        $this->config->shouldReceive('get')->with('mpesa.b2b.account_reference')->andReturn('Ref');
        $this->config->shouldReceive('get')->with('mpesa.b2b.sender_identifier_type')->andReturn('4');
        $this->config->shouldReceive('get')->with('mpesa.b2b.reciever_identifier_type')->andReturn('4');

        // Mock methods called during execution
        $this->core->shouldReceive('computeSecurityCredential')->with('password')->andReturn('encrypted_password');
        $this->core->shouldReceive('setValidationRules')->once();
        
        // Mock parameter normalization
        $this->core->shouldReceive('normalizeParams')
            ->with(['amount' => 1000, 'party_b' => '600001'], Mockery::type('array'))
            ->andReturn([]);
        
        $expectedBody = [
            'Initiator'              => 'user',
            'SecurityCredential'     => 'encrypted_password',
            'CommandID'              => 'BusinessPayBill',
            'SenderIdentifierType'   => '4',
            'RecieverIdentifierType' => '4', // Note: Check typo in actual class 'Reciever' vs 'Receiver'
            'Amount'                 => 1000,
            'PartyA'                 => '600000',
            'PartyB'                 => '600001',
            'AccountReference'       => 'Ref',
            'Remarks'                => 'Remarks',
            'QueueTimeOutURL'        => 'http://timeout.url',
            'ResultURL'              => 'http://result.url',
        ];

        // Mock parameter normalization for merged
        $this->core->shouldReceive('normalizeParams')
              ->with(Mockery::on(function ($arg) {
                 return isset($arg['Initiator']) && $arg['Initiator'] === 'user';
            }), Mockery::type('array'))
            ->andReturn($expectedBody);

        // Mock the POST request
        $this->core->shouldReceive('makePostRequest')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedBody) {
                if ($arg['endpoint'] !== 'mpesa/b2b/v1/paymentrequest') return false;
                foreach ($expectedBody as $k => $v) {
                    if (!isset($arg['body'][$k]) || $arg['body'][$k] !== $v) return false;
                }
                return true;
            }), 'default')
            ->andReturn(['ResponseCode' => '0', 'ResponseDescription' => 'Success']);

        // Instantiate and run
        $b2b = new Pay($this->core);
        $response = $b2b->submit([
            'amount' => 1000,
            'party_b' => '600001'
        ]);

        $this->assertEquals(['ResponseCode' => '0', 'ResponseDescription' => 'Success'], $response);
    }
}
