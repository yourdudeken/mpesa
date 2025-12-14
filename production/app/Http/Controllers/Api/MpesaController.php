<?php

namespace App\Http\Controllers\Api;

use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class MpesaController extends Controller
{
    protected $mpesaService;
    
    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }
    
    /**
     * Initiate STK Push
     */
    public function stkPush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'account_reference' => 'nullable|string|max:12',
            'transaction_desc' => 'nullable|string|max:13',
        ]);
        
        $result = $this->mpesaService->stkPush($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Query STK Push Status
     */
    public function stkQuery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkout_request_id' => 'required|string',
        ]);
        
        $result = $this->mpesaService->stkQuery($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Register C2B URLs
     */
    public function c2bRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'confirmation_url' => 'required|url',
            'validation_url' => 'required|url',
            'response_type' => 'nullable|in:Completed,Cancelled',
        ]);
        
        $result = $this->mpesaService->c2bRegister($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Simulate C2B Payment
     */
    public function c2bSimulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'bill_ref_number' => 'nullable|string',
        ]);
        
        $result = $this->mpesaService->c2bSimulate($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * B2C Payment
     */
    public function b2c(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'remarks' => 'nullable|string',
            'occasion' => 'nullable|string',
            'command_id' => 'nullable|in:BusinessPayment,SalaryPayment,PromotionPayment',
        ]);
        
        $result = $this->mpesaService->b2c($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * B2B Payment
     */
    public function b2b(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'receiver_shortcode' => 'required|string',
            'account_reference' => 'nullable|string',
            'remarks' => 'nullable|string',
            'command_id' => 'nullable|in:BusinessPayBill,BusinessBuyGoods,DisburseFundsToBusiness,BusinessToBusinessTransfer,MerchantToMerchantTransfer',
        ]);
        
        $result = $this->mpesaService->b2b($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Account Balance
     */
    public function accountBalance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string',
            'identifier_type' => 'nullable|in:1,2,4',
        ]);
        
        $result = $this->mpesaService->accountBalance($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Transaction Status
     */
    public function transactionStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'identifier_type' => 'nullable|in:1,2,4',
            'remarks' => 'nullable|string',
        ]);
        
        $result = $this->mpesaService->transactionStatus($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Reversal
     */
    public function reversal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'receiver_identifier_type' => 'nullable|in:1,2,4,11',
            'remarks' => 'nullable|string',
        ]);
        
        $result = $this->mpesaService->reversal($validated);
        
        return response()->json($result, $result['success'] ? 200 : 400);
    }
    
    /**
     * Handle STK Push Callback
     */
    public function stkCallback(Request $request): JsonResponse
    {
        // Log the callback
        \Log::info('STK Push Callback', $request->all());
        
        // Process the callback data
        $data = $request->all();
        
        // TODO: Store in database, trigger events, etc.
        
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }
    
    /**
     * Handle C2B Callback
     */
    public function c2bCallback(Request $request): JsonResponse
    {
        // Log the callback
        \Log::info('C2B Callback', $request->all());
        
        // Process the callback data
        $data = $request->all();
        
        // TODO: Store in database, trigger events, etc.
        
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }
    
    /**
     * Handle B2C Callback
     */
    public function b2cCallback(Request $request): JsonResponse
    {
        // Log the callback
        \Log::info('B2C Callback', $request->all());
        
        // Process the callback data
        $data = $request->all();
        
        // TODO: Store in database, trigger events, etc.
        
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }
}
