<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Merchant;
use Illuminate\Support\Facades\Log;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from header
        $apiKey = $request->header('X-API-Key') ?? $request->header('Authorization');
        
        // Remove "Bearer " prefix if present
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }
        
        if (!$apiKey) {
            Log::warning('API request without API key', [
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-Key header.',
                'code' => 'MISSING_API_KEY'
            ], 401);
        }
        
        // Find merchant by API key
        $merchant = Merchant::where('api_key', $apiKey)->first();
        
        if (!$merchant) {
            Log::warning('API request with invalid API key', [
                'api_key' => substr($apiKey, 0, 20) . '...',
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key.',
                'code' => 'INVALID_API_KEY'
            ], 401);
        }
        
        // Check if merchant is active
        if (!$merchant->is_active) {
            Log::warning('API request with inactive merchant', [
                'merchant_id' => $merchant->id,
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Merchant account is inactive.',
                'code' => 'INACTIVE_MERCHANT'
            ], 403);
        }
        
        // Update last used timestamp
        $merchant->markAsUsed();
        
        // Attach merchant to request for use in controllers
        $request->merge(['merchant' => $merchant]);
        $request->attributes->set('merchant', $merchant);
        
        Log::info('API request authenticated', [
            'merchant_id' => $merchant->id,
            'merchant_name' => $merchant->merchant_name,
            'environment' => $merchant->environment,
            'path' => $request->path()
        ]);
        
        return $next($request);
    }
}
