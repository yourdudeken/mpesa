<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MerchantController extends Controller
{
    /**
     * Display the merchant registration form
     */
    public function index()
    {
        return view('merchants.index');
    }

    /**
     * Display all merchants (admin view)
     */
    public function list()
    {
        $merchants = Merchant::orderBy('created_at', 'desc')->get();
        return view('merchants.list', compact('merchants'));
    }

    /**
     * Store a new merchant
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'merchant_name' => 'required|string|max:255|unique:merchants,merchant_name',
                'mpesa_shortcode' => 'required|string|max:20',
                'mpesa_passkey' => 'required|string',
                'mpesa_initiator_name' => 'required|string|max:255',
                'mpesa_initiator_password' => 'required|string',
                'mpesa_consumer_key' => 'required|string',
                'mpesa_consumer_secret' => 'required|string',
                'environment' => 'nullable|in:sandbox,production',
            ]);

            // Set default environment if not provided
            $validated['environment'] = $validated['environment'] ?? 'sandbox';

            // Create merchant (encryption happens automatically in the model)
            $merchant = Merchant::create($validated);

            Log::info('Merchant created successfully', [
                'merchant_id' => $merchant->id,
                'merchant_name' => $merchant->merchant_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Merchant created successfully',
                'data' => [
                    'merchant_id' => $merchant->id,
                    'merchant_name' => $merchant->merchant_name,
                    'api_key' => $merchant->api_key,
                    'environment' => $merchant->environment,
                    'created_at' => $merchant->created_at->toISOString(),
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create merchant', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create merchant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get merchant details by API key
     */
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string'
        ]);

        $merchant = Merchant::findByApiKey($request->api_key);

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant not found or inactive'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'merchant_id' => $merchant->id,
                'merchant_name' => $merchant->merchant_name,
                'environment' => $merchant->environment,
                'is_active' => $merchant->is_active,
                'created_at' => $merchant->created_at->toISOString(),
                'last_used_at' => $merchant->last_used_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Regenerate API key for a merchant
     */
    public function regenerateApiKey(Request $request, $id): JsonResponse
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $newApiKey = $merchant->regenerateApiKey();

            Log::info('API key regenerated', [
                'merchant_id' => $merchant->id,
                'merchant_name' => $merchant->merchant_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'API key regenerated successfully',
                'data' => [
                    'api_key' => $newApiKey
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate API key',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle merchant active status
     */
    public function toggleStatus(Request $request, $id): JsonResponse
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $merchant->is_active = !$merchant->is_active;
            $merchant->save();

            return response()->json([
                'success' => true,
                'message' => 'Merchant status updated',
                'data' => [
                    'is_active' => $merchant->is_active
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update merchant status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a merchant
     */
    public function destroy($id): JsonResponse
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $merchantName = $merchant->merchant_name;
            $merchant->delete();

            Log::info('Merchant deleted', [
                'merchant_id' => $id,
                'merchant_name' => $merchantName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Merchant deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete merchant',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
