@extends('layouts.public')

@section('title', 'Aurora — Home2')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/aurora-general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home2.css') }}">
@endpush

@section('content')

    @include('partials.header')

    {{-- Background frame image (replaces video) --}}

    @push('head')
        {{-- GSAP core + TextPlugin for small headline animations --}}
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/TextPlugin.min.js"></script>
    @endpush

    {{-- Fullscreen loader (shows while app loads) --}}
    <div id="home2-loader" aria-hidden="false">
        <div class="home2-loader-inner">
            <video id="home2-loader-video" width="640" height="360" muted playsinline webkit-playsinline preload="auto" autoplay crossorigin>
                <source src="{{ asset('assets/vids/1.webm') }}" type="video/webm">
                Your browser does not support the video tag.
            </video>
            <div class="home2-loader-caption" role="status" aria-live="polite">
                <span class="loading-text">Loading</span>
                <span class="loading-dots" aria-hidden="true">
                    <span class="dot">.</span>
                    <span class="dot">.</span>
                    <span class="dot">.</span>
                </span>
            </div>
        </div>
    </div>
    {{-- Expose frames array to JS (ordered numeric ascending). If you change files, update this list accordingly. --}}


    {{-- Scrollable sections --}}
    <main class="sections">
        <div class="bg-wrap">
            <div class="bg-container">
                <img id="bg-frame" src="{{ asset('assets/home/imgs/frames/frame_13.webp') }}" alt="background frame" aria-hidden="true" />
            </div>
            <div class='sections-wrapper'>
                <section class="section section-1">
                    <div id="page-1" class="fade-in inner">
                        <h3><span class="highlights-c">Servicios funerarios para mascotas</span> Quito - Guayaquil - Cuenca</h3>
                        <h1 class="trigger"><span class="highlights-c">Celebra la vida</span> <br>de tu mascota</h1>
                        
                        <div class="text">
                            <p>Aurora es la primera <span class="highlights-c">urna compostable</span> diseñada para convertir las cenizas de <span class="highlights-c">nuestra mascota</span> en su planta favorita.</p>
                        </div>
                    </div>
                </section>
                
                <section class="section section-2">
                    
                    <img src="{{ asset('assets/urn.png') }}" alt="frame" aria-hidden="true" />
                    <div id="page-2" class="inner">
                        <h2>¿Cómo funciona?</h2>
                        <p>Aurora tiene una fórmula <span class="highlights-c">100% compostable </span>que proporciona el mejor hábitat posible para la planta en crecimiento y le permite asimilar las cenizas al crecer.<br></p>
                            <div class="container">
                                <span><img src="{{ asset('assets/how1.png') }}" alt=""></span>
                            </div>
                    </div>
                </section>
                
                <section class="section section-3">
                    <div id="page-3" class="inner">
                    </div>
                </section>
            </div>
        </div>
        
        <div class='wrapper-2'>
                <video class="page-bg-video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                    <source src="{{ asset('assets/vids/2.webm') }}" type="video/webm">
                    <!-- fallback: poster image will show in browsers that can't autoplay video -->
                </video>
            <section class="section section-4">
                <div class='image-container'>
                    <img src="{{ asset('assets/plantscan-logo.png') }}" alt="frame" aria-hidden="true" />
                </div>
                <div id="page-4" class="inner">
                    <div class='scan-container'>
                            <p>Descubre cuál es la planta favorita de tu mascota</p>
                            <button class="scan-button">Realizar test</button>               
                    </div>
                </div>
                <div id="page-5">
                </div>

            </section>
        </div>
            <section class="section-5">
                        <p><span class="highlights-b">Sabemos lo importante que es tu mascota.</span> Por eso en Aurora nos hemos comprometido para que cuentes con el apoyo y con <span class="highlights-b"> todos los servicios</span> que podrías necesitar en su partida:</p>
                        <div class="service-cards">
                            <div class="service-card">
                                <i class="ph ph-car"></i>
                                <div class="service-card-title">Recogida y cremación</div>
                                <div class="service-card-desc">Recogida a domicilio y cremación individual.</div>
                            </div>
                            <div class="service-card">
                                <i class="ph ph-leaf"></i>
                                <div class="service-card-title">Urna ecológica Aurora</div>
                                <div class="service-card-desc">Urna ecológica Aurora.</div>
                            </div>
                            <div class="service-card">
                                <i class="ph ph-plant"></i>
                                <div class="service-card-title">Asesoría de plantas</div>
                                <div class="service-card-desc">Asesoría para elegir la planta adecuada para ti.</div>
                            </div>
                            <div class="service-card">
                                <i class="ph ph-paw-print"></i>
                                <div class="service-card-title">Perfil digital</div>
                                <div class="service-card-desc">Perfil digital de tu mascota y planta en nuestra app, con cuidados y seguimiento.</div>
                            </div>
                            <div class="service-card">
                                <i class="ph ph-hand-heart"></i>
                                <div class="service-card-title">Acompañamiento</div>
                                <div class="service-card-desc">Acompañamiento en el proceso de adaptación de la planta.</div>
                            </div>
                        </div>
            </section>   
    </main>

