@extends('admin.layout')

@section('title','Clients')

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
        <h1 style="margin: 0;">Clients</h1>
        
        <!-- Control Panel -->
        <div style="display: flex; gap: 1rem; align-items: center;">
            <button 
                id="addClientBtn" 
                class="icon-btn icon-btn-primary" 
                title="Add New Client"
                onclick="openAddClientModal()"
            >
                <i class="ph ph-plus-circle" style="font-size: 1.5rem;"></i>
            </button>
            <button 
                id="deleteSelectedBtn" 
                class="icon-btn icon-btn-danger" 
                title="Delete Selected Clients"
                onclick="deleteSelectedClients()"
                disabled
            >
                <i class="ph ph-trash" style="font-size: 1.5rem;"></i>
            </button>
        </div>
    </div>

    <div class="admin-toolbar">
        <div>
            Showing {{ $clients->count() }} of {{ $clients->total() }} clients
        </div>
        <div>
            <form method="GET" class="inline-form toolbar-form">
                <input type="search" name="q" placeholder="Search name, email, pet..." value="{{ request('q') }}" />
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
    <div class="client-grid">
        @forelse($clients as $c)
            <div class="client-card selectable-card" data-client-id="{{ $c->id }}" onclick="toggleCardSelection(this, event)">
                <!-- Selection Checkbox -->
                <input 
                    type="checkbox" 
                    class="card-checkbox" 
                    data-client-id="{{ $c->id }}"
                    onclick="event.stopPropagation(); toggleCardSelection(this.parentElement, event)"
                >
                <div class="client-card-header">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <strong>{{ $c->client ?? '—' }}</strong>
                        <a href="/admin/clients/{{ $c->id }}/edit" class="edit-client-btn" title="Edit Client" onclick="event.stopPropagation();">
                            <i class="ph ph-pencil-simple"></i>
                        </a>
                        @if($c->campaigns_count > 0)
                            <span class="badge badge-count" title="{{ $c->campaigns_count }} email campaign(s)" style="font-size: 0.75rem;">
                                📧 {{ $c->campaigns_count }}
                            </span>
                        @endif
                    </div>
                    <small class="muted">{{ $c->created_at->diffForHumans() }}</small>
                </div>
                <div class="client-card-info">
                    <div><strong>Email:</strong> <a href="mailto:{{ $c->email }}">{{ $c->email }}</a></div>
                    @if($c->phone)<div><strong>Phone:</strong> {{ $c->phone }}</div>@endif
                    @if($c->address)<div><strong>Address:</strong> {{ $c->address }}</div>@endif
                </div>
                <hr class="client-hr">
                <div class="client-details">
                    @php
                        $hasPetsInRelationship = $c->pets->count() > 0;
                        $hasOldPetData = !empty($c->pet_name);
                    @endphp

                    @if($hasPetsInRelationship)
                        {{-- NEW STRUCTURE: Show pets from pets table --}}
                        <div><strong>Pets:</strong> {{ $c->pets->count() }}</div>
                        @foreach($c->pets as $pet)
                            <div style="margin-left: 1rem; padding: 0.5rem 0; border-left: 3px solid #fe8d2c; padding-left: 0.75rem;">
                                {{-- Pet Name (Always Visible - Clickable) --}}
                                <div 
                                    class="pet-name-toggle"
                                    style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem;" 
                                    onclick="togglePetDetails(this, event)"
                                    title="Click to expand/collapse details"
                                >
                                    <i class="ph ph-caret-right" style="transition: transform 0.3s ease; font-size: 1rem;"></i>
                                    <strong>🐾 {{ $pet->name }}</strong> 
                                    <span class="muted">({{ $pet->species ?? '—' }})</span>
                                </div>
                                
                                {{-- Pet Details (Collapsible) --}}
                                <div class="pet-details-collapse" style="display: none; margin-top: 0.5rem; padding-left: 1.5rem;">
                                    @if($pet->breed)<div><strong>Breed:</strong> {{ $pet->breed }}</div>@endif
                                    @if($pet->gender)<div><strong>Gender:</strong> {{ $pet->gender }}</div>@endif
                                    @if($pet->birthday)<div><strong>Birthday:</strong> {{ $pet->birthday->format('Y-m-d') }} ({{ $pet->birthday->diffForHumans() }})</div>@endif
                                    @if($pet->weight)<div><strong>Weight:</strong> {{ $pet->weight }}</div>@endif
                                    @if($pet->color)<div><strong>Colors:</strong> {{ is_array($pet->color) ? implode(', ', $pet->color) : $pet->color }}</div>@endif
                                    @if($pet->living_space)<div><strong>Living:</strong> {{ $pet->living_space }}</div>@endif
                                    @if($pet->characteristics)<div><strong>Traits:</strong> {{ is_array($pet->characteristics) ? implode(', ', $pet->characteristics) : $pet->characteristics }}</div>@endif
                                    
                                    {{-- Test Result Plant --}}
                                    @if($pet->plant_test)
                                        <div><strong>🌱 Test Result:</strong> {{ $pet->plant_test }}</div>
                                    @endif
                                    
                                    {{-- Final Associated Plant --}}
                                    @if($pet->plant)
                                        <div><strong>🌿 Final Plant:</strong> {{ $pet->plant->name }} <span class="muted">({{ $pet->plant->family ?? '—' }})</span></div>
                                    @endif
                                    
                                    @if($pet->profile_slug)
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <strong>Profile:</strong> 
                                            <a href="/profile/{{ $pet->profile_slug }}" target="_blank" style="flex: 1;" onclick="event.stopPropagation();">{{ $pet->profile_slug }}</a>
                                            <a href="/admin/pets/{{ $pet->id }}/edit" class="edit-profile-btn" title="Edit Profile" onclick="event.stopPropagation();">
                                                <i class="ph ph-pencil-simple"></i>
                                            </a>
                                        </div>
                                    @endif
                                    @if($pet->deceased)
                                        <div style="color: #999;"><strong>⚰️ Deceased:</strong> {{ $pet->deceased_at ? $pet->deceased_at->format('Y-m-d') : 'Yes' }}</div>
                                    @endif
                                </div>
                            </div>
                            @if(!$loop->last)<hr style="margin: 0.5rem 0;">@endif
                        @endforeach
                    @elseif($hasOldPetData)
                        {{-- OLD STRUCTURE: Show pet data from clients table columns --}}
                        <div style="background: #fff3cd; padding: 0.5rem; border-radius: 6px; margin-bottom: 0.5rem;">
                            <small style="color: #856404;">⚠️ Legacy data (needs migration)</small>
                        </div>
                        <div style="margin-left: 1rem; padding: 0.5rem 0; border-left: 3px solid #ffc107; padding-left: 0.75rem;">
                            <div><strong>🐾 {{ $c->pet_name }}</strong> <span class="muted">({{ $c->pet_species ?? '—' }})</span></div>
                            @if($c->pet_breed)<div><strong>Breed:</strong> {{ $c->pet_breed }}</div>@endif
                            @if($c->gender)<div><strong>Gender:</strong> {{ $c->gender }}</div>@endif
                            @if($c->pet_birthday)<div><strong>Birthday:</strong> {{ $c->pet_birthday->format('Y-m-d') }} ({{ $c->pet_birthday->diffForHumans() }})</div>@endif
                            @if($c->pet_weight)<div><strong>Weight:</strong> {{ $c->pet_weight }}</div>@endif
                            @if($c->pet_color)<div><strong>Colors:</strong> {{ is_array($c->pet_color) ? implode(', ', $c->pet_color) : $c->pet_color }}</div>@endif
                            @if($c->living_space)<div><strong>Living:</strong> {{ $c->living_space }}</div>@endif
                            @if($c->pet_characteristics)<div><strong>Traits:</strong> {{ is_array($c->pet_characteristics) ? implode(', ', $c->pet_characteristics) : $c->pet_characteristics }}</div>@endif
                            @if($c->plant)
                                <div><strong>🌿 Plant:</strong> {{ $c->plant }}</div>
                            @endif
                            @if($c->plant_description)
                                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 4px; font-size: 0.9rem;">
                                    {{ Str::limit($c->plant_description, 150) }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="muted">No pets registered yet</div>
                    @endif
                </div>
            </div>
        @empty
            <div>No clients found.</div>
        @endforelse
    </div>

    <div class="pagination">
        {{ $clients->links() }}
    </div>

    <!-- Add Client Modal -->
    <div id="addClientModal" class="modal" onclick="closeModalOnBackdrop(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>Add New Client</h2>
                <button class="modal-close" onclick="closeAddClientModal()">
                    <i class="ph ph-x" style="font-size: 1.5rem;"></i>
                </button>
            </div>
            <form method="POST" action="/admin/clients/create" class="modal-form">
                @csrf
                <div class="form-group">
                    <label for="client_name">Client Name *</label>
                    <input type="text" id="client_name" name="client" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeAddClientModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Create Client</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedClients = new Set();

        function togglePetDetails(element, event) {
            event.stopPropagation(); // Prevent card selection
            
            const detailsDiv = element.nextElementSibling;
            const caretIcon = element.querySelector('i');
            
            if (detailsDiv && detailsDiv.classList.contains('pet-details-collapse')) {
                // Toggle visibility
                if (detailsDiv.style.display === 'none') {
                    detailsDiv.style.display = 'block';
                    caretIcon.style.transform = 'rotate(90deg)';
                } else {
                    detailsDiv.style.display = 'none';
                    caretIcon.style.transform = 'rotate(0deg)';
                }
            }
        }

        function toggleCardSelection(card, event) {
            const checkbox = card.querySelector('.card-checkbox');
            const clientId = card.dataset.clientId;
            
            // Toggle selection
            if (selectedClients.has(clientId)) {
                selectedClients.delete(clientId);
                card.classList.remove('selected');
                checkbox.checked = false;
            } else {
                selectedClients.add(clientId);
                card.classList.add('selected');
                checkbox.checked = true;
            }
            
            updateDeleteButton();
        }

        function updateDeleteButton() {
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            deleteBtn.disabled = selectedClients.size === 0;
            
            if (selectedClients.size > 0) {
                deleteBtn.title = `Delete ${selectedClients.size} selected client(s)`;
            } else {
                deleteBtn.title = 'Delete Selected Clients';
            }
        }

        function deleteSelectedClients() {
            if (selectedClients.size === 0) return;
            
            const count = selectedClients.size;
            const confirmed = confirm(`Are you sure you want to delete ${count} client(s)? This will also delete all their pets and cannot be undone.`);
            
            if (confirmed) {
                // Create form to submit deletion
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/clients/delete-multiple';
                
                // CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // Add client IDs
                selectedClients.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'client_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openAddClientModal() {
            document.getElementById('addClientModal').classList.add('active');
        }

        function closeAddClientModal() {
            document.getElementById('addClientModal').classList.remove('active');
        }

        function closeModalOnBackdrop(event) {
            if (event.target.id === 'addClientModal') {
                closeAddClientModal();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC to close modal
            if (e.key === 'Escape') {
                closeAddClientModal();
            }
        });
    </script>
@endsection
