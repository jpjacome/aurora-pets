@extends('admin.layout')

@section('title', 'Edit Client')

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="ph ph-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">
            <i class="ph ph-warning-circle"></i>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            <i class="ph ph-warning-circle"></i>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="page-header">
        <div>
            <a href="{{ route('admin.clients') }}" class="back-link">
                <i class="ph ph-arrow-left"></i> Back to Clients
            </a>
            <h1>Edit Client: {{ $client->client }}</h1>
            <p class="muted">Client ID: #{{ $client->id }}</p>
            <p><strong>Has {{ $client->pets_count }} pet(s)</strong></p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.clients.update', $client->id) }}" class="edit-form">
        @csrf
        
        <div class="form-section">
            <h2><i class="ph ph-user"></i> Client Information</h2>
            
            <div class="form-group">
                <label for="client">Client Name *</label>
                <input 
                    type="text" 
                    id="client" 
                    name="client" 
                    value="{{ old('client', $client->client) }}" 
                    required
                    placeholder="Full name"
                >
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', $client->email) }}"
                    required
                    placeholder="email@example.com"
                >
                <small class="form-help">Must be unique</small>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    value="{{ old('phone', $client->phone) }}"
                    placeholder="+593 99 999 9999"
                >
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea 
                    id="address" 
                    name="address" 
                    rows="3"
                    placeholder="Full address"
                >{{ old('address', $client->address) }}</textarea>
            </div>
        </div>

        @if($client->pets_count > 0)
            <div class="info-section">
                <h2><i class="ph ph-paw-print"></i> Associated Pets</h2>
                <p class="muted">This client has {{ $client->pets_count }} pet(s). To edit pet information, use the edit button on each pet card in the Clients list.</p>
            </div>
        @endif

        @if($client->campaigns->count() > 0)
            <div class="form-section">
                <h2><i class="ph ph-envelope"></i> Campaign History</h2>
                <p class="muted" style="margin-bottom: 1rem;">This client has participated in {{ $client->campaigns->count() }} email campaign(s).</p>
                
                <div style="overflow-x: auto;">
                    <table class="admin-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Sent</th>
                                <th>Opened</th>
                                <th>Clicked</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->campaigns as $campaign)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.email-campaigns.show', $campaign->id) }}" style="font-weight: 600;">
                                            {{ $campaign->name }}
                                        </a>
                                    </td>
                                    <td>{{ $campaign->subject ?? '—' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $campaign->pivot->status }}">
                                            {{ ucfirst($campaign->pivot->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($campaign->pivot->delivered_at)
                                            <i class="ph ph-check" style="color: #28a745;"></i>
                                            {{ $campaign->pivot->delivered_at->format('M d, Y H:i') }}
                                        @else
                                            <span style="color: #999;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign->pivot->opened_at)
                                            <i class="ph ph-eye" style="color: #17a2b8;"></i>
                                            {{ $campaign->pivot->opened_at->format('M d, Y H:i') }}
                                        @else
                                            <span style="color: #999;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign->pivot->clicked_at)
                                            <i class="ph ph-hand-pointing" style="color: #fe8d2c;"></i>
                                            {{ $campaign->pivot->clicked_at->format('M d, Y H:i') }}
                                        @else
                                            <span style="color: #999;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.email-campaigns.show', $campaign->id) }}" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                                            <i class="ph ph-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="info-section">
                <h2><i class="ph ph-envelope"></i> Campaign History</h2>
                <p class="muted">This client has not participated in any email campaigns yet.</p>
            </div>
        @endif

        <div class="form-actions">
            <a href="{{ route('admin.clients') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="ph ph-floppy-disk"></i> Save Changes
            </button>
        </div>
    </form>
@endsection
