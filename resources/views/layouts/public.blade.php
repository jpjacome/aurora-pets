<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Aurora ofrece servicios funerarios dignos para mascotas en Ecuador. Urnas biodegradables que transforman las cenizas en plantas. Cremación en Quito, Guayaquil y Cuenca.')">
    <meta name="keywords" content="@yield('meta_keywords', 'urnas mascotas, cremación mascotas Ecuador, urna biodegradable, servicios funerarios mascotas, Quito, Guayaquil, Cuenca')">
    <title>@yield('title', 'Aurora Pets')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    
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
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VX4252T3K9"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-VX4252T3K9');
    </script>
</head>
<body>
    @yield('content')
    
    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
