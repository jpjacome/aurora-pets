<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $og['title'] }}</title>

    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ $og['url'] }}" />
    <meta property="og:title" content="{{ $og['title'] }}" />
    <meta property="og:description" content="{{ $og['description'] }}" />
    <meta property="og:image" content="{{ $og['image'] }}" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $og['title'] }}" />
    <meta name="twitter:description" content="{{ $og['description'] }}" />
    <meta name="twitter:image" content="{{ $og['image'] }}" />

    <link rel="stylesheet" href="/css/aurora-general.css">
    <link rel="stylesheet" href="/css/prevention-style.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="permalink-result">
        <div class="container">
            <h1>{{ $test->plant ?? 'Schefflera' }}</h1>
            <p>La planta de {{ $test->pet_name ?? 'tu mascota' }}.</p>
            <img src="{{ $og['image'] }}" alt="{{ $test->plant }}" style="max-width:600px; width:100%;">
            <p>{{ $test->plant_description }}</p>
            <p><a href="/plantscan">Volver al PlantScan</a></p>
        </div>
    </div>
</body>
</html>
