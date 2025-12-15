@extends('layout')

@section('title', 'Edit Merchant - M-Pesa Gateway')

@section('content')
<div class="container">
    <div class="header">
        <div class="logo">
            <div class="logo-icon">M</div>
        </div>
        <h1>Edit Merchant</h1>
        <p>Update merchant credentials and settings</p>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <h2>Edit: {{ $merchant->merchant_name }}</h2>
            <p>Update M-Pesa credentials and environment settings</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">
                <span class="alert-icon">[X]</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <span class="alert-icon">[OK]</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('merchants.update', $merchant->id) }}">
            @csrf
            @method('PUT')
            
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
                    value="{{ old('merchant_name', $merchant->merchant_name) }}"
                    required
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
                    <option value="sandbox" {{ old('environment', $merchant->environment) == 'sandbox' ? 'selected' : '' }}>
                        Sandbox (Testing)
                    </option>
                    <option value="production" {{ old('environment', $merchant->environment) == 'production' ? 'selected' : '' }}>
                        Production (Live)
                    </option>
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
                    value="{{ old('mpesa_shortcode', $merchant->mpesa_shortcode) }}"
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
                    placeholder="Enter new passkey or leave current"
                    value="{{ old('mpesa_passkey', $merchant->mpesa_passkey) }}"
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
                    value="{{ old('mpesa_initiator_name', $merchant->mpesa_initiator_name) }}"
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
                    placeholder="Enter new password or leave current"
                    value="{{ old('mpesa_initiator_password', $merchant->mpesa_initiator_password) }}"
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
                    value="{{ old('mpesa_consumer_key', $merchant->mpesa_consumer_key) }}"
                    required
                >
                @error('mpesa_consumer_key')
                    <span class="error-text">{{ $message }}</span>
                @enderror
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
                    placeholder="Enter new secret or leave current"
                    value="{{ old('mpesa_consumer_secret', $merchant->mpesa_consumer_secret) }}"
                    required
                >
                @error('mpesa_consumer_secret')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    Update Merchant
                </button>
                <a href="{{ route('merchants.list') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
