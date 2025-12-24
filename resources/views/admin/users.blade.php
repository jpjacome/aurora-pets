@extends('admin.layout')

@section('title','Users')

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

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h1 style="margin: 0;">Users</h1>
        
        <!-- Control Panel -->
        <div style="display: flex; gap: 1rem; align-items: center;">
            <button 
                id="addUserBtn" 
                class="icon-btn icon-btn-primary" 
                title="Add New User"
                onclick="openAddUserModal()"
            >
                <i class="ph ph-plus-circle" style="font-size: 1.5rem;"></i>
            </button>
            <button 
                id="deleteSelectedBtn" 
                class="icon-btn icon-btn-danger" 
                title="Delete Selected Users"
                onclick="deleteSelectedUsers()"
                disabled
            >
                <i class="ph ph-trash" style="font-size: 1.5rem;"></i>
            </button>
        </div>
    </div>

    <div class="admin-toolbar">
        <div>
            Showing {{ $users->count() }} of {{ $users->total() }} users
        </div>
        <div>
            <form method="GET" class="inline-form toolbar-form">
                <input type="search" name="q" placeholder="Search name, email, role..." value="{{ request('q') }}" />
                <label>Per page:</label>
                <select name="perPage" onchange="this.form.submit()">
                    <option value="15" {{ request('perPage',15)==15? 'selected':'' }}>15</option>
                    <option value="50" {{ request('perPage')==50? 'selected':'' }}>50</option>
                    <option value="100" {{ request('perPage')==100? 'selected':'' }}>100</option>
                    <option value="all" {{ request('perPage')=='all'? 'selected':'' }}>All</option>
                </select>
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 50px;">
                    <input 
                        type="checkbox" 
                        id="selectAllCheckbox" 
                        onclick="toggleSelectAll(this)"
                    >
                </th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th style="width: 100px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="selectable-row" data-user-id="{{ $user->id }}">
                    <td>
                        <input 
                            type="checkbox" 
                            class="row-checkbox" 
                            data-user-id="{{ $user->id }}"
                            onchange="updateDeleteButton()"
                            @if($user->id === auth()->id()) disabled title="You cannot select your own account" @endif
                        >
                    </td>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if($user->id === auth()->id())
                            <span class="badge badge-info" style="margin-left: 0.5rem;">You</span>
                        @endif
                    </td>
                    <td>
                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    </td>
                    <td>
                        @if($user->role === 'admin')
                            <span class="badge badge-danger">Admin</span>
                        @elseif($user->role === 'editor')
                            <span class="badge badge-warning">Editor</span>
                        @else
                            <span class="badge badge-secondary">Regular</span>
                        @endif
                    </td>
                    <td>
                        <span class="muted" title="{{ $user->created_at->format('Y-m-d H:i:s') }}">
                            {{ $user->created_at->diffForHumans() }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <a 
                                href="/admin/users/{{ $user->id }}/edit" 
                                class="icon-btn icon-btn-small icon-btn-primary" 
                                title="Edit User"
                            >
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            @if($user->id !== auth()->id())
                                <form 
                                    method="POST" 
                                    action="/admin/users/{{ $user->id }}/delete" 
                                    style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete user: {{ $user->name }}?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="icon-btn icon-btn-small icon-btn-danger" 
                                        title="Delete User"
                                    >
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                        <i class="ph ph-users" style="font-size: 3rem; display: block; margin-bottom: 0.5rem;"></i>
                        No users found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button class="modal-close" onclick="closeAddUserModal()">&times;</button>
            </div>
            <form method="POST" action="/admin/users/create">
                @csrf
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required maxlength="255" value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required maxlength="255" value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="">Select role...</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="editor" {{ old('role') === 'editor' ? 'selected' : '' }}>Editor</option>
                        <option value="regular" {{ old('role') === 'regular' ? 'selected' : '' }}>Regular</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small class="muted">Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Multiple Users Form -->
    <form id="deleteMultipleForm" method="POST" action="/admin/users/delete-multiple" style="display: none;">
        @csrf
        <input type="hidden" name="ids" id="deleteMultipleIds">
    </form>

    <script>
        // Modal functions
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'flex';
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addUserModal');
            if (event.target === modal) {
                closeAddUserModal();
            }
        }

        // Select all checkbox functionality
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox:not([disabled])');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateDeleteButton();
        }

        // Update delete button state
        function updateDeleteButton() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
            deleteBtn.disabled = checkboxes.length === 0;
            
            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.row-checkbox:not([disabled])');
            const allChecked = allCheckboxes.length > 0 && 
                Array.from(allCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        }

        // Delete selected users
        function deleteSelectedUsers() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkboxes.length === 0) return;

            const userIds = Array.from(checkboxes).map(cb => cb.dataset.userId);
            const count = userIds.length;

            if (!confirm(`Are you sure you want to delete ${count} user(s)? This action cannot be undone.`)) {
                return;
            }

            document.getElementById('deleteMultipleIds').value = JSON.stringify(userIds);
            document.getElementById('deleteMultipleForm').submit();
        }

        // Auto-open modal if there are validation errors
        @if($errors->any() && old('name'))
            document.addEventListener('DOMContentLoaded', function() {
                openAddUserModal();
            });
        @endif
    </script>

    <style>
        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }


        .modal form {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }
    </style>
@endsection
