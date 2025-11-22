# Admin: On-demand Image Generation — Implementation Plan

Goal
----
Add an admin UI and server endpoints so site admins can compose and generate the PlantScan share image (the PNG currently created in `plantscan.blade.php`) on demand. Admins should be able to preview, download, and optionally email or store the generated image.

High-level approach
-------------------
- Reuse the client-side `generateStoryImage()` logic for fast WYSIWYG preview and browser download/attach.
- Add a server endpoint so admins can POST the generated dataURL (or request server-side generation) to store the image and trigger emails. This avoids CORS issues for externally-hosted images.
- Provide a fallback server-side renderer (Job/Controller) using the existing `GenerateOgImage` job or a lightweight Imagick/GD script when client-side export is unavailable due to CORS.
- Keep UI/UX consistent with current admin pages by reusing `admin/layout.blade.php` and the modal/form patterns in `admin/plants.blade.php`.

Files to create / modify
------------------------
- Create: `resources/views/admin/image-generator.blade.php` — Admin page (or modal partial) for composing and previewing images.
  - Purpose: form fields, live preview area, buttons for Preview, Download, Save, Email.
  - Styling: reuse classes like `modal`, `modal-content`, `btn-primary`, `icon-btn`, grid/card classes from `admin/plants.blade.php`.

- Modify: `resources/views/admin/plants.blade.php` (optional)
  - Purpose: add a small `Generate Image` button (open modal) next to each plant or pet row if desired.

- Create: `app/Http/Controllers/Admin/ImageGeneratorController.php`
  - Methods:
    - `index()` — show admin UI (GET /admin/images/generator)
    - `generateClientUpload(Request $r)` — accept POST with dataURL + metadata, save image to `storage/app/public/og-images`, return JSON with stored path and URL.
    - `generateServerSide(Request $r)` — accept payload (plant id / pet id / template data) and queue `GenerateOgImage` job for server-side generation (useful for bypassing CORS).
    - `emailImage(Request $r)` — send the stored image (or dataURL) using existing `PlantScanResultWithImageMail` or `PlantScanResultMail`.

- Modify: `routes/web.php`
  - Add routes under admin middleware (grouped with `auth`/admin guard):
    - GET `/admin/images/generator` -> ImageGeneratorController@index
    - POST `/admin/images/generator/upload` -> ImageGeneratorController@generateClientUpload
    - POST `/admin/images/generator/server` -> ImageGeneratorController@generateServerSide
    - POST `/admin/images/generator/email` -> ImageGeneratorController@emailImage

- Create: `app/Http/Requests/GenerateImageRequest.php` (optional but recommended)
  - Validate payloads (dataURL length limits, allowed keys, sanitized image URL, email format, size).

- Create: small JS helper `resources/js/admin-image-generator.js` (or inline in blade) that:
  - Reuses `generateStoryImage()` (import from `plantscan.blade.php` script or refactor `generateStoryImage` to a shared JS file under `resources/js/` so both public UI and admin can use same function).
  - Handles preview (open in new tab), convert canvas to dataURL, POST to `/admin/images/generator/upload`, show success/errors.
  - Calls server-side generate endpoint when admin chooses `Server Render` (for external images that would taint canvas).

- Storage: store generated images under `storage/app/public/og-images/` and expose via `public/storage/og-images` (ensure `php artisan storage:link` exists in deploy).

- Mail: Reuse `app/Mail/PlantScanResultWithImageMail.php` (exists) to send images. If it expects a file path or URL, adapt accordingly.

Design: UI & Behavior
---------------------
Two acceptable UI patterns (pick one):
1) Dedicated admin page: `GET /admin/images/generator` — a form with inputs + preview area. Use existing admin layout, full page. Good for multi-field composition.
2) Modal inside `admin/plants` or `admin/clients`: quick composer modal that opens pre-filled based on plant/pet/owner context.

Form fields (minimum):
- Pet name (text) — default from pet record (or free text).
- Plant name (text) — default from plant record.
- Description (textarea) — the sentence(s) that appear under plant image.
- Plant image URL (text) or file upload (optional) — if URL is external, warn about CORS; prefer supplying uploaded asset.
- Owner name/email (optional) — for emailing the result.
- Options: use client-side render (fast preview), server-side render (reliable but queued).

Buttons:
- Preview (Client) — open a new tab with the canvas PNG (reuses the same technique used in DevTools snippets).
- Download — client-side download (if canvas not tainted), else use server-stored URL.
- Save — POST the dataURL to server to store as asset and return a permanent URL.
- Email — save (if needed) and then send email using existing mail classes.

