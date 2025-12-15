@extends('layout')

@section('title', 'Create Merchant - M-Pesa Gateway')

@section('content')
<div class="container">
    <div class="header">
        <div class="logo">
            <div class="logo-icon">M</div>
        </div>
        <h1>M-Pesa Gateway</h1>
        <p>Create your merchant account and start accepting payments</p>
    </div>


    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2>Register New Merchant</h2>
                    <p>Enter your M-Pesa credentials to create a merchant account. All sensitive data is encrypted.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <div id="alertContainer"></div>

        <form id="merchantForm">
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
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">
                    Environment
                    <span class="required">*</span>
                </label>
                <select name="environment" class="form-select" required>
                    <option value="sandbox">Sandbox (Testing)</option>
                    <option value="production">Production (Live)</option>
                </select>
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
                    required
                >
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
                    required
                >
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
                    required
                >
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
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Creating your merchant account...</p>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                Create Merchant Account
            </button>

            <a href="/merchants" class="btn btn-secondary btn-block" style="margin-top: 1rem;">
                View All Merchants
            </a>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('merchantForm');
    const submitBtn = document.getElementById('submitBtn');
    const loading = document.getElementById('loading');
    const alertContainer = document.getElementById('alertContainer');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Disable submit button and show loading
        submitBtn.disabled = true;
        loading.classList.add('active');
        alertContainer.innerHTML = '';

        // Get form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/merchants', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Show success message with API key
                alertContainer.innerHTML = `
                    <div class="alert alert-success">
                        <span class="alert-icon">[OK]</span>
                        <div>
                            <strong>Success!</strong> Merchant created successfully.
                        </div>
                    </div>
                    <div class="card" style="margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.1); border: 2px solid var(--success);">
                        <div style="text-align: center;">
                            <h3 style="margin-bottom: 1rem; color: var(--primary-light);">Your API Key</h3>
                            <div style="background: rgba(15, 23, 42, 0.8); padding: 1.5rem; border-radius: var(--radius-sm); margin-bottom: 1rem; word-break: break-all; font-family: monospace; font-size: 0.9rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                                ${result.data.api_key}
                            </div>
                            <p style="color: var(--gray-light); font-size: 0.9rem; margin-bottom: 1rem;">
                                [WARNING] Save this API key securely. You won't be able to see it again!
                            </p>
                            <button onclick="copyApiKey('${result.data.api_key}')" class="btn btn-primary">
                                Copy API Key
                            </button>
                        </div>
                    </div>
                `;

                // Reset form
                form.reset();

                // Scroll to top to see the API key
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Show error message
                let errorMessage = result.message || 'Failed to create merchant';
                
                if (result.errors) {
                    errorMessage += '<ul style="margin-top: 0.5rem; margin-left: 1.5rem;">';
                    for (const [field, messages] of Object.entries(result.errors)) {
                        messages.forEach(msg => {
                            errorMessage += `<li>${msg}</li>`;
                        });
                    }
                    errorMessage += '</ul>';
                }

                alertContainer.innerHTML = `
                    <div class="alert alert-error">
                        <span class="alert-icon">[X]</span>
                        <div>${errorMessage}</div>
                    </div>
                `;
            }
        } catch (error) {
            alertContainer.innerHTML = `
                <div class="alert alert-error">
                    <span class="alert-icon">[X]</span>
                    <div>
                        <strong>Error!</strong> Failed to create merchant. Please try again.
                    </div>
                </div>
            `;
        } finally {
            // Re-enable submit button and hide loading
            submitBtn.disabled = false;
            loading.classList.remove('active');
        }
    });

    function copyApiKey(apiKey) {
        navigator.clipboard.writeText(apiKey).then(() => {
            alert('API Key copied to clipboard!');
        }).catch(() => {
            alert('Failed to copy API key. Please copy it manually.');
        });
    }
</script>
@endsection
