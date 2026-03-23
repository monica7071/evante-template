/**
 * Load Create Request Form
 * Dynamically loads the create request form component into the modal container
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page that needs the create request form
    const path = window.location.pathname;
    if (path.includes('customer-rent') || path.includes('customer-buy-sale')) {
        // Find the container where the form should be loaded
        const container = document.querySelector('.create-request-modal-container');
        if (!container) return;
        
        // Initialize Alpine.js data if not already initialized
        if (typeof Alpine !== 'undefined' && !Alpine.store('modal')) {
            Alpine.store('modal', {
                showCreateRequestModal: false
            });
        }
        
        // Set up event listeners for the Create Request button
        const createRequestButtons = document.querySelectorAll('.create-request-btn');
        createRequestButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof Alpine !== 'undefined') {
                    Alpine.store('modal').showCreateRequestModal = true;
                }
            });
        });
        
        // Initialize date pickers when the modal is shown
        document.addEventListener('modalShown', function() {
            if (typeof flatpickr !== 'undefined') {
                flatpickr(".flatpickr-date", {
                    dateFormat: "Y-m-d",
                    allowInput: true
                });
            }
        });
        
        // Handle form submission
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'submitRequest') {
                const form = document.getElementById('createRequestForm');
                if (form && form.checkValidity()) {
                    // Here you would normally submit the form data via AJAX
                    // For now, we'll just show a success message
                    alert('Request submitted successfully!');
                    if (typeof Alpine !== 'undefined') {
                        Alpine.store('modal').showCreateRequestModal = false;
                    }
                } else if (form) {
                    form.reportValidity();
                }
            }
        });
    }
});
