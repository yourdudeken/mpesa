<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Session::has('authenticated')) {
            return redirect('/merchants');
        }
        
        return view('auth.login');
    }
    
    /**
     * Show signup form
     */
    public function showSignup()
    {
        return view('auth.signup');
    }
    
    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'consumer_key' => 'required|string|max:255',
            'consumer_secret' => 'required|string|max:255',
        ]);
        
        // Find merchant with matching credentials using timing-safe comparison
        $merchants = Merchant::all();
        $authenticated = false;
        $merchantId = null;
        
        foreach ($merchants as $merchant) {
            // Use hash_equals for timing-safe comparison
            if (hash_equals($merchant->mpesa_consumer_key, $validated['consumer_key']) && 
                hash_equals($merchant->mpesa_consumer_secret, $validated['consumer_secret'])) {
                
                // Check if merchant is active
                if (!$merchant->is_active) {
                    \Log::warning('Login attempt for inactive merchant', [
                        'merchant_id' => $merchant->id,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                    
                    return back()->withErrors([
                        'credentials' => 'This merchant account is inactive. Please contact support.'
                    ]);
                }
                
                $authenticated = true;
                $merchantId = $merchant->id;
                break;
            }
        }
        
        if ($authenticated) {
            // Regenerate session ID to prevent session fixation
            $request->session()->regenerate();
            
            // Set session
            Session::put('authenticated', true);
            Session::put('merchant_id', $merchantId);
            
            // Log successful login
            \Log::info('Successful login', [
                'merchant_id' => $merchantId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return redirect('/merchants')->with('success', 'Login successful');
        }
        
        // Log failed login attempt
        \Log::warning('Failed login attempt', [
            'consumer_key' => substr($validated['consumer_key'], 0, 10) . '...',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Generic error message to prevent user enumeration
        return back()->withErrors([
            'credentials' => 'Invalid credentials. Please check your consumer key and secret.'
        ])->withInput($request->only('consumer_key'));
    }
    
    /**
     * Handle signup (create first merchant)
     */
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'merchant_name' => 'required|string|max:255|unique:merchants,merchant_name',
            'mpesa_shortcode' => 'required|string|max:20',
            'mpesa_passkey' => 'required|string|min:64|max:255',
            'mpesa_initiator_name' => 'required|string|max:255',
            'mpesa_initiator_password' => 'required|string|min:8|max:255',
            'mpesa_consumer_key' => 'required|string|min:20|max:255',
            'mpesa_consumer_secret' => 'required|string|min:20|max:255',
            'environment' => 'required|in:sandbox,production',
        ]);
        
        try {
            // Create merchant
            $merchant = Merchant::create($validated);
            
            // Regenerate session ID
            $request->session()->regenerate();
            
            // Auto-login
            Session::put('authenticated', true);
            Session::put('merchant_id', $merchant->id);
            
            // Log successful signup
            \Log::info('New merchant signup', [
                'merchant_id' => $merchant->id,
                'merchant_name' => $merchant->merchant_name,
                'environment' => $merchant->environment,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return redirect('/merchants')->with('success', 'Account created successfully')
                               ->with('api_key', $merchant->api_key);
        } catch (\Exception $e) {
            \Log::error('Signup failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to create account. Please try again.'
            ])->withInput();
        }
    }
    
    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $merchantId = Session::get('merchant_id');
        
        // Log logout
        \Log::info('User logout', [
            'merchant_id' => $merchantId,
            'ip' => $request->ip()
        ]);
        
        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
