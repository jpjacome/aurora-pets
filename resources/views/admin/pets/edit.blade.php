@extends('admin.layout')

@section('title', 'Edit Pet Profile')

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
            <h1>Edit Pet Profile: {{ $pet->name }}</h1>
            <p class="muted">Owner: {{ $pet->client->client }} ({{ $pet->client->email }})</p>
            @if($pet->profile_slug)
                <p>
                    <strong>Public Profile:</strong> 
                    <a href="/profile/{{ $pet->profile_slug }}" target="_blank" class="profile-link">
                        /profile/{{ $pet->profile_slug }}
                        <i class="ph ph-arrow-square-out"></i>
                    </a>
                </p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('admin.pets.update', $pet->id) }}" enctype="multipart/form-data" class="edit-form">
        @csrf
        
        <div class="form-section">
            <h2><i class="ph ph-info"></i> Basic Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Pet Name *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $pet->name) }}" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="species">Species</label>
                    <input 
                        type="text" 
                        id="species" 
                        name="species" 
                        value="{{ old('species', $pet->species) }}"
                        placeholder="e.g., Dog, Cat, Hamster"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="breed">Breed</label>
                    <input 
                        type="text" 
                        id="breed" 
                        name="breed" 
                        value="{{ old('breed', $pet->breed) }}"
                        placeholder="e.g., Golden Retriever"
                    >
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $pet->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $pet->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="birthday">Birthday</label>
                    <input 
                        type="date" 
                        id="birthday" 
                        name="birthday" 
                        value="{{ old('birthday', $pet->birthday?->format('Y-m-d')) }}"
                    >
                </div>
                
                <div class="form-group">
                    <label for="weight">Weight</label>
                    <input 
                        type="text" 
                        id="weight" 
                        name="weight" 
                        value="{{ old('weight', $pet->weight) }}"
                        placeholder="e.g., 5-10kg"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="living_space">Living Space</label>
                <input 
                    type="text" 
                    id="living_space" 
                    name="living_space" 
                    value="{{ old('living_space', $pet->living_space) }}"
                    placeholder="e.g., House with garden"
                >
            </div>
        </div>

        <div class="form-section">
            <h2><i class="ph ph-plant"></i> Associated Plant</h2>
            
            <div class="form-group">
                <label for="plant_id">Plant</label>
                <select id="plant_id" name="plant_id" class="plant-select">
                    <option value="">No plant assigned</option>
                    @foreach($plants as $plant)
                        <option 
                            value="{{ $plant->id }}" 
                            {{ old('plant_id', $pet->plant_id) == $plant->id ? 'selected' : '' }}
                        >
                            {{ $plant->name }} ({{ $plant->family }})
                        </option>
                    @endforeach
                </select>
                <small class="form-help">Select the plant associated with this pet from the PlantScan test</small>
            </div>

            @if($pet->plant)
                <div class="current-plant-info">
                    <p><strong>Current Plant:</strong> {{ $pet->plant->name }}</p>
                    <p><strong>Family:</strong> {{ $pet->plant->family }}</p>
                    @if($pet->plant->species)
                        <p><strong>Species:</strong> {{ $pet->plant->species }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="form-section">
            <h2><i class="ph ph-images"></i> Pet Photos</h2>
            
            <div class="form-group">
                <label for="profile_photo">Main Profile Photo</label>
                <input 
                    type="file" 
                    id="profile_photo" 
                    name="profile_photo" 
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    onchange="previewProfilePhoto(event)"
                >
                <small class="form-help">This will be the primary photo shown first on the profile (JPEG, PNG, WEBP - max 5MB)</small>
                
                @if($pet->profile_photo)
                    <div class="current-photo">
                        <img 
                            id="profile-photo-preview" 
                            src="{{ Storage::url($pet->profile_photo) }}" 
                            alt="{{ $pet->name }}"
                        >
                        <p class="muted"><strong>Current main profile photo</strong></p>
                    </div>
                @else
                    <div class="current-photo" id="profile-photo-preview-container" style="display: none;">
                        <img id="profile-photo-preview" src="" alt="Preview">
                        <p class="muted">New profile photo (preview)</p>
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="pet_photos">Additional Photos (Gallery)</label>
                <input 
                    type="file" 
                    id="pet_photos" 
                    name="pet_photos[]" 
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    multiple
                    onchange="previewAdditionalPhotos(event)"
                >
                <small class="form-help">These will appear as a gallery below the main photo. Upload multiple at once (JPEG, PNG, WEBP - max 5MB each)</small>
                
                <div id="new-photos-preview" class="photos-preview"></div>

                @if($pet->photos && count($pet->photos) > 0)
                    <div class="existing-photos">
                        <h4>Current Additional Photos</h4>
                        <div class="photo-grid">
                            @foreach($pet->photos as $photo)
                                <div class="photo-item" data-photo-path="{{ $photo }}">
                                    <img src="{{ Storage::url($photo) }}" alt="{{ $pet->name }}">
                                    <button 
                                        type="button" 
                                        class="delete-photo-btn" 
                                        onclick="deletePhoto('{{ $photo }}')"
                                        title="Delete photo"
                                    >
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="form-section">
            <h2><i class="ph ph-warning"></i> Status</h2>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input 
                        type="checkbox" 
                        id="deceased" 
                        name="deceased" 
                        value="1"
                        {{ old('deceased', $pet->deceased) ? 'checked' : '' }}
                        onchange="toggleDeceasedDate()"
                    >
                    <span>Pet is deceased</span>
                </label>
            </div>

            <div class="form-group" id="deceased-date-group" style="{{ old('deceased', $pet->deceased) ? '' : 'display: none;' }}">
                <label for="deceased_at">Date of Death</label>
                <input 
                    type="date" 
                    id="deceased_at" 
                    name="deceased_at" 
                    value="{{ old('deceased_at', $pet->deceased_at?->format('Y-m-d')) }}"
                >
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.clients') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="ph ph-floppy-disk"></i> Save Changes
            </button>
        </div>
    </form>

    <script>
        function toggleDeceasedDate() {
            const checkbox = document.getElementById('deceased');
            const dateGroup = document.getElementById('deceased-date-group');
            
            if (checkbox.checked) {
                dateGroup.style.display = 'block';
            } else {
                dateGroup.style.display = 'none';
                document.getElementById('deceased_at').value = '';
            }
        }

        function previewProfilePhoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profile-photo-preview');
                const container = document.getElementById('profile-photo-preview-container');
                
                preview.src = e.target.result;
                
                if (container) {
                    container.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }

        function previewAdditionalPhotos(event) {
            const files = event.target.files;
            const previewContainer = document.getElementById('new-photos-preview');
            previewContainer.innerHTML = '';

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        function deletePhoto(photoPath) {
            if (!confirm('Are you sure you want to delete this photo? This cannot be undone.')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.pets.deletePhoto', $pet->id) }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const photoInput = document.createElement('input');
            photoInput.type = 'hidden';
            photoInput.name = 'photo_path';
            photoInput.value = photoPath;
            form.appendChild(photoInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
@endsection
