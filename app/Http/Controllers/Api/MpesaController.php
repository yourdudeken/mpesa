<?php

namespace App\Http\Controllers\Api;

use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa API Controller
 * 
 * Handles all M-Pesa API requests with validation and proper HTTP responses
 */
class MpesaController extends Controller
{
    protected $mpesaService;
    
    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }
    
    /**
     * Initiate STK Push
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stkPush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'account_reference' => 'nullable|string|max:12',
            'transaction_desc' => 'nullable|string|max:13',
            'callback_url' => 'nullable|url',
        ]);
        
        $result = $this->mpesaService->stkPush($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * Query STK Push Status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stkQuery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkout_request_id' => 'required|string',
        ]);
        
        $result = $this->mpesaService->stkQuery($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * Register C2B URLs
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function c2bRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'confirmation_url' => 'required|url',
            'validation_url' => 'required|url',
            'response_type' => 'nullable|in:Completed,Cancelled',
        ]);
        
        $result = $this->mpesaService->c2bRegister($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * Simulate C2B Payment
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function c2bSimulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'bill_ref_number' => 'nullable|string',
        ]);
        
        $result = $this->mpesaService->c2bSimulate($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * B2C Payment
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function b2c(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'remarks' => 'nullable|string',
            'occasion' => 'nullable|string',
            'command_id' => 'nullable|in:BusinessPayment,SalaryPayment,PromotionPayment',
            'result_url' => 'nullable|url',
            'timeout_url' => 'nullable|url',
        ]);
        
        $result = $this->mpesaService->b2c($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * B2B Payment
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function b2b(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'receiver_shortcode' => 'required|string',
            'account_reference' => 'nullable|string',
            'remarks' => 'nullable|string',
            'command_id' => 'nullable|in:BusinessPayBill,BusinessBuyGoods,DisburseFundsToBusiness,BusinessToBusinessTransfer,MerchantToMerchantTransfer',
            'result_url' => 'nullable|url',
            'timeout_url' => 'nullable|url',
        ]);
        
        $result = $this->mpesaService->b2b($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * Account Balance
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function accountBalance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string',
            'identifier_type' => 'nullable|in:1,2,4',
            'result_url' => 'nullable|url',
            'timeout_url' => 'nullable|url',
        ]);
        
        $result = $this->mpesaService->accountBalance($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * Transaction Status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function transactionStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'identifier_type' => 'nullable|in:1,2,4',
            'remarks' => 'nullable|string',
            'result_url' => 'nullable|url',
            'timeout_url' => 'nullable|url',
        ]);
        
        $result = $this->mpesaService->transactionStatus($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * Reversal
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function reversal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'receiver_identifier_type' => 'nullable|in:1,2,4,11',
            'remarks' => 'nullable|string',
            'result_url' => 'nullable|url',
            'timeout_url' => 'nullable|url',
        ]);
        
        $result = $this->mpesaService->reversal($validated);
        
        return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
    }
    
    /**
     * Handle STK Push Callback
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stkCallback(Request $request): JsonResponse
    {
        Log::info('STK Push Callback Received', $request->all());
        
        // TODO: Process callback data
        // - Store in database
        // - Trigger events
        // - Send notifications
        
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }
    
    /**
     * Handle C2B Callback
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function c2bCallback(Request $request): JsonResponse
    {
        Log::info('C2B Callback Received', $request->all());
        
        // TODO: Process callback data
        
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }
    
    /**
     * Handle B2C Callback
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function b2cCallback(Request $request): JsonResponse
    {
        Log::info('B2C Callback Received', $request->all());
        
        // TODO: Process callback data
        
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }
}
