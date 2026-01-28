<?php

namespace Yourdudeken\Mpesa;

use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Cache;
use Yourdudeken\Mpesa\Engine\Config;
use Yourdudeken\Mpesa\Auth\Authenticator;
use Yourdudeken\Mpesa\Engine\CurlRequest;

// Service Classes
use Yourdudeken\Mpesa\AccountBalance\Balance;
use Yourdudeken\Mpesa\B2B\Pay as B2BPay;
use Yourdudeken\Mpesa\B2C\Pay as B2CPay;
use Yourdudeken\Mpesa\B2Pochi\Pay as B2PochiPay;
use Yourdudeken\Mpesa\C2B\Register;
use Yourdudeken\Mpesa\C2B\Simulate;
use Yourdudeken\Mpesa\Reversal\Reversal;
use Yourdudeken\Mpesa\TransactionStatus\TransactionStatus;
use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKPush;
use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKStatusQuery;

/**
 * Class Mpesa
 *
 * The main entry point for the M-Pesa package.
 *
 * @category PHP
 * @author   Kennedy Muthengi <Kenmwendwamuthengi@gmail.com>
 */
class Init
{
    /** @var Core */
    protected Core $engine;

    // Services
    public STKPush $stk;
    public STKStatusQuery $stkStatus;
    public B2CPay $b2c;
    public B2BPay $b2b;
    public B2PochiPay $b2pochi;
    public Register $c2b;
    public Simulate $c2bSimulate;
    public Balance $balance;
    public Reversal $reversal;
    public TransactionStatus $status;

    /**
     * Mpesa constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $configStore = new Config($config);
        $cache = new Cache($configStore);
        $httpClient = new CurlRequest();
        $auth = new Authenticator($configStore, $cache, $httpClient);
        
        $this->engine = new Core($configStore, $cache, $httpClient, $auth);

        // Initialize Services
        $this->stk          = new STKPush($this->engine);
        $this->stkStatus    = new STKStatusQuery($this->engine);
        $this->b2c          = new B2CPay($this->engine);
        $this->b2b          = new B2BPay($this->engine);
        $this->b2pochi      = new B2PochiPay($this->engine);
        $this->c2b          = new Register($this->engine);
        $this->c2bSimulate  = new Simulate($this->engine);
        $this->balance      = new Balance($this->engine);
        $this->reversal     = new Reversal($this->engine);
        $this->status       = new TransactionStatus($this->engine);
    }

    /**
     * Get the core engine.
     * 
     * @return Core
     */
    public function getEngine(): Core
    {
        return $this->engine;
    }
}