Data flow examples
------------------
Client-side preview + save flow:
1. Admin submits form -> client JS calls `generateStoryImage(data)` to draw canvas and get dataURL via `canvas.toDataURL('image/png')`.
2. JS POSTs { dataURL, metadata } to `/admin/images/generator/upload`.
3. Server decodes dataURL, validates size, saves file to storage, returns { url: '/storage/og-images/xxxx.png' }.
4. Admin can then click Email — POST `/admin/images/generator/email` with stored path and recipient email.

Server-side generation flow (fallback when CORS would taint canvas):
1. Admin submits form with plant image URL and metadata to `/admin/images/generator/server`.
2. Controller validates input, dispatches `GenerateOgImage` job (or internal generation using PHP ImageMagick), and returns a job id / expected URL when done.
3. The job creates the PNG into `storage/app/public/og-images` and optionally fires a notification/email.

API payload examples
--------------------
- POST /admin/images/generator/upload
  {
    "dataURL": "data:image/png;base64,iVBORw0...",
    "pet_name": "Milo",
    "plant_name": "Peace Lily",
    "metadata": { "pet_id": 12, "owner_id": 42 }
  }

- POST /admin/images/generator/server
  {
    "plant_image_url": "https://.../plant.jpg",
    "pet_name": "Milo",
    "description": "Loves low light",
    "owner_email": "owner@example.com",
    "mode": "immediate|queued"
  }

Security & validation
---------------------
- Routes must be inside an admin-auth middleware group (already used by other admin routes). Require proper authorization.
- CSRF: all POST forms must include CSRF tokens.
- Validate all inputs server-side. Reject extremely large images; limit base64 size and decoded file size (e.g., max 3–8 MB depending on storage strategy).
- Sanitize text fields (description) and limit lengths (e.g., description max 600 chars). The canvas has display limits; warn admins.
- If accepting external image URLs for server-side processing, ensure the server only downloads from allowed sources or performs content-type checks to avoid SSRF. Use a download timeout and max bytes.
- If using client-submitted dataURLs, decode carefully and verify MIME type and binary headers.

CORS note
---------
- Client-side canvas export requires the source image to be CORS-enabled (image server must set Access-Control-Allow-Origin: * or the same origin). If admins provide external image URLs that do not set CORS headers, the canvas will be tainted and `toDataURL()` will throw or return an unusable result.
- Provide UI guidance and a `Server Render` option to avoid this problem.

Reuse & refactor recommendations
--------------------------------
- Move `generateStoryImage()` out of `plantscan.blade.php` into `resources/js/shared/generateStoryImage.js` (or create a function that returns the canvas given a data object). Then import/compile it into public JS so the admin UI can reuse it.
- Keep font-loading code portable: centralize the fonts setup used by the canvas to a shared JS module.
- Use existing `GenerateOgImage` job (in `app/Jobs/GenerateOgImage.php`) as starting point for server-side generation. Adapt parameters (pet/plant metadata) and ensure output filename conventions match storage location.

Testing plan
------------
1. Unit & Feature tests (PHPUnit):
   - Feature test for `POST /admin/images/generator/upload` using a small sample dataURL; assert file saved and DB entry or response structure.
   - Feature test for `POST /admin/images/generator/server` enqueues the job and returns expected response.
   - Authorization tests: unauthenticated or non-admin requests must be denied.

2. Manual browser tests:
   - Use the admin UI to create an image with a local plant asset (no CORS). Verify preview/download works.
   - Try using an external image without CORS; confirm canvas is tainted and client-side download fails but server-side path works.
   - Test email flow: generate and email the PNG to a test inbox.

3. Visual regression (optional):
   - Keep a small set of canonical outputs (PNG) and visually compare generated images with a baseline for important templates.

Quality gates
-------------
- Build: `npm run dev` (or appropriate Vite command) must compile the shared JS if created.
- Lint/Typecheck: Ensure no new PHP syntax or lint errors.
- Tests: Run PHPUnit Feature tests for the new endpoints. Aim for green before merging.

Implementation checklist (incremental)
-------------------------------------
1. Create `resources/views/admin/image-generator.blade.php` with basic form and preview area. (Small scope: GET page only)
2. Add route + controller `index()` to render the page.
3. Copy or refactor `generateStoryImage()` into a shared JS module and include it in the admin page. Wire Preview -> open new tab using current canvas logic.
4. Implement client-side Upload: POST dataURL to server endpoint; implement server controller `generateClientUpload` to decode, validate and save file.
5. Implement Email endpoint that takes stored image path and sends email using existing `PlantScanResultWithImageMail`.
6. Implement server-side generation endpoint that queues `GenerateOgImage` when external images taint the canvas.
7. Add PHPUnit feature tests for upload, server-job queueing, and email flow.
8. Polish UI, add success/error alerts and storage URL listing in admin.

