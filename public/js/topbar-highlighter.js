/**
 * Topbar Highlighter
 * Highlights the active menu item in the topbar navigation with #C19165 color
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get current URL path
    const currentPath = window.location.pathname;
    
    // Find all horizontal menu items
    const horizontalMenuItems = document.querySelectorAll('.horizontal-menu .menu-item');
    
    // Handle highlighting for horizontal menu
    horizontalMenuItems.forEach(item => {
        // Check if this is a dropdown menu item
        if (item.classList.contains('dropdown')) {
            const dropdownLink = item.querySelector('a');
            const dropdownItems = item.querySelectorAll('.dropdown-content a');
            
            // Check if any dropdown items should be active
            let isDropdownActive = false;
            dropdownItems.forEach(dropdownItem => {
                const href = dropdownItem.getAttribute('href');
                if (href && (currentPath === href || currentPath.startsWith(href.split('?')[0]))) {
                    dropdownItem.classList.add('active');
                    isDropdownActive = true;
                }
            });
            
            // Check for customer-rent paths
            if (currentPath.includes('customer-rent')) {
                const rentLink = item.querySelector('a[href*="customer-rent"]');
                if (rentLink) {
                    rentLink.classList.add('active');
                    isDropdownActive = true;
                }
            }
            
            // Check for customer-buy-sale paths
            // Added condition to check for URLs that start with 'customer-buy-sale-' as mentioned in the memory
            if (currentPath.includes('customer-buy-sale') || currentPath.match(/^\/customer-buy-sale-/)) {
                const buySaleLink = item.querySelector('a[href*="customer-buy-sale"]');
                if (buySaleLink) {
                    buySaleLink.classList.add('active');
                    isDropdownActive = true;
                }
            }
            
            // If any dropdown item is active, make the parent dropdown active too
            if (isDropdownActive && dropdownLink) {
                dropdownLink.classList.add('active');
                
                // Open the dropdown if using Alpine.js
                if (typeof Alpine !== 'undefined') {
                    const alpineComponent = Alpine.$data(item);
                    if (alpineComponent) {
                        alpineComponent.open = true;
                    }
                }
            }
        } else {
            // Regular menu item
            const href = item.getAttribute('href');
            if (href && (currentPath === href || currentPath.startsWith(href.split('?')[0]))) {
                item.classList.add('active');
            }
        }
    });
    
    // Handle sidebar menu items if they still exist
    const sidebarMenuLinks = document.querySelectorAll('.sidebar .nav-link');
    if (sidebarMenuLinks.length > 0) {
        // Remove any existing active classes
        sidebarMenuLinks.forEach(link => {
            link.classList.remove('active');
            
            // If parent has submenu class, remove active from parent too
            if (link.parentElement.classList.contains('has-submenu')) {
                link.parentElement.classList.remove('active');
            }
        });
        
        // Check for customer-rent paths
        if (currentPath.includes('customer-rent')) {
            const customerRentMenu = document.querySelector('.sidebar [data-menu="customer-rent"]');
            if (customerRentMenu) {
                customerRentMenu.classList.add('active');
                
                // Open the submenu
                const submenuParent = customerRentMenu.closest('.has-submenu');
                if (submenuParent && typeof Alpine !== 'undefined') {
                    const alpineComponent = Alpine.$data(submenuParent);
                    if (alpineComponent) {
                        alpineComponent.open = true;
                    }
                }
            }
        }
        
        // Check for customer-buy-sale paths
        if (currentPath.includes('customer-buy-sale') || currentPath.match(/^\/customer-buy-sale-/)) {
            const customerBuySaleMenu = document.querySelector('.sidebar [data-menu="customer-buy-sale"]');
            if (customerBuySaleMenu) {
                customerBuySaleMenu.classList.add('active');
                
                // Open the submenu
                const submenuParent = customerBuySaleMenu.closest('.has-submenu');
                if (submenuParent && typeof Alpine !== 'undefined') {
                    const alpineComponent = Alpine.$data(submenuParent);
                    if (alpineComponent) {
                        alpineComponent.open = true;
                    }
                }
            }
        }
        
        // Highlight specific submenu items
        sidebarMenuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href) && !link.parentElement.classList.contains('has-submenu')) {
                link.classList.add('active');
            }
        });
    }
});
