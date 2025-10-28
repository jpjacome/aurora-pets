<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { margin:0; padding:0; font-family: 'Buenard', serif; }
    .card { width:1200px; height:630px; display:flex; align-items:center; justify-content:center; background: #f7f9f6; }
    .content { text-align:left; display:flex; align-items:center; gap:28px; padding:40px; }
    .plant { width:420px; height:420px; background:#fff; display:flex; align-items:center; justify-content:center; border-radius:12px; overflow:hidden; }
    .plant img { width:100%; height:100%; object-fit:cover; }
    .meta { max-width:620px; }
    .title { font-size:48px; font-weight:700; color:#16311a; margin-bottom:12px; }
    .subtitle { font-size:28px; color:#3b7660; }
  </style>
  </head>
<body>
  <div class="card">
    <div class="content">
      <div class="plant">
        @php
          $img = $test->og_image ? url('storage/' . $test->og_image) : url('./assets/plantscan/imgs/plants/' . (strtolower($test->plant) ?: 'schefflera.png'));
        @endphp
        <img src="{{ $img }}" alt="{{ $test->plant }}">
      </div>
      <div class="meta">
        <div class="title">La planta de {{ $test->pet_name ?? 'tu mascota' }}</div>
        <div class="subtitle">{{ $test->plant ?? 'Schefflera' }}</div>
      </div>
    </div>
  </div>
</body>
</html>
