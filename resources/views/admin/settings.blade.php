@extends('admin.layout')

@section('title', 'Admin Settings')

@section('content')
<div class="settings-container">
    <h1 class="admin-page-title">Admin Settings</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="settings-card">
        <h2 class="settings-section-title">Profile Information</h2>
        
        <form method="POST" action="/admin/settings/profile" class="settings-form">
            @csrf
            
            <div class="form-group">
                <label for="name" class="form-label">Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', auth()->user()->name) }}" 
                    class="form-input"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', auth()->user()->email) }}" 
                    class="form-input"
                    required
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk"></i>
                    Update Profile
                </button>
            </div>
        </form>
    </div>

    <div class="settings-card">
        <h2 class="settings-section-title">Change Password</h2>
        
        <form method="POST" action="/admin/settings/password" class="settings-form">
            @csrf
            
            <div class="form-group">
                <label for="current_password" class="form-label">Current Password</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    class="form-input"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">New Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="form-input"
                    required
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-lock-key"></i>
                    Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
}

.admin-page-title {
    font-size: 2rem;
    font-family: 'Playfair Display', serif;
    color: var(--color-2);
    margin-bottom: 2rem;
}

.settings-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--color-gray-200);
}

.settings-section-title {
    font-size: 1.5rem;
    font-family: 'Playfair Display', serif;
    color: var(--color-2);
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--color-2);
}

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--color-text);
    font-size: 0.95rem;
}

.form-input {
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-gray-300);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: var(--transition-base);
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: var(--color-2);
    box-shadow: 0 0 0 3px rgba(var(--color-2-rgb), 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius-md);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-base);
    text-decoration: none;
    font-family: inherit;
}

.btn-primary {
    background: var(--color-2);
    color: var(--color-1);
}

.btn-primary:hover {
    background: var(--color-1);
    color: var(--color-2);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: var(--radius-md);
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

.alert li {
    margin: 0.25rem 0;
}
</style>
@endsection
