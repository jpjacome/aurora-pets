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
@endsection

@section('scripts')
    <script src="/js/admin-email-campaigns.js"></script>
    <script>
        // Select All functionality
        document.addEventListener('DOMContentLoaded', function() {
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
