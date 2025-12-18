@extends('admin.layout')

@section('title', 'Email Campaigns')

@section('content')
    <h1>Email Campaigns</h1>

    <div class="admin-toolbar">
        <div>
            Showing {{ $campaigns->count() }} of {{ $campaigns->total() }} campaigns
        </div>
        <div>
            <form method="GET" class="inline-form toolbar-form">
                <input type="search" name="q" placeholder="Search campaigns..." value="{{ request('q') }}" class="admin-toolbar-search" />
                <label>Per page:</label>
                <select name="perPage" onchange="this.form.submit()">
                    <option value="15" {{ request('perPage', 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('perPage') == 'all' ? 'selected' : '' }}>All</option>
                </select>
                <button type="submit">Search</button>
                <a class="btn" href="{{ route('admin.email-campaigns.create') }}">Create Campaign</a>
            </form>
        </div>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Provider</th>
                <th>Recipient Type</th>
                <th>Status</th>
                <th>Messages</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaigns as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->subject ?: '—' }}</td>
                    <td>{{ strtoupper($c->metadata['provider'] ?? 'smtp') }}</td>
                    <td>{{ ucfirst($c->metadata['recipient_type'] ?? 'all') }}</td>
                    <td><span class="badge badge-{{ $c->status }}">{{ ucfirst($c->status) }}</span></td>
                    <td>{{ $c->messages_count }}</td>
                    <td>{{ $c->created_at->diffForHumans() }}</td>
                    <td>
                        <a href="{{ route('admin.email-campaigns.show', $c) }}" class="campaign-action-btn" title="View">
                            <i class="ph ph-eye"></i>
                        </a>
                        
                        <form method="POST" action="{{ route('admin.email-campaigns.run', $c) }}" class="inline-form">
                            @csrf
                            <button type="submit" class="campaign-action-btn" title="Run">
                                <i class="ph ph-play"></i>
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.email-campaigns.stop', $c) }}" class="inline-form">
                            @csrf
                            <button type="submit" class="campaign-action-btn" title="Stop">
                                <i class="ph ph-stop"></i>
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.email-campaigns.destroy', $c) }}" class="inline-form" onsubmit="return confirm('Delete this campaign and all its messages?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="campaign-action-btn campaign-action-btn-delete" title="Delete">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">No campaigns found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $campaigns->links('vendor.pagination.admin') }}
    </div>
@endsection
