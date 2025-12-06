<?php

namespace Yourdudeken\Mpesa\Api\Controllers;

use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKPush;
use Yourdudeken\Mpesa\LipaNaMpesaOnline\STKStatusQuery;
use Yourdudeken\Mpesa\B2C\Pay as B2CPay;
use Yourdudeken\Mpesa\B2B\Pay as B2BPay;
use Yourdudeken\Mpesa\C2B\Register;
use Yourdudeken\Mpesa\C2B\Simulate;
use Yourdudeken\Mpesa\AccountBalance\Balance;
use Yourdudeken\Mpesa\TransactionStatus\TransactionStatus;
use Yourdudeken\Mpesa\Reversal\Reversal;
use Yourdudeken\Mpesa\Engine\Core;
use Yourdudeken\Mpesa\Engine\Config;
use Yourdudeken\Mpesa\Engine\Cache;

class MpesaController extends BaseController
{
    private $engine;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->initializeEngine();
    }

    /**
     * Initialize M-Pesa engine
     */
    private function initializeEngine()
    {
        $mpesaConfig = new Config();
        $cache = new Cache($mpesaConfig);
        $this->engine = new Core($mpesaConfig, $cache);
    }

    /**
     * STK Push - Initiate payment request
     * POST /api/stk-push
     */
    public function stkPush()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'amount',
            'phoneNumber',
            'accountReference',
            'transactionDesc',
            'callBackURL'
        ]);

        try {
            $stkPush = new STKPush($this->engine);
            $result = $stkPush->submit($data);
            
            $this->log('STK Push initiated', 'info', [
                'phone' => $data['phoneNumber'],
                'amount' => $data['amount']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('STK Push failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'STK_PUSH_ERROR', 500);
        }
    }

    /**
     * STK Push Query - Check payment status
     * POST /api/stk-query
     */
    public function stkQuery()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, ['CheckoutRequestID']);

        try {
            $stkQuery = new STKStatusQuery($this->engine);
            $result = $stkQuery->submit($data);
            
            $this->log('STK Query executed', 'info', [
                'checkout_request_id' => $data['CheckoutRequestID']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('STK Query failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'STK_QUERY_ERROR', 500);
        }
    }

    /**
     * B2C Payment - Business to Customer payment
     * POST /api/b2c
     */
    public function b2c()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'amount',
            'partyB',
            'remarks',
            'resultURL',
            'queueTimeOutURL'
        ]);

        try {
            $b2c = new B2CPay($this->engine);
            $result = $b2c->submit($data);
            
            $this->log('B2C payment initiated', 'info', [
                'recipient' => $data['partyB'],
                'amount' => $data['amount']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('B2C payment failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'B2C_ERROR', 500);
        }
    }

    /**
     * B2B Payment - Business to Business payment
     * POST /api/b2b
     */
    public function b2b()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'amount',
            'partyB',
            'accountReference',
            'remarks',
            'resultURL',
            'queueTimeOutURL'
        ]);

        try {
            $b2b = new B2BPay($this->engine);
            $result = $b2b->submit($data);
            
            $this->log('B2B payment initiated', 'info', [
                'recipient' => $data['partyB'],
                'amount' => $data['amount']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('B2B payment failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'B2B_ERROR', 500);
        }
    }

    /**
     * C2B Register - Register C2B URLs
     * POST /api/c2b/register
     */
    public function c2bRegister()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'confirmationURL',
            'validationURL'
        ]);

        try {
            $c2b = new Register($this->engine);
            $result = $c2b->submit($data);
            
            $this->log('C2B URLs registered', 'info', $data);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('C2B registration failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'C2B_REGISTER_ERROR', 500);
        }
    }

    /**
     * C2B Simulate - Simulate C2B payment
     * POST /api/c2b/simulate
     */
    public function c2bSimulate()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'amount',
            'phoneNumber',
            'billRefNumber'
        ]);

        try {
            $c2b = new Simulate($this->engine);
            $result = $c2b->submit($data);
            
            $this->log('C2B payment simulated', 'info', [
                'phone' => $data['phoneNumber'],
                'amount' => $data['amount']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('C2B simulation failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'C2B_SIMULATE_ERROR', 500);
        }
    }

    /**
     * Account Balance - Check account balance
     * POST /api/balance
     */
    public function balance()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'partyB',
            'remarks',
            'resultURL',
            'queueTimeOutURL'
        ]);

        try {
            $balance = new Balance($this->engine);
            $result = $balance->submit($data);
            
            $this->log('Balance check initiated', 'info', [
                'party' => $data['partyB']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('Balance check failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'BALANCE_ERROR', 500);
        }
    }

    /**
     * Transaction Status - Check transaction status
     * POST /api/transaction-status
     */
    public function transactionStatus()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'TransactionID',
            'partyB',
            'remarks',
            'resultURL',
            'queueTimeOutURL'
        ]);

        try {
            $status = new TransactionStatus($this->engine);
            $result = $status->submit($data);
            
            $this->log('Transaction status checked', 'info', [
                'transaction_id' => $data['TransactionID']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('Transaction status check failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'TRANSACTION_STATUS_ERROR', 500);
        }
    }

    /**
     * Reversal - Reverse a transaction
     * POST /api/reversal
     */
    public function reversal()
    {
        $data = $this->getJsonInput();
        
        $this->validateRequired($data, [
            'amount',
            'transactionID',
            'remarks',
            'resultURL',
            'queueTimeOutURL'
        ]);

        try {
            $reversal = new Reversal($this->engine);
            $result = $reversal->submit($data);
            
            $this->log('Reversal initiated', 'info', [
                'transaction_id' => $data['transactionID'],
                'amount' => $data['amount']
            ]);

            $this->sendResponse($result);
        } catch (\Exception $e) {
            $this->log('Reversal failed', 'error', ['error' => $e->getMessage()]);
            $this->sendError($e->getMessage(), 'REVERSAL_ERROR', 500);
        }
    }

    /**
     * Health check endpoint
     * GET /api/health
     */
    public function health()
    {
        $this->sendResponse([
            'status' => 'healthy',
            'service' => 'M-Pesa API',
            'version' => '1.0.0',
            'uptime' => sys_getloadavg()[0],
        ]);
    }
}
