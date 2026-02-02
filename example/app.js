async function handleForm(event, type) {
    if (event) event.preventDefault();

    const form = event && event.target ? event.target : null;
    const statusMsg = document.getElementById('statusMessage');
    const responseOutput = document.getElementById('responseOutput');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    const originalBtnText = submitBtn ? submitBtn.innerHTML : null;

    // Loading State
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `Processing...`;
    }

    statusMsg.classList.remove('hidden');
    responseOutput.innerHTML = 'Loading...';
    responseOutput.className = 'p-4 bg-gray-900 text-blue-400 rounded-xl overflow-x-auto text-[11px] font-mono';

    // Collect Data
    let data = {};
    if (form) {
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            // Auto prefix phone if it's 7XXXXXXXX or 07XXXXXXXX
            if (key === 'phone') {
                if (value.startsWith('0')) value = '254' + value.substring(1);
                else if (value.startsWith('7')) value = '254' + value;
            }
            data[key] = value;
        });
    }

    try {
        const endpoint = `api/${type}.php`;
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success || result.ResponseCode === '0' || result.ResultCode === '0') {
            responseOutput.className = 'p-4 bg-gray-900 text-green-400 rounded-xl overflow-x-auto text-[11px] font-mono shadow-inner border border-green-900';
            responseOutput.innerHTML = JSON.stringify(result, null, 4);
        } else {
            responseOutput.className = 'p-4 bg-gray-900 text-red-400 rounded-xl overflow-x-auto text-[11px] font-mono shadow-inner border border-red-900';
            responseOutput.innerHTML = JSON.stringify(result, null, 4);
        }

    } catch (error) {
        responseOutput.className = 'p-4 bg-gray-900 text-orange-400 rounded-xl overflow-x-auto text-[11px] font-mono shadow-inner border border-orange-900';
        responseOutput.innerHTML = 'Client Error: ' + error.message;
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    }
}

// Global expose for onclick
window.handleForm = handleForm;
