<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Merchant;

class MerchantAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the first merchant (the one used for authentication)
        $firstMerchant = Merchant::orderBy('id', 'asc')->first();
        
        // If no merchant exists, allow access to create the first one
        if (!$firstMerchant) {
            return $next($request);
        }
        
        // Get HTTP Basic Auth credentials
        $username = $request->getUser();
        $password = $request->getPassword();
        
        // Check if credentials are provided
        if (!$username || !$password) {
            return $this->unauthorized();
        }
        
        // Verify credentials against the first merchant
        // Consumer key is username, consumer secret is password
        if ($username === $firstMerchant->mpesa_consumer_key && 
            $password === $firstMerchant->mpesa_consumer_secret) {
            return $next($request);
        }
        
        return $this->unauthorized();
    }
    
    /**
     * Return unauthorized response
     */
    private function unauthorized(): Response
    {
        return response('Unauthorized', 401)
            ->header('WWW-Authenticate', 'Basic realm="M-Pesa Merchant Portal"');
    }
}
