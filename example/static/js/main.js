// ============================================
// State Management
// ============================================
const state = {
    currentEndpoint: 'stkpush',
    isSandbox: true,
    isLoading: false
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
    environmentToggle: document.getElementById('environmentToggle')
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
}

// ============================================
// Environment Toggle Handler
// ============================================
function initEnvironmentToggle() {
    elements.environmentToggle.addEventListener('change', (e) => {
        state.isSandbox = e.target.checked;
        const toggleText = e.target.parentElement.querySelector('.toggle-text');
        toggleText.textContent = state.isSandbox ? 'Sandbox Mode' : 'Production Mode';

        // Show notification
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
        // Set loading state
        state.isLoading = true;
        submitButton.classList.add('loading');
        submitButton.disabled = true;

        // Clear previous response
        displayResponse({ message: 'Sending request...' }, 'info');

        // Make API request with updated path
        const response = await fetch('api/handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                endpoint: endpoint,
                data: data,
                isSandbox: state.isSandbox
            })
        });

        const result = await response.json();

        // Display response
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
            details: 'Failed to connect to the API. Please check your connection and try again.'
        }, 'error');
        showNotification('Network error occurred', 'error');
    } finally {
        // Reset loading state
        state.isLoading = false;
        submitButton.classList.remove('loading');
        submitButton.disabled = false;
    }
}

// ============================================
// Response Display
// ============================================
function displayResponse(data, type = 'info') {
    // Format JSON with syntax highlighting
    const formattedJson = JSON.stringify(data, null, 2);

    // Update response container
    elements.responseContent.innerHTML = `<code>${escapeHtml(formattedJson)}</code>`;

    // Update container class for styling
    elements.responseContainer.classList.remove('success', 'error', 'info');
    elements.responseContainer.classList.add(type);

    // Scroll to response
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

            // Visual feedback
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
    // Remove existing notifications
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Add styles
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

    // Add to DOM
    document.body.appendChild(notification);

    // Auto remove after 4 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Add notification animations to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ============================================
// Utility Functions
// ============================================
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// ============================================
// Form Auto-fill for Testing (Development Helper)
// ============================================
function initTestDataHelper() {
    // Add keyboard shortcut: Ctrl/Cmd + Shift + T to fill test data
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
    if (!currentData) return;

    const currentSection = document.getElementById(state.currentEndpoint);
    const form = currentSection.querySelector('.api-form');

    Object.entries(currentData).forEach(([key, value]) => {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) {
            input.value = value;
            // Trigger input event for any listeners
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    showNotification('Test data filled! Press Ctrl/Cmd+Shift+T to refill.', 'info');
}

// ============================================
// Phone Number Formatter
// ============================================
function initPhoneFormatters() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');

    phoneInputs.forEach(input => {
        input.addEventListener('blur', (e) => {
            let value = e.target.value.replace(/\D/g, '');

            // Auto-add Kenya country code if missing
            if (value.length === 9 && value.startsWith('7')) {
                value = '254' + value;
                e.target.value = value;
            } else if (value.length === 10 && value.startsWith('07')) {
                value = '254' + value.substring(1);
                e.target.value = value;
            }
        });

        // Add pattern validation
        input.setAttribute('pattern', '254[0-9]{9}');
        input.setAttribute('title', 'Enter phone number in format: 254722000000');
    });
}

// ============================================
// Initialize Application
// ============================================
function init() {
    console.log('M-Pesa API Tester initialized');

    initNavigation();
    initEnvironmentToggle();
    initForms();
    initCopyButton();
    initTestDataHelper();
    initPhoneFormatters();

    // Show welcome message
    displayResponse({
        message: 'Welcome to M-Pesa API Tester!',
        instructions: [
            'Select an endpoint from the sidebar',
            'Fill in the required fields',
            'Click the submit button to test the API',
            'View the response below'
        ],
        tips: [
            'Use Ctrl/Cmd + Shift + T to auto-fill test data',
            'Toggle between Sandbox and Production modes using the switch above',
            'All responses can be copied to clipboard'
        ]
    }, 'info');

    console.log('Tip: Press Ctrl/Cmd + Shift + T to auto-fill test data');
}

// ============================================
// Start Application
// ============================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// ============================================
// Export for debugging (optional)
// ============================================
window.mpesaTester = {
    state,
    switchEndpoint,
    fillTestData,
    displayResponse
};
