/**
 * M-Pesa Payment System - Frontend Application
 * Supports all M-Pesa transaction types
 */

// Application State
const App = {
    currentPage: 'dashboard',
    transactions: [],
    stats: {},
    refreshInterval: null
};

// API Base URL
const API_BASE = 'api/payment.php';
const LOGS_API = 'api/logs.php';

// Page title mapping
const PAGE_TITLES = {
    'dashboard': 'Dashboard',
    'stk-push': 'STK Push',
    'stk-status': 'STK Status',
    'b2c': 'B2C Payment',
    'b2b': 'B2B Transfer',
    'b2pochi': 'B2Pochi Payment',
    'c2b': 'C2B Payments',
    'balance': 'Account Balance',
    'status': 'Transaction Status',
    'reversal': 'Transaction Reversal',
    'transactions': 'Transactions',
    'callbacks': 'Callback Logs'
};

// Initialize Application
document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initAllForms();
    initFilters();
    initRefreshButton();
    loadDashboard();
    startAutoRefresh();
});

/**
 * Navigation
 */
function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const page = item.dataset.page;
            switchPage(page);
        });
    });
}

function switchPage(page) {
    // Update navigation
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.toggle('active', item.dataset.page === page);
    });

    // Update pages
    document.querySelectorAll('.page').forEach(pageEl => {
        pageEl.classList.toggle('active', pageEl.id === page);
    });

    // Update page title
    document.getElementById('pageTitle').textContent = PAGE_TITLES[page] || page;

    // Load page data
    App.currentPage = page;
    loadPageData(page);
}

function loadPageData(page) {
    switch (page) {
        case 'dashboard':
            loadDashboard();
            break;
        case 'transactions':
            loadTransactions();
            break;
        case 'callbacks':
            loadCallbacks();
            break;
    }
}

/**
 * Dashboard
 */
async function loadDashboard() {
    try {
        // Load stats
        const statsResponse = await apiRequest('get_stats');
        if (statsResponse.success) {
            updateStats(statsResponse.data);
        }

        // Load recent transactions
        const transactionsResponse = await apiRequest('get_transactions', { limit: 10 });
        if (transactionsResponse.success) {
            App.transactions = transactionsResponse.data;
            updateRecentTransactions(transactionsResponse.data);
        }
    } catch (error) {
        console.error('Failed to load dashboard:', error);
        showToast('Failed to load dashboard data', 'error');
    }
}

function updateStats(stats) {
    App.stats = stats;

    document.getElementById('statSuccessful').textContent = stats.successful || 0;
    document.getElementById('statPending').textContent = stats.pending || 0;
    document.getElementById('statFailed').textContent = stats.failed || 0;
    document.getElementById('statTotalAmount').textContent =
        'KES ' + formatCurrency(stats.total_amount || 0);
}

function updateRecentTransactions(transactions) {
    const tbody = document.getElementById('recentTransactions');

    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No transactions yet</td></tr>';
        return;
    }

    tbody.innerHTML = transactions.map(tx => `
        <tr onclick="showTransactionDetails(${tx.id})" style="cursor: pointer;">
            <td>${formatDate(tx.created_at)}</td>
            <td>${formatPhone(tx.phone_number)}</td>
            <td>${tx.account_reference}</td>
            <td>KES ${formatCurrency(tx.amount)}</td>
            <td>${getStatusBadge(tx.status)}</td>
        </tr>
    `).join('');
}

/**
 * Form Handling
 */
function initAllForms() {
    const forms = document.querySelectorAll('.form');

    forms.forEach(form => {
        // Auto-format phone inputs
        const phoneInputs = form.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('blur', () => {
                input.value = formatPhoneInput(input.value);
            });
        });

        // Handle form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleFormSubmit(form);
        });
    });
}

