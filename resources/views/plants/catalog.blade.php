@extends('layouts.public')

@section('title', 'Aurora - Catálogo de Plantas de Interior')

@push('head')
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Buenard:wght@400;700&display=swap" rel="stylesheet">
@endpush

@push('styles')
    <style>
        :root {
            --color-1: #fe8d2c;
            --color-2: #04472b;
            --color-3: #dcffd6;
            scroll-behavior: smooth;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Buenard', Verdana, Geneva, Tahoma, sans-serif;
            background-color: var(--color-2);
            background-image: url('/assets/home/imgs/bg8.png');
            background-size: cover;
            background-position: right;
            background-attachment: fixed;
            overflow-x: hidden;
            color: var(--color-3);
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 400;
            font-size: 4em;
            color: var(--color-3);
            text-align: center;
            margin: 11rem 2rem 0;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6em;
            font-weight: 400;
            color: var(--color-1);
            margin: 0 1rem 0;
        }

        h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1em;
            font-weight: 400;
            color: var(--color-3);
            margin: 0 1rem 0.5rem;
        }

        .highlights-c {
            color: var(--color-1);
        }

        /* Main Container */
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            min-height: 100vh;
            padding: 0 2rem 4rem;
        }

        /* Plant Grid */
        .plant-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4rem;
            justify-content: center;
            margin-top: 4rem;
            margin-bottom: 6rem;
            width: 100%;
            max-width: 1400px;
        }

        .plant {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: calc(25% - 3rem);
            min-width: 280px;
            background-color: var(--color-2);
            aspect-ratio: 1;
            border-radius: 10px;
            box-shadow: 5px 5px 15px rgba(18, 53, 39, 0.8);
            transition: transform 0.3s ease;
            position: relative;
        }

        .plant:hover {
            transform: translateY(-5px);
        }

        .plant img {
            width: 90%;
            margin: 1rem 5% 0;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 10px;
            filter: grayscale(0.4);
            cursor: pointer;
            transition: filter 0.3s ease, transform 0.3s ease;
        }

        .plant img:hover {
            filter: grayscale(0);
            transform: scale(1.02);
        }

        .plant-info {
            padding: 0 1rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .plant-info h3 {
            margin: 0;
            font-size: 0.9em;
        }

        /* Fullscreen Image Modal */
        .plant-fullsize {
            visibility: hidden;
            opacity: 0;
            position: fixed;
            display: flex;
            width: 100vw;
            height: 100vh;
            top: 0;
            left: 0;
            align-items: center;
            justify-content: center;
            background-color: rgba(4, 71, 43, 0.95);
            backdrop-filter: blur(10px);
            cursor: zoom-out;
            transition: opacity 0.3s ease;
            z-index: 9999;
        }

        .plant-fullsize.fullsize-active {
            opacity: 1;
            visibility: visible;
        }

        .plant-fullsize img {
            max-height: 80vh;
            max-width: 90vw;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        /* Responsive */
        @media only screen and (max-width: 800px) {
            h1 {
                font-size: 2.5em;
                margin-top: 8rem;
            }

            .plant-container {
                gap: 2rem;
            }

            .plant {
                width: 80%;
                min-width: 250px;
            }
        }

        /* Loading fade-in animation */
        .fade-in {
            opacity: 0;
            animation: fadeIn 1s ease-in forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .no-plants {
            text-align: center;
            padding: 4rem 2rem;
            font-size: 1.2em;
            color: var(--color-3);
        }
    </style>
@endpush

@section('content')

    @include('partials.header')

    <div class="main-container">
        <h1 class="fade-in">Catálogo de <span class="highlights-c">Plantas de Interior</span></h1>

        <div class="plant-container">
            @forelse($plants as $plant)
                <div class="plant fade-in" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                    {{-- Prioritize admin-uploaded photos (in photos array) over seeded default_photo --}}
                    @if($plant->photos && count($plant->photos) > 0)
                        @php
                            $photoPath = $plant->photos[0];
                            $photoSrc = str_starts_with($photoPath, 'plant-photos/') 
                                ? Storage::url($photoPath)
                                : (str_starts_with($photoPath, 'http') ? $photoPath : asset($photoPath));
                        @endphp
                        <img 
                            class="plant-img" 
                            src="{{ $photoSrc }}" 
                            alt="{{ $plant->name }}"
                            onclick="openFullscreen(this)"
                        >
                    @elseif($plant->default_photo)
                        @php
                            // Check if it's a storage path (uploaded by admin) or static asset path (from seeder)
                            $imageSrc = str_starts_with($plant->default_photo, 'plant-photos/') 
                                ? Storage::url($plant->default_photo)  // Storage path: plant-photos/xyz.png
                                : asset($plant->default_photo);         // Static path: /assets/plantscan/imgs/plants/xyz.png
                        @endphp
                        <img 
                            class="plant-img" 
                            src="{{ $imageSrc }}" 
                            alt="{{ $plant->name }}"
                            onclick="openFullscreen(this)"
                        >
                    @else
                        <img 
                            class="plant-img" 
                            src="{{ asset('assets/plantscan/imgs/plants/default-plant.png') }}" 
                            alt="{{ $plant->name }}"
                        >
                    @endif

                    <h2 class="plant-name">{{ $plant->name }}</h2>
                    
                    <div class="plant-info">
                        @if($plant->difficulty)
                            <h3><strong>Dificultad:</strong> {{ $plant->difficulty }}</h3>
                        @endif
                        
                        @if($plant->origin)
                            <h3><strong>Origen:</strong> {{ $plant->origin }}</h3>
                        @endif
                        
                        @if($plant->light_requirement)
                            <h3><strong>Luz:</strong> {{ $plant->light_requirement }}</h3>
                        @endif
                        
                        @if($plant->water_requirement)
                            <h3><strong>Riego:</strong> {{ $plant->water_requirement }}</h3>
                        @endif
                    </div>
                </div>
            @empty
                <div class="no-plants">
                    <p>No hay plantas disponibles en el catálogo en este momento.</p>
                </div>
            @endforelse
        </div>

        <!-- Fullscreen Image Modal -->
        <div class="plant-fullsize" onclick="closeFullscreen()">
            <img class="plant-fullsize-img" src="" alt="Plant fullsize">
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openFullscreen(imgElement) {
            const fullsizeDiv = document.querySelector('.plant-fullsize');
            const fullsizeImg = document.querySelector('.plant-fullsize-img');
            
            fullsizeImg.src = imgElement.src;
            fullsizeDiv.classList.add('fullsize-active');
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeFullscreen() {
            const fullsizeDiv = document.querySelector('.plant-fullsize');
            fullsizeDiv.classList.remove('fullsize-active');
            
            // Restore body scroll
            document.body.style.overflow = '';
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFullscreen();
            }
        });

        // Fade-in animations on load (consistent with login page)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.fade-in').forEach(el => el.classList.add('fade-in-1'));
        });
    </script>
@endpush
