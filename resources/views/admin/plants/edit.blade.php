@extends('admin.layout')

@section('title', 'Edit Plant')

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
            <a href="{{ route('admin.plants') }}" class="back-link">
                <i class="ph ph-arrow-left"></i> Back to Plants
            </a>
            <h1>🌿 Edit Plant: {{ $plant->name }}</h1>
            <p class="muted">Plant #{{ $plant->plant_number ?? $plant->id }}</p>
            <p><strong>Assigned to {{ $plant->pets_count }} pet(s)</strong></p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.plants.update', $plant->id) }}" enctype="multipart/form-data" class="edit-form">
        @csrf
        
        <div class="form-section">
            <h2><i class="ph ph-plant"></i> Información Básica</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre de Planta (Común) *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $plant->name) }}" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="scientific_name">Nombre Científico</label>
                    <input 
                        type="text" 
                        id="scientific_name" 
                        name="scientific_name" 
                        value="{{ old('scientific_name', $plant->scientific_name) }}"
                        placeholder="ej., Monstera deliciosa"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="family">Familia</label>
                    <input 
                        type="text" 
                        id="family" 
                        name="family" 
                        value="{{ old('family', $plant->family) }}"
                        placeholder="ej., Araceae"
                    >
                </div>
                
                <div class="form-group">
                    <label for="species">Especie</label>
                    <input 
                        type="text" 
                        id="species" 
                        name="species" 
                        value="{{ old('species', $plant->species) }}"
                        placeholder="ej., deliciosa"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="plant_number">Número PlantScan</label>
                    <input 
                        type="number" 
                        id="plant_number" 
                        name="plant_number" 
                        value="{{ old('plant_number', $plant->plant_number) }}"
                        min="1" 
                        max="27"
                        placeholder="1-27"
                        disabled
                    >
                    <label class="checkbox-label" style="margin-top: 0.5rem;">
                        <input 
                            type="checkbox" 
                            id="enable_plant_number" 
                            onchange="document.getElementById('plant_number').disabled = !this.checked"
                        >
                        <span>Marcar para cambiar número PlantScan</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug URL</label>
                    <input 
                        type="text" 
                        id="slug" 
                        name="slug" 
                        value="{{ old('slug', $plant->slug) }}"
                        placeholder="monstera-deliciosa"
                    >
                    <small class="form-help">Nombre amigable para URL</small>
                </div>
            </div>

            <div class="form-group">
                <label>Disponible</label>
                <div class="radio-group" style="display: flex; gap: 2rem;">
                    <label class="radio-label" style="display: flex; align-items: center;">
                        <input 
                            type="radio" 
                            name="is_active" 
                            value="1"
                            {{ old('is_active', $plant->is_active) == '1' || old('is_active', $plant->is_active) === true ? 'checked' : '' }}
                        >
                        <span>Sí</span>
                    </label>
                    <label class="radio-label" style="display: flex; align-items: center;">
                        <input 
                            type="radio" 
                            name="is_active" 
                            value="0"
                            {{ old('is_active', $plant->is_active) == '0' || old('is_active', $plant->is_active) === false ? 'checked' : '' }}
                        >
                        <span>No</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2><i class="ph ph-info"></i> Información de Cuidados</h2>
            
            <div class="form-group">
                <label for="substrate_info">🪴 Información de Sustrato</label>
                <textarea 
                    id="substrate_info" 
                    name="substrate_info" 
                    rows="3"
                    placeholder="Tipo de tierra, requisitos de drenaje..."
                >{{ old('substrate_info', $plant->substrate_info) }}</textarea>
            </div>

            <div class="form-group">
                <label for="lighting_info">☀️ Información de Iluminación (Detallada)</label>
                <textarea 
                    id="lighting_info" 
                    name="lighting_info" 
                    rows="3"
                    placeholder="Requisitos de luz detallados, exposición ideal..."
                >{{ old('lighting_info', $plant->lighting_info) }}</textarea>
            </div>

            <div class="form-group">
                <label for="light_requirement">Requisito de Luz (Catálogo)</label>
                <input 
                    type="text" 
                    id="light_requirement" 
                    name="light_requirement" 
                    value="{{ old('light_requirement', $plant->light_requirement) }}"
                    placeholder="ej., Indirecta, Directa, Semisombra"
                >
                <small class="form-help">Versión corta para mostrar en catálogo</small>
            </div>

            <div class="form-group">
                <label for="watering_info">💦 Información de Riego (Detallada)</label>
                <textarea 
                    id="watering_info" 
                    name="watering_info" 
                    rows="3"
                    placeholder="Frecuencia de riego detallada, cantidad..."
                >{{ old('watering_info', $plant->watering_info) }}</textarea>
            </div>

            <div class="form-group">
                <label for="water_requirement">Requisito de Riego (Catálogo)</label>
                <input 
                    type="text" 
                    id="water_requirement" 
                    name="water_requirement" 
                    value="{{ old('water_requirement', $plant->water_requirement) }}"
                    placeholder="ej., Abundante, Moderado, Escaso"
                >
                <small class="form-help">Versión corta para mostrar en catálogo</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="difficulty">Nivel de Dificultad</label>
                    <select id="difficulty" name="difficulty">
                        <option value="">Seleccionar dificultad</option>
                        <option value="Baja" {{ old('difficulty', $plant->difficulty) == 'Baja' ? 'selected' : '' }}>Baja</option>
                        <option value="Media" {{ old('difficulty', $plant->difficulty) == 'Media' ? 'selected' : '' }}>Media</option>
                        <option value="Alta" {{ old('difficulty', $plant->difficulty) == 'Alta' ? 'selected' : '' }}>Alta</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="origin">Origen</label>
                    <input 
                        type="text" 
                        id="origin" 
                        name="origin" 
                        value="{{ old('origin', $plant->origin) }}"
                        placeholder="ej., Sudamérica, Asia, África"
                    >
                    <small class="form-help">Origen geográfico</small>
                </div>
            </div>

            <div class="form-group">
                <label for="plant_type">Tipo de Planta</label>
                <select id="plant_type" name="plant_type">
                    <option value="">Seleccionar tipo</option>
                    <option value="Con flor" {{ old('plant_type', $plant->plant_type) == 'Con flor' ? 'selected' : '' }}>Con flor 🌺</option>
                    <option value="Foliar" {{ old('plant_type', $plant->plant_type) == 'Foliar' ? 'selected' : '' }}>Foliar 🌿</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Descripción General</label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="4"
                    maxlength="225"
                    placeholder="Descripción general de la planta..."
                >{{ old('description', $plant->description) }}</textarea>
                <small id="description-counter" class="form-help">0 / 225</small>
            </div>
        </div>

        <div class="form-section">
            <h2><i class="ph ph-images"></i> Fotos de Planta</h2>
            
            <div class="form-group">
                <label for="plant_photos">Subir Fotos</label>
                <input 
                    type="file" 
                    id="plant_photos" 
                    name="plant_photos[]" 
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    multiple
                    onchange="previewPlantPhotos(event)"
                >
                <small class="form-help">Subir múltiples fotos (JPEG, PNG, WEBP - máx 5MB cada una)</small>
                
                <div id="new-photos-preview" class="photos-preview"></div>

                @if($plant->photos && count($plant->photos) > 0)
                    <div class="existing-photos">
                        <h4>Fotos Existentes</h4>
                        <div class="photo-grid">
                            @foreach($plant->photos as $photo)
                                <div class="photo-item" data-photo-path="{{ $photo }}">
                                    <img src="{{ Storage::url($photo) }}" alt="{{ $plant->name }}">
                                    <button 
                                        type="button" 
                                        class="delete-photo-btn" 
                                        onclick="deletePhoto('{{ $photo }}')"
                                        title="Eliminar foto"
                                    >
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="muted" style="margin-top: 1rem;">No se han subido fotos aún</p>
                @endif
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.plants') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">
                <i class="ph ph-floppy-disk"></i> Guardar Cambios
            </button>
        </div>
    </form>

    <script>
        function previewPlantPhotos(event) {
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
            if (!confirm('¿Estás seguro de que quieres eliminar esta foto? Esto no se puede deshacer.')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.plants.deletePhoto', $plant->id) }}';

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

        // Character counter for description (max 225)
        (function() {
            const textarea = document.getElementById('description');
            const counter = document.getElementById('description-counter');
            const MAX = 225;

            if (!textarea || !counter) return;

            function updateCounter() {
                const len = textarea.value.length;
                counter.textContent = `${len} / ${MAX}`;
                if (len > MAX) {
                    // Trim excess (shouldn't normally happen because of maxlength)
                    textarea.value = textarea.value.slice(0, MAX);
                    counter.textContent = `${MAX} / ${MAX}`;
                }
            }

            // Initialize
            updateCounter();

            // Update on input
            textarea.addEventListener('input', updateCounter);
        })();
    </script>
@endsection