async function handleFormSubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const resultDiv = form.parentElement.querySelector('.payment-result');
    const action = form.dataset.action;

    if (!action) {
        showToast('Form action not defined', 'error');
        return;
    }

    // Get form data
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
        // Show loading
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        if (resultDiv) resultDiv.style.display = 'none';

        // Submit request
        const response = await apiRequest(action, data);

        if (response.success) {
            // Show success
            if (resultDiv) {
                showPaymentResult(resultDiv, true, response);
            }
            form.reset();
            showToast(response.message || 'Request successful!', 'success');

            // Refresh dashboard if needed
            if (App.currentPage === 'dashboard') {
                setTimeout(loadDashboard, 2000);
            }
        } else {
            throw new Error(response.error || 'Request failed');
        }

    } catch (error) {
        console.error('Request error:', error);
        if (resultDiv) {
            showPaymentResult(resultDiv, false, { message: error.message });
        }
        showToast(error.message, 'error');
    } finally {
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
    }
}

function showPaymentResult(resultDiv, success, data) {
    const iconDiv = resultDiv.querySelector('.result-icon');
    const titleDiv = resultDiv.querySelector('.result-title');
    const messageDiv = resultDiv.querySelector('.result-message');
    const detailsDiv = resultDiv.querySelector('.result-details');

    resultDiv.className = 'payment-result ' + (success ? 'success' : 'error');

    if (success) {
        iconDiv.innerHTML = `
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-success);">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        `;
        titleDiv.textContent = 'Request Successful';
        messageDiv.textContent = data.message || 'Your request has been processed successfully';

        // Format response data
        const responseData = data.data || {};
        const details = Object.entries(responseData)
            .map(([key, value]) => {
                const label = key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
                return `<div style="margin-bottom: 0.5rem;"><strong>${label}:</strong> ${value || 'N/A'}</div>`;
            })
            .join('');

        detailsDiv.innerHTML = details || '<div>Request accepted and processing</div>';
    } else {
        iconDiv.innerHTML = `
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-error);">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        `;
        titleDiv.textContent = 'Request Failed';
        messageDiv.textContent = data.message || 'An error occurred while processing your request';
        detailsDiv.innerHTML = '';
    }

    resultDiv.style.display = 'block';
    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Transactions
 */
async function loadTransactions(status = null) {
    try {
        const params = { limit: 100 };
        if (status) params.status = status;

        const response = await apiRequest('get_transactions', params);

        if (response.success) {
            App.transactions = response.data;
            displayTransactions(response.data);
        }
    } catch (error) {
        console.error('Failed to load transactions:', error);
        showToast('Failed to load transactions', 'error');
    }
}

function displayTransactions(transactions) {
    const tbody = document.getElementById('allTransactions');

    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No transactions found</td></tr>';
        return;
    }

    tbody.innerHTML = transactions.map(tx => `
        <tr>
            <td>#${tx.id}</td>
            <td>${formatDate(tx.created_at)}</td>
            <td>${formatPhone(tx.phone_number)}</td>
            <td>${tx.account_reference}</td>
            <td>KES ${formatCurrency(tx.amount)}</td>
            <td>${tx.mpesa_receipt_number || '-'}</td>
            <td>${getStatusBadge(tx.status)}</td>
            <td>
                <button class="btn-text" onclick="showTransactionDetails(${tx.id})">View</button>
            </td>
        </tr>
    `).join('');
}

function showTransactionDetails(transactionId) {
    const transaction = App.transactions.find(tx => tx.id === transactionId);
    if (!transaction) return;

    const modal = document.getElementById('transactionModal');
    const modalBody = document.getElementById('modalBody');

    modalBody.innerHTML = `
        <div style="display: grid; gap: 1rem;">
            <div><strong>Transaction ID:</strong> #${transaction.id}</div>
            <div><strong>Checkout Request ID:</strong> ${transaction.checkout_request_id || 'N/A'}</div>
            <div><strong>Phone Number:</strong> ${formatPhone(transaction.phone_number)}</div>
            <div><strong>Amount:</strong> KES ${formatCurrency(transaction.amount)}</div>
            <div><strong>Account Reference:</strong> ${transaction.account_reference}</div>
            <div><strong>Description:</strong> ${transaction.transaction_desc || 'N/A'}</div>
            <div><strong>M-Pesa Receipt:</strong> ${transaction.mpesa_receipt_number || 'N/A'}</div>
            <div><strong>Status:</strong> ${getStatusBadge(transaction.status)}</div>
            <div><strong>Result:</strong> ${transaction.result_desc || 'Pending'}</div>
            <div><strong>Created:</strong> ${formatDateTime(transaction.created_at)}</div>
            <div><strong>Updated:</strong> ${formatDateTime(transaction.updated_at)}</div>
        </div>
    `;

    modal.classList.add('active');
}

