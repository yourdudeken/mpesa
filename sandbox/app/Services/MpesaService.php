<?php

namespace App\Services;

use Yourdudeken\Mpesa\Init as MpesaSDK;

class MpesaService
{
    protected $mpesa;
    
    public function __construct()
    {
        // Initialize M-Pesa SDK with config from Laravel config
        $config = config('mpesa');
        $this->mpesa = new MpesaSDK($config);
    }
    
    /**
     * Initiate STK Push
     */
    public function stkPush(array $data)
    {
        try {
            $response = $this->mpesa->STKPush([
                'amount' => $data['amount'],
                'phoneNumber' => $data['phone_number'],
                'accountReference' => $data['account_reference'] ?? 'Payment',
                'transactionDesc' => $data['transaction_desc'] ?? 'Payment',
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * Query STK Push Status
     */
    public function stkQuery(array $data)
    {
        try {
            $response = $this->mpesa->STKStatus([
                'checkoutRequestID' => $data['checkout_request_id']
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * Register C2B URLs
     */
    public function c2bRegister(array $data)
    {
        try {
            $response = $this->mpesa->C2BRegister([
                'confirmationURL' => $data['confirmation_url'],
                'validationURL' => $data['validation_url'],
                'responseType' => $data['response_type'] ?? 'Completed'
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * Simulate C2B Payment
     */
    public function c2bSimulate(array $data)
    {
        try {
            $response = $this->mpesa->C2BSimulate([
                'amount' => $data['amount'],
                'phoneNumber' => $data['phone_number'],
                'billRefNumber' => $data['bill_ref_number'] ?? 'TEST'
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * B2C Payment
     */
    public function b2c(array $data)
    {
        try {
            $response = $this->mpesa->B2C([
                'amount' => $data['amount'],
                'partyB' => $data['phone_number'],
                'remarks' => $data['remarks'] ?? 'Payment',
                'occasion' => $data['occasion'] ?? 'Payment',
                'commandID' => $data['command_id'] ?? 'BusinessPayment'
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * B2B Payment
     */
    public function b2b(array $data)
    {
        try {
            $response = $this->mpesa->B2B([
                'amount' => $data['amount'],
                'partyB' => $data['receiver_shortcode'],
                'accountReference' => $data['account_reference'] ?? 'Payment',
                'remarks' => $data['remarks'] ?? 'Payment',
                'commandID' => $data['command_id'] ?? 'BusinessPayBill'
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * Account Balance
     */
    public function accountBalance(array $data)
    {
        try {
            $response = $this->mpesa->accountBalance([
                'remarks' => $data['remarks'] ?? 'Balance Query',
                'identifierType' => $data['identifier_type'] ?? 4
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * Transaction Status
     */
    public function transactionStatus(array $data)
    {
        try {
            $response = $this->mpesa->transactionStatus([
                'transactionID' => $data['transaction_id'],
                'identifierType' => $data['identifier_type'] ?? 4,
                'remarks' => $data['remarks'] ?? 'Status Query'
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
    
    /**
     * Reversal
     */
    public function reversal(array $data)
    {
        try {
            $response = $this->mpesa->reversal([
                'transactionID' => $data['transaction_id'],
                'amount' => $data['amount'],
                'recieverIdentifierType' => $data['receiver_identifier_type'] ?? 4,
                'remarks' => $data['remarks'] ?? 'Reversal'
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => json_decode($e->getMessage(), true)
            ];
        }
    }
}
