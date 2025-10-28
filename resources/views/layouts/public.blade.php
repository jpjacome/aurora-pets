<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aurora Pets')</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/home/imgs/icono1.ico') }}" type="image/x-icon">
    
    <!-- Stylesheets -->
    <!-- Global styles (loaded for all pages) -->
    <link rel="stylesheet" href="{{ asset('css/aurora-general.css') }}">
    @stack('styles')
    
    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="@yield('og_title', 'Aurora')">
    <meta property="og:description" content="@yield('og_description', 'Servicios funerarios para mascotas en Ecuador')">
    <meta property="og:image" content="@yield('og_image', asset('assets/home/imgs/11.png'))">
    <meta property="og:url" content="@yield('og_url', 'https://auroraurn.pet')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', 'Aurora')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Servicios funerarios para mascotas en Ecuador')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('assets/home/imgs/11.png'))">
    
    <!-- Additional Head Content -->
    @stack('head')
    <!-- Phosphor Icons (web) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    @yield('content')
    
    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
