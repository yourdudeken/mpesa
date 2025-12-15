@extends('layout')

@section('title', 'Login - M-Pesa Gateway')

@section('content')
<div class="container">
    <div class="header">
        <div class="logo">
            <div class="logo-icon">M</div>
        </div>
        <h1>M-Pesa Gateway</h1>
        <p>Login to manage your merchants</p>
    </div>

    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-header">
            <h2>Login</h2>
            <p>Enter your M-Pesa consumer credentials</p>
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

        @if($errors->has('credentials'))
            <div class="alert alert-error">
                <span class="alert-icon">[X]</span>
                <div>{{ $errors->first('credentials') }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">
                    Consumer Key
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="consumer_key" 
                    class="form-input" 
                    placeholder="Enter your M-Pesa consumer key"
                    value="{{ old('consumer_key') }}"
                    required
                    autofocus
                >
                @error('consumer_key')
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
                    name="consumer_secret" 
                    class="form-input" 
                    placeholder="Enter your M-Pesa consumer secret"
                    required
                >
                @error('consumer_secret')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Login
            </button>

            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: var(--gray-light);">
                    Don't have an account?
                    <a href="{{ route('signup') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Sign Up
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
