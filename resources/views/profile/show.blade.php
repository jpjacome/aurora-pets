<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pet->name }} | Aurora Pets</title>
    
    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/profile-template.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/favicon.png') }}" type="image/x-icon">

    {{-- Open Graph tags for social media sharing --}}
    <meta property="og:title" content="{{ $ogData['title'] }}">
    <meta property="og:description" content="{{ $ogData['description'] }}">
    <meta property="og:image" content="{{ $ogData['image'] }}">
    <meta property="og:url" content="{{ $ogData['url'] }}">
    <meta property="og:type" content="website">

    {{-- Twitter Card tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogData['title'] }}">
    <meta name="twitter:description" content="{{ $ogData['description'] }}">
    <meta name="twitter:image" content="{{ $ogData['image'] }}">
</head>

<body>
    <header>
        <img src="{{ asset('assets/home/imgs/logo4.png') }}" alt="Aurora Logo">
        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>
    
    <span id="mascota"></span>
    <div class="menu" id="menu">
        <span><a href="#mascota">Información mascota</a></span>
        <span><a href="#info-planta">Información planta</a></span>    
    </div>

    <main>
        <div class="wrapper">
            {{-- Pet Section --}}
            <div class="mascota">
                <div class="foto-mascota">
                    @if(count($petPhotos) > 0)
                        <div id="slider-mascota" class="slider-mascota">
                            @foreach($petPhotos as $photo)
                                <img src="{{ $photo }}" alt="{{ $pet->name }}" style="display: {{ $loop->first ? 'block' : 'none' }};">
                            @endforeach
                        </div>
                        @if(count($petPhotos) > 1)
                            <div class="thumbnails-mascota" id="thumbnails-mascota">
                                @foreach($petPhotos as $index => $photo)
                                    <img src="{{ $photo }}" alt="Thumbnail {{ $index + 1 }}" onclick="showSlideMascota({{ $index }})">
                                @endforeach
                            </div>
                        @endif
                    @else
                        <img src="{{ asset('assets/plantscan/default-pet.png') }}" alt="{{ $pet->name }}" class="default-image">
                    @endif
                </div>
                
                <div class="datos-mascota">
                    <h1>{{ $pet->name }}</h1>
                    @if($pet->breed)
                        <p>Raza: {{ $pet->breed }}</p>
                    @endif
                    @if($pet->birthday)
                        <p>Fecha de nacimiento: {{ $pet->birthday->format('d/m/Y') }}</p>
                    @endif
                    @if($petAge)
                        <p>Edad: {{ $petAge }}</p>
                    @endif
                </div>
            </div>
            
            <span id="info-planta"></span>
            
            {{-- Plant Section --}}
            @if($plantData['type'] !== 'none')
                <div class="planta">
                    <div class="foto-planta">
                        <div id="slider" class="slider">
                            @foreach($plantPhotos as $photo)
                                <img src="{{ $photo }}" alt="{{ $plantData['name'] }}" style="display: {{ $loop->first ? 'block' : 'none' }};">
                            @endforeach
                        </div>
                        @if(count($plantPhotos) > 1)
                            <div class="thumbnails" id="thumbnails">
                                @foreach($plantPhotos as $index => $photo)
                                    <img src="{{ $photo }}" alt="Thumbnail {{ $index + 1 }}" onclick="showSlide({{ $index }})">
                                @endforeach
                            </div>
                        @endif
                    </div>
                
                    <div class="datos-planta">
                        {{-- Display plant name --}}
                        <h3 id="tipodeplanta">
                            <strong>{{ $plantData['name'] }}</strong>
                        </h3>
                        
                        {{-- Show detailed info if we have plant data (final or test with matching plant) --}}
                        @if($plantData['hasCareInfo'] && $plantData['plant'])
                            @if($plantData['family'])
                                <p><strong>Familia:</strong> {{ $plantData['family'] }}</p>
                            @endif
                            
                            @if($plantData['species'])
                                <p><strong>Especie:</strong> {{ $plantData['species'] }}</p>
                            @endif
                            
                            @if($plantData['plant']->substrate_info)
                                <h3 class="description-title" onclick="toggleDescription(this)"><strong>🪴 Tipo de sustrato:</strong></h3>
                                <p class="description">{{ $plantData['plant']->substrate_info }}</p>
                            @endif
                            
                            @if($plantData['plant']->lighting_info)
                                <h3 class="description-title" onclick="toggleDescription(this)"><strong>☀️ Iluminación:</strong></h3>
                                <p class="description">{{ $plantData['plant']->lighting_info }}</p>
                            @endif
                            
                            @if($plantData['plant']->watering_info)
                                <h3 class="description-title" onclick="toggleDescription(this)"><strong>💦 Riego:</strong></h3>
                                <p class="description">{{ $plantData['plant']->watering_info }}</p>
                            @endif
                        @else
                            {{-- Test result plant but no matching plant found --}}
                            <div style="margin-top: 1rem; padding: 1rem; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #4CAF50;">
                                <p style="margin: 0; color: #555;">
                                    Esta es la planta sugerida según el test de personalidad de {{ $pet->name }}. 
                                    Pronto agregaremos información detallada sobre sus cuidados.
                                </p>
                            </div>
                        @endif
                        
                        <span id="desarrollo-planta"></span>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="info">
                <p>Quito, Ecuador.</p>
                <p>+593 99 784 402</p>
                <p>info@aurorapets.com</p>
            </div>
            <img src="{{ asset('assets/home/imgs/logo1.png') }}" alt="Aurora">
        </div>
    </footer>

    <script>
        // Hamburger menu toggle
        function toggleMenu() {
            const hamburger = document.querySelector('.hamburger');
            const menu = document.getElementById('menu');
            hamburger.classList.toggle('active');
            menu.classList.toggle('menu-active');
        }

        // Pet photo slider
        let currentIndexMascota = 0;
        function showSlideMascota(index) {
            const slides = document.querySelectorAll('#slider-mascota img');
            if (slides.length === 0) return;
            
            if (index >= slides.length) {
                currentIndexMascota = 0;
            } else if (index < 0) {
                currentIndexMascota = slides.length - 1;
            } else {
                currentIndexMascota = index;
            }
            
            slides.forEach(slide => slide.style.display = 'none');
            slides[currentIndexMascota].style.display = 'block';
        }

        // Plant photo slider
        let currentIndex = 0;
        function showSlide(index) {
            const slides = document.querySelectorAll('#slider img');
            if (slides.length === 0) return;
            
            if (index >= slides.length) {
                currentIndex = 0;
            } else if (index < 0) {
                currentIndex = slides.length - 1;
            } else {
                currentIndex = index;
            }
            
            slides.forEach(slide => slide.style.display = 'none');
            slides[currentIndex].style.display = 'block';
        }

        // Toggle description sections (accordion-style)
        function toggleDescription(element) {
            element.classList.toggle('description-title-on');
            const description = element.nextElementSibling;
            if (description && description.classList.contains('description')) {
                description.classList.toggle('description-on');
            }
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close menu after clicking on mobile
                    if (window.innerWidth <= 768) {
                        toggleMenu();
                    }
                }
            });
        });
    </script>
</body>
</html>
