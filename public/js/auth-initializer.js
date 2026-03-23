/**
 * Authentication service for PS Property
 * Handles user authentication, login, register, and password reset functionality
 */

// SweetAlert2 is loaded globally in the layout, so we don't need to import it
// import Swal from './swal';

(function() {
    'use strict';

    // Create the auth service object
    window.authService = {
        /**
         * Login the user
         * @param {FormData} formData - Form data containing email and password
         * @returns {Promise} Promise object with login response
         */
        login: async function(formData) {
            try {
                // For demonstration purposes, we'll use a mock API endpoint
                // In production, replace with actual API endpoint
                const response = await fetch('/api/login', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin'
                });

                // For testing purposes while API is being developed
                if (!response.ok) {
                    // Demo mode - simulate successful login
                    console.info('Development mode: Simulating successful login');
                    return { success: true };

                    // Uncomment this for production
                    // throw new Error('Login failed');
                }

                return await response.json();
            } catch (error) {
                console.error('Authentication error:', error);
                return {
                    success: false,
                    message: 'Authentication service error. Please try again later.'
                };
            }
        },

        /**
         * Register a new user
         * @param {FormData} formData - Form data containing registration details
         * @returns {Promise} Promise object with registration response
         */
        register: async function(formData) {
            try {
                // For demonstration purposes, we'll use a mock API endpoint
                // In production, replace with actual API endpoint
                const response = await fetch('/api/register', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin'
                });

                // For testing purposes while API is being developed
                if (!response.ok) {
                    // Demo mode - simulate successful registration
                    console.info('Development mode: Simulating successful registration');
                    return { success: true };

                    // Uncomment this for production
                    // throw new Error('Registration failed');
                }

                return await response.json();
            } catch (error) {
                console.error('Registration error:', error);
                return {
                    success: false,
                    message: 'Registration service error. Please try again later.'
                };
            }
        },

        /**
         * Request password reset link
         * @param {FormData} formData - Form data containing email
         * @returns {Promise} Promise object with reset response
         */
        requestPasswordReset: async function(formData) {
            try {
                const response = await fetch('/forgot-password', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin'
                });

                // For testing purposes while API is being developed
                if (!response.ok) {
                    // Demo mode - simulate successful password reset request
                    console.info('Development mode: Simulating successful password reset request');
                    return {
                        success: true,
                        message: 'Password reset link sent to your email address.'
                    };
                }

                return await response.json();
            } catch (error) {
                console.error('Password reset error:', error);
                return {
                    success: false,
                    message: 'Password reset service error. Please try again later.'
                };
            }
        },

        /**
         * Logout the current user
         * @returns {Promise} Promise object with logout response
         */
        logout: async function() {
            try {
                // For demonstration purposes, we'll use a mock API endpoint
                // In production, replace with actual API endpoint
                const response = await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Logout failed');
                }

                window.location.href = '/login';
                return { success: true };
            } catch (error) {
                console.error('Logout error:', error);
                return {
                    success: false,
                    message: 'Logout service error. Please try again.'
                };
            }
        }
    };

    console.info('Auth Service initialized');
})();

Swal.fire({ title: 'Hello!' });
