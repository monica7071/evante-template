/**
 * Create Request Handler
 * Handles the create request functionality for both customer-rent and customer-buy-sale pages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page that needs the create request handler
    const path = window.location.pathname;
    if (path.includes('customer-rent') || path.includes('customer-buy-sale')) {
        initializeCreateRequestHandler();
    }
});

function initializeCreateRequestHandler() {
    // Set up Alpine.js data store for the modal
    if (typeof Alpine !== 'undefined') {
        Alpine.store('modal', {
            showCreateRequestModal: false
        });
    }
    
    // Set up event listeners for the Create Request button
    const createRequestButtons = document.querySelectorAll('.create-request-btn');
    createRequestButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            showCreateRequestModal();
        });
    });
    
    // Set up event listeners for the modal close buttons
    const closeButtons = document.querySelectorAll('.modal .btn-close, .modal .btn-secondary');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            hideCreateRequestModal();
        });
    });
    
    // Handle form submission
    const submitButton = document.getElementById('submitRequest');
    if (submitButton) {
        submitButton.addEventListener('click', handleRequestSubmission);
    }
    
    // Initialize date pickers
    initializeDatePickers();
}

function showCreateRequestModal() {
    if (typeof Alpine !== 'undefined') {
        Alpine.store('modal').showCreateRequestModal = true;
    } else {
        const modal = document.getElementById('createRequestModal');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
            
            // Create backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
            
            // Trigger custom event for other components
            document.dispatchEvent(new CustomEvent('modalShown'));
        }
    }
}

function hideCreateRequestModal() {
    if (typeof Alpine !== 'undefined') {
        Alpine.store('modal').showCreateRequestModal = false;
    } else {
        const modal = document.getElementById('createRequestModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Trigger custom event for other components
            document.dispatchEvent(new CustomEvent('modalHidden'));
        }
    }
}

function handleRequestSubmission() {
    const form = document.getElementById('createRequestForm');
    if (form && form.checkValidity()) {
        // Get form data
        const formData = new FormData(form);
        
        // Determine the API endpoint based on the current page
        const path = window.location.pathname;
        let endpoint = '/api/requests';
        
        if (path.includes('customer-rent')) {
            endpoint = '/api/rent-requests';
        } else if (path.includes('customer-buy-sale')) {
            endpoint = '/api/buy-sale-requests';
        }
        
        // Send the request to the server
        fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Request submitted successfully!');
                
                // Close the modal
                hideCreateRequestModal();
                
                // Reload the page to show the new request
                window.location.reload();
            } else {
                // Show error message
                alert('Error: ' + (data.message || 'Failed to submit request'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Failed to submit request');
        });
    } else if (form) {
        form.reportValidity();
    }
}

function initializeDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    }
}
