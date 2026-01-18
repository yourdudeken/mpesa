// ============================================
// State Management
// ============================================
const state = {
    currentEndpoint: 'stkpush',
    isSandbox: true,
    isLoading: false,
    logInterval: null
};

// ============================================
// DOM Elements
// ============================================
const elements = {
    navItems: document.querySelectorAll('.nav-item'),
    endpointSections: document.querySelectorAll('.endpoint-section'),
    forms: document.querySelectorAll('.api-form'),
    responseContainer: document.getElementById('responseContainer'),
    responseContent: document.getElementById('responseContent'),
    copyButton: document.getElementById('copyResponse'),
    environmentToggle: document.getElementById('environmentToggle'),
    logsContainer: document.getElementById('logsContainer'),
    refreshLogsBtn: document.getElementById('refreshLogs'),
    clearLogsBtn: document.getElementById('clearLogs')
};

// ============================================
// Navigation Handler
// ============================================
function initNavigation() {
    elements.navItems.forEach(item => {
        item.addEventListener('click', () => {
            const endpoint = item.dataset.endpoint;
            switchEndpoint(endpoint);
        });
    });
}

function switchEndpoint(endpoint) {
    // Update state
    state.currentEndpoint = endpoint;

    // Update nav items
    elements.navItems.forEach(item => {
        if (item.dataset.endpoint === endpoint) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // Update sections
    elements.endpointSections.forEach(section => {
        if (section.id === endpoint) {
            section.classList.add('active');
        } else {
            section.classList.remove('active');
        }
    });

    // Handle Callbacks Tab
    if (endpoint === 'callbacks') {
        fetchLogs();
        startLogPolling();
        // Hide response container for callbacks tab as it has its own view
        elements.responseContainer.style.display = 'none';
    } else {
        stopLogPolling();
        elements.responseContainer.style.display = 'block';
    }
}

// ============================================
// Environment Toggle Handler
// ============================================
function initEnvironmentToggle() {
    elements.environmentToggle.addEventListener('change', (e) => {
        state.isSandbox = e.target.checked;
        const toggleText = e.target.parentElement.querySelector('.toggle-text');
        toggleText.textContent = state.isSandbox ? 'Sandbox Mode' : 'Production Mode';

        showNotification(
            state.isSandbox ? 'Switched to Sandbox Mode' : 'Switched to Production Mode',
            'info'
        );
    });
}

// ============================================
// Form Submission Handler
// ============================================
function initForms() {
    elements.forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (state.isLoading) return;

            const endpoint = form.dataset.endpoint;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Convert numeric fields
            if (data.amount) data.amount = parseFloat(data.amount);

            await submitRequest(endpoint, data, form);
        });
    });
}

// ============================================
// API Request Handler
// ============================================
async function submitRequest(endpoint, data, form) {
    const submitButton = form.querySelector('button[type="submit"]');

    try {
        state.isLoading = true;
        submitButton.classList.add('loading');
        submitButton.disabled = true;

        displayResponse({ message: 'Sending request...' }, 'info');

        const response = await fetch('api/handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                endpoint: endpoint,
                data: data,
                isSandbox: state.isSandbox
            })
        });

        const result = await response.json();

        if (response.ok) {
            displayResponse(result, 'success');
            showNotification('Request successful!', 'success');
        } else {
            displayResponse(result, 'error');
            showNotification('Request failed. Check response for details.', 'error');
        }

    } catch (error) {
        console.error('Request error:', error);
        displayResponse({
            error: 'Network error',
            message: error.message,
            details: 'Failed to connect to the API.'
        }, 'error');
        showNotification('Network error occurred', 'error');
    } finally {
        state.isLoading = false;
        submitButton.classList.remove('loading');
        submitButton.disabled = false;
    }
}

// ============================================
// Log Viewer Logic
// ============================================
function initLogViewer() {
    if (elements.refreshLogsBtn) {
        elements.refreshLogsBtn.addEventListener('click', () => {
            fetchLogs();
            showNotification('Refreshing logs...', 'info');
        });
    }

    if (elements.clearLogsBtn) {
        elements.clearLogsBtn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to clear all logs?')) return;
            await clearLogs();
        });
    }
}

async function fetchLogs() {
    const btn = elements.refreshLogsBtn;
    if (btn) btn.classList.add('loading');

    try {
        const response = await fetch('api/logs.php');
        const logs = await response.json();
        renderLogs(logs);
    } catch (error) {
        console.error('Failed to fetch logs', error);
        elements.logsContainer.innerHTML = `<div class="error-state">Failed to load logs: ${error.message}</div>`;
    } finally {
        if (btn) btn.classList.remove('loading');
    }
}

async function clearLogs() {
    try {
        await fetch('api/logs.php?action=clear');
        renderLogs([]);
        showNotification('Logs cleared', 'success');
    } catch (error) {
        showNotification('Failed to clear logs', 'error');
    }
}

