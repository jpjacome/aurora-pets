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
        <div class='sections-wrapper'>
        <div class="bg-wrap">
            <div class="bg-container">
                <img id="bg-frame" src="{{ asset('assets/home/imgs/frames/frame_13.webp') }}" alt="background frame" aria-hidden="true" />
            </div>            
        </div>
            <section class="section section-1">
                <div id="page-1" class="fade-in inner">
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
                            <span><img id="how-image" src="{{ asset('assets/how1.png') }}" alt=""></span>
                        </div>
                </div>
            </section>
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

            </section>
        </div>

            
            <section class='section section-5'>
                <div class='container-1'>
                    <p>Toma decisiones con calma y amor</p>
                    <h2><span class="highlights-c">Plan Preventivo</span> Aurora</h2>
                    <div class="service-cards">
                        <h3>¿Por qué planificar con anticipación?</h3>
                        <div class="service-card">
                            <i class="service-card-icon ph ph-shield-check"></i>
                            <div class="service-card-title">Evita tomar decisiones en momentos de vulnerabilidad<span> — envía un mensaje y nosotros nos encargamos de todo</span></div>
                        </div>                        
                        <div class="service-card">
                            <i class="service-card-icon ph ph-currency-dollar"></i>
                            <div class="service-card-title">Reserva con un depósito inicial de $35</div>
                        </div>
                        <div class="service-card">
                            <i class="service-card-icon ph ph-lock"></i>
                            <div class="service-card-title">Precio total garantizado sin aumentos</div>
                        </div>
                        <div class="service-card">
                            <i class="service-card-icon ph ph-credit-card"></i>
                            <div class="service-card-title">Facilidades de pago</div>
                        </div>
                    </div>
                    <button class="plan-button">Contáctanos</button>
                </div>
                <div class='container-2'>
                    
                </div>
            </section>

            <section class="section-6">
                <h2>Calcula <span class="highlights-b">el precio de tus servicios</span></h2>
                        <p><span class="highlights-b">Sabemos lo importante que es tu mascota para ti.</span> Por eso en <span class="highlights-b">Aurora</span> nos hemos comprometido para que cuentes con el apoyo y con <span class="highlights-b"> todos los servicios</span> que podrías necesitar en su partida:</p>
                        <div class="container">

                            <div class="service-cards">
                                <h3>Servicios incluidos:</h3>
                                <div class="service-card">
                                    <i class="service-card-icon ph ph-ambulance"></i>
                                    <div class="service-card-title">Recogida y cremación <i class="ph ph-check included-check" aria-hidden="true"></i></div>
                                </div>
                                <div class="service-card">
                                    <i class="service-card-icon ph ph-leaf"></i>
                                    <div class="service-card-title">Urna ecológica Aurora <i class="ph ph-check included-check" aria-hidden="true"></i></div>
                                </div>
                                <div class="service-card">
                                    <i class="service-card-icon ph ph-plant"></i>
                                    <div class="service-card-title">Asesoría de plantas <i class="ph ph-check included-check" aria-hidden="true"></i></div>
                                </div>
                                <div class="service-card">
                                    <i class="service-card-icon ph ph-paw-print"></i>
                                    <div class="service-card-title">Perfil digital <i class="ph ph-check included-check" aria-hidden="true"></i></div>
                                </div>
                                <div class="service-card">
                                    <i class="service-card-icon ph ph-hand-heart"></i>
                                    <div class="service-card-title">Acompañamiento <i class="ph ph-check included-check" aria-hidden="true"></i></div>
                                </div>
                            </div>
                            
                            <div class='price-calculator-wrapper'>
                                <label for="weight-range" class="sr-only">Peso de tu mascota:</label>
                                <div class='container'>
                                    <select id="weight-range" name="weight_range" class="price-select">
                                        <option value="0-5">0 - 5 kg</option>
                                        <option value="5-10">5 - 10 kg</option>
                                        <option value="10-15">10 - 15 kg</option>
                                        <option value="15-25">15 - 25 kg</option>
                                        <option value="25-35">25 - 35 kg</option>
                                        <option value="35-plus">&gt;35 kg</option>
                                    </select>
                                </div>
                                <div class="service-card">
                                    <i class="ph ph-plus-circle"></i>
                                    <div class="service-card-title">Adicionales:
                                        <div class="extras-list" role="group" aria-label="Servicios adicionales">
                                            <label class="extra-item"><input type="checkbox" name="extras[]" value="maceta">Maceta ($10)</label>
                                            <label class="extra-item"><input type="checkbox" name="extras[]" value="cuidado">Cuidado de la planta a domicilio ($20 mensual)</label>
                                            <label class="extra-item"><input type="checkbox" name="extras[]" value="eutanasia">Eutanasia ($50)</label>
                                            <label class="extra-item"><input type="checkbox" name="extras[]" value="transplante">Transplante ($25)</label>
                                        </div>
                                    </div>
                                </div>
                                <div id="price-result" class="price-result" aria-live="polite">
                                    <div class="price-breakdown">
                                        <div class="price-rows">
                                            <div class="price-row"><span class="price-label">Subtotal:</span> <span id="subtotal-price">$140.00</span></div>
                                            <div class="price-row"><span class="price-label">IVA (15%):</span> <span id="tax-price">$21.00</span></div>
                                        </div>
                                        <div class="price-row price-final"><span id="total-price-label">Total (incl. IVA):</span> <span id="total-price">$161.00</span></div>
                                    </div>
                                    <div class="price-fee-control">
                                        <label class="card-fee-label"><input type="checkbox" id="include-card-fee">5% adicional tarjeta de crédito</label>
                                    </div>
                                </div>
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

    <script>
        // Price calculator: computes base price from weight band + extras and updates #total-price.
        (function(){
            // Base prices by select value
            var basePrices = {
                '0-5': 140,
                '5-10': 170,
                '10-15': 200,
                '15-25': 220,
                '25-35': 250,
                '35-plus': 265
            };

            // Extras prices (values match the checkbox values)
            var extrasPrices = {
                'maceta': 10,
                'cuidado': 20,
                'eutanasia': 50,
                'transplante': 25
            };

            function formatCurrency(n){
                return '$' + Number(n).toFixed(2);
            }

            function calculateTotal(){
                var sel = document.getElementById('weight-range');
                var totalEl = document.getElementById('total-price');
                if (!sel || !totalEl) return;

                var weightKey = sel.value;
                var base = basePrices[weightKey] || 0;

                // Sum checked extras and add to subtotal
                var extrasNodes = document.querySelectorAll('input[name="extras[]"]:checked');
                var extrasTotal = 0;
                extrasNodes.forEach(function(chk){
                    var v = chk.value;
                    if (extrasPrices[v]) extrasTotal += extrasPrices[v];
                });

                var subtotal = base + extrasTotal;
                var tax = subtotal * 0.15; // 15% IVA
                var totalWithTax = subtotal + tax;

                // Card fee handling (5% on totalWithTax) if checkbox checked — applied to final total but not shown as separate row
                var includeCardFeeEl = document.getElementById('include-card-fee');
                var includeCard = includeCardFeeEl && includeCardFeeEl.checked;
                var cardFee = includeCard ? totalWithTax * 0.05 : 0;
                var finalTotal = totalWithTax + cardFee;

                // Update visible breakdown elements (no separate card-fee row)
                var subtotalEl = document.getElementById('subtotal-price');
                var taxEl = document.getElementById('tax-price');
                var totalEl = document.getElementById('total-price');

                if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
                if (taxEl) taxEl.textContent = formatCurrency(tax);
                if (totalEl) totalEl.textContent = formatCurrency(finalTotal);

                // Update data attributes for accessibility or later use
                var priceResult = document.getElementById('price-result');
                if (priceResult){
                    priceResult.setAttribute('data-calculated', finalTotal);
                    priceResult.setAttribute('data-subtotal', subtotal);
                    priceResult.setAttribute('data-tax', tax);
                    priceResult.setAttribute('data-card-fee', cardFee);
                    priceResult.setAttribute('data-extras', extrasTotal);
                }

                return {
                    subtotal: subtotal,
                    extras: extrasTotal,
                    tax: tax,
                    cardFee: cardFee,
                    total: finalTotal
                };
            }

            // Wire up events: calculate on button click, select change, and extras change
            var btn = document.getElementById('calculate-price');
            if (btn) btn.addEventListener('click', calculateTotal);

            var sel = document.getElementById('weight-range');
            if (sel) sel.addEventListener('change', calculateTotal);

            // Recalculate when card-fee checkbox changes
            var includeCardFeeEl = document.getElementById('include-card-fee');
            if (includeCardFeeEl) includeCardFeeEl.addEventListener('change', calculateTotal);

            // Recalculate when any extras checkbox changes
            var extrasContainer = document.querySelector('.extras-list');
            if (extrasContainer){
                extrasContainer.addEventListener('change', function(e){
                    if (e.target && e.target.matches('input[name="extras[]"]')) calculateTotal();
                });
            } else {
                document.querySelectorAll('input[name="extras[]"]').forEach(function(chk){
                    chk.addEventListener('change', calculateTotal);
                });
            }

            // Initialize displayed total on page load
            // Use a small timeout to ensure DOM is fully parsed if script is in head
            setTimeout(calculateTotal, 0);
        })();
    </script>
@endpush


