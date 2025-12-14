<?php

namespace App\Services;

use Yourdudeken\Mpesa\Init as MpesaSDK;
use Yourdudeken\Mpesa\Exceptions\MpesaException;
use Yourdudeken\Mpesa\Exceptions\ConfigurationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * M-Pesa Service
 * 
 * Gateway service to interact with the official M-Pesa API
 * Handles all M-Pesa operations with proper error handling and logging
 */
class MpesaService
{
    protected $mpesa;
    protected $config;
    
    public function __construct()
    {
        $this->config = config('mpesa');
        $this->mpesa = new MpesaSDK($this->config);
    }
    
    /**
     * Initiate STK Push (Lipa Na M-Pesa Online)
     * 
     * @param array $data
     * @return array
     */
    public function stkPush(array $data): array
    {
        try {
            Log::info('STK Push Request', ['data' => $this->sanitizeLogData($data)]);
            
            $params = [
                'amount' => $data['amount'],
                'phoneNumber' => $data['phone_number'],
                'accountReference' => $data['account_reference'] ?? 'Payment',
                'transactionDesc' => $data['transaction_desc'] ?? 'Payment',
            ];
            
            // Add callback URL if provided
            if (isset($data['callback_url'])) {
                $params['CallBackURL'] = $data['callback_url'];
            }
            
            $response = $this->mpesa->STKPush($params);
            
            Log::info('STK Push Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (ConfigurationException $e) {
            Log::error('STK Push Configuration Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), 422);
            
        } catch (MpesaException $e) {
            Log::error('STK Push M-Pesa Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('STK Push Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while processing your request', 500);
        }
    }
    
    /**
     * Query STK Push Status
     * 
     * @param array $data
     * @return array
     */
    public function stkQuery(array $data): array
    {
        try {
            Log::info('STK Query Request', ['checkout_request_id' => $data['checkout_request_id']]);
            
            $response = $this->mpesa->STKStatus([
                'checkoutRequestID' => $data['checkout_request_id']
            ]);
            
            Log::info('STK Query Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('STK Query Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('STK Query Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while querying transaction status', 500);
        }
    }
    
    /**
     * Register C2B URLs
     * 
     * @param array $data
     * @return array
     */
    public function c2bRegister(array $data): array
    {
        try {
            Log::info('C2B Register Request', ['urls' => $this->sanitizeLogData($data)]);
            
            $response = $this->mpesa->C2BRegister([
                'confirmationURL' => $data['confirmation_url'],
                'validationURL' => $data['validation_url'],
                'responseType' => $data['response_type'] ?? 'Completed'
            ]);
            
            Log::info('C2B Register Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('C2B Register Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('C2B Register Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while registering C2B URLs', 500);
        }
    }
    
    /**
     * Simulate C2B Payment
     * 
     * @param array $data
     * @return array
     */
    public function c2bSimulate(array $data): array
    {
        try {
            Log::info('C2B Simulate Request', ['data' => $this->sanitizeLogData($data)]);
            
            $response = $this->mpesa->C2BSimulate([
                'amount' => $data['amount'],
                'phoneNumber' => $data['phone_number'],
                'billRefNumber' => $data['bill_ref_number'] ?? 'TEST'
            ]);
            
            Log::info('C2B Simulate Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('C2B Simulate Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('C2B Simulate Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while simulating C2B payment', 500);
        }
    }
    
    /**
     * B2C Payment (Business to Customer)
     * 
     * @param array $data
     * @return array
     */
    public function b2c(array $data): array
    {
        try {
            Log::info('B2C Request', ['data' => $this->sanitizeLogData($data)]);
            
            $params = [
                'amount' => $data['amount'],
                'partyB' => $data['phone_number'],
                'remarks' => $data['remarks'] ?? 'Payment',
                'occasion' => $data['occasion'] ?? 'Payment',
                'commandID' => $data['command_id'] ?? 'BusinessPayment'
            ];
            
            // Add result and timeout URLs if provided
            if (isset($data['result_url'])) {
                $params['ResultURL'] = $data['result_url'];
            }
            if (isset($data['timeout_url'])) {
                $params['QueueTimeOutURL'] = $data['timeout_url'];
            }
            
            $response = $this->mpesa->B2C($params);
            
            Log::info('B2C Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('B2C Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('B2C Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while processing B2C payment', 500);
        }
    }
    
    /**
     * B2B Payment (Business to Business)
     * 
     * @param array $data
     * @return array
     */
    public function b2b(array $data): array
    {
        try {
            Log::info('B2B Request', ['data' => $this->sanitizeLogData($data)]);
            
            $params = [
                'amount' => $data['amount'],
                'partyB' => $data['receiver_shortcode'],
                'accountReference' => $data['account_reference'] ?? 'Payment',
                'remarks' => $data['remarks'] ?? 'Payment',
                'commandID' => $data['command_id'] ?? 'BusinessPayBill'
            ];
            
            // Add result and timeout URLs if provided
            if (isset($data['result_url'])) {
                $params['ResultURL'] = $data['result_url'];
            }
            if (isset($data['timeout_url'])) {
                $params['QueueTimeOutURL'] = $data['timeout_url'];
            }
            
            $response = $this->mpesa->B2B($params);
            
            Log::info('B2B Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('B2B Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('B2B Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while processing B2B payment', 500);
        }
    }
    
    /**
     * Query Account Balance
     * 
     * @param array $data
     * @return array
     */
    public function accountBalance(array $data): array
    {
        try {
            Log::info('Account Balance Request', ['data' => $data]);
            
            $params = [
                'remarks' => $data['remarks'] ?? 'Balance Query',
                'identifierType' => $data['identifier_type'] ?? 4
            ];
            
            // Add result and timeout URLs if provided
            if (isset($data['result_url'])) {
                $params['ResultURL'] = $data['result_url'];
            }
            if (isset($data['timeout_url'])) {
                $params['QueueTimeOutURL'] = $data['timeout_url'];
            }
            
            $response = $this->mpesa->accountBalance($params);
            
            Log::info('Account Balance Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('Account Balance Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('Account Balance Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while querying account balance', 500);
        }
    }
    
    /**
     * Query Transaction Status
     * 
     * @param array $data
     * @return array
     */
    public function transactionStatus(array $data): array
    {
        try {
            Log::info('Transaction Status Request', ['transaction_id' => $data['transaction_id']]);
            
            $params = [
                'transactionID' => $data['transaction_id'],
                'identifierType' => $data['identifier_type'] ?? 4,
                'remarks' => $data['remarks'] ?? 'Status Query'
            ];
            
            // Add result and timeout URLs if provided
            if (isset($data['result_url'])) {
                $params['ResultURL'] = $data['result_url'];
            }
            if (isset($data['timeout_url'])) {
                $params['QueueTimeOutURL'] = $data['timeout_url'];
            }
            
            $response = $this->mpesa->transactionStatus($params);
            
            Log::info('Transaction Status Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('Transaction Status Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('Transaction Status Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while querying transaction status', 500);
        }
    }
    
    /**
     * Reverse Transaction
     * 
     * @param array $data
     * @return array
     */
    public function reversal(array $data): array
    {
        try {
            Log::info('Reversal Request', ['data' => $this->sanitizeLogData($data)]);
            
            $params = [
                'transactionID' => $data['transaction_id'],
                'amount' => $data['amount'],
                'recieverIdentifierType' => $data['receiver_identifier_type'] ?? 4,
                'remarks' => $data['remarks'] ?? 'Reversal'
            ];
            
            // Add result and timeout URLs if provided
            if (isset($data['result_url'])) {
                $params['ResultURL'] = $data['result_url'];
            }
            if (isset($data['timeout_url'])) {
                $params['QueueTimeOutURL'] = $data['timeout_url'];
            }
            
            $response = $this->mpesa->reversal($params);
            
            Log::info('Reversal Response', ['response' => $response]);
            
            return $this->successResponse($response);
            
        } catch (MpesaException $e) {
            Log::error('Reversal Error', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('Reversal Error', ['error' => $e->getMessage()]);
            return $this->errorResponse('An error occurred while reversing transaction', 500);
        }
    }
    
    /**
     * Format success response
     * 
     * @param mixed $data
     * @return array
     */
    private function successResponse($data): array
    {
        return [
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];
    }
    
    /**
     * Format error response
     * 
     * @param string $message
     * @param int $code
     * @return array
     */
    private function errorResponse(string $message, int $code = 400): array
    {
        $error = json_decode($message, true);
        
        return [
            'success' => false,
            'message' => is_array($error) ? ($error[0] ?? $message) : $message,
            'error' => $error ?? $message,
            'code' => $code,
            'timestamp' => now()->toISOString()
        ];
    }
    
    /**
     * Sanitize sensitive data for logging
     * 
     * @param array $data
     * @return array
     */
    private function sanitizeLogData(array $data): array
    {
        $sanitized = $data;
        
        // Remove or mask sensitive fields
        $sensitiveFields = ['password', 'pin', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '***REDACTED***';
            }
        }
        
        return $sanitized;
    }
}
