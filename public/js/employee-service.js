/**
 * Employee Service
 * Handles employee data operations including profile picture uploads
 */
(function() {
    // Create global PropertyEX namespace if it doesn't exist
    window.PropertyEX = window.PropertyEX || {};
    
    // Employee Service
    PropertyEX.EmployeeService = {
        /**
         * Create a new employee
         * @param {Object} employeeData - Employee data
         * @param {File} profilePicture - Profile picture file
         * @returns {Promise<Object>} - Created employee data
         */
        createEmployee: function(employeeData, profilePicture) {
            return new Promise((resolve, reject) => {
                if (!employeeData) {
                    reject(new Error('Employee data is required'));
                    return;
                }
                
                // Upload profile picture if provided
                let uploadPromise = Promise.resolve(null);
                if (profilePicture) {
                    uploadPromise = PropertyEX.StorageService.uploadImage(
                        profilePicture, 
                        'employees/profile-pictures'
                    );
                }
                
                // After uploading the profile picture, create the employee
                uploadPromise
                    .then(profilePictureUrl => {
                        // Create FormData for API request
                        const formData = new FormData();
                        
                        // Add employee data to FormData
                        Object.keys(employeeData).forEach(key => {
                            formData.append(key, employeeData[key]);
                        });
                        
                        // Add profile picture URL if uploaded
                        if (profilePictureUrl) {
                            formData.append('profile_picture', profilePictureUrl);
                        }
                        
                        // Send API request to create employee
                        return fetch('/employee/created', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to create employee');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Employee created successfully:', data);
                        resolve(data);
                    })
                    .catch(error => {
                        console.error('Failed to create employee:', error);
                        reject(error);
                    });
            });
        },
        
        /**
         * Update an existing employee
         * @param {number} employeeId - Employee ID
         * @param {Object} employeeData - Updated employee data
         * @param {File} profilePicture - New profile picture file (optional)
         * @returns {Promise<Object>} - Updated employee data
         */
        updateEmployee: function(employeeId, employeeData, profilePicture) {
            return new Promise((resolve, reject) => {
                if (!employeeId || !employeeData) {
                    reject(new Error('Employee ID and data are required'));
                    return;
                }
                
                // Upload new profile picture if provided
                let uploadPromise = Promise.resolve(null);
                if (profilePicture) {
                    uploadPromise = PropertyEX.StorageService.uploadImage(
                        profilePicture, 
                        'employees/profile-pictures'
                    );
                }
                
                // After uploading the profile picture, update the employee
                uploadPromise
                    .then(profilePictureUrl => {
                        // Create FormData for API request
                        const formData = new FormData();
                        
                        // Add employee data to FormData
                        Object.keys(employeeData).forEach(key => {
                            formData.append(key, employeeData[key]);
                        });
                        
                        // Add profile picture URL if uploaded
                        if (profilePictureUrl) {
                            formData.append('profile_picture', profilePictureUrl);
                        }
                        
                        // Send API request to update employee
                        return fetch(`/employee/update/${employeeId}`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to update employee');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Employee updated successfully:', data);
                        resolve(data);
                    })
                    .catch(error => {
                        console.error('Failed to update employee:', error);
                        reject(error);
                    });
            });
        },
        
        /**
         * Delete an employee
         * @param {number} employeeId - Employee ID
         * @returns {Promise<void>}
         */
        deleteEmployee: function(employeeId) {
            return new Promise((resolve, reject) => {
                if (!employeeId) {
                    reject(new Error('Employee ID is required'));
                    return;
                }
                
                // Send API request to delete employee
                fetch(`/employee/${employeeId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete employee');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Employee deleted successfully:', data);
                    resolve();
                })
                .catch(error => {
                    console.error('Failed to delete employee:', error);
                    reject(error);
                });
            });
        },
        
        /**
         * Get all employees
         * @returns {Promise<Array<Object>>} - Array of employee data
         */
        getAllEmployees: function() {
            return new Promise((resolve, reject) => {
                // Send API request to get all employees
                fetch('/employee/show', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to get employees');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Employees retrieved successfully:', data);
                    resolve(data);
                })
                .catch(error => {
                    console.error('Failed to get employees:', error);
                    reject(error);
                });
            });
        },
        
        /**
         * Get an employee by ID
         * @param {number} employeeId - Employee ID
         * @returns {Promise<Object>} - Employee data
         */
        getEmployeeById: function(employeeId) {
            return new Promise((resolve, reject) => {
                if (!employeeId) {
                    reject(new Error('Employee ID is required'));
                    return;
                }
                
                // Send API request to get employee by ID
                fetch(`/employee/show/${employeeId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to get employee');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Employee retrieved successfully:', data);
                    resolve(data);
                })
                .catch(error => {
                    console.error('Failed to get employee:', error);
                    reject(error);
                });
            });
        }
    };
})();
