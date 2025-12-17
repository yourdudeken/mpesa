<?php

namespace Mpesa\Tests\Unit;

use Mpesa\Tests\TestCase;
use Mpesa\Auth\Authenticator;
use Mpesa\Contracts\CacheStore;
use Mpesa\Contracts\ConfigurationStore;

class CoreTest extends TestCase
{
    /**
     * Test that the authenticator is set.
     *
     * @test
     **/
    public function testAuthSet()
    {
        $this->assertInstanceOf(Authenticator::class, $this->engine->auth);
    }

    /**
     * Test that the configuration store is set.
     *
     * @test
     **/
    public function testConfigStoreSet()
    {
        $this->assertInstanceOf(ConfigurationStore::class, $this->engine->config);
    }

    /**
     * Test that the cache store is set.
     *
     * @test
     **/
    public function testCacheSet()
    {
        $this->assertInstanceOf(CacheStore::class, $this->engine->cache);
    }
}
