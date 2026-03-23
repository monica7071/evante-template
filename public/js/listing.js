document.addEventListener('DOMContentLoaded', function() {
    const userRole = window.AppUserRole || null;
    const canDeleteListing = ['Admin', 'Agent'].includes(userRole);
    const modal = document.getElementById('listingModal');
    const openCreateBtn = document.getElementById('openCreateModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelModalBtn = document.getElementById('cancelModal');
    const form = document.getElementById('listingForm');
    const methodField = document.getElementById('method_field');
    const modalTitle = document.getElementById('modalTitle');
    const imagesInput = document.getElementById('images');
    const newImagesPreview = document.getElementById('newImagesPreview');
    const existingImagesWrap = document.getElementById('existingImages');
    const clearNewImagesBtn = document.getElementById('clearNewImagesBtn');
    const editProjectId = document.getElementById('edit_project_id');
    const deleteListingBtn = canDeleteListing ? document.getElementById('deleteListingBtn') : null;
    // Delete confirmation & status modals
    const confirmDeleteModal = canDeleteListing ? document.getElementById('confirmDeleteModal') : null;
    const statusModal = document.getElementById('statusModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const closeConfirmDelete = document.getElementById('closeConfirmDelete');
    const closeStatusModal = document.getElementById('closeStatusModal');
    const statusOkBtn = document.getElementById('statusOkBtn');
    const statusTitle = document.getElementById('statusTitle');
    const statusMessage = document.getElementById('statusMessage');
    let pendingDeleteId = null;
    let shouldReloadAfterStatus = false;
    let imagesDT = new DataTransfer();
    let currentEditingId = null;

    // Multi-image preview & management
    function renderNewPreviews() {
        if (!newImagesPreview || !imagesInput) return;
        newImagesPreview.innerHTML = '';
        const files = imagesInput.files;
        Array.from(files).forEach((file, index) => {
            const url = URL.createObjectURL(file);
            const item = document.createElement('div');
            item.className = 'img-item';
            item.innerHTML = `
                <img src="${url}" alt="preview" />
                <button type="button" class="remove-new-img" data-idx="${index}" title="Remove">&times;</button>
            `;
            newImagesPreview.appendChild(item);
        });
    }

    if (imagesInput) {
        imagesInput.addEventListener('change', function() {
            if (!imagesInput.files) return;
            const incoming = Array.from(imagesInput.files);
            // Merge with existing DT to allow cumulative selection
            const tempDT = new DataTransfer();
            Array.from(imagesDT.files).forEach(f => tempDT.items.add(f));

            const existingCount = existingImagesWrap ? existingImagesWrap.querySelectorAll('.img-item').length : 0;
            let currentCount = tempDT.files.length;
            const initialCount = currentCount;

            for (const file of incoming) {
                const isImageType = file.type && file.type.startsWith('image/');
                const isImageExt = /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test((file.name || ''));
                if (!(isImageType || isImageExt)) {
                    continue;
                }
                if (file.size > 5 * 1024 * 1024) {
                    continue; // 5MB limit
                }
                if (existingCount + currentCount >= 20) {
                    break; // enforce 20 total
                }
                tempDT.items.add(file);
                currentCount++;
            }

            imagesDT = tempDT; // assign back
            imagesInput.files = imagesDT.files;
            const acceptedNew = currentCount - initialCount;
            if (acceptedNew === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'No valid images',
                        text: 'Please select JPEG/JPG/PNG/GIF/WEBP/BMP/SVG up to 5MB each. Note: uploads are only sent when you click Save.'
                    });
                }
            }
            renderNewPreviews();

            // If we're in EDIT mode (existing listing), upload immediately via AJAX
            try {
                const methodFieldEl = document.getElementById('method_field');
                const editIdEl = document.getElementById('edit_project_id');
                const isEdit = (methodFieldEl && methodFieldEl.value === 'PUT') && (editIdEl && editIdEl.value);
                if (isEdit) {
                    const listingId = editIdEl.value;
                    const filesToSend = Array.from(imagesInput.files || []);
                    if (filesToSend.length > 0) {
                        const fd = new FormData();
                        filesToSend.forEach(f => fd.append('images[]', f));

                        const csrfInput = document.querySelector('input[name="_token"]');
                        const metaToken = document.querySelector('meta[name="csrf-token"]');
                        const token = csrfInput?.value || metaToken?.getAttribute('content') || '';

                        // Optional: show loading
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Uploading images',
                                text: 'Please wait...',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => { Swal.showLoading(); }
                            });
                        }

                        fetch(`/listing-setting/listing/${listingId}/images`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: fd
                        }).then(async res => {
                            let payload = null;
                            const ct = res.headers.get('content-type') || '';
                            if (ct.includes('application/json')) {
                                payload = await res.json().catch(() => null);
                            }
                            if (!res.ok) {
                                const msg = (payload && (payload.message || payload.error)) || 'Failed to upload images';
                                throw new Error(msg);
                            }
                            // Append to existing images grid
                            if (payload && payload.images && existingImagesWrap) {
                                payload.images.forEach(img => {
                                    const card = document.createElement('div');
                                    card.className = 'img-item';
                                    card.setAttribute('data-id', String(img.id));
                                    card.innerHTML = `
                                        <img src="${img.src || (img.path ? ('/storage/' + img.path) : '/images/No-Image-Placeholder.png')}" alt="image" />
                                        <button type="button" class="remove-img" title="Remove">&times;</button>
                                    `;
                                    // Remove handler mirrors Blade behavior
                                    card.querySelector('.remove-img')?.addEventListener('click', function() {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'remove_image_ids[]';
                                        input.value = String(img.id);
                                        form.appendChild(input);
                                        card.remove();
                                    });
                                    existingImagesWrap.appendChild(card);
                                });
                            }
                            if (typeof Swal !== 'undefined') {
                                Swal.close();
                                Swal.fire({ icon: 'success', title: 'Uploaded', text: 'Images uploaded successfully.' });
                            }
                        }).catch(err => {
                            console.error('Upload images error:', err);
                            if (typeof Swal !== 'undefined') {
                                Swal.close();
                                Swal.fire({ icon: 'error', title: 'Upload failed', text: err?.message || 'Failed to upload images.' });
                            }
                        }).finally(() => {
                            // Clear selection and previews after attempt
                            imagesDT = new DataTransfer();
                            imagesInput.value = '';
                            if (newImagesPreview) newImagesPreview.innerHTML = '';
                        });
                    }
                }
            } catch (e) {
                console.error('Edit-mode image upload handler error:', e);
            }
        });
    }

    if (newImagesPreview) {
        newImagesPreview.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-new-img')) {
                const idx = parseInt(e.target.getAttribute('data-idx'), 10);
                const tempDT = new DataTransfer();
                Array.from(imagesInput.files).forEach((f, i) => {
                    if (i !== idx) tempDT.items.add(f);
                });
                imagesDT = tempDT;
                imagesInput.files = imagesDT.files;
                renderNewPreviews();
            }
        });
    }

    if (clearNewImagesBtn) {
        clearNewImagesBtn.addEventListener('click', function() {
            imagesDT = new DataTransfer();
            if (imagesInput) imagesInput.value = '';
            if (newImagesPreview) newImagesPreview.innerHTML = '';
        });
    }

    // Open modal for creating new listing
    openCreateBtn.addEventListener('click', function() {
        resetForm();
        modalTitle.textContent = 'Create Listing';
        methodField.value = 'POST';
        form.setAttribute('action', form.getAttribute('data-create-action'));
        editProjectId.value = '';
        showDeleteListingBtn(false);
        // --- Reset owner dropdown ---
        const ownerSelect = document.getElementById('owner_name');
        if (ownerSelect) {
            ownerSelect.innerHTML = '<option value="">Select Owner</option>';
        }
        modal.style.display = 'flex';
    });

    // See Details handled by inline jQuery in Blade to centralize existing image rendering

    // Filter functionality
    const propertyFilter = document.getElementById('property-filter');
    const unitCategoryFilter = document.getElementById('unit-category-filter');
    const statusFilter = document.getElementById('status-filter');
    const searchInput = document.getElementById('search-input');

    // Simple filter tag removal
    document.querySelectorAll('.remove-tag').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.tag').remove();
        });
    });

    // Close modal functions
    function closeModal() {
        modal.style.display = 'none';
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelModalBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Helpers for custom modals
    function showOverlay(el) { if (el) el.style.display = 'flex'; }
    function hideOverlay(el) { if (el) el.style.display = 'none'; }
    function setStatus(title, msg) {
        if (statusTitle) statusTitle.textContent = title || 'Status';
        if (statusMessage) statusMessage.textContent = msg || '';
    }

    // SweetAlert2-based delete flow (align with rent page UX)
    async function performDelete(listingId) {
        const csrfInput = document.querySelector('input[name="_token"]');
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        const token = csrfInput?.value || metaToken?.getAttribute('content') || '';

        Swal.fire({
            title: 'Deleting',
            text: 'Please wait...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await fetch('/listing-setting/listing/' + listingId, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token }
            });
            if (!res.ok) {
                const txt = await res.text();
                throw new Error(txt || 'Delete failed');
            }
            await Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: 'The listing has been deleted successfully.',
                confirmButtonText: 'OK'
            });
            window.location.reload();
        } catch (err) {
            console.error('Delete error:', err);
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete the listing. Please try again.'
            });
        }
    }

    function confirmDeleteWithSwal(listingId) {
        if (!listingId) return;
        Swal.fire({
            title: 'Delete Listing',
            text: 'Are you sure you want to delete this listing? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                performDelete(listingId);
            }
        });
    }

    // (Legacy custom modal controls are no longer used for delete; keeping helpers for potential reuse.)

    // Reset form
    function resetForm() {
        form.reset();
        // Clear existing and new image previews
        if (existingImagesWrap) existingImagesWrap.innerHTML = '';
        if (newImagesPreview) newImagesPreview.innerHTML = '';
        imagesDT = new DataTransfer();
        if (imagesInput) imagesInput.value = '';
        // Remove any hidden remove_image_ids inputs
        if (form) {
            form.querySelectorAll('input[name="remove_image_ids[]"]').forEach(el => el.remove());
        }
    }

    // Simple slider controls for listing cards
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.slider-btn');
        if (!btn) return;
        const slider = btn.closest('.listing-slider');
        if (!slider) return;
        const slides = Array.from(slider.querySelectorAll('.slide'));
        if (!slides.length) return;
        const isNext = btn.classList.contains('next');
        let current = slides.findIndex(s => s.classList.contains('active'));
        if (current === -1) current = 0;
        slides[current].classList.remove('active');
        let nextIdx = isNext ? current + 1 : current - 1;
        if (nextIdx >= slides.length) nextIdx = 0;
        if (nextIdx < 0) nextIdx = slides.length - 1;
        slides[nextIdx].classList.add('active');
    });

    // Show delete listing button only in edit mode
    function showDeleteListingBtn(show) {
        if (!deleteListingBtn) return;
        deleteListingBtn.style.display = show ? 'inline-block' : 'none';
    }

    // Delete listing from modal (SweetAlert2 confirm)
    if (canDeleteListing && deleteListingBtn) {
        deleteListingBtn.addEventListener('click', function() {
            const listingId = editProjectId ? editProjectId.value : null;
            if (!listingId) return;
            confirmDeleteWithSwal(listingId);
        });
    }

    // (Legacy confirmDeleteBtn handler removed; handled by SweetAlert2.)

    // Delete listing from card (SweetAlert2 confirm)
    if (canDeleteListing) {
        const cardDeleteBtns = document.querySelectorAll('.delete-listing-btn');
        cardDeleteBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const listingId = this.getAttribute('data-listing-id');
                if (!listingId) return;
                confirmDeleteWithSwal(listingId);
            });
        });
    }

    // Building/owner population is handled inline within listing.blade.php to avoid conflicts here.

    // SweetAlert2-based save flow for create/update
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Basic required field check (optional: extend as needed)
            const requiredIds = [
                'project_name','project_id','owner_name','house_no','unit_no','phone_number',
                'floor','unit_type','unit_category','approximate_area','price','status'
            ];
            for (const id of requiredIds) {
                const el = document.getElementById(id);
                if (el && !String(el.value).trim()) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please fill out all required fields before saving.'
                    });
                    return;
                }
            }

            const saveBtn = document.getElementById('saveListing');
            if (saveBtn) saveBtn.disabled = true;

            // Show loading
            Swal.fire({
                title: 'Saving',
                text: 'Please wait...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const csrfInput = form.querySelector('input[name="_token"]');
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                const token = csrfInput?.value || metaToken?.getAttribute('content') || '';

                const fd = new FormData(form);
                // Ensure project_id is present and numeric (guard against missing data-id on option)
                const pid = (fd.get('project_id') || '').toString().trim();
                if (!pid || isNaN(Number(pid))) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Project not selected correctly',
                        text: 'Please select a project again so the system can capture its internal ID.'
                    });
                    if (saveBtn) saveBtn.disabled = false;
                    return;
                }
                
                // Debug: Log form data with more context
                console.group('Form Submission');
                console.log('URL:', form.getAttribute('action'));
                console.log('Method:', 'POST');
                console.log('Headers:', {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                });
                console.log('Form Data:');
                for (let [key, value] of fd.entries()) {
                    console.log(`${key}:`, value);
                }
                console.groupEnd();

                const res = await fetch(form.getAttribute('action'), {
                    method: 'POST', // method spoofing via _method handles PUT
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd,
                    redirect: 'follow'
                });

                let data = null;
                const ct = res.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    data = await res.json().catch(() => null);
                } else {
                    // try text for debugging if needed
                    // const txt = await res.text();
                }

                if (res.status === 422) {
                    // Validation errors
                    const errs = (data && data.errors) ? data.errors : null;
                    let html = 'Please fix the following errors:';
                    if (errs) {
                        html += '\n\n';
                        const list = [];
                        Object.keys(errs).forEach(k => {
                            const arr = Array.isArray(errs[k]) ? errs[k] : [errs[k]];
                            arr.forEach(msg => list.push(`• ${msg}`));
                        });
                        html += list.join('\n');
                    }
                    await Swal.fire({ icon: 'error', title: 'Validation Error', text: html });
                    if (saveBtn) saveBtn.disabled = false;
                    return;
                }

                if (!res.ok) {
                    let errorMessage = 'Failed to save the listing. ';
                    try {
                        const clone = res.clone();
                        const ct = res.headers.get('content-type') || '';
                        let errorData = null;

                        if (ct.includes('application/json')) {
                            errorData = await res.json().catch(() => null);
                        } else {
                            // Try to parse JSON from text body; otherwise keep text for display
                            const txt = await clone.text();
                            try { errorData = JSON.parse(txt); }
                            catch {
                                console.error('Server error (raw text):', txt);
                                errorMessage += `Status: ${res.status} ${res.statusText}`;
                                if (txt) {
                                    const snippet = txt.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                                    if (snippet) errorMessage += `\nDetails: ${snippet.substring(0, 300)}...`;
                                }
                            }
                        }

                        if (errorData) {
                            if (errorData.message) errorMessage += errorData.message + ' ';
                            if (errorData.error && typeof errorData.error === 'string') errorMessage += errorData.error + ' ';
                            if (errorData.errors) errorMessage += Object.values(errorData.errors).flat().join(' ');
                        }
                    } catch (e) {
                        errorMessage += `Status: ${res.status} ${res.statusText}`;
                    }

                    console.error('Save failed:', errorMessage);
                    console.error('Response status:', res.status, res.statusText);

                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    });

                    if (saveBtn) saveBtn.disabled = false;
                    return;
                }

                // Success
                await Swal.fire({
                    icon: 'success',
                    title: 'Saved',
                    text: 'The listing has been saved successfully.',
                    confirmButtonText: 'OK'
                });
                window.location.reload();
            } catch (err) {
                console.error('Save error:', err);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred while saving.'
                });
                const saveBtn = document.getElementById('saveListing');
                if (saveBtn) saveBtn.disabled = false;
            }
        });
    }
});
