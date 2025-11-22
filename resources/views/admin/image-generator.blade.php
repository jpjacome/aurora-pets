@extends('admin.layout')

@section('title','Image Generator')

@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <h1 style="margin:0;">Image Generator</h1>
    <div>
      <a href="/admin/plants" class="btn-secondary">Back</a>
    </div>
  </div>

  <div class="admin-grid" style="gap:1.5rem;display:flex;flex-wrap:wrap;align-items:flex-start;">
    <div style="flex:1 1 600px;min-width:280px;">
      <form id="imageForm">
        @csrf
        <div class="form-row">
          <div class="form-group" style="flex:1;">
            <label for="plant_select">Choose plant</label>
            <select id="plant_select" style="width:100%;">
              <option value="">-- Select plant --</option>
              @foreach($plants as $p)
                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group" style="flex:1;">
            <label for="pet_select">Choose pet</label>
            <select id="pet_select" style="width:100%;">
              <option value="">-- Select pet --</option>
              @foreach($pets as $pet)
                <option value="{{ $pet['id'] }}">{{ $pet['name'] }}{{ $pet['client'] ? ' — ' . $pet['client'] : '' }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="pet_name">Pet name (editable)</label>
          <input id="pet_name" name="pet_name" type="text" value="" />
        </div>
        <div class="form-group">
          <label for="plant_name">Plant name (editable)</label>
          <input id="plant_name" name="plant_name" type="text" value="" />
        </div>
        <div class="form-group">
          <label for="inspirations">Inspirations / Virtues (comma-separated)</label>
          <input id="inspirations" name="inspirations" type="text" placeholder="e.g. valiente, curioso, tierno" />
          <small class="muted">These appear under the pet's name on the image, separated by " - "</small>
        </div>
        <div class="form-group">
          <label for="description">Description (editable)</label>
          <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
          <label for="plant_image_url">Plant image URL (editable)</label>
          <input id="plant_image_url" name="plant_image_url" type="text" placeholder="/storage/plants/plant1.jpg" />
          <small class="muted">If external, CORS may block client-side download. Use Server Render if so.</small>
        </div>

        <div class="admin-buttons"">
          <button type="button" id="previewBtn" class="btn-primary">Preview</button>
          <button type="button" id="downloadBtn" class="btn-secondary">Download</button>
          <button type="button" id="saveBtn" class="btn-primary">Save</button>
          <button type="button" id="serverRenderBtn" class="btn-secondary">Server Render</button>
        </div>
      </form>
    </div>

  <aside class="preview-wrapper">
      <h4>Preview</h4>
      <div id="previewArea">
        <canvas id="previewCanvas" width="1080" height="1600" style="max-width:100%;height:auto;display:none;"></canvas>
        <div id="previewPlaceholder" class="muted">Use Preview to render canvas here</div>
      </div>
    </aside>
  </div>

  <script>
    // Provide asset URLs used by the shared generator (so we don't need to change the public view)
    window.PLANTSCAN_ASSETS = {
      topLogo: '{{ asset("assets/plantscan-logo.png") }}',
      logo: '{{ asset("assets/plantscan/imgs/logo.png") }}'
    };
    // Provide plant/pet data for the admin UI
    window.PLANT_DATA = {!! json_encode($plants ?? []) !!};
    window.PET_DATA = {!! json_encode($pets ?? []) !!};
  </script>
  <script src="/js/shared/generateStoryImage.js"></script>
  <script src="/js/admin-image-generator.js"></script>
@endsection
