/**
 * Storage Service
 * Comprehensive methods for uploading images and managing data
 */
(function() {
    // Create global PropertyEX namespace if it doesn't exist
    window.PropertyEX = window.PropertyEX || {};
    
    // Storage Service
    PropertyEX.StorageService = {
        /**
         * Upload an image to Firebase Storage
         * @param {File} file - The file to upload
         * @param {string} path - The storage path (e.g., 'employees/profile-pictures')
         * @param {function} progressCallback - Optional callback for upload progress
         * @returns {Promise<string>} - URL of the uploaded file
         */
        uploadImage: function(file, path, progressCallback) {
            return new Promise((resolve, reject) => {
                if (!file || !path) {
                    reject(new Error('File and path are required'));
                    return;
                }
                
                if (!PropertyEX.storage) {
                    reject(new Error('Firebase Storage not initialized'));
                    return;
                }
                
                // Create a unique filename
                const timestamp = new Date().getTime();
                const filename = `${timestamp}_${file.name}`;
                const fullPath = `${path}/${filename}`;
                
                // Create storage reference
                const storageRef = PropertyEX.storage.ref();
                const fileRef = storageRef.child(fullPath);
                
                // Upload file
                const uploadTask = fileRef.put(file);
                
                // Monitor upload progress
                uploadTask.on('state_changed', 
                    // Progress callback
                    (snapshot) => {
                        const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        if (typeof progressCallback === 'function') {
                            progressCallback(progress);
                        }
                        console.log(`Upload progress: ${progress}%`);
                    },
                    // Error callback
                    (error) => {
                        console.error('Upload failed:', error);
                        reject(error);
                    },
                    // Success callback
                    () => {
                        // Get download URL
                        uploadTask.snapshot.ref.getDownloadURL()
                            .then((downloadURL) => {
                                console.log('File uploaded successfully:', downloadURL);
                                resolve(downloadURL);
                            })
                            .catch((error) => {
                                console.error('Failed to get download URL:', error);
                                reject(error);
                            });
                    }
                );
            });
        },
        
        /**
         * Delete an image from Firebase Storage
         * @param {string} url - The URL of the file to delete
         * @returns {Promise<void>}
         */
        deleteImage: function(url) {
            return new Promise((resolve, reject) => {
                if (!url) {
                    reject(new Error('URL is required'));
                    return;
                }
                
                if (!PropertyEX.storage) {
                    reject(new Error('Firebase Storage not initialized'));
                    return;
                }
                
                // Create a reference to the file
                const storageRef = PropertyEX.storage.refFromURL(url);
                
                // Delete the file
                storageRef.delete()
                    .then(() => {
                        console.log('File deleted successfully');
                        resolve();
                    })
                    .catch((error) => {
                        console.error('Failed to delete file:', error);
                        reject(error);
                    });
            });
        },
        
        /**
         * Upload multiple images to Firebase Storage
         * @param {Array<File>} files - Array of files to upload
         * @param {string} path - The storage path
         * @param {function} progressCallback - Optional callback for overall progress
         * @returns {Promise<Array<string>>} - Array of download URLs
         */
        uploadMultipleImages: function(files, path, progressCallback) {
            return new Promise((resolve, reject) => {
                if (!files || !files.length) {
                    reject(new Error('Files array is required'));
                    return;
                }
                
                const uploadPromises = [];
                const urls = [];
                
                // Create upload promises for each file
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const uploadPromise = this.uploadImage(file, path, (progress) => {
                        // Calculate overall progress
                        if (typeof progressCallback === 'function') {
                            const overallProgress = (i / files.length) * 100 + (progress / files.length);
                            progressCallback(overallProgress);
                        }
                    })
                    .then(url => {
                        urls.push(url);
                        return url;
                    });
                    
                    uploadPromises.push(uploadPromise);
                }
                
                // Wait for all uploads to complete
                Promise.all(uploadPromises)
                    .then(() => {
                        console.log('All files uploaded successfully');
                        resolve(urls);
                    })
                    .catch(error => {
                        console.error('Failed to upload all files:', error);
                        reject(error);
                    });
            });
        },
        
        /**
         * Get a signed URL for a file (with expiration)
         * @param {string} path - The storage path to the file
         * @param {number} expirationSeconds - Expiration time in seconds
         * @returns {Promise<string>} - Signed URL
         */
        getSignedUrl: function(path, expirationSeconds = 3600) {
            return new Promise((resolve, reject) => {
                if (!path) {
                    reject(new Error('Path is required'));
                    return;
                }
                
                if (!PropertyEX.storage) {
                    reject(new Error('Firebase Storage not initialized'));
                    return;
                }
                
                // Create a reference to the file
                const storageRef = PropertyEX.storage.ref();
                const fileRef = storageRef.child(path);
                
                // Get signed URL
                fileRef.getDownloadURL()
                    .then(url => {
                        console.log('Signed URL generated successfully');
                        resolve(url);
                    })
                    .catch(error => {
                        console.error('Failed to generate signed URL:', error);
                        reject(error);
                    });
            });
        }
    };
})();
