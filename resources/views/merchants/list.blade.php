@extends('layout')

@section('title', 'Manage Merchants - M-Pesa Gateway')

@section('styles')
<style>
    .merchants-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .merchant-card {
        background: rgba(30, 41, 59, 0.8);
        backdrop-filter: blur(20px);
        border-radius: var(--radius);
        padding: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .merchant-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .merchant-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .merchant-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .merchant-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--white);
        margin-bottom: 0.25rem;
    }

    .merchant-id {
        font-size: 0.85rem;
        color: var(--gray-light);
        font-family: monospace;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: rgba(16, 185, 129, 0.2);
        color: var(--primary-light);
        border: 1px solid var(--success);
    }

    .status-inactive {
        background: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
        border: 1px solid var(--danger);
    }

    .merchant-info {
        margin: 1.5rem 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--gray-light);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .info-value {
        color: var(--white);
        font-size: 0.85rem;
        font-weight: 600;
    }

    .env-badge {
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .env-sandbox {
        background: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
        border: 1px solid var(--warning);
    }

    .env-production {
        background: rgba(99, 102, 241, 0.2);
        color: #a5b4fc;
        border: 1px solid var(--secondary);
    }

    .merchant-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .btn-sm {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger), var(--danger-dark));
        color: var(--white);
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 30px rgba(239, 68, 68, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
        color: var(--white);
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 30px rgba(245, 158, 11, 0.4);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-light);
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .api-key-display {
        background: rgba(15, 23, 42, 0.8);
        padding: 1rem;
        border-radius: var(--radius-sm);
        margin-top: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .api-key-text {
        font-family: monospace;
        font-size: 0.8rem;
        color: var(--primary-light);
        word-break: break-all;
        margin-bottom: 0.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: rgba(30, 41, 59, 0.8);
        backdrop-filter: blur(20px);
        border-radius: var(--radius);
        padding: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        color: var(--gray-light);
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .merchants-grid {
            grid-template-columns: 1fr;
        }

        .merchant-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="header">
        <div class="logo">
            <div class="logo-icon">M</div>
        </div>
        <h1>Merchant Management</h1>
        <p>Manage all your M-Pesa merchant accounts</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $merchants->count() }}</div>
            <div class="stat-label">Total Merchants</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $merchants->where('is_active', true)->count() }}</div>
            <div class="stat-label">Active Merchants</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $merchants->where('environment', 'production')->count() }}</div>
            <div class="stat-label">Production</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $merchants->where('environment', 'sandbox')->count() }}</div>
            <div class="stat-label">Sandbox</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2>All Merchants</h2>
                    <p>View and manage your merchant accounts</p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="/" class="btn btn-primary">
                        Add New Merchant
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>

        @if($merchants->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">[STORE]</div>
                <h3 style="margin-bottom: 0.5rem;">No merchants yet</h3>
                <p>Create your first merchant account to get started</p>
                <a href="/" class="btn btn-primary" style="margin-top: 1.5rem;">
                    Create First Merchant
                </a>
            </div>
        @else
            <div class="merchants-grid">
                @foreach($merchants as $merchant)
                <div class="merchant-card" id="merchant-{{ $merchant->id }}">
                    <div class="merchant-header">
                        <div>
                            <div class="merchant-name">{{ $merchant->merchant_name }}</div>
                            <div class="merchant-id">#{{ $merchant->id }}</div>
                        </div>
                        <span class="status-badge {{ $merchant->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $merchant->is_active ? '● Active' : '○ Inactive' }}
                        </span>
                    </div>

                    <div class="merchant-info">
                        <div class="info-row">
                            <span class="info-label">Environment</span>
                            <span class="env-badge {{ $merchant->environment === 'production' ? 'env-production' : 'env-sandbox' }}">
                                {{ ucfirst($merchant->environment) }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Shortcode</span>
                            <span class="info-value">{{ $merchant->mpesa_shortcode }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Created</span>
                            <span class="info-value">{{ $merchant->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Used</span>
                            <span class="info-value">
                                {{ $merchant->last_used_at ? $merchant->last_used_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                    </div>

                    <div class="merchant-actions">
                        <a 
                            href="{{ route('merchants.edit', $merchant->id) }}" 
                            class="btn btn-sm btn-primary"
                            style="text-decoration: none; text-align: center;"
                        >
                            Edit
                        </a>
                        <button 
                            onclick="toggleStatus({{ $merchant->id }}, {{ $merchant->is_active ? 'true' : 'false' }})" 
                            class="btn btn-sm {{ $merchant->is_active ? 'btn-warning' : 'btn-secondary' }}"
                        >
                            {{ $merchant->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button 
                            onclick="regenerateKey({{ $merchant->id }})" 
                            class="btn btn-sm btn-secondary"
                        >
                            New API Key
                        </button>
                        <button 
                            onclick="deleteMerchant({{ $merchant->id }}, '{{ $merchant->merchant_name }}')" 
                            class="btn btn-sm btn-danger"
                        >
                            Delete
                        </button>
                    </div>

                    <div id="api-key-{{ $merchant->id }}" style="display: none;" class="api-key-display">
                        <div class="api-key-text" id="api-key-text-{{ $merchant->id }}"></div>
                        <button onclick="copyApiKey({{ $merchant->id }})" class="btn btn-sm btn-primary btn-block">
                            Copy API Key
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    const alertContainer = document.getElementById('alertContainer');

    function showAlert(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        const icon = type === 'success' ? '[OK]' : '[X]';
        
        alertContainer.innerHTML = `
            <div class="alert ${alertClass}">
                <span class="alert-icon">${icon}</span>
                <div>${message}</div>
            </div>
        `;

        setTimeout(() => {
            alertContainer.innerHTML = '';
        }, 5000);
    }

    async function toggleStatus(merchantId, isActive) {
        try {
            const response = await fetch(`/merchants/${merchantId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();

            if (result.success) {
                showAlert(`Merchant ${result.data.is_active ? 'activated' : 'deactivated'} successfully`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(result.message, 'error');
            }
        } catch (error) {
            showAlert('Failed to update merchant status', 'error');
        }
    }

    async function regenerateKey(merchantId) {
        if (!confirm('Are you sure you want to regenerate the API key? The old key will stop working immediately.')) {
            return;
        }

        try {
            const response = await fetch(`/merchants/${merchantId}/regenerate-key`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();

            if (result.success) {
                const apiKeyDiv = document.getElementById(`api-key-${merchantId}`);
                const apiKeyText = document.getElementById(`api-key-text-${merchantId}`);
                
                apiKeyText.textContent = result.data.api_key;
                apiKeyDiv.style.display = 'block';
                
                showAlert('API key regenerated successfully! Save it now.');
            } else {
                showAlert(result.message, 'error');
            }
        } catch (error) {
            showAlert('Failed to regenerate API key', 'error');
        }
    }

    async function deleteMerchant(merchantId, merchantName) {
        if (!confirm(`Are you sure you want to delete "${merchantName}"? This action cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch(`/merchants/${merchantId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();

            if (result.success) {
                showAlert('Merchant deleted successfully');
                document.getElementById(`merchant-${merchantId}`).remove();
                
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(result.message, 'error');
            }
        } catch (error) {
            showAlert('Failed to delete merchant', 'error');
        }
    }

    function copyApiKey(merchantId) {
        const apiKeyText = document.getElementById(`api-key-text-${merchantId}`).textContent;
        navigator.clipboard.writeText(apiKeyText).then(() => {
            showAlert('API Key copied to clipboard!');
        }).catch(() => {
            showAlert('Failed to copy API key', 'error');
        });
    }
</script>
@endsection
