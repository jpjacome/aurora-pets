document.addEventListener('DOMContentLoaded', function () {
    // Setup CSRF token for fetch
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const previewButton = document.getElementById('previewCampaign');
    const previewButtonShow = document.getElementById('previewCampaignShow');

    const templateBody = document.querySelector('textarea[name="template_body"]');
    const subjectInput = document.querySelector('input[name="subject"]');
    const modal = document.getElementById('campaignPreviewModal');
    const closePreview = document.getElementById('closePreview');
    const previewArea = document.getElementById('previewArea');

    async function openPreview(body, subject) {
        const data = {
            subject: subject || (subjectInput ? subjectInput.value : ''),
            template_body: body || (templateBody ? templateBody.value : '')
        };

        const resp = await fetch('/admin/email-campaigns/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        });

        if (!resp.ok) {
            alert('Failed to render preview');
            return;
        }

        const json = await resp.json();
        previewArea.innerHTML = json.html;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
    }

    if (previewButton) {
        previewButton.addEventListener('click', function(e) {
            e.preventDefault();
            openPreview();
        });
    }
    if (previewButtonShow) {
        previewButtonShow.addEventListener('click', function () {
            const subj = document.getElementById('show_subject')?.value || '';
            const body = document.getElementById('show_template_body')?.value || '';
            openPreview(body, subj);
        }, false);
    }
    if (closePreview) {
        closePreview.addEventListener('click', function () {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        });
    }
    // Close preview modal on background click
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    }
    // Recipient count preview
    const previewRecipientsButton = document.getElementById('previewRecipients');
    const recipientsCountSpan = document.getElementById('recipientsCount');
    const recipientSelect = document.getElementById('recipient_type');
    if (previewRecipientsButton && recipientSelect && recipientsCountSpan) {
        previewRecipientsButton.addEventListener('click', async function () {
            const filter = recipientSelect.value;
            // include selected client ids or manual emails when relevant
            const payload = { filter };
            if (filter === 'clients_selected') {
                const checked = Array.from(document.querySelectorAll('.client-checkbox:checked')).map(cb => parseInt(cb.value));
                payload.selected_clients = checked;
            }
            if (filter === 'manual') {
                const manualData = document.getElementById('manual_emails_hidden')?.value || '[]';
                payload.manual_emails = manualData;
            }
            const resp = await fetch('/admin/email-campaigns/recipients/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });
            if (!resp.ok) {
                alert('Failed to get recipients count');
                return;
            }
            const json = await resp.json();
            recipientsCountSpan.textContent = json.count;
        });
    }

    // Client selection modal
    const clientSelectorModal = document.getElementById('clientSelectorModal');
    const openClientSelectorBtn = document.getElementById('openClientSelector');
    const closeClientSelectorBtn = document.getElementById('closeClientSelector');
    const cancelClientSelectionBtn = document.getElementById('cancelClientSelection');
    const confirmClientSelectionBtn = document.getElementById('confirmClientSelection');
    const modalClientsList = document.getElementById('modalClientsList');
    const modalPagination = document.getElementById('modalPagination');
    const clientSearch = document.getElementById('clientSearch');
    const selectAllClientsBtn = document.getElementById('selectAllClients');
    const clearAllClientsBtn = document.getElementById('clearAllClients');
    const selectedCount = document.getElementById('selectedCount');
    const selectedClientsList = document.getElementById('selectedClientsList');
    const selectedClientsInputs = document.getElementById('selectedClientsInputs');
    
    const recipientTypeSelect = document.getElementById('recipient_type');
    const clientsContainer = document.getElementById('clientsContainer');
    const manualContainer = document.getElementById('manualContainer');
    const manualEmailInput = document.getElementById('manual_email');
    const manualNameInput = document.getElementById('manual_name');
    const addRecipientBtn = document.getElementById('addRecipient');
    const recipientsItems = document.getElementById('recipientsItems');
    const manualEmailsHidden = document.getElementById('manual_emails_hidden');
    
    // Make these global so they can be accessed from other pages (like show.blade.php)
    window.selectedClients = window.selectedClients || new Map(); // Store selected clients: id => {id, name, email}
    let selectedClients = window.selectedClients;
    let currentClientsPage = 1;
    let searchTimeout = null;
    let manualRecipients = [];

    // Client selector modal functions - make global
    window.loadModalClients = async function(page = 1, search = '') {
        if (!modalClientsList) return;
        
        try {
            const url = `/admin/email-campaigns/clients?page=${page}&search=${encodeURIComponent(search)}`;
            const resp = await fetch(url);
            if (!resp.ok) throw new Error('Failed to load clients');
            
            const data = await resp.json();
            currentClientsPage = data.current_page;
            
            // Render clients
            modalClientsList.innerHTML = '';
            data.data.forEach(client => {
                const div = document.createElement('div');
                div.className = 'modal-client-item';
                const isChecked = selectedClients.has(client.id);
                
                // Build pet names display
                const petsDisplay = client.pets && client.pets.length > 0 
                    ? `<span style="font-size: 0.85rem; color: #fe8d2c;">🐾 ${client.pets.join(', ')}</span>`
                    : '<span style="font-size: 0.85rem; color: #999;">No pets</span>';
                
                div.innerHTML = `
                    <label class="checkbox-label" style="width: 100%; padding: 0.75rem; cursor: pointer; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" class="modal-client-checkbox" value="${client.id}" 
                               data-name="${client.name}" data-email="${client.email}" 
                               ${isChecked ? 'checked' : ''}>
                        <div style="flex: 1;">
                            <strong>${client.name}</strong><br>
                            <span style="font-size: 0.9rem; color: #666;">${client.email}</span><br>
                            ${petsDisplay}
                        </div>
                    </label>
                `;
                modalClientsList.appendChild(div);
            });
            
            // Render pagination
            renderModalPagination(data);
            updateSelectedCount();
            
        } catch (error) {
            console.error('Error loading clients:', error);
            modalClientsList.innerHTML = '<p style="padding: 1rem; text-align: center; color: #999;">Failed to load clients</p>';
        }
    };
    
    window.renderModalPagination = function(data) {
        if (!modalPagination) return;
        
        modalPagination.innerHTML = '';
        
        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'btn btn-secondary';
        prevBtn.style.cssText = 'padding: 0.5rem 1rem; font-size: 0.9rem;';
        prevBtn.textContent = '← Previous';
        prevBtn.disabled = data.current_page === 1;
        prevBtn.onclick = () => loadModalClients(data.current_page - 1, clientSearch.value);
        modalPagination.appendChild(prevBtn);
        
        // Page info
        const pageInfo = document.createElement('span');
        pageInfo.style.cssText = 'padding: 0.5rem 1rem; color: #666;';
        pageInfo.textContent = `Page ${data.current_page} of ${data.last_page}`;
        modalPagination.appendChild(pageInfo);
        
        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'btn btn-secondary';
        nextBtn.style.cssText = 'padding: 0.5rem 1rem; font-size: 0.9rem;';
        nextBtn.textContent = 'Next →';
        nextBtn.disabled = data.current_page === data.last_page;
        nextBtn.onclick = () => loadModalClients(data.current_page + 1, clientSearch.value);
        modalPagination.appendChild(nextBtn);
    };
    
    window.updateSelectedCount = function() {
        if (selectedCount) {
            selectedCount.textContent = `${selectedClients.size} selected`;
        }
    };
    
    window.updateSelectedClientsList = function() {
        if (!selectedClientsList || !selectedClientsInputs) return;
        
        selectedClientsInputs.innerHTML = '';
        
        if (selectedClients.size === 0) {
            selectedClientsList.innerHTML = '<p style="color: rgba(220, 255, 214, 0.5); margin: 0;">No clients selected</p>';
        } else {
            selectedClientsList.innerHTML = '';
            selectedClients.forEach((client) => {
                // Add to display list
                const div = document.createElement('div');
                div.style.cssText = 'padding: 0.5rem; margin-bottom: 0.5rem; background: rgba(254, 141, 44, 0.1); border: 1px solid var(--color-1); border-radius: 6px; display: flex; justify-content: space-between; align-items: center;';
                div.innerHTML = `
                    <span style="color: var(--color-3); font-size: 0.9rem;">
                        <strong>${client.name}</strong> &lt;${client.email}&gt;
                    </span>
                    <button type="button" onclick="removeSelectedClient(${client.id})" 
                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Remove
                    </button>
                `;
                selectedClientsList.appendChild(div);
                
                // Add hidden input
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_clients[]';
                input.value = client.id;
                selectedClientsInputs.appendChild(input);
            });
        }
    };
    
    // Make removeSelectedClient global
    window.removeSelectedClient = function(clientId) {
        selectedClients.delete(clientId);
        updateSelectedClientsList();
        // If modal is open, update checkboxes
        const modalClientsList = document.getElementById('modalClientsList');
        const clientSelectorModal = document.getElementById('clientSelectorModal');
        if (clientSelectorModal && clientSelectorModal.classList.contains('active')) {
            const checkbox = modalClientsList?.querySelector(`input[value="${clientId}"]`);
            if (checkbox) checkbox.checked = false;
            updateSelectedCount();
        }
    };
    
    // Modal event listeners
    if (openClientSelectorBtn && clientSelectorModal) {
        openClientSelectorBtn.addEventListener('click', function() {
            clientSelectorModal.classList.add('active');
            clientSelectorModal.setAttribute('aria-hidden', 'false');
            loadModalClients(1, '');
        });
    }
    
    function closeModal() {
        if (clientSelectorModal) {
            clientSelectorModal.classList.remove('active');
            clientSelectorModal.setAttribute('aria-hidden', 'true');
        }
    }
    
    if (closeClientSelectorBtn) {
        closeClientSelectorBtn.addEventListener('click', closeModal);
    }
    
    if (cancelClientSelectionBtn) {
        cancelClientSelectionBtn.addEventListener('click', closeModal);
    }
    
    if (confirmClientSelectionBtn) {
        confirmClientSelectionBtn.addEventListener('click', function() {
            updateSelectedClientsList();
            closeModal();
        });
    }
    
    // Checkbox change listener
    if (modalClientsList) {
        modalClientsList.addEventListener('change', function(e) {
            if (e.target.classList.contains('modal-client-checkbox')) {
                const checkbox = e.target;
                const id = parseInt(checkbox.value);
                const name = checkbox.dataset.name;
                const email = checkbox.dataset.email;
                
                if (checkbox.checked) {
                    selectedClients.set(id, { id, name, email });
                    // Auto-fill manual email fields if manual type is selected
                    if (recipientTypeSelect && recipientTypeSelect.value === 'manual') {
                        if (manualEmailInput) manualEmailInput.value = email;
                        if (manualNameInput) manualNameInput.value = name;
                    }
                } else {
                    selectedClients.delete(id);
                }
                updateSelectedCount();
            }
        });
    }
    
    // Search functionality
    if (clientSearch) {
        clientSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadModalClients(1, this.value);
            }, 300);
        });
    }
    
    // Select all on page
    if (selectAllClientsBtn) {
        selectAllClientsBtn.addEventListener('click', function() {
            const checkboxes = modalClientsList.querySelectorAll('.modal-client-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = true;
                const id = parseInt(cb.value);
                selectedClients.set(id, {
                    id: id,
                    name: cb.dataset.name,
                    email: cb.dataset.email
                });
            });
            updateSelectedCount();
        });
    }
    // Clear all
    if (clearAllClientsBtn) {
        clearAllClientsBtn.addEventListener('click', function() {
            selectedClients.clear();
            const checkboxes = modalClientsList.querySelectorAll('.modal-client-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectedCount();
        });
    }

    // Add recipient to manual list
    if (addRecipientBtn) {
        const addRecipient = function() {
            const email = manualEmailInput.value.trim();
            const name = manualNameInput.value.trim();
            
            if (!email) {
                alert('Email is required');
                return;
            }
            
            // Basic email validation
            if (!email.includes('@') || !email.includes('.')) {
                alert('Please enter a valid email address');
                return;
            }
            
            if (!name) {
                alert('Name is required');
                return;
            }
            
            // Add to list
            manualRecipients.push({ email, name });
            
            // Update UI
            updateManualRecipientsList();
            
            // Clear inputs
            manualEmailInput.value = '';
            manualNameInput.value = '';
            manualEmailInput.focus();
        };
        
        addRecipientBtn.addEventListener('click', addRecipient);
        
        // Allow Enter key to add recipient
        if (manualEmailInput) {
            manualEmailInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addRecipient();
                }
            });
        }
        if (manualNameInput) {
            manualNameInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addRecipient();
                }
            });
        }
    }

    function updateManualRecipientsList() {
        if (!recipientsItems) return;
        
        recipientsItems.innerHTML = '';
        
        if (manualRecipients.length === 0) {
            recipientsItems.innerHTML = '<p style="color: rgba(220, 255, 214, 0.5); text-align: center; padding: 1rem;">No recipients added yet</p>';
        } else {
            manualRecipients.forEach((r, index) => {
                const div = document.createElement('div');
                div.innerHTML = `
                    <span><strong>${r.name}</strong> &lt;${r.email}&gt;</span>
                    <button type="button" onclick="removeRecipient(${index})">Remove</button>
                `;
                recipientsItems.appendChild(div);
            });
        }
        
        // Update hidden input with JSON
        if (manualEmailsHidden) {
            manualEmailsHidden.value = JSON.stringify(manualRecipients);
        }
        
        // Update count display
        if (recipientsCountSpan) {
            recipientsCountSpan.textContent = `${manualRecipients.length} recipient${manualRecipients.length !== 1 ? 's' : ''}`;
        }
    }

    // Make removeRecipient global so inline onclick works
    window.removeRecipient = function(index) {
        manualRecipients.splice(index, 1);
        updateManualRecipientsList();
    };
    
    // Debug: Log when script loads
    console.log('admin-email-campaigns.js loaded', {
        manualEmailInput: !!manualEmailInput,
        manualNameInput: !!manualNameInput,
        addRecipientBtn: !!addRecipientBtn,
        recipientsItems: !!recipientsItems,
        manualEmailsHidden: !!manualEmailsHidden
    });
    
    // Form submission validation for manual recipients
    const campaignForm = document.querySelector('form[action*="email-campaigns"]');
    if (campaignForm && recipientTypeSelect) {
        campaignForm.addEventListener('submit', function(e) {
            const selectedType = recipientTypeSelect.value;
            if (selectedType === 'manual') {
                const hiddenValue = manualEmailsHidden ? manualEmailsHidden.value : '[]';
                const recipients = JSON.parse(hiddenValue);
                console.log('Form submitting with manual recipients:', recipients);
                
                if (recipients.length === 0) {
                    const proceed = confirm('No manual recipients added. Continue anyway?');
                    if (!proceed) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });
    }

    // Toggle visibility based on recipient type
    function updateRecipientUI() {
        if (!recipientTypeSelect) return;
        
        const val = recipientTypeSelect.value;
        if (val === 'manual') {
            if (clientsContainer) clientsContainer.style.display = 'none';
            if (manualContainer) manualContainer.style.display = 'block';
        } else if (val === 'clients_selected') {
            if (clientsContainer) clientsContainer.style.display = 'block';
            if (manualContainer) manualContainer.style.display = 'none';
        } else {
            // all or subscribed
            if (clientsContainer) clientsContainer.style.display = 'none';
            if (manualContainer) manualContainer.style.display = 'none';
        }
    }

    if (recipientTypeSelect) {
        recipientTypeSelect.addEventListener('change', updateRecipientUI);
        // initialize
        updateRecipientUI();
    }
    // Resend selected in show page
    const resendBtn = document.getElementById('resendSelectedBtn');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
        });
    }
    
    if (resendBtn) {
        resendBtn.addEventListener('click', function () {
            const checked = Array.from(document.querySelectorAll('.message-checkbox:checked')).map(cb => cb.value);
            if (checked.length === 0) {
                alert('Please select at least one recipient to resend.');
                return;
            }
            // create hidden inputs and submit form
            const inputContainer = document.getElementById('messageIdsInputs');
            if (!inputContainer) return;
            // Clear previous inputs
            inputContainer.innerHTML = '';
            checked.forEach(id => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'message_ids[]';
                hidden.value = id;
                inputContainer.appendChild(hidden);
            });
            document.getElementById('resendForm').submit();
        });
    }
});
