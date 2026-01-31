<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Mockery;
use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Contracts\ConfigurationStore;
use Yourdudeken\Mpesa\Contracts\CacheStore;
use Yourdudeken\Mpesa\Contracts\HttpRequest;
use Yourdudeken\Mpesa\Auth\Authenticator;

class CoreTest extends TestCase
{
    protected $config;
    protected $cache;
    protected $httpClient;
    protected $auth;
    protected $core;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = Mockery::mock(ConfigurationStore::class);
        $this->cache = Mockery::mock(CacheStore::class);
        $this->httpClient = Mockery::mock(HttpRequest::class);
        $this->auth = Mockery::mock(Authenticator::class);
        
        // Setup default config behavior for constructor
        $this->config->shouldReceive('get')->with('mpesa.apiUrl', 'https://sandbox.safaricom.co.ke/')->andReturn('https://sandbox.safaricom.co.ke/');
        
        $this->core = new Core($this->config, $this->cache, $this->httpClient, $this->auth);
    }

    public function testNormalizeParams()
    {
        $params = [
            'amount' => 100,
            'phone' => '254712345678',
            'remarks' => ' Test Remarks ',
            'transaction_desc' => 'Description'
        ];
        
        $normalized = $this->core->normalizeParams($params);
        
        $this->assertArrayHasKey('Amount', $normalized);
        $this->assertEquals(100, $normalized['Amount']);
        
        $this->assertArrayHasKey('PhoneNumber', $normalized);
        $this->assertEquals('254712345678', $normalized['PhoneNumber']);
        
        $this->assertArrayHasKey('Remarks', $normalized);
        $this->assertEquals('Test Remarks', $normalized['Remarks']); // Truncated/Trimmed
        
        $this->assertArrayHasKey('TransactionDesc', $normalized);
        $this->assertEquals('Description', $normalized['TransactionDesc']);
    }

    public function testPrepareBodySanitization()
    {
        // Test truncation
        $longString = str_repeat('a', 150);
        $params = ['remarks' => $longString];
        
        $normalized = $this->core->normalizeParams($params);
        
        $this->assertEquals(100, strlen($normalized['Remarks']));
    }

    public function testMakePostRequest()
    {
        $endpoint = 'mpesa/stkpush/v1/processrequest';
        $body = ['key' => 'value'];
        
        $this->auth->shouldReceive('authenticate')->with('default')->andReturn('access_token');
        
        $this->httpClient->shouldReceive('reset')->once();
        $this->httpClient->shouldReceive('setOption')->andReturn($this->httpClient);
        $this->httpClient->shouldReceive('execute')->andReturn(json_encode(['ResponseCode' => '0']));
        $this->httpClient->shouldReceive('getInfo')->with(CURLINFO_HTTP_CODE)->andReturn(200);
        $this->httpClient->shouldReceive('error')->andReturn('');
        $this->config->shouldReceive('get')->with('mpesa.ca_bundle')->andReturn(null);

        $response = $this->core->makePostRequest([
            'endpoint' => $endpoint,
            'body' => $body
        ]);
        
        $this->assertEquals('0', $response->ResponseCode);
    }
}
