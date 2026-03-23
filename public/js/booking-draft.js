/**
 * Booking Draft Functionality
 * 
 * This file contains functions for saving booking drafts and related functionality
 */

// Define saveDraft in the global scope
window.saveDraft = async function () {
    console.log('saveDraft function called - execution started');
    try {
        // Show loading overlay
        const loadingOverlay = document.querySelector('.loading-overlay');
        console.log('Loading overlay:', loadingOverlay);
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
            console.log('Loading overlay displayed');
        }

        // Get form data
        const form = document.getElementById('bookingForm');
        if (!form) {
            console.error('Form not found');
            return;
        }

        const formData = new FormData(form);
        formData.append('is_draft', true);

        // Ensure ID card/passport temp path is attached (same logic as final submit)
        try {
            let idCardPhotoPath = null;

            if (window.idCardPhotoPath) {
                idCardPhotoPath = window.idCardPhotoPath;
                console.log('Draft: Found ID card photo path in window variable:', idCardPhotoPath);
            } else {
                const hiddenInput = document.getElementById('temp_id_passport_path');
                const hiddenValue = hiddenInput ? hiddenInput.value : '';
                if (hiddenValue && hiddenValue !== '') {
                    idCardPhotoPath = hiddenValue;
                    console.log('Draft: Found ID card photo path in hidden input:', idCardPhotoPath);
                } else {
                    const storedPath = localStorage.getItem('idCardPhotoPath');
                    if (storedPath) {
                        idCardPhotoPath = storedPath;
                        console.log('Draft: Found ID card photo path in localStorage:', idCardPhotoPath);
                    }
                }
            }

            if (idCardPhotoPath) {
                if (formData.has('temp_id_passport_path')) {
                    formData.delete('temp_id_passport_path');
                }
                formData.append('temp_id_passport_path', idCardPhotoPath);
                console.log('Draft: Added ID card photo path to form data:', idCardPhotoPath);
            } else {
                console.log('Draft: No ID card photo path found to attach');
            }
        } catch (e) {
            console.warn('Draft: Error while attaching ID card photo path:', e);
        }

        // Debug: log furniture image paths being submitted
        try {
            const furniturePaths = formData.get('temp_furniture_images_path');
            console.log('temp_furniture_images_path before submit:', furniturePaths);
        } catch (e) {
            console.warn('Unable to read temp_furniture_images_path from FormData:', e);
        }

        // Get CSRF token from data attribute
        const dataContainer = document.getElementById('js-data-container');
        if (!dataContainer) {
            console.error('Data container not found');
            return;
        }

        const csrfToken = dataContainer.dataset.csrfToken;
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }

        // Get request ID and draft booking ID from data attributes
        const requestId = dataContainer.dataset.requestId;
        const draftBookingId = dataContainer.dataset.draftBookingId || '';

        // Add request ID to form data
        formData.append('request_id', requestId);

        // Add draft booking ID if it exists
        if (draftBookingId) {
            formData.append('booking_id', draftBookingId);
        }

        console.log('Saving draft with request ID:', requestId);
        console.log('Draft booking ID:', draftBookingId || 'New draft');

        // Get the route from data attribute
        const saveRoute = dataContainer.dataset.saveDraftRoute;
        if (!saveRoute) {
            console.error('Save draft route not found');
            return;
        }

        // Set up a timeout for the request
        const controller = new AbortController();
        const signal = controller.signal;
        const timeout = setTimeout(() => {
            controller.abort();
            console.log('Request timed out after 30 seconds');
        }, 30000); // 30 second timeout

        let response;
        try {
            // Make the AJAX request with timeout
            response = await fetch(saveRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                signal: signal
            });

            // Clear the timeout since the request completed
            clearTimeout(timeout);
        } catch (error) {
            // Hide loading overlay
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }

            // Check if it was a timeout
            if (error.name === 'AbortError') {
                showNotification('Error', 'Request timed out. The server might be busy. Please try again later.');
                return;
            }

            throw error;
        }

        // Hide loading overlay
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Save draft response:', result);

        if (result.success) {
            // Show success notification
            showNotification('Success', result.message || 'Draft saved successfully!');
            console.log('Success notification shown');

            // Store request_id if provided
            if (result.booking_id) {
                window.submittedRequestId = result.booking_id;
                console.log('Stored request ID:', window.submittedRequestId);

                // Update UI to show signature buttons
                const tenantSignatureBtn = document.getElementById('tenant_signature_btn');
                const ownerServiceBtn = document.getElementById('owner_service_btn');
                const saveDraftBtn = document.getElementById('save_draft_button');

                if (tenantSignatureBtn && ownerServiceBtn) {
                    tenantSignatureBtn.style.display = 'inline-block';
                    ownerServiceBtn.style.display = 'inline-block';
                    console.log('Signature buttons displayed');
                }

                // Update Save Draft button text only (keep original styling)
                if (saveDraftBtn) {
                    saveDraftBtn.textContent = 'Update Draft';
                    console.log('Save Draft button updated to Update Draft');
                }

                // Create or update hidden input for booking_id
                let bookingIdInput = document.getElementById('booking_id');
                if (!bookingIdInput) {
                    bookingIdInput = document.createElement('input');
                    bookingIdInput.type = 'hidden';
                    bookingIdInput.id = 'booking_id';
                    bookingIdInput.name = 'booking_id';
                    form.appendChild(bookingIdInput);
                }
                bookingIdInput.value = result.booking_id;
                console.log('Booking ID input updated with value:', result.booking_id);
            }
        } else {
            // Show error notification
            showNotification('Error', 'Failed to save draft. Please try again.');
        }
    } catch (error) {
        console.error('Error saving draft:', error);

        // Hide loading overlay
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }

        // Show error notification
        showNotification('Error', 'Failed to save draft: ' + error.message);
    }
};

