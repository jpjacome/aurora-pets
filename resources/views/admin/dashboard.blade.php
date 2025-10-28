@extends('admin.layout')

@section('title','Dashboard')

@section('content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <h1 style="margin: 0;">Hello, {{ auth()->user()->name }}</h1>
        
        <form method="GET" action="/admin" style="display: flex; align-items: center; gap: 0.75rem;">
            <label for="days" style="font-size: 0.95rem; font-weight: 500; color: #333;">New entries from last:</label>
            <select name="days" id="days" onchange="this.form.submit()" style="padding: 0.5rem 1rem; border: 1px solid #ddd; border-radius: 8px; background: white; font-size: 0.95rem; cursor: pointer; font-family: inherit;">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 days</option>
                <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 days</option>
            </select>
        </form>
    </div>

    <div class="dashboard-grid">
        <!-- Clients Card -->
        <a href="/admin/clients" class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="ph ph-users dashboard-card-icon"></i>
                <h2>Clients</h2>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-stat-main">
                    <span class="dashboard-stat-value">{{ $totalClients }}</span>
                    <span class="dashboard-stat-label">Total Clients</span>
                </div>
                <div class="dashboard-hr"></div>
                <div class="dashboard-stat-secondary">
                    <span class="dashboard-stat-value-small">{{ $newClients }}</span>
                    <span class="dashboard-stat-label-small">New (last {{ $days }} days)</span>
                </div>
            </div>
        </a>

        <!-- Pets Card -->
        <a href="/admin/clients" class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="ph ph-paw-print dashboard-card-icon"></i>
                <h2>Pets</h2>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-stat-main">
                    <span class="dashboard-stat-value">{{ $activePets }}</span>
                    <span class="dashboard-stat-label">Prevention Plans</span>
                </div>
                <div class="dashboard-hr"></div>
                <div class="dashboard-stat-secondary">
                    <span class="dashboard-stat-value-small">{{ $deceasedPets }}</span>
                    <span class="dashboard-stat-label-small">Memorial</span>
                </div>
            </div>
        </a>

        <!-- Tests Card -->
        <a href="/admin/tests" class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="ph ph-test-tube dashboard-card-icon"></i>
                <h2>Tests</h2>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-stat-main">
                    <span class="dashboard-stat-value">{{ $totalTests }}</span>
                    <span class="dashboard-stat-label">Total Tests</span>
                </div>
                <div class="dashboard-hr"></div>
                <div class="dashboard-stat-secondary">
                    <span class="dashboard-stat-value-small">{{ $newTests }}</span>
                    <span class="dashboard-stat-label-small">New (last {{ $days }} days)</span>
                </div>
            </div>
        </a>
    </div>
@endsection
