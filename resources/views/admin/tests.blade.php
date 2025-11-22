@extends('admin.layout')

@section('title','Tests')

@section('content')
    <h1>Tests</h1>

    <div class="admin-toolbar">
        <div>
            Showing {{ $tests->count() }} of {{ $tests->total() }} tests
        </div>
        <div>
            <form method="GET" class="inline-form toolbar-form">
                <input type="search" name="q" placeholder="Search client, email, pet, plant..." value="{{ request('q') }}" />
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
                <th>ID</th>
                <th>Client</th>
                <th>Email</th>
                <th>Pet</th>
                <th>Species</th>
                <th>Gender</th>
                <th>Birthday</th>
                <th>Breed</th>
                <th>Weight</th>
                <th>Colors</th>
                <th>Living</th>
                <th>Characteristics</th>
                <th>Plant</th>
                <th>Plant #</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
        @forelse($tests as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->client }}</td>
                <td>{{ $t->email }}</td>
                <td>{{ $t->pet_name }}</td>
                <td>{{ $t->pet_species }}</td>
                <td>{{ $t->gender }}</td>
                <td>{{ optional($t->pet_birthday)->toDateString() }}</td>
                <td>{{ $t->pet_breed }}</td>
                <td>{{ $t->pet_weight }}</td>
                <td>{{ is_array($t->pet_color) ? implode(', ', $t->pet_color) : $t->pet_color }}</td>
                <td>{{ $t->living_space }}</td>
                <td>{{ is_array($t->pet_characteristics) ? implode(', ', $t->pet_characteristics) : $t->pet_characteristics }}</td>
                <td>{{ $t->plant_description ? ($t->plant . ' — ' . (strlen($t->plant_description) > 30 ? substr($t->plant_description,0,30) . '...' : $t->plant_description)) : $t->plant }}</td>
                <td>{{ $t->plant_number }}</td>
                <td>{{ $t->created_at->diffForHumans() }}</td>
            </tr>
        @empty
            <tr><td colspan="15">No tests found.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $tests->links('vendor.pagination.admin') }}
    </div>
@endsection
