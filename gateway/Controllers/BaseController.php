<?php

namespace Gateway\Controllers;

use Gateway\Core\Response;
use Yourdudeken\Mpesa\Init;

/**
 * Base Controller
 * Provides common functionality for all controllers
 */
abstract class BaseController
{
    protected $mpesa;
    protected $logDir;

    public function __construct()
    {
        $this->logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Get M-Pesa instance with configuration
     */
    protected function getMpesaInstance()
    {
        if ($this->mpesa) {
            return $this->mpesa;
        }

        $config = $this->getMpesaConfig();
        $this->mpesa = new Init($config);

        return $this->mpesa;
    }

    /**
     * Get M-Pesa configuration
     */
    protected function getMpesaConfig()
    {
        $isSandbox = $_ENV['MPESA_ENV'] === 'sandbox';
        
        return [
            'is_sandbox' => $isSandbox,
            'apiUrl' => $isSandbox 
                ? 'https://sandbox.safaricom.co.ke/' 
                : 'https://api.safaricom.co.ke/',
            
            'apps' => [
                'default' => [
                    'consumer_key' => $_ENV['MPESA_CONSUMER_KEY'],
                    'consumer_secret' => $_ENV['MPESA_CONSUMER_SECRET'],
                ]
            ],

            // Lipa Na M-Pesa Online (STK Push) Configuration
            'lnmo' => [
                'short_code' => $_ENV['MPESA_SHORTCODE'],
                'passkey' => $_ENV['MPESA_PASSKEY'],
                'callback' => $_ENV['APP_URL'] . '/api/v1/callbacks/stkpush',
                'default_transaction_type' => 'CustomerPayBillOnline'
            ],

            // C2B Configuration
            'c2b' => [
                'short_code' => $_ENV['MPESA_SHORTCODE'],
                'confirmation_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/c2b/confirmation',
                'validation_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/c2b/validation',
                'responseType' => 'Completed'
            ],

            // B2C Configuration
            'b2c' => [
                'short_code' => $_ENV['MPESA_SHORTCODE'],
                'initiator_name' => $_ENV['MPESA_INITIATOR_NAME'],
                'initiator_password' => $_ENV['MPESA_INITIATOR_PASSWORD'],
                'result_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/b2c/result',
                'timeout_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/b2c/timeout',
                'default_command_id' => 'BusinessPayment',
                'test_phone_number' => '254708374149' // Safaricom test number
            ],

            // B2B Configuration
            'b2b' => [
                'short_code' => $_ENV['MPESA_SHORTCODE'],
                'initiator_name' => $_ENV['MPESA_INITIATOR_NAME'],
                'initiator_password' => $_ENV['MPESA_INITIATOR_PASSWORD'],
                'result_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/b2b/result',
                'timeout_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/b2b/timeout',
                'default_command_id' => 'BusinessToBusinessTransfer'
            ],

            // Account Balance Configuration
            'balance' => [
                'short_code' => $_ENV['MPESA_SHORTCODE'],
                'initiator_name' => $_ENV['MPESA_INITIATOR_NAME'],
                'initiator_password' => $_ENV['MPESA_INITIATOR_PASSWORD'],
                'result_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/balance/result',
                'timeout_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/balance/timeout',
                'default_command_id' => 'AccountBalance'
            ],

            // Transaction Status Configuration
            'status' => [
                'short_code' => $_ENV['MPESA_SHORTCODE'],
                'initiator_name' => $_ENV['MPESA_INITIATOR_NAME'],
                'initiator_password' => $_ENV['MPESA_INITIATOR_PASSWORD'],
                'result_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/status/result',
                'timeout_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/status/timeout',
                'default_command_id' => 'TransactionStatusQuery'
            ],

            // Reversal Configuration
            'reversal' => [
                'short_code' => $_ENV['MPESA_SHORTCODE'],
                'initiator_name' => $_ENV['MPESA_INITIATOR_NAME'],
                'initiator_password' => $_ENV['MPESA_INITIATOR_PASSWORD'],
                'result_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/reversal/result',
                'timeout_url' => $_ENV['APP_URL'] . '/api/v1/callbacks/reversal/timeout',
                'default_command_id' => 'TransactionReversal'
            ],

            // Cache Configuration
            'cache_location' => __DIR__ . '/../../storage/cache'
        ];
    }

    /**
     * Log transaction
     */
    protected function logTransaction($type, $request, $response)
    {
        $logFile = $this->logDir . '/transactions.log';
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'request' => $request,
            'response' => $response,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        file_put_contents(
            $logFile,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * Log error
     */
    protected function logError($type, $exception)
    {
        $logFile = $this->logDir . '/errors.log';
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        file_put_contents(
            $logFile,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * Log callback
     */
    protected function logCallback($type, $data)
    {
        $logFile = $this->logDir . '/callbacks.log';
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'data' => $data,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        file_put_contents(
            $logFile,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND
        );
    }
}