// Test function for onclick handler
window.testSaveDraft = function () {
    console.log('testSaveDraft called via onclick');
    showNotification('Info', 'Save Draft button clicked');
    window.saveDraft();
};

// Helper function to show notifications using SweetAlert2
function showNotification(title, message) {
    // Check if SweetAlert2 is available
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded');
        alert(title + ': ' + message); // Fallback to basic alert
        return;
    }

    // Determine icon based on title
    let icon = 'info';
    if (title.toLowerCase().includes('success')) {
        icon = 'success';
    } else if (title.toLowerCase().includes('error')) {
        icon = 'error';
    } else if (title.toLowerCase().includes('warning')) {
        icon = 'warning';
    }

    // Show SweetAlert2 toast
    Swal.fire({
        title: title,
        text: message,
        icon: icon,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

// Note: We've replaced the custom notification modal with SweetAlert2
// The createNotificationModal and closeNotificationModal functions have been removed
// SweetAlert2 handles all notification display and closing automatically

// Copy tenant signature link function
function handleSignatureCopySuccess(roleLabel, fallbackMessage) {
    if (typeof showSignatureCopyAlert === 'function') {
        showSignatureCopyAlert(roleLabel);
    } else if (typeof showNotification === 'function') {
        showNotification('Success', fallbackMessage);
    } else {
        alert(fallbackMessage);
    }
}

window.copyLinkTenantSignReservationContract = function () {
    // Get data container for request ID and CSRF token
    const dataContainer = document.getElementById('js-data-container');
    const requestId = dataContainer ? dataContainer.dataset.requestId : null;
    const menuItem = document.getElementById('tenant_signature_btn');
    if (menuItem) {

        // Get CSRF token from data container or meta tag
        let csrfToken = '';

        if (dataContainer && dataContainer.dataset.csrfToken) {
            csrfToken = dataContainer.dataset.csrfToken;
        } else {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                csrfToken = metaTag.getAttribute('content');
            }
        }

        // Generate signature token via AJAX
        const generateUrl = dataContainer.dataset.tenantSignatureRoute || '/generate-tenant-signature-token';

        fetch(generateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                request_id: requestId,
                type: 'rent'
            })
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    // Create the full URL
                    const signatureUrl = `${window.location.origin}/tenant-signature/${data.token}`;

                    // Copy to clipboard
                    navigator.clipboard.writeText(signatureUrl)
                        .then(function () {
                            handleSignatureCopySuccess('ผู้เช่า', 'Tenant signature link copied to clipboard!');
                        })
                        .catch(function (err) {
                            console.error('Could not copy text: ', err);
                            showNotification('Error', 'Failed to copy link to clipboard');
                        });
                } else {
                    showNotification('Error', 'Failed to generate signature link');
                }
            })
            .catch(function (error) {
                console.error('Error generating signature token:', error);
                showNotification('Error', 'Failed to generate signature link');
            });
    }
};

