<?php

namespace Yourdudeken\Mpesa\Engine;

use Yourdudeken\Mpesa\AccountBalance\Balance;
use Yourdudeken\Mpesa\B2B\Pay;
use Yourdudeken\Mpesa\B2C\Pay as B2CPay;
use Yourdudeken\Mpesa\B2Pochi\Pay as B2PochiPay;
use Yourdudeken\Mpesa\C2B\Register;
use Yourdudeken\Mpesa\C2B\Simulate;
use Yourdudeken\Mpesa\Reversal\Reversal;
use Yourdudeken\Mpesa\TransactionStatus\TransactionStatus;
use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKPush;
use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKStatusQuery;

trait MpesaTrait{
    public function STKPush($params = [],$appName='default'){
        $stk = new STKPush($this->engine);
        return $stk->submit($params,$appName);
    }

    public function STKStatus($params = [],$appName='default'){
        $stk = new STKStatusQuery($this->engine);
        return $stk->submit($params,$appName);
    }

    public function C2BRegister($params = [],$appName='default'){
        $c2b = new Register($this->engine);
        return $c2b->submit($params,$appName);
    }

    public function C2BSimulate($params = [],$appName='default'){
        $simulate = new Simulate($this->engine);
        return $simulate->submit($params,$appName);
    }

    public function B2C($params = [],$appName='default'){
        $b2c = new B2CPay($this->engine);
        return $b2c->submit($params,$appName);
    }

    public function B2B($params = [],$appName='default'){
        $b2c = new Pay($this->engine);
        return $b2c->submit($params,$appName);
    }

    public function B2Pochi($params = [],$appName='default'){
        $b2pochi = new B2PochiPay($this->engine);
        return $b2pochi->submit($params,$appName);
    }

    public function accountBalance($params = [],$appName='default'){
       $bl = new Balance($this->engine);
       return $bl->submit($params,$appName);
    }

    public function reversal($params = [],$appName='default'){
        $rv = new Reversal($this->engine);
        return $rv->submit($params,$appName);
    }

    public function transactionStatus($params = [],$appName='default'){
        $tn = new TransactionStatus($this->engine);
        return $tn->submit($params,$appName);
    }
}