/**
 * Callbacks
 */
async function loadCallbacks() {
    try {
        const response = await fetch(LOGS_API);
        const logs = await response.json();
        displayCallbacks(logs);
    } catch (error) {
        console.error('Failed to load callbacks:', error);
        showToast('Failed to load callback logs', 'error');
    }
}

function displayCallbacks(logs) {
    const container = document.getElementById('callbackLogs');

    if (!logs || logs.length === 0) {
        container.innerHTML = '<div class="empty-state">No callbacks received yet</div>';
        return;
    }

    container.innerHTML = logs.map((log, index) => {
        let displayData = log.data;
        try {
            if (log.data && log.data.trim() !== '') {
                displayData = JSON.stringify(JSON.parse(log.data), null, 2);
            } else {
                displayData = 'No payload received';
            }
        } catch (e) {
            console.warn('Failed to parse callback data:', log.data);
            displayData = log.data; // Show raw if not JSON
        }

        return `
            <div class="callback-entry">
                <div class="callback-header">
                    <strong>#${logs.length - index}</strong>
                    <span class="callback-time">${log.timestamp}</span>
                    <span class="callback-type-badge">${log.type}</span>
                </div>
                <div class="callback-body">
                    <pre><code>${escapeHtml(displayData)}</code></pre>
                </div>
            </div>
        `;
    }).join('');
}

document.getElementById('clearCallbacks')?.addEventListener('click', async () => {
    if (!confirm('Clear all callback logs?')) return;

    try {
        await fetch(LOGS_API + '?action=clear');
        loadCallbacks();
        showToast('Callback logs cleared', 'success');
    } catch (error) {
        showToast('Failed to clear logs', 'error');
    }
});

/**
 * Filters
 */
function initFilters() {
    const statusFilter = document.getElementById('statusFilter');

    statusFilter?.addEventListener('change', (e) => {
        const status = e.target.value;
        loadTransactions(status || null);
    });
}

/**
 * Refresh
 */
function initRefreshButton() {
    document.getElementById('refreshBtn')?.addEventListener('click', () => {
        loadPageData(App.currentPage);
        showToast('Refreshed', 'info');
    });
}

function startAutoRefresh() {
    // Refresh every 30 seconds
    App.refreshInterval = setInterval(() => {
        if (App.currentPage === 'dashboard') {
            loadDashboard();
        }
    }, 30000);
}

/**
 * Modal
 */
document.querySelector('.modal-close')?.addEventListener('click', () => {
    document.getElementById('transactionModal').classList.remove('active');
});

document.getElementById('transactionModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'transactionModal') {
        e.target.classList.remove('active');
    }
});

/**
 * API Helper
 */
async function apiRequest(action, data = {}) {
    const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, data })
    });

    if (!response.ok) {
        throw new Error('Network error');
    }

    return await response.json();
}

/**
 * Toast Notifications
 */
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';

    toast.innerHTML = `
        <div style="font-size: 1.25rem;">${icon}</div>
        <div>${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * Utility Functions
 */
function formatCurrency(amount) {
    return parseFloat(amount || 0).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatPhone(phone) {
    if (!phone) return 'N/A';
    return phone.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4');
}

function formatPhoneInput(phone) {
    phone = phone.replace(/\D/g, '');

    if (phone.length === 9 && phone[0] === '7') {
        return '254' + phone;
    }

    if (phone.length === 10 && phone.substring(0, 2) === '07') {
        return '254' + phone.substring(1);
    }

    return phone;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusBadge(status) {
    const statusMap = {
        completed: { label: 'Completed', class: 'completed' },
        pending: { label: 'Pending', class: 'pending' },
        failed: { label: 'Failed', class: 'failed' }
    };

    const statusInfo = statusMap[status] || { label: status, class: 'pending' };

    return `
        <span class="status-badge ${statusInfo.class}">
            <span class="status-dot"></span>
            ${statusInfo.label}
        </span>
    `;
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}