// Copy owner service link function
window.copyLinkOwnerService = function () {
    // Get data container for request ID and CSRF token
    const dataContainer = document.getElementById('js-data-container');
    const bookingIdInput = document.getElementById('booking_id');
    const requestIdInput = document.getElementById('request_id');

    // Try to get the request ID from various sources
    let requestId = null;
    if (requestIdInput && requestIdInput.value) {
        requestId = requestIdInput.value;
    } else if (dataContainer && dataContainer.dataset.requestId) {
        requestId = dataContainer.dataset.requestId;
    }

    // Fallback to booking ID if request ID is not available
    const bookingId = bookingIdInput ? bookingIdInput.value : null;
    const idToUse = requestId || bookingId;

    const menuItem = document.getElementById('owner_service_btn');

    // Debug logging
    console.log('Data Container:', dataContainer);
    console.log('Request ID from input:', requestId);
    console.log('Booking ID from input:', bookingId);
    console.log('ID to use for token generation:', idToUse);
    console.log('Data attributes:', dataContainer ? {
        requestId: dataContainer.dataset.requestId,
        draftBookingId: dataContainer.dataset.draftBookingId,
        draftStatus: dataContainer.dataset.draftStatus,
        csrfToken: dataContainer.dataset.csrfToken,
        saveDraftRoute: dataContainer.dataset.saveDraftRoute,
        tenantSignatureRoute: dataContainer.dataset.tenantSignatureRoute,
        ownerServiceRoute: dataContainer.dataset.ownerServiceRoute
    } : 'No data container');

    if (menuItem) {

        // Get CSRF token from data container or meta tag
        let csrfToken = '';

        if (dataContainer && dataContainer.dataset.csrfToken) {
            csrfToken = dataContainer.dataset.csrfToken;
        } else {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                csrfToken = metaTag.getAttribute('content');
            }
        }

        // Check if we have an ID to use
        if (!idToUse) {
            showNotification('Error', 'Request ID not found. Please save the booking first.');
            console.error('No request ID or booking ID found');
            return;
        }

        // Generate the URL with the ID
        const baseUrl = window.location.origin;
        const generateUrl = `${baseUrl}/generate-owner-service-token/${idToUse}`;
        console.log('Owner Service Route URL:', generateUrl);

        fetch(generateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                type: 'rent'
            })
        })
            .then(function (response) {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    console.error('Response not OK:', response.status, response.statusText);
                    throw new Error('Server responded with status: ' + response.status);
                }
                return response.json();
            })
            .then(function (data) {
                console.log('Response data:', data);
                if (data.success) {
                    // Create the full URL
                    const serviceUrl = `${window.location.origin}/owner-service/${data.token}`;
                    console.log('Generated service URL:', serviceUrl);

                    // Copy to clipboard
                    navigator.clipboard.writeText(serviceUrl)
                        .then(function () {
                            handleSignatureCopySuccess('เจ้าของ', 'Owner service link copied to clipboard!');
                        })
                        .catch(function (err) {
                            console.error('Could not copy text: ', err);
                            showNotification('Error', 'Failed to copy link to clipboard');
                        });
                } else {
                    console.error('API returned success: false', data.message || 'No error message provided');
                    showNotification('Error', data.message || 'Failed to generate service link');
                }
            })
            .catch(function (error) {
                console.error('Error generating service token:', error);
                showNotification('Error', 'Failed to generate service link: ' + error.message);
            });
    }
};

// Furniture list functionality
document.addEventListener('DOMContentLoaded', function () {
    // Initialize signature buttons
    initSignatureButtons();

    // Initialize furniture list functionality
    initFurnitureList();
});

// Initialize signature buttons with event listeners
function initSignatureButtons() {
    const signatureButtons = document.querySelectorAll('.signature-btn');

    signatureButtons.forEach(button => {
        button.addEventListener('click', function () {
            const requestId = this.dataset.requestId;
            const action = this.dataset.action;

            if (action === 'tenant-signature') {
                window.copyLinkTenantSignReservationContract(requestId);
            } else if (action === 'owner-service') {
                window.copyLinkOwnerService(requestId);
            }
        });
    });
}

// Initialize furniture list functionality
function initFurnitureList() {
    const textarea = document.getElementById('furniture_other');
    const addBtn = document.getElementById('add_furniture_btn');
    const listDiv = document.getElementById('furniture_list');
    const STORAGE_KEY = 'rent_furniture_other';

    // Skip if elements don't exist
    if (!textarea || !addBtn || !listDiv) {
        console.log('Furniture list elements not found, skipping initialization');
        return;
    }

    // Load from localStorage
    function loadFurniture() {
        const data = localStorage.getItem(STORAGE_KEY);
        if (data) {
            try {
                const arr = JSON.parse(data);
                if (Array.isArray(arr)) {
                    renderList(arr);
                    textarea.value = arr.join('\n');
                }
            } catch (e) {
                textarea.value = data;
                renderList(data.split('\n').filter(x => x.trim() !== ''));
            }
        }
    }

    // Save to localStorage
    function saveFurniture(arr) {
        if (!arr || !Array.isArray(arr)) {
            console.error('Invalid array passed to saveFurniture');
            return;
        }
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
            if (textarea) {
                textarea.value = arr.join('\n');
            }
            renderList(arr);
        } catch (e) {
            console.error('Error saving furniture:', e);
        }
    }

    // Render list below textarea
    function renderList(arr) {
        if (!listDiv) {
            console.log('listDiv not found');
            return;
        }

        // Clear the list
        listDiv.innerHTML = '';

        // Check if arr is valid
        if (!arr || !Array.isArray(arr)) {
            console.error('Invalid array passed to renderList');
            return;
        }

        console.log('Rendering furniture list with', arr.length, 'items');

        // Use traditional for loop instead of forEach
        for (let i = 0; i < arr.length; i++) {
            const item = arr[i];
            if (item && item.trim() !== '') {
                const div = document.createElement('div');
                div.className = 'list-item';
                div.textContent = item;

                const removeBtn = document.createElement('button');
                removeBtn.textContent = 'X';
                removeBtn.className = 'remove-btn';
                removeBtn.onclick = function () {
                    const newArr = arr.filter(x => x !== item);
                    saveFurniture(newArr);
                };

                div.appendChild(removeBtn);
                listDiv.appendChild(div);
            }
        }
    }

    // Initialize
    loadFurniture();

    addBtn.addEventListener('click', function () {
        const text = textarea.value;
        const items = text.split('\n').filter(x => x.trim() !== '');
        saveFurniture(items);
    });
}