Timeline & effort estimate (rough)
---------------------------------
- Small MVP (admin page + client preview + upload + save): 1–2 days
- Emailing + server-side generation fallback + tests: +1–2 days
- Polish, visual tests, edge-case hardening (SSRF/CORS): +1 day

Assumptions & open questions
---------------------------
- Where should saved image metadata live? (Filesystem only is okay initially; a DB table `admin_images` could be added later if tracking is required.)
- Should admins be able to pick existing `Pet` or `Plant` records and auto-fill fields? (Recommended: yes — add a small select dropdown populated server-side.)
- Existing mail classes `PlantScanResultMail` and `PlantScanResultWithImageMail` appear in `app/Mail/` — confirm their constructor signatures to pass either image URL or attachment.

Next immediate step (I will implement if you want)
-------------------------------------------------
- Create `IMPLEMENTATION_PLAN.md` (this file) — done.
- If you want me to proceed: I can scaffold `resources/views/admin/image-generator.blade.php` and `app/Http/Controllers/Admin/ImageGeneratorController.php` with the minimal MVP: admin page, route, JS for preview and client upload, and server endpoint that saves base64 dataURLs to `storage/app/public/og-images` and returns a JSON URL.

If you want me to proceed with scaffolding the files listed under "Files to create / modify", say "Please scaffold the admin image generator MVP" and I'll create the Blade, controller, route, and a minimal JS file and tests as a next step.

---

Scaffold examples (copy-ready)
------------------------------
Below are copy-paste-ready examples you can use as a scaffold for the MVP. I kept them small and dependency-free so they integrate easily.

1) Blade: `resources/views/admin/image-generator.blade.php`

```blade
@extends('admin.layout')

@section('title','Image Generator')

@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <h1 style="margin:0;">Image Generator</h1>
    <div>
      <a href="/admin/plants" class="btn-secondary">Back</a>
    </div>
  </div>

  <div class="admin-grid" style="gap:1.5rem;display:grid;grid-template-columns:1fr 420px;">
    <div>
      <form id="imageForm">
        @csrf
        <div class="form-group">
          <label for="pet_name">Pet name</label>
          <input id="pet_name" name="pet_name" type="text" value="Milo" />
        </div>
        <div class="form-group">
          <label for="plant_name">Plant name</label>
          <input id="plant_name" name="plant_name" type="text" value="Peace Lily" />
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="4">A resilient indoor friend that tolerates low light.</textarea>
        </div>
        <div class="form-group">
          <label for="plant_image_url">Plant image URL</label>
          <input id="plant_image_url" name="plant_image_url" type="text" placeholder="/storage/plants/plant1.jpg" />
          <small class="muted">If external, CORS may block client-side download. Use Server Render if so.</small>
        </div>

        <div style="display:flex;gap:0.5rem;margin-top:1rem;">
          <button type="button" id="previewBtn" class="btn-primary">Preview</button>
          <button type="button" id="downloadBtn" class="btn-secondary">Download</button>
          <button type="button" id="saveBtn" class="btn-primary">Save</button>
          <button type="button" id="serverRenderBtn" class="btn-secondary">Server Render</button>
        </div>
      </form>
    </div>

    <aside style="border:1px solid #eee;padding:1rem;border-radius:6px;">
      <h4>Preview</h4>
      <div id="previewArea" style="width:100%;height:760px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;">
        <canvas id="previewCanvas" width="1080" height="1600" style="max-width:100%;height:auto;display:none;"></canvas>
        <div id="previewPlaceholder" class="muted">Use Preview to render canvas here</div>
      </div>
    </aside>
  </div>

  <script src="/js/admin-image-generator.js"></script>
@endsection
```

2) Controller: `app/Http/Controllers/Admin/ImageGeneratorController.php` (minimal)

```php
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageGeneratorController extends Controller
{
    public function index()
    {
        return view('admin.image-generator');
    }

    // Accepts { dataURL, pet_name, plant_name, metadata }
    public function upload(Request $r)
    {
        $r->validate([ 'dataURL' => 'required|string' ]);

        $data = $r->input('dataURL');
        if (!preg_match('/^data:image\/(png|jpeg);base64,/', $data, $m)) {
            return response()->json(['error' => 'Invalid image data'], 422);
        }

        $mime = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $raw = substr($data, strpos($data, ',') + 1);
        $decoded = base64_decode($raw);
        if ($decoded === false) return response()->json(['error' => 'Could not decode'], 422);

        // Limit size (example: 6MB)
        if (strlen($decoded) > 6 * 1024 * 1024) {
            return response()->json(['error' => 'Image too large'], 413);
        }

        $filename = 'og-' . Str::random(10) . '.' . $mime;
        Storage::disk('public')->put('og-images/' . $filename, $decoded);

        return response()->json([ 'url' => Storage::url('og-images/' . $filename) ]);
    }

    // Server-side render (enqueue job or run inline)
    public function server(Request $r)
    {
        $r->validate([ 'plant_image_url' => 'required|string' ]);
        // For MVP, just return a placeholder.
        // Ideally dispatch new \App\Jobs\GenerateOgImage($payload)
        return response()->json(['status' => 'queued']);
    }
}
```

