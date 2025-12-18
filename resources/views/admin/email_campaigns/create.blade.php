@extends('admin.layout')

@section('title', 'Create Email Campaign')

@section('content')
    <h1>Create Email Campaign</h1>

    <form method="POST" action="{{ route('admin.email-campaigns.store') }}" class="campaign-form">
        @csrf
        
        <div class="form-group">
            <label for="name">Campaign Name</label>
            <input type="text" id="name" name="name" class="form-input" required>
        </div>

        <div class="form-group">
            <label for="subject">Email Subject</label>
            <input type="text" id="subject" name="subject" class="form-input">
        </div>

        <div class="form-group">
            <label for="mailable_class">Mailable Class (optional)</label>
            <input type="text" id="mailable_class" name="mailable_class" class="form-input">
        </div>
        
        <div class="form-group">
            <label for="provider">Email Provider</label>
            <select id="provider" name="metadata[provider]" class="form-input">
                <option value="smtp">SMTP (default)</option>
                <option value="brevo">Brevo API</option>
            </select>
        </div>

        <div class="form-group">
            <label for="template_body">Template Body (HTML allowed)</label>
            <textarea id="template_body" name="template_body" class="form-input" rows="8"></textarea>
        </div>

        <div class="form-section">
            <h3 style="color: var(--color-3); margin-bottom: 1rem;">📧 Recipients</h3>
            
            <div class="form-group">
                <label for="recipient_type">Recipient Type</label>
                <select name="recipient_type" id="recipient_type" class="form-input">
                    <option value="all">All clients</option>
                    <option value="subscribed">Subscribed (not unsubscribed)</option>
                    <option value="clients_selected">Specific clients (select below)</option>
                    <option value="manual">Manual emails (enter addresses)</option>
                </select>
            </div>

            <div id="clientsContainer" class="clients-list" style="display:block; margin-top:1.5rem;">
                <p class="form-help" style="color: var(--color-3); margin-bottom: 0.75rem;">Click the button below to select specific clients.</p>
                
                <button type="button" id="openClientSelector" class="btn btn-secondary" style="margin-bottom: 1rem;">
                    <i class="ph ph-users"></i> Select Clients
                </button>
                
                <div id="selectedClientsList" style="padding: 1rem; background: rgba(220, 255, 214, 0.05); border: 1px solid var(--color-1); border-radius: 8px; min-height: 60px;">
                    <p style="color: rgba(220, 255, 214, 0.5); margin: 0;">No clients selected</p>
                </div>
                
                <!-- Hidden inputs for selected client IDs -->
                <div id="selectedClientsInputs"></div>
            </div>

            <div id="manualContainer" style="display:none; margin-top:1.5rem;">
                <label style="color: var(--color-3); display: block; margin-bottom: 1rem; font-weight: 600;">Add Manual Recipients</label>
                <div style="display:grid; grid-template-columns:2fr 2fr auto; gap:0.75rem; align-items:end; margin-bottom:1rem;">
                    <div>
                        <label for="manual_email" style="font-size:0.9rem; display:block; margin-bottom:0.5rem; color: var(--color-3);">Email</label>
                        <input type="email" id="manual_email" class="form-input" placeholder="email@example.com" style="width:100%;">
                    </div>
                    <div>
                        <label for="manual_name" style="font-size:0.9rem; display:block; margin-bottom:0.5rem; color: var(--color-3);">Name</label>
                        <input type="text" id="manual_name" class="form-input" placeholder="Full Name" style="width:100%;">
                    </div>
                    <button type="button" id="addRecipient" class="btn btn-secondary" style="height:fit-content; padding: 0.75rem 1.5rem;">Add</button>
                </div>
                
                <div id="recipientsItems" style="max-height:200px; overflow-y:auto; margin-bottom:1rem; padding: 0.5rem; background: rgba(220, 255, 214, 0.05); border-radius: 8px; border: 1px solid var(--color-1);">
                    <!-- Recipients will be added here dynamically -->
                </div>
                
                <!-- Hidden field that stores the JSON array -->
                <input type="hidden" name="manual_emails" id="manual_emails_hidden" value="[]">
                
                <p class="form-help" style="color: var(--color-3); font-size: 0.9rem;">💡 Click on a client above to auto-fill email and name, or type manually. Press Enter or click "Add" to add to list.</p>
            </div>

            <div style="margin-top:1.5rem; display: flex; align-items: center; gap: 1rem;">
                <button type="button" id="previewRecipients" class="btn btn-secondary">Preview recipients</button>
                <span id="recipientsCount" style="color: var(--color-1); font-weight: 600; font-size: 1.1rem;">0 recipients</span>
            </div>
        </div>

        <div class="form-group" style="margin-top: 2rem;">
            <label for="scheduled_at">Schedule at (optional)</label>
            <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="form-input">
        </div>

        <div class="admin-buttons" style="margin-top:2rem;">
            <button type="submit" class="btn btn-primary">Create Campaign</button>
            <button type="button" id="previewCampaign" class="btn btn-secondary">Preview Template</button>
        </div>
    </form>

    <!-- Client Selection Modal -->
    <div id="clientSelectorModal" class="modal" aria-hidden="true">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Select Clients</h2>
                <button id="closeClientSelector" class="modal-close" aria-label="Close">✕</button>
            </div>
            <div class="modal-form">
                <!-- Search and filters -->
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
                
                <!-- Clients list -->
                <div id="modalClientsList" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 0.5rem; background: #f9f9f9;">
                    <!-- Populated via AJAX -->
                </div>
                
                <!-- Pagination -->
                <div id="modalPagination" style="margin-top: 1rem; display: flex; justify-content: center; gap: 0.5rem;">
                    <!-- Populated via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmClientSelection" class="btn btn-primary">Confirm Selection</button>
                <button type="button" id="cancelClientSelection" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Preview modal -->
    <div id="campaignPreviewModal" class="modal" aria-hidden="true">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Campaign Preview</h2>
                <button id="closePreview" class="modal-close" aria-label="Close preview">✕</button>
            </div>
            <div class="modal-form">
                <div id="previewArea"></div>
            </div>
        </div>
    </div>
    
    <script src="/js/admin-email-campaigns.js"></script>

@endsection
