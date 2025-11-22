@extends('admin.layout')

@section('title','Plants')

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
            <ul class="errors-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="plants-header">
        <h1>Plants</h1>
        
        <!-- Control Panel -->
        <div class="plants-controls">
            <button 
                id="addPlantBtn" 
                class="icon-btn icon-btn-primary" 
                title="Add New Plant"
                onclick="openAddPlantModal()"
            >
                <i class="ph ph-plus-circle icon-lg"></i>
            </button>

            <!-- Plantscan Image Generator Link -->
            <a 
                id="openImageGeneratorBtn"
                class="icon-btn icon-btn-secondary icon-btn-inline"
                href="/admin/images/generator"
                title="Generate PlantScan Image"
            >
                <i class="ph ph-camera icon-md"></i>
            </a>

            <button 
                id="deleteSelectedBtn" 
                class="icon-btn icon-btn-danger" 
                title="Delete Selected Plants"
                onclick="deleteSelectedPlants()"
                disabled
            >
                <i class="ph ph-trash icon-lg"></i>
            </button>
        </div>
    </div>

    <div class="admin-toolbar">
        <div>
            Showing {{ $plants->count() }} of {{ $plants->total() }} plants
        </div>
        <div>
            <form method="GET" class="inline-form toolbar-form">
                <input type="search" name="q" placeholder="Search name, family, species..." value="{{ request('q') }}" />
                <label>Per page:</label>
                <select name="perPage" onchange="this.form.submit()">
                    <option value="15" {{ request('perPage',15)==15? 'selected':'' }}>15</option>
                    <option value="50" {{ request('perPage')==50? 'selected':'' }}>50</option>
                    <option value="100" {{ request('perPage')==100? 'selected':'' }}>100</option>
                    <option value="all" {{ request('perPage')=='all'? 'selected':'' }}>All</option>
                </select>
                <button class="admin-toolbar-search" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="plant-grid">
        @forelse($plants as $plant)
            <div class="plant-card selectable-card {{ !$plant->is_active ? 'inactive-plant' : '' }}" data-plant-id="{{ $plant->id }}" onclick="toggleCardSelection(this, event)">
                <!-- Selection Checkbox -->
                <input 
                    type="checkbox" 
                    class="card-checkbox" 
                    data-plant-id="{{ $plant->id }}"
                    onclick="event.stopPropagation(); toggleCardSelection(this.parentElement, event)"
                >
                
                <div class="plant-card-header">
                    <div class="plant-card-meta">
                            <strong class="plant-name">{{ $plant->plant_type == 'Con flor' ? '�' : '�🌿' }} {{ $plant->name }}</strong>
                        @if(!$plant->is_active)
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                        @if($plant->difficulty)
                            <span class="badge badge-difficulty-{{ strtolower($plant->difficulty) }}">
                                {{ $plant->difficulty }}
                            </span>
                        @endif
                    </div>
                    <small class="muted">Plant #{{ $plant->plant_number ?? $plant->id }}</small>
                </div>

                <div class="plant-card-info">
                    @if($plant->plant_type)
                        <div><strong>Tipo:</strong> {{ $plant->plant_type }}</div>
                    @endif
                    @if($plant->family)
                        <div><strong>Family:</strong> {{ $plant->family }}</div>
                    @endif
                    @if($plant->species)
                        <div><strong>Species:</strong> {{ $plant->species }}</div>
                    @endif
                </div>

                {{-- Care information removed from plant card display per request --}}

                <div class="plant-card-footer">
                    <small class="muted">Created {{ $plant->created_at->diffForHumans() }}</small>
                    <a href="/admin/plants/{{ $plant->id }}/edit" class="edit-btn" title="Edit Plant">
                        <i class="ph ph-pencil-simple"></i> Edit
                    </a>
                </div>
            </div>
        @empty
            <div>No plants found.</div>
        @endforelse
    </div>

    <div class="pagination">
        {{ $plants->links('vendor.pagination.admin') }}
    </div>

    <!-- Add Plant Modal -->
    <div id="addPlantModal" class="modal" onclick="closeModalOnBackdrop(event)">
        <div class="modal-content modal-large" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>Add New Plant</h2>
                <button class="modal-close" onclick="closeAddPlantModal()">
                    <i class="ph ph-x icon-lg"></i>
                </button>
            </div>
            <form method="POST" action="/admin/plants/create" class="modal-form">
                @csrf
                
                <div class="form-section">
                    <h3><i class="ph ph-plant"></i> Basic Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Plant Name (Common) *</label>
                            <input type="text" id="name" name="name" required placeholder="e.g., Monstera Deliciosa">
                        </div>
                        
                        <div class="form-group">
                            <label for="scientific_name">Scientific Name</label>
                            <input type="text" id="scientific_name" name="scientific_name" placeholder="e.g., Monstera deliciosa">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="family">Family</label>
                            <input type="text" id="family" name="family" placeholder="e.g., Araceae">
                        </div>
                        
                        <div class="form-group">
                            <label for="species">Species</label>
                            <input type="text" id="species" name="species" placeholder="e.g., deliciosa">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="plant_number">PlantScan Number</label>
                            <input type="number" id="plant_number" name="plant_number" min="1" max="27" placeholder="1-27 (optional)">
                            <small class="form-help">Leave empty for auto-assignment</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="slug">URL Slug</label>
                            <input type="text" id="slug" name="slug" placeholder="monstera-deliciosa (optional)">
                            <small class="form-help">Auto-generated if left empty</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Active (available for PlantScan)</span>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="ph ph-info"></i> Care Information</h3>
                    
                    <div class="form-group">
                        <label for="substrate_info">🪴 Substrate Information</label>
                        <textarea id="substrate_info" name="substrate_info" rows="2" placeholder="Type of soil, drainage requirements..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="lighting_info">☀️ Lighting Information (Detailed)</label>
                        <textarea id="lighting_info" name="lighting_info" rows="2" placeholder="Detailed light requirements, ideal exposure..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="light_requirement">Light Requirement (Catalog)</label>
                            <input type="text" id="light_requirement" name="light_requirement" placeholder="e.g., Indirecta, Directa, Semisombra">
                            <small class="form-help">Short version for catalog display</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="watering_info">💦 Watering Information (Detailed)</label>
                        <textarea id="watering_info" name="watering_info" rows="2" placeholder="Detailed watering frequency, amount..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="water_requirement">Water Requirement (Catalog)</label>
                            <input type="text" id="water_requirement" name="water_requirement" placeholder="e.g., Abundante, Moderado, Escaso">
                            <small class="form-help">Short version for catalog display</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="difficulty">Difficulty Level</label>
                            <select id="difficulty" name="difficulty">
                                <option value="">Select difficulty</option>
                                <option value="Baja">Baja</option>
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="origin">Origin</label>
                            <input type="text" id="origin" name="origin" placeholder="e.g., Sudamérica, Asia, África">
                            <small class="form-help">Geographic origin</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="plant_type">Plant Type</label>
                        <select id="plant_type" name="plant_type">
                            <option value="">Select type</option>
                            <option value="Con flor">Con flor 🌺</option>
                            <option value="Foliar">Foliar 🌿</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">General Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="General description of the plant..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeAddPlantModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Create Plant</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedPlants = new Set();

        function toggleCardSelection(card, event) {
            const checkbox = card.querySelector('.card-checkbox');
            const plantId = card.dataset.plantId;
            
            // Toggle selection
            if (selectedPlants.has(plantId)) {
                selectedPlants.delete(plantId);
                card.classList.remove('selected');
                checkbox.checked = false;
            } else {
                selectedPlants.add(plantId);
                card.classList.add('selected');
                checkbox.checked = true;
            }
            
            updateDeleteButton();
        }

        function updateDeleteButton() {
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            deleteBtn.disabled = selectedPlants.size === 0;
            
            if (selectedPlants.size > 0) {
                deleteBtn.title = `Delete ${selectedPlants.size} selected plant(s)`;
            } else {
                deleteBtn.title = 'Delete Selected Plants';
            }
        }

        function deleteSelectedPlants() {
            if (selectedPlants.size === 0) return;
            
            const count = selectedPlants.size;
            const confirmed = confirm(`Are you sure you want to delete ${count} plant(s)? This will remove the plant assignment from any associated pets.`);
            
            if (confirmed) {
                // Create form to submit deletion
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/plants/delete-multiple';
                
                // CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // Add plant IDs
                selectedPlants.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'plant_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openAddPlantModal() {
            document.getElementById('addPlantModal').classList.add('active');
        }

        function closeAddPlantModal() {
            document.getElementById('addPlantModal').classList.remove('active');
        }

        function closeModalOnBackdrop(event) {
            if (event.target.id === 'addPlantModal') {
                closeAddPlantModal();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC to close modal
            if (e.key === 'Escape') {
                closeAddPlantModal();
            }
        });
    </script>
@endsection