3) Routes: add to `routes/web.php` (inside admin auth group)

```php
// Admin image generator
Route::middleware(['auth','admin'])->prefix('admin')->group(function () {
    Route::get('/images/generator', [\App\Http\Controllers\Admin\ImageGeneratorController::class, 'index']);
    Route::post('/images/generator/upload', [\App\Http\Controllers\Admin\ImageGeneratorController::class, 'upload']);
    Route::post('/images/generator/server', [\App\Http\Controllers\Admin\ImageGeneratorController::class, 'server']);
});
```

4) Client JS: `resources/js/admin-image-generator.js` (minimal, uses canvas draw stub)

```javascript
// Minimal helper: draw a simple image + text to canvas and return dataURL
document.addEventListener('DOMContentLoaded', function () {
  const previewCanvas = document.getElementById('previewCanvas');
  const previewPlaceholder = document.getElementById('previewPlaceholder');

  function drawPreview(data) {
    const ctx = previewCanvas.getContext('2d');
    previewCanvas.style.display = 'block';
    previewPlaceholder.style.display = 'none';
    ctx.fillStyle = '#fff';
    ctx.fillRect(0,0,previewCanvas.width,previewCanvas.height);

    // Load image
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function () {
      const iw = img.width, ih = img.height;
      const maxW = 800;
      const scale = Math.min(maxW / iw, 1);
      const dw = iw * scale;
      const dh = ih * scale;
      ctx.drawImage(img, (previewCanvas.width - dw)/2, 120, dw, dh);

      // Pet name
      ctx.fillStyle = '#222';
      ctx.font = 'bold 56px "Chunkfive", Georgia, serif';
      ctx.fillText(data.pet_name || '', 80, 80);

      // Description (simple)
      ctx.font = '28px "Buenard", serif';
      ctx.fillStyle = '#444';
      wrapText(ctx, data.description || '', 80, previewCanvas.height - 220, 800, 36, 3);
    };
    img.onerror = function () { alert('Could not load image (CORS or URL)'); };
    img.src = data.plant_image_url;
  }

  function wrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines) {
    const words = text.split(' ');
    let line = '';
    let lineCount = 0;
    for (let n = 0; n < words.length; n++) {
      const testLine = line + words[n] + ' ';
      const metrics = ctx.measureText(testLine);
      if (metrics.width > maxWidth && n > 0) {
        ctx.fillText(line, x, y + lineCount * lineHeight);
        line = words[n] + ' ';
        lineCount++;
        if (lineCount >= maxLines) return;
      } else {
        line = testLine;
      }
    }
    if (lineCount < maxLines) ctx.fillText(line, x, y + lineCount * lineHeight);
  }

  document.getElementById('previewBtn').addEventListener('click', function () {
    const data = {
      pet_name: document.getElementById('pet_name').value,
      plant_name: document.getElementById('plant_name').value,
      description: document.getElementById('description').value,
      plant_image_url: document.getElementById('plant_image_url').value
    };
    drawPreview(data);
  });

  // Save: capture dataURL and POST to upload endpoint
  document.getElementById('saveBtn').addEventListener('click', function () {
    const canvas = previewCanvas;
    if (!canvas) return alert('Preview first');
    try {
      const dataURL = canvas.toDataURL('image/png');
      fetch('/admin/images/generator/upload', {
        method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: JSON.stringify({ dataURL, pet_name: document.getElementById('pet_name').value })
      }).then(r=>r.json()).then(j=>{
        if (j.url) window.open(j.url, '_blank'); else alert('Save error');
      });
    } catch (e) { alert('Canvas export failed (likely CORS): ' + e.message); }
  });
});
```

Notes on these examples
----------------------
- The JS preview uses a tiny draw routine — replace with the shared `generateStoryImage()` to match the exact visual output.
- The controller `upload()` decodes base64 and writes to the `public` disk. Ensure `FILESYSTEM_DRIVER` and `storage:link` are configured.
- Add route middleware and auth checks to match the rest of the admin routes.

If you'd like, I can now scaffold these files directly (Blade, Controller, routes, and JS), run a quick smoke check (route:list) and add a PHPUnit feature test for the upload endpoint. Say "Scaffold the MVP now" and I'll implement the scaffold and run verification steps.