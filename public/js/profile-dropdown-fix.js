/**
 * Profile Dropdown Fix
 * Ensures proper functioning of the profile dropdown menu in the topbar
 */
 
document.addEventListener('alpine:init', () => {
    // Make sure Alpine.js knows to update the dropdown state when clicking outside
    document.addEventListener('click', (event) => {
        // Find any open profile dropdowns
        const profileDropdowns = document.querySelectorAll('.profile-dropdown');
        
        profileDropdowns.forEach(dropdown => {
            // If the click wasn't inside this dropdown
            if (!dropdown.contains(event.target)) {
                // Get the Alpine component instance (Alpine.js v3)
                const xDataEl = dropdown.closest('[x-data]');
                const alpineComponent = xDataEl ? Alpine.$data(xDataEl) : null;
                
                // Close the dropdown if it's open
                if (alpineComponent && alpineComponent.profileOpen) {
                    alpineComponent.profileOpen = false;
                }
            }
        });
    });
});