function renderLogs(logs) {
    if (!logs || logs.length === 0) {
        elements.logsContainer.innerHTML = `
            <div class="empty-state" style="text-align: center; color: var(--color-text-secondary); padding: 40px;">
                No callbacks received yet.
            </div>
        `;
        return;
    }

    const html = logs.map((log, index) => {
        return `
            <div class="log-entry" style="background: var(--color-bg-tertiary); margin-bottom: 1rem; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div class="log-header" style="display: flex; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                    <span style="color: var(--color-primary); font-weight: 600;">#${logs.length - index} Received</span>
                    <span style="color: var(--color-text-secondary); font-family: monospace;">${log.timestamp}</span>
                </div>
                <div class="log-body">
                    <h4 style="color: var(--color-text-secondary); font-size: 0.8rem; margin-bottom: 0.5rem;">PAYLOAD</h4>
                    <div style="background: var(--color-bg-primary); padding: 1rem; border-radius: var(--radius-sm); overflow-x: auto;">
                        <pre style="margin: 0; font-family: monospace; color: var(--color-text-primary); font-size: 0.85rem;"><code>${escapeHtml(JSON.stringify(log.body, null, 2))}</code></pre>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    elements.logsContainer.innerHTML = html;
}

function startLogPolling() {
    if (state.logInterval) clearInterval(state.logInterval);
    state.logInterval = setInterval(fetchLogs, 5000); // Poll every 5 seconds
}

function stopLogPolling() {
    if (state.logInterval) {
        clearInterval(state.logInterval);
        state.logInterval = null;
    }
}

// ============================================
// Response Display
// ============================================
function displayResponse(data, type = 'info') {
    const formattedJson = JSON.stringify(data, null, 2);
    elements.responseContent.innerHTML = `<code>${escapeHtml(formattedJson)}</code>`;
    elements.responseContainer.classList.remove('success', 'error', 'info');
    elements.responseContainer.classList.add(type);
    elements.responseContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ============================================
// Copy to Clipboard
// ============================================
function initCopyButton() {
    elements.copyButton.addEventListener('click', async () => {
        const text = elements.responseContent.textContent;
        try {
            await navigator.clipboard.writeText(text);
            showNotification('Response copied to clipboard!', 'success');
            const originalHTML = elements.copyButton.innerHTML;
            elements.copyButton.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            `;
            setTimeout(() => {
                elements.copyButton.innerHTML = originalHTML;
            }, 2000);
        } catch (error) {
            showNotification('Failed to copy to clipboard', 'error');
        }
    });
}

// ============================================
// Notification System
// ============================================
function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '16px 24px',
        background: type === 'success' ? 'var(--color-success)' :
            type === 'error' ? 'var(--color-error)' :
                type === 'warning' ? 'var(--color-warning)' :
                    'var(--color-info)',
        color: 'white',
        borderRadius: 'var(--radius-md)',
        boxShadow: 'var(--shadow-lg)',
        zIndex: '10000',
        animation: 'slideInRight 0.3s ease-out',
        fontWeight: '500',
        fontSize: 'var(--font-size-sm)',
        maxWidth: '400px'
    });

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Add notification animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);

// ============================================
// Utility Functions
// ============================================
function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function initTestDataHelper() {
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
            e.preventDefault();
            fillTestData();
        }
    });
}

function fillTestData() {
    const testData = {
        stkpush: {
            amount: '100',
            phoneNumber: '254722000000',
            accountReference: 'TEST-' + Date.now(),
            transactionDesc: 'Test payment'
        },
        stkstatus: {
            checkoutRequestID: 'ws_CO_191220191020363925'
        },
        c2bregister: {
            validationURL: 'https://example.com/validate',
            confirmationURL: 'https://example.com/confirm',
            responseType: 'Completed'
        },
        c2bsimulate: {
            amount: '100',
            msisdn: '254722000000',
            billRefNumber: 'INV-' + Date.now()
        },
        b2c: {
            amount: '500',
            partyB: '254722000000',
            commandID: 'BusinessPayment',
            remarks: 'Test B2C payment',
            occasion: 'Testing'
        },
        b2b: {
            amount: '1000',
            partyB: '600000',
            commandID: 'BusinessPayBill',
            accountReference: 'INV-' + Date.now(),
            remarks: 'Test B2B transfer'
        },
        b2pochi: {
            amount: '100',
            partyB: '254722000000',
            remarks: 'Test Pochi deposit'
        },
        balance: {
            remarks: 'Balance query test'
        },
        status: {
            transactionID: 'NLJ7RT61SV',
            remarks: 'Status check test'
        },
        reversal: {
            transactionID: 'NLJ7RT61SV',
            amount: '100',
            receiverParty: '600000',
            remarks: 'Test reversal'
        }
    };

    const currentData = testData[state.currentEndpoint];
    const section = document.getElementById(state.currentEndpoint);
    if (!currentData || !section) return;

    const form = section.querySelector('.api-form');
    if (!form) return;

    Object.entries(currentData).forEach(([key, value]) => {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) {
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    showNotification('Test data filled!', 'info');
}

function initPhoneFormatters() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length === 9 && value.startsWith('7')) {
                value = '254' + value;
                e.target.value = value;
            } else if (value.length === 10 && value.startsWith('07')) {
                value = '254' + value.substring(1);
                e.target.value = value;
            }
        });
        input.setAttribute('pattern', '254[0-9]{9}');
        input.setAttribute('title', 'Enter phone number in format: 254722000000');
    });
}

function init() {
    console.log('M-Pesa API Tester initialized');
    initNavigation();
    initEnvironmentToggle();
    initForms();
    initCopyButton();
    initTestDataHelper();
    initPhoneFormatters();
    initLogViewer();

    // Check if we start on a different endpoint (optional)
    const activeNav = document.querySelector('.nav-item.active');
    if (activeNav) {
        switchEndpoint(activeNav.dataset.endpoint);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
