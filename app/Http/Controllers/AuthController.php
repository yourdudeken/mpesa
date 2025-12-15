<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Session::has('authenticated')) {
            return redirect('/');
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
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
        ]);
        
        // Find merchant with matching credentials
        $merchants = Merchant::all();
        
        foreach ($merchants as $merchant) {
            if ($merchant->mpesa_consumer_key === $validated['consumer_key'] && 
                $merchant->mpesa_consumer_secret === $validated['consumer_secret']) {
                
                // Set session
                Session::put('authenticated', true);
                Session::put('merchant_id', $merchant->id);
                
                return redirect('/')->with('success', 'Login successful');
            }
        }
        
        return back()->withErrors(['credentials' => 'Invalid consumer key or secret']);
    }
    
    /**
     * Handle signup (create first merchant)
     */
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'merchant_name' => 'required|string|max:255|unique:merchants,merchant_name',
            'mpesa_shortcode' => 'required|string|max:20',
            'mpesa_passkey' => 'required|string',
            'mpesa_initiator_name' => 'required|string|max:255',
            'mpesa_initiator_password' => 'required|string',
            'mpesa_consumer_key' => 'required|string',
            'mpesa_consumer_secret' => 'required|string',
            'environment' => 'required|in:sandbox,production',
        ]);
        
        // Create merchant
        $merchant = Merchant::create($validated);
        
        // Auto-login
        Session::put('authenticated', true);
        Session::put('merchant_id', $merchant->id);
        
        return redirect('/')->with('success', 'Account created successfully')
                           ->with('api_key', $merchant->api_key);
    }
    
    /**
     * Handle logout
     */
    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
