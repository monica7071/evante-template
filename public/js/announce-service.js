/**
 * Announce Service
 * Handles announcement data operations
 */
(function() {
    // Create global PropertyEX namespace if it doesn't exist
    window.PropertyEX = window.PropertyEX || {};
    
    // Announce Service
    PropertyEX.AnnounceService = {
        /**
         * Create a new announcement
         * @param {Object} announceData - Announcement data (date, location, title, description)
         * @returns {Promise<Object>} - Created announcement data
         */
        createAnnouncement: function(announceData) {
            return new Promise((resolve, reject) => {
                if (!announceData) {
                    reject(new Error('Announcement data is required'));
                    return;
                }
                
                // Create FormData for API request
                const formData = new FormData();
                
                // Add announcement data to FormData
                Object.keys(announceData).forEach(key => {
                    formData.append(key, announceData[key]);
                });
                
                // Send API request to create announcement
                fetch('/our_product/created', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to create announcement');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Announcement created successfully:', data);
                    resolve(data);
                })
                .catch(error => {
                    console.error('Failed to create announcement:', error);
                    reject(error);
                });
            });
        },
        
        /**
         * Update an existing announcement
         * @param {number} announceId - Announcement ID
         * @param {Object} announceData - Updated announcement data
         * @returns {Promise<Object>} - Updated announcement data
         */
        updateAnnouncement: function(announceId, announceData) {
            return new Promise((resolve, reject) => {
                if (!announceId || !announceData) {
                    reject(new Error('Announcement ID and data are required'));
                    return;
                }
                
                // Create FormData for API request
                const formData = new FormData();
                
                // Add announcement data to FormData
                Object.keys(announceData).forEach(key => {
                    formData.append(key, announceData[key]);
                });
                
                // Send API request to update announcement
                fetch(`/our_product/update/${announceId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to update announcement');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Announcement updated successfully:', data);
                    resolve(data);
                })
                .catch(error => {
                    console.error('Failed to update announcement:', error);
                    reject(error);
                });
            });
        },
        
        /**
         * Delete an announcement
         * @param {number} announceId - Announcement ID
         * @returns {Promise<void>}
         */
        deleteAnnouncement: function(announceId) {
            return new Promise((resolve, reject) => {
                if (!announceId) {
                    reject(new Error('Announcement ID is required'));
                    return;
                }
                
                // Send API request to delete announcement
                fetch(`/product/our_product/${announceId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete announcement');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Announcement deleted successfully:', data);
                    resolve();
                })
                .catch(error => {
                    console.error('Failed to delete announcement:', error);
                    reject(error);
                });
            });
        },
        
        /**
         * Get all announcements
         * @returns {Promise<Array<Object>>} - Array of announcement data
         */
        getAllAnnouncements: function() {
            return new Promise((resolve, reject) => {
                // Send API request to get all announcements
                fetch('/our_product/show', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to get announcements');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Announcements retrieved successfully:', data);
                    resolve(data);
                })
                .catch(error => {
                    console.error('Failed to get announcements:', error);
                    reject(error);
                });
            });
        },
        
        /**
         * Get announcement by ID
         * @param {number} announceId - Announcement ID
         * @returns {Promise<Object>} - Announcement data
         */
        getAnnouncementById: function(announceId) {
            return new Promise((resolve, reject) => {
                if (!announceId) {
                    reject(new Error('Announcement ID is required'));
                    return;
                }
                
                // Send API request to get announcement by ID
                fetch(`/our_product/show/${announceId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to get announcement');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Announcement retrieved successfully:', data);
                    resolve(data);
                })
                .catch(error => {
                    console.error('Failed to get announcement:', error);
                    reject(error);
                });
            });
        },
        
        /**
         * Helper function to get today's date in YYYY-MM-DD format
         * @returns {string} - Today's date in YYYY-MM-DD format
         */
        getTodayDate: function() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    };
})();
