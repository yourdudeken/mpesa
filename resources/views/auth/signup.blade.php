@extends('layout')

@section('title', 'Sign Up - M-Pesa Gateway')

@section('content')
<div class="container">
    <div class="header">
        <div class="logo">
            <div class="logo-icon">M</div>
        </div>
        <h1>M-Pesa Gateway</h1>
        <p>Create your merchant account</p>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <h2>Sign Up</h2>
            <p>Create your first merchant account. All sensitive data is encrypted.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">
                <span class="alert-icon">[X]</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('signup.submit') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">
                    Merchant Name
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="merchant_name" 
                    class="form-input" 
                    placeholder="e.g., My Business Ltd"
                    value="{{ old('merchant_name') }}"
                    required
                    autofocus
                >
                @error('merchant_name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    Environment
                    <span class="required">*</span>
                </label>
                <select name="environment" class="form-select" required>
                    <option value="sandbox" {{ old('environment') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                    <option value="production" {{ old('environment') == 'production' ? 'selected' : '' }}>Production (Live)</option>
                </select>
                @error('environment')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    M-Pesa Shortcode
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="mpesa_shortcode" 
                    class="form-input" 
                    placeholder="e.g., 174379"
                    value="{{ old('mpesa_shortcode') }}"
                    required
                >
                @error('mpesa_shortcode')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    M-Pesa Passkey
                    <span class="required">*</span>
                </label>
                <input 
                    type="password" 
                    name="mpesa_passkey" 
                    class="form-input" 
                    placeholder="Your M-Pesa passkey"
                    required
                >
                @error('mpesa_passkey')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    Initiator Name
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="mpesa_initiator_name" 
                    class="form-input" 
                    placeholder="e.g., testapi"
                    value="{{ old('mpesa_initiator_name') }}"
                    required
                >
                @error('mpesa_initiator_name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    Initiator Password
                    <span class="required">*</span>
                </label>
                <input 
                    type="password" 
                    name="mpesa_initiator_password" 
                    class="form-input" 
                    placeholder="Your initiator password"
                    required
                >
                @error('mpesa_initiator_password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    Consumer Key
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="mpesa_consumer_key" 
                    class="form-input" 
                    placeholder="Your M-Pesa consumer key"
                    value="{{ old('mpesa_consumer_key') }}"
                    required
                >
                @error('mpesa_consumer_key')
                    <span class="error-text">{{ $message }}</span>
                @enderror
                <p style="color: var(--gray-light); font-size: 0.85rem; margin-top: 0.5rem;">
                    This will be your login username
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Consumer Secret
                    <span class="required">*</span>
                </label>
                <input 
                    type="password" 
                    name="mpesa_consumer_secret" 
                    class="form-input" 
                    placeholder="Your M-Pesa consumer secret"
                    required
                >
                @error('mpesa_consumer_secret')
                    <span class="error-text">{{ $message }}</span>
                @enderror
                <p style="color: var(--gray-light); font-size: 0.85rem; margin-top: 0.5rem;">
                    This will be your login password
                </p>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Create Account
            </button>

            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: var(--gray-light);">
                    Already have an account?
                    <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Login
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
