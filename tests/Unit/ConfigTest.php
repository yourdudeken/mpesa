<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Yourdudeken\Mpesa\Tests\TestCase;
use Yourdudeken\Mpesa\Engine\Config;

class ConfigTest extends TestCase
{
    public function testCanLoadConfiguration()
    {
        $config = new Config(['key' => 'value']);
        $this->assertEquals('value', $config->get('key'));
    }

    public function testCanAccessNestedConfigurationDOTNotation()
    {
        $config = new Config(['nested' => ['key' => 'value']]);
        $this->assertEquals('value', $config->get('nested.key'));
    }

    public function testFallsBackToDefaultValue()
    {
        $config = new Config([]);
        $this->assertEquals('default', $config->get('non_existent', 'default'));
    }
    
    public function testMpesaPrefixIsStripped()
    {
        $config = new Config(['key' => 'value']);
        $this->assertEquals('value', $config->get('mpesa.key'));
    }

    public function testCredentialsNormalization()
    {
        $config = new Config([
            'auth' => [
                'consumer_key' => 'key',
                'consumer_secret' => 'secret'
            ]
        ]);
        
        $this->assertEquals('key', $config->get('apps.default.consumer_key'));
        $this->assertEquals('secret', $config->get('apps.default.consumer_secret'));
    }
}
