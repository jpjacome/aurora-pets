@extends('admin.layout')

@section('title', 'Campaign Details')

@section('content')\<div class="">
    @if(session('success'))
        <div class="alert alert-success">
            <i class="ph ph-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="ph ph-warning-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <a href="{{ route('admin.email-campaigns.index') }}" class="back-link">
            <i class="ph ph-arrow-left"></i>
            Back to Campaigns
        </a>
    </div>

    <!-- Campaign Header -->
    <div class="campaign-header">
        <div class="campaign-title-section">
            <h1>
                {{ $campaign->name }}
                <span class="status-badge status-{{ $campaign->status }}">{{ $campaign->status }}</span>
            </h1>
            <p class="campaign-subject"><strong>Subject:</strong> {{ $campaign->subject }}</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="campaign-stats-grid">
        <div class="stat-card">
            <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-card-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-value">{{ $stats['delivered'] ?? 0 }}</div>
            <div class="stat-card-label">Delivered</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-value">{{ $stats['opened'] ?? 0 }}</div>
            <div class="stat-card-label">Opened</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-value">{{ $stats['clicked'] ?? 0 }}</div>
            <div class="stat-card-label">Clicked</div>
        </div>
    </div>

    <!-- Campaign Actions -->
    <div class="campaign-actions-section">
        <h2><i class="ph ph-lightning"></i> Campaign Actions</h2>

        <!-- Run Campaign Form -->
        <form method="POST" action="{{ route('admin.email-campaigns.run', $campaign) }}" class="action-form">
            @csrf
            <div class="form-inline-group">
                <div class="form-group">
                    <label for="recipient_filter">Recipients</label>
                    <select name="recipient_filter" id="recipient_filter" class="form-input">
                        <option value="all">All clients</option>
                        <option value="subscribed">Subscribed (not unsubscribed)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="provider">Provider</label>
                    <select name="provider" id="provider" class="form-input">
                        <option value="{{ $campaign->metadata['provider'] ?? 'smtp' }}">{{ strtoupper($campaign->metadata['provider'] ?? 'smtp') }}</option>
                        <option value="smtp">SMTP</option>
                        <option value="brevo">Brevo API</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">
                        <i class="ph ph-play"></i> Run Campaign
                    </button>
                </div>
            </div>
        </form>

        <!-- Preview Button -->
        <div class="action-form">
            <button id="previewCampaignShow" class="btn-secondary">
                <i class="ph ph-eye"></i> Preview Email
            </button>
        </div>

        <!-- Schedule Campaign Form -->
        <form method="POST" action="{{ route('admin.email-campaigns.schedule', $campaign) }}" class="action-form">
            @csrf
            <div class="form-inline-group">
                <div class="form-group">
                    <label for="scheduled_at">Schedule For</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-input">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-secondary">
                        <i class="ph ph-clock"></i> Schedule Campaign
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Hidden inputs for preview -->
    <input type="hidden" id="show_subject" value="{{ $campaign->subject }}">
    <textarea id="show_template_body" style="display:none;">{{ $campaign->template_body }}</textarea>

    <!-- Add Recipients Section -->
    <div class="campaign-actions-section">
        <h2><i class="ph ph-user-plus"></i> Add Recipients</h2>
        
        <form method="POST" action="{{ route('admin.email-campaigns.run', $campaign) }}" class="action-form">
            @csrf
            
            <div class="form-inline-group" style="flex-wrap: wrap;">
                <div class="form-group">
                    <label for="recipient_type_add">Recipient Type</label>
                    <select name="recipient_type" id="recipient_type_add" class="form-input">
                        <option value="clients_selected">Select Clients</option>
                        <option value="manual">Manual Emails</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="provider_add">Email Provider</label>
                    <select name="provider" id="provider_add" class="form-input">
                        <option value="{{ $campaign->metadata['provider'] ?? 'smtp' }}">{{ strtoupper($campaign->metadata['provider'] ?? 'smtp') }} (Current)</option>
                        <option value="smtp">SMTP</option>
                        <option value="brevo">Brevo API</option>
                    </select>
                </div>
                
                <!-- Client Selection -->
                <div id="clientsContainerAdd" style="display: block; width: 100%; margin-top: 1rem;">
                    <button type="button" id="openClientSelectorAdd" class="btn btn-secondary" style="margin-bottom: 0.5rem;">
                        <i class="ph ph-users"></i> Select Clients
                    </button>
                    
                    <div id="selectedClientsListAdd" style="padding: 0.75rem; background: rgba(220, 255, 214, 0.05); border: 1px solid var(--color-1); border-radius: 8px; min-height: 50px; margin-bottom: 1rem;">
                        <p style="color: rgba(220, 255, 214, 0.5); margin: 0; font-size: 0.9rem;">No clients selected</p>
                    </div>
                    
                    <div id="selectedClientsInputsAdd"></div>
                </div>
                
                <!-- Manual Email Entry -->
                <div id="manualContainerAdd" style="display: none; width: 100%; margin-top: 1rem;">
                    <div style="display: grid; grid-template-columns: 2fr 2fr auto; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label for="manual_email_add" style="font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: var(--color-3);">Email</label>
                            <input type="email" id="manual_email_add" class="form-input" placeholder="email@example.com">
                        </div>
                        <div>
                            <label for="manual_name_add" style="font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: var(--color-3);">Name</label>
                            <input type="text" id="manual_name_add" class="form-input" placeholder="Full Name">
                        </div>
                        <button type="button" id="addRecipientAdd" class="btn btn-secondary" style="height: fit-content; padding: 0.75rem 1.5rem; align-self: end;">Add</button>
                    </div>
                    
                    <div id="recipientsItemsAdd" style="max-height: 200px; overflow-y: auto; margin-bottom: 1rem; padding: 0.5rem; background: rgba(220, 255, 214, 0.05); border-radius: 8px; border: 1px solid var(--color-1);">
                        <!-- Recipients added here -->
                    </div>
                    
                    <input type="hidden" name="manual_emails" id="manual_emails_hidden_add" value="[]">
                </div>
                
                <div class="form-group" style="width: 100%;">
                    <button type="submit" class="btn-primary">
                        <i class="ph ph-paper-plane-tilt"></i> Add & Send to New Recipients
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Recipients Section -->
    <div class="recipients-section">
        <h2>
            <i class="ph ph-users"></i>
            Recipients ({{ $messages->total() }})
        </h2>

        @if($messages->count() > 0)
            <div class="recipients-actions">
                <form id="resendForm" method="POST" action="{{ route('admin.email-campaigns.resend', $campaign) }}" style="display: inline;">
                    @csrf
                    <div id="messageIdsInputs"></div>
                    <button id="resendSelectedBtn" class="btn-secondary" type="button">
                        <i class="ph ph-arrow-clockwise"></i> Resend Selected (<span id="selectedCount">0</span>)
                    </button>
                </form>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" id="selectAll" class="row-checkbox">
                        </th>
                        <th>Recipient Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Delivered At</th>
                        <th>Opened At</th>
                        <th>Clicked At</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $m)
                        <tr class="selectable-row" data-message-id="{{ $m->id }}">
                            <td>
                                <input type="checkbox" class="message-checkbox row-checkbox" value="{{ $m->id }}">
                            </td>
                            <td>{{ $m->metadata['client_name'] ?? ($m->client->client ?? 'N/A') }}</td>
                            <td>{{ $m->email }}</td>
                            <td><span class="status-badge status-{{ $m->status }}">{{ $m->status }}</span></td>
                            <td>{{ $m->delivered_at ? $m->delivered_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $m->opened_at ? $m->opened_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $m->clicked_at ? $m->clicked_at->format('Y-m-d H:i') : '-' }}</td>
                            <td style="color: #dc3545; font-size: 0.875rem;">{{ $m->error ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper">
                {{ $messages->links() }}
            </div>
        @else
            <div class="alert alert-error">
                <i class="ph ph-info"></i>
                <span>No recipients yet. Click "Run Campaign" above to send to recipients.</span>
            </div>
        @endif
    </div>
</div>

<!-- Preview Modal -->
<div id="campaignPreviewModal" class="modal" aria-hidden="true">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Email Preview</h2>
            <button id="closePreview" class="modal-close" aria-label="Close">
                <i class="ph ph-x" style="font-size: 1.5rem;"></i>
            </button>
        </div>
        <div class="modal-form">
            <div id="previewArea" style="min-height: 400px; background: #fff; padding: 1rem; border-radius: 6px; overflow-x: auto;">
                <!-- Preview content loads here -->
            </div>
        </div>
    </div>
</div>

<!-- Client Selection Modal -->
<div id="clientSelectorModal" class="modal" aria-hidden="true">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Select Clients</h2>
            <button id="closeClientSelector" class="modal-close" aria-label="Close">✕</button>
        </div>
        <div class="modal-form">
            <div style="margin-bottom: 1.5rem;">
                <input type="search" id="clientSearch" placeholder="Search by name, email, pet name..." 
                       style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;">
            </div>
            
            <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="button" id="selectAllClients" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Select All on Page</button>
                    <button type="button" id="clearAllClients" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Clear All</button>
                </div>
                <span id="selectedCount" style="color: #666; font-size: 0.9rem;">0 selected</span>
            </div>
            
            <div id="modalClientsList" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 0.5rem; background: #f9f9f9;">
                <!-- Populated via AJAX -->
            </div>
            
            <div id="modalPagination" style="margin-top: 1rem; display: flex; justify-content: center; gap: 0.5rem;">
                <!-- Populated via AJAX -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="confirmClientSelection" class="btn-primary">Confirm Selection</button>
            <button type="button" id="cancelClientSelection" class="btn-secondary">Cancel</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/js/admin-email-campaigns.js"></script>
    <script>
        // Add Recipients functionality
        document.addEventListener('DOMContentLoaded', function() {
            const recipientTypeAdd = document.getElementById('recipient_type_add');
            const clientsContainerAdd = document.getElementById('clientsContainerAdd');
            const manualContainerAdd = document.getElementById('manualContainerAdd');
            const openClientSelectorAdd = document.getElementById('openClientSelectorAdd');
            const manualEmailAdd = document.getElementById('manual_email_add');
            const manualNameAdd = document.getElementById('manual_name_add');
            const addRecipientAdd = document.getElementById('addRecipientAdd');
            const recipientsItemsAdd = document.getElementById('recipientsItemsAdd');
            const manualEmailsHiddenAdd = document.getElementById('manual_emails_hidden_add');
            const selectedClientsListAdd = document.getElementById('selectedClientsListAdd');
            const selectedClientsInputsAdd = document.getElementById('selectedClientsInputsAdd');
            
            let manualRecipientsAdd = [];
            let selectedClientsAdd = new Map();
            
            // Toggle between client and manual
            if (recipientTypeAdd) {
                recipientTypeAdd.addEventListener('change', function() {
                    if (this.value === 'manual') {
                        clientsContainerAdd.style.display = 'none';
                        manualContainerAdd.style.display = 'block';
                    } else {
                        clientsContainerAdd.style.display = 'block';
                        manualContainerAdd.style.display = 'none';
                    }
                });
            }
            
            // Client selector modal
            if (openClientSelectorAdd) {
                openClientSelectorAdd.addEventListener('click', function() {
                    const modal = document.getElementById('clientSelectorModal');
                    if (modal) {
                        modal.classList.add('active');
                        modal.setAttribute('aria-hidden', 'false');
                        // Load clients - the function is now global
                        if (window.loadModalClients) {
                            window.loadModalClients(1, '');
                        }
                    }
                });
            }
            
            // Override confirm selection to use our add section
            const confirmBtn = document.getElementById('confirmClientSelection');
            if (confirmBtn) {
                // Store original handler
                const originalHandler = confirmBtn.onclick;
                
                confirmBtn.addEventListener('click', function(e) {
                    // Update our add section
                    updateSelectedClientsListAdd();
                    
                    // Close modal
                    const modal = document.getElementById('clientSelectorModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.setAttribute('aria-hidden', 'true');
                    }
                    
                    e.stopImmediatePropagation();
                }, true);
            }
            
            function updateSelectedClientsListAdd() {
                if (!selectedClientsListAdd || !selectedClientsInputsAdd) return;
                
                // Get selected from the global selectedClients Map
                const selectedFromModal = window.selectedClients || new Map();
                selectedClientsAdd = new Map(selectedFromModal);
                
                selectedClientsInputsAdd.innerHTML = '';
                
                if (selectedClientsAdd.size === 0) {
                    selectedClientsListAdd.innerHTML = '<p style="color: rgba(220, 255, 214, 0.5); margin: 0; font-size: 0.9rem;">No clients selected</p>';
                } else {
                    selectedClientsListAdd.innerHTML = '';
                    selectedClientsAdd.forEach((client) => {
                        const div = document.createElement('div');
                        div.style.cssText = 'padding: 0.5rem; margin-bottom: 0.5rem; background: rgba(254, 141, 44, 0.1); border: 1px solid var(--color-1); border-radius: 6px; display: flex; justify-content: space-between; align-items: center;';
                        div.innerHTML = `
                            <span style="color: var(--color-3); font-size: 0.9rem;">
                                <strong>${client.name}</strong> &lt;${client.email}&gt;
                            </span>
                            <button type="button" onclick="removeSelectedClientAdd(${client.id})" 
                                    style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Remove
                            </button>
                        `;
                        selectedClientsListAdd.appendChild(div);
                        
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'selected_clients[]';
                        input.value = client.id;
                        selectedClientsInputsAdd.appendChild(input);
                    });
                }
            }
            
            window.removeSelectedClientAdd = function(clientId) {
                selectedClientsAdd.delete(clientId);
                updateSelectedClientsListAdd();
            };
            
            // Manual email functionality
            const addManualRecipient = function() {
                const email = manualEmailAdd.value.trim();
                const name = manualNameAdd.value.trim();
                
                if (!email) {
                    alert('Email is required');
                    return;
                }
                
                if (!email.includes('@') || !email.includes('.')) {
                    alert('Please enter a valid email address');
                    return;
                }
                
                if (!name) {
                    alert('Name is required');
                    return;
                }
                
                manualRecipientsAdd.push({ email, name });
                updateManualRecipientsListAdd();
                
                manualEmailAdd.value = '';
                manualNameAdd.value = '';
                manualEmailAdd.focus();
            };
            
            if (addRecipientAdd) {
                addRecipientAdd.addEventListener('click', addManualRecipient);
            }
            
            if (manualEmailAdd) {
                manualEmailAdd.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addManualRecipient();
                    }
                });
            }
            
            if (manualNameAdd) {
                manualNameAdd.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addManualRecipient();
                    }
                });
            }
            
            function updateManualRecipientsListAdd() {
                if (!recipientsItemsAdd) return;
                
                recipientsItemsAdd.innerHTML = '';
                
                if (manualRecipientsAdd.length === 0) {
                    recipientsItemsAdd.innerHTML = '<p style="color: rgba(220, 255, 214, 0.5); text-align: center; padding: 1rem; margin: 0;">No recipients added yet</p>';
                } else {
                    manualRecipientsAdd.forEach((r, index) => {
                        const div = document.createElement('div');
                        div.style.cssText = 'padding: 0.75rem; margin-bottom: 0.5rem; background: rgba(254, 141, 44, 0.1); border: 1px solid var(--color-1); border-radius: 6px; display: flex; justify-content: space-between; align-items: center;';
                        div.innerHTML = `
                            <span style="color: var(--color-3); font-size: 0.95rem;"><strong>${r.name}</strong> &lt;${r.email}&gt;</span>
                            <button type="button" onclick="removeRecipientAdd(${index})" style="padding: 0.35rem 0.75rem; font-size: 0.85rem; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer;">Remove</button>
                        `;
                        recipientsItemsAdd.appendChild(div);
                    });
                }
                
                if (manualEmailsHiddenAdd) {
                    manualEmailsHiddenAdd.value = JSON.stringify(manualRecipientsAdd);
                }
            }
            
            window.removeRecipientAdd = function(index) {
                manualRecipientsAdd.splice(index, 1);
                updateManualRecipientsListAdd();
            };
            
            // Initialize
            updateManualRecipientsListAdd();

            // Original select all functionality for existing recipients table
            const selectAllCheckbox = document.getElementById('selectAll');
            const messageCheckboxes = document.querySelectorAll('.message-checkbox');
            const resendBtn = document.getElementById('resendSelectedBtn');
            const selectedCountSpan = document.getElementById('selectedCount');
            const messageIdsInputs = document.getElementById('messageIdsInputs');

            function updateSelectedCount() {
                const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
                const count = checkedBoxes.length;
                selectedCountSpan.textContent = count;
                resendBtn.disabled = count === 0;
                
                // Update visual selection
                messageCheckboxes.forEach(cb => {
                    const row = cb.closest('tr');
                    if (cb.checked) {
                        row.classList.add('selected');
                    } else {
                        row.classList.remove('selected');
                    }
                });
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    messageCheckboxes.forEach(cb => {
                        cb.checked = selectAllCheckbox.checked;
                    });
                    updateSelectedCount();
                });
            }

            messageCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    updateSelectedCount();
                    
                    // Update select all checkbox state
                    const allChecked = Array.from(messageCheckboxes).every(c => c.checked);
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                    }
                });
            });

            if (resendBtn) {
                resendBtn.addEventListener('click', function() {
                    const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
                    if (checkedBoxes.length === 0) {
                        alert('Please select at least one recipient to resend.');
                        return;
                    }

                    if (!confirm(`Resend email to ${checkedBoxes.length} recipient(s)?`)) {
                        return;
                    }

                    // Clear existing inputs
                    messageIdsInputs.innerHTML = '';

                    // Add hidden inputs for each selected message
                    checkedBoxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'message_ids[]';
                        input.value = cb.value;
                        messageIdsInputs.appendChild(input);
                    });

                    // Submit the form
                    document.getElementById('resendForm').submit();
                });
            }

            // Initial state
            updateSelectedCount();
        });
    </script>
@endsection
