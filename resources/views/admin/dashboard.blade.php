@extends('admin.layout')

@section('title','Dashboard')

@section('content')
    <div class="dashboard-header">
        <h1>Hello, {{ auth()->user()->name }}</h1>
        
        <form method="GET" action="/admin">
            <label for="days">New entries from last:</label>
            <select name="days" id="days" onchange="this.form.submit()">
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
