@extends('admin.layout')

@section('title','Edit User')

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

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <a href="/admin/users" class="back-link">
                <i class="ph ph-arrow-left"></i> Back to Users
            </a>
            <h1 style="margin: 0.5rem 0 0 0;">
                Edit User: {{ $user->name }}
                @if($user->id === auth()->id())
                    <span class="badge badge-info">You</span>
                @endif
            </h1>
        </div>
    </div>

    <div class="edit-container">
        <div class="edit-card">
            <h2>User Information</h2>
            <form method="POST" action="/admin/users/{{ $user->id }}/update">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            maxlength="255"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email', $user->email) }}" 
                            required 
                            maxlength="255"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="editor" {{ old('role', $user->role) === 'editor' ? 'selected' : '' }}>Editor</option>
                        <option value="regular" {{ old('role', $user->role) === 'regular' ? 'selected' : '' }}>Regular</option>
                    </select>
                    <small class="muted">
                        <strong>Admin:</strong> Full access to all features | 
                        <strong>Editor:</strong> Can edit content | 
                        <strong>Regular:</strong> View-only access
                    </small>
                </div>

                <hr style="margin: 2rem 0;">

                <h3 style="margin-bottom: 1rem;">Change Password</h3>
                <p class="muted" style="margin-bottom: 1rem;">Leave blank to keep current password</p>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            minlength="8"
                            autocomplete="new-password"
                        >
                        <small class="muted">Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            minlength="8"
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <a href="/admin/users" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <i class="ph ph-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="edit-card">
            <h2>Account Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">User ID:</span>
                    <span class="info-value">{{ $user->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Created:</span>
                    <span class="info-value">{{ $user->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Last Updated:</span>
                    <span class="info-value">{{ $user->updated_at->format('Y-m-d H:i:s') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Verified:</span>
                    <span class="info-value">
                        @if($user->email_verified_at)
                            <span class="badge badge-success">✓ Verified</span>
                            <small class="muted" style="display: block; margin-top: 0.25rem;">
                                {{ $user->email_verified_at->format('Y-m-d') }}
                            </small>
                        @else
                            <span class="badge badge-warning">✗ Not Verified</span>
                        @endif
                    </span>
                </div>
            </div>

            @if($user->id !== auth()->id())
                <hr style="margin: 2rem 0;">
                <h3 style="color: #dc3545; margin-bottom: 1rem;">Danger Zone</h3>
                <p class="muted" style="margin-bottom: 1rem;">
                    Deleting this user will permanently remove all their data. This action cannot be undone.
                </p>
                <form 
                    method="POST" 
                    action="/admin/users/{{ $user->id }}/delete"
                    onsubmit="return confirm('Are you absolutely sure you want to delete user: {{ $user->name }}? This action cannot be undone!')"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <i class="ph ph-trash"></i> Delete User
                    </button>
                </form>
            @else
                <hr style="margin: 2rem 0;">
                <div class="alert alert-info" style="margin: 0;">
                    <i class="ph ph-info"></i>
                    You cannot delete your own account. Please contact another admin if you need to remove this account.
                </div>
            @endif
        </div>
    </div>

    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-2, #333);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: var(--color-1, #fe8d2c);
        }

        .edit-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .edit-container {
                grid-template-columns: 1fr;
            }
        }

        .edit-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .edit-card h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: var(--color-2, #333);
        }

        .edit-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-2, #333);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--color-1, #fe8d2c);
        }

        .form-group small.muted {
            display: block;
            margin-top: 0.5rem;
            color: #666;
            font-size: 0.875rem;
        }

        .info-grid {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
        }

        .info-value {
            text-align: right;
            color: var(--color-2, #333);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 1rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
    </style>
@endsection