<footer>

    <div class="container fade-in">
    <img src="{{ asset('assets/logo-hor.png') }}" alt="">
    <div class="info">
        <p>Quito, Ecuador.</p>
        <p>+593 9 9784 402</p>
        <p>info@aurorapets.com</p>
    </div>
    </div>
</footer>

@endsection




@push('scripts')
    <script>

        window.HOME2_FRAMES = [
            'frame_13.webp','frame_23.webp','frame_33.webp','frame_43.webp','frame_53.webp','frame_63.webp','frame_73.webp','frame_83.webp','frame_93.webp','frame_103.webp','frame_113.webp','frame_123.webp','frame_133.webp','frame_143.webp','frame_153.webp','frame_163.webp','frame_173.webp','frame_183.webp','frame_193.webp','frame_203.webp','frame_213.webp','frame_223.webp','frame_233.webp','frame_243.webp','frame_253.webp','frame_263.webp','frame_273.webp','frame_283.webp','frame_293.webp','frame_303.webp','frame_313.webp'
        ].map(name => '/assets/home/imgs/frames/' + name);

        // Replicate the small fade-in activation from home.blade (no jQuery needed)
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.fade-in').forEach(function(el){ el.classList.add('fade-in-1'); });
            document.querySelectorAll('.fade-in-b').forEach(function(el){ el.classList.add('fade-in-1'); });
        });
    </script>
    <script src="{{ asset('js/home2.js') }}"></script>
    <script src="{{ asset('js/home2-gsap.js') }}"></script>
    <script>
        // Loader: ensure it stays visible at least 5000ms (5s).
        (function(){
            const loader = document.getElementById('home2-loader');
            const vid = document.getElementById('home2-loader-video');
            let hidden = false;
            let hideTimer = null;
            const MIN_MS = 6000;
            const start = (window.performance && performance.now) ? performance.now() : Date.now();

            function clearHideTimer(){ if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; } }

            function hideLoader(){
                if (hidden) return; hidden = true;
                clearHideTimer();
                if (!loader) return;
                loader.style.transition = 'opacity 400ms ease';
                loader.style.opacity = '0';
                setTimeout(()=> loader.remove(), 450);
            }

            function scheduleHideAfter(ms){
                clearHideTimer();
                hideTimer = setTimeout(hideLoader, ms);
            }

            // Try to autoplay the loader video (muted allows autoplay on most browsers)
            if (vid){
                vid.play().catch(()=>{
                    // Autoplay failed or format unsupported. Replace with poster image as fallback
                    try {
                        const poster = vid.getAttribute('poster');
                        if (poster){
                            const img = document.createElement('img');
                            img.src = poster;
                            img.alt = 'loading';
                            img.style.maxWidth = '70vw';
                            img.style.maxHeight = '60vh';
                            img.style.borderRadius = '8px';
                            img.style.boxShadow = '0 8px 30px rgba(0,0,0,0.4)';
                            const holder = vid.parentNode;
                            if (holder) { holder.innerHTML = ''; holder.appendChild(img); }
                        }
                    } catch(e){}
                });
            }

            // Always ensure a fallback to hide after MIN_MS in case 'load' never fires
            scheduleHideAfter(MIN_MS);

            // When the window loads, wait the remaining time (if any) to reach MIN_MS, then hide
            window.addEventListener('load', function(){
                const now = (window.performance && performance.now) ? performance.now() : Date.now();
                const elapsed = Math.max(0, now - start);
                const remaining = Math.max(0, MIN_MS - elapsed);
                // small extra buffer for visual stability
                scheduleHideAfter(remaining + 80);
            });
        })();
    </script>
@endpush


