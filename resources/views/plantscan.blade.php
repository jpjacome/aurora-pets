@extends('layouts.public')

@section('title', 'Aurora Plant Scan')

@section('og_title', 'Aurora Pets - Plant Scan')
@section('og_description', 'Cada mascota tiene una planta que la representa. ¿Cuál es la tuya?')
@section('og_image'){{ asset('assets/home/imgs/11.png') }}@endsection
@section('og_url'){{ url('/plantscan') }}@endsection

@section('twitter_title', 'Aurora Pets - Plant Scan')
@section('twitter_description', 'Cada mascota tiene una planta que la representa. ¿Cuál es la tuya?')
@section('twitter_image'){{ asset('assets/home/imgs/11.png') }}@endsection

@push('head')
    <!-- Basic SEO -->
    <meta name="description" content="Responde estas simples preguntas y descubre la planta que representa a tu mascota.">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Aurora">
    <meta name="theme-color" content="#ffffff">

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="./assets/favicon.png">
    <link rel="apple-touch-icon" href="./assets/favicon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&family=Buenard:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
    
    <!-- GSAP Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollSmoother.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/SplitText.min.js"></script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
    <link rel="stylesheet" href="./css/aurora-general.css">
    <link rel="stylesheet" href="./css/prevention-style.css">
@endpush

@section('content')
    <nav class="bullet-navbar">
        <h3 class="bullet-label"></h3>
        <ul>
            <li><div class="bullet" title="Inicio"></div></li>
            <li><div class="bullet" title="Información básica"></div></li>
            <li><div class="bullet" title="Detalles de tu mascota"></div></li>
            <li><div class="bullet" title="Su entorno"></div></li>
            <li><div class="bullet" title="Resultado (cálculo)"></div></li>
            <li><div class="bullet" title="Resultado final"></div></li>
        </ul>
    </nav>
    <div id="smooth-wrapper">
        <div id="smooth-content">           
            <!-- Container 1 -->
            <div class="wrapper wrapper-1" id="section-1">
                <div class="inner-container">
                    <div class="container container-1">

                        <div class="logo">
                            <img src="./assets/plantscan/imgs/logo.png" alt="">
                        </div>                        
                        <div class="title">
                            <h1 data-nav-title="01. Bienvenid@">PlantScan</h1>
                        </div>
                        <div class="paragraph">
                            <p>Cada mascota tiene una planta que la representa.<br>¿Cuál es la tuya?</p>
                        </div>
                        <div class="cta-buttons">
                            <a href="#" onclick="scrollToSection(2); return false;" class="btn btn-primary">Comenzar</a>
                        </div>

                    </div>
                    <div class="container container-2">
                    </div>
                </div>
    
                        <!-- Minimal share handler: uses Web Share API with platform fallbacks -->
                        <script>
                        (function () {
                            function getResultData() {
                                const petNameEl = document.querySelector('.pet-name[data-pet]');
                                const plantNameEl = document.querySelector('.plant-name[data-plant]');
                                const descEl = document.querySelector('.plant-description[data-description]');
                                const imgEl = document.querySelector('[data-plant-img]');

                                return {
                                    petName: petNameEl ? petNameEl.textContent.trim() : '',
                                    plantName: plantNameEl ? plantNameEl.textContent.trim() : '',
                                    description: descEl ? descEl.textContent.trim() : '',
                                    image: imgEl ? imgEl.src : '',
                                    url: (window.lastShareUrl || window.location.href)
                                };
                            }

                            function openPopup(url) {
                                const left = (screen.width / 2) - (600 / 2);
                                const top = (screen.height / 2) - (600 / 2);
                                window.open(url, '_blank', `toolbar=0,location=0,status=0,menubar=0,width=600,height=600,top=${top},left=${left}`);
                            }

                            function copyToClipboard(text) {
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    return navigator.clipboard.writeText(text).catch(() => fallbackCopy(text));
                                }
                                return fallbackCopy(text);
                            }

                            function fallbackCopy(text) {
                                const ta = document.createElement('textarea');
                                ta.value = text;
                                document.body.appendChild(ta);
                                ta.select();
                                try { document.execCommand('copy'); } catch (e) {}
                                document.body.removeChild(ta);
                            }

                            function showToast(msg) {
                                const existing = document.getElementById('plantscan-share-toast');
                                if (existing) { 
                                    existing.textContent = msg; 
                                    existing.style.opacity = 1; 
                                    clearTimeout(existing._timeout);
                                    existing._timeout = setTimeout(()=> existing.style.opacity = 0, 3000); 
                                    return; 
                                }
                                const d = document.createElement('div');
                                d.id = 'plantscan-share-toast';
                                d.textContent = msg;
                                Object.assign(d.style, {
                                    position: 'fixed', bottom: '20px', left: '50%', transform: 'translateX(-50%)',
                                    background: '#00452A', color: '#fff', padding: '14px 20px', borderRadius: '8px', 
                                    zIndex: 9999, opacity: 1, transition: 'opacity 0.3s ease',
                                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)', fontSize: '16px',
                                    maxWidth: '90%', textAlign: 'center'
                                });
                                document.body.appendChild(d);
                                d._timeout = setTimeout(()=> { 
                                    d.style.opacity = 0;
                                    setTimeout(() => d.remove(), 300);
                                }, 3000);
                            }

                            // Generate shareable Instagram Story image (1080x1920px)
                            // Matches the exact styling from wrapper-6 results page
                            async function generateStoryImage(data) {
                                return new Promise(async (resolve, reject) => {
                                    try {
                                        // Load custom fonts before drawing
                                        await Promise.all([
                                            document.fonts.load('bold 64px "Playfair Display"'),
                                            document.fonts.load('32px "Buenard"'),
                                            document.fonts.load('bold 80px "Playfair Display"'),
                                            document.fonts.load('bold 42px "Playfair Display"'),
                                            document.fonts.load('36px "Buenard"')
                                        ]);
                                    } catch (e) {
                                        console.warn('Font loading failed, using fallbacks:', e);
                                    }

                                    const canvas = document.createElement('canvas');
                                    canvas.width = 1080;
                                    canvas.height = 1600;
                                    const ctx = canvas.getContext('2d');

                                    // Background: solid dark green (var(--color-2))
                                    ctx.fillStyle = '#00452A';
                                    ctx.fillRect(0, 0, 1080, 1600);

                                    // Load and draw plant image
                                    const img = new Image();
                                    img.crossOrigin = 'anonymous';
                                    img.onload = function() {
                                        // Top title: "¡La planta perfecta para [pet name]!"
                                        ctx.fillStyle = '#fe8d2c'; // var(--color-1) - orange
                                        ctx.font = 'bold 64px "Playfair Display", Georgia, serif';
                                        ctx.textAlign = 'center';
                                        
                                        // Wrap title text if needed
                                        const titleText = `¡La planta perfecta para ${data.petName}!`;
                                        const maxWidth = 900;
                                        const words = titleText.split(' ');
                                        let line = '';
                                        let y = 150; // Reduced from 280 to 150 for more compact top
                                        const lineHeight = 75;
                                        
                                        for (let word of words) {
                                            const testLine = line + word + ' ';
                                            const metrics = ctx.measureText(testLine);
                                            if (metrics.width > maxWidth && line !== '') {
                                                ctx.fillText(line.trim(), 540, y);
                                                line = word + ' ';
                                                y += lineHeight;
                                            } else {
                                                line = testLine;
                                            }
                                        }
                                        ctx.fillText(line.trim(), 540, y);

                                        // Plant image (3:4 aspect ratio, centered)
                                        const imgWidth = 600;
                                        const imgHeight = 800; // 3:4 ratio
                                        const imgX = (1080 - imgWidth) / 2;
                                        const imgY = 230; // Reduced from 500 to 230 (moved up even more)
                                        
                                        // Draw plant image
                                        ctx.drawImage(img, imgX, imgY, imgWidth, imgHeight);

                                        // Plant name below image
                                        ctx.fillStyle = '#fe8d2c'; // var(--color-1) - orange
                                        ctx.font = 'bold 80px "Playfair Display", Georgia, serif';
                                        ctx.fillText(data.plantName, 540, imgY + imgHeight + 100);

                                        // Plant description (smaller, wrapped text)
                                        ctx.fillStyle = '#dcffd6'; // var(--color-3) - light green
                                        ctx.font = '32px "Buenard", Georgia, serif';
                                        const descMaxWidth = 800;
                                        const descWords = data.description.split(' ');
                                        let descLine = '';
                                        let descY = imgY + imgHeight + 180;
                                        const descLineHeight = 42;
                                        let lineCount = 0;
                                        const maxLines = 3;
                                        
                                        for (let word of descWords) {
                                            if (lineCount >= maxLines) break;
                                            const testLine = descLine + word + ' ';
                                            const metrics = ctx.measureText(testLine);
                                            if (metrics.width > descMaxWidth && descLine !== '') {
                                                ctx.fillText(descLine.trim(), 540, descY);
                                                descLine = word + ' ';
                                                descY += descLineHeight;
                                                lineCount++;
                                            } else {
                                                descLine = testLine;
                                            }
                                        }
                                        if (lineCount < maxLines && descLine.trim()) {
                                            ctx.fillText(descLine.trim() + (lineCount === maxLines - 1 ? '...' : ''), 540, descY);
                                        }

                                        // Bottom: Aurora logo (replacing "Aurora Pets" text)
                                        const logo = new Image();
                                        logo.crossOrigin = 'anonymous';
                                        logo.onload = function() {
                                            // Draw logo centered at bottom (adjusted to fit nicely)
                                            const logoWidth = 300; // Adjust size as needed
                                            const logoHeight = (logo.height / logo.width) * logoWidth; // Maintain aspect ratio
                                            const logoX = (1080 - logoWidth) / 2;
                                            const logoY = 1360; // Adjusted for shorter canvas height
                                            
                                            ctx.drawImage(logo, logoX, logoY, logoWidth, logoHeight);
                                            
                                            // Website URL (moved closer to logo)
                                            ctx.font = '36px "Buenard", Georgia, serif';
                                            ctx.fillStyle = '#dcffd6';
                                            ctx.textAlign = 'center';
                                            ctx.fillText('auroraurn.pet/plantscan', 540, logoY + logoHeight + 50);
                                            
                                            // Convert to blob
                                            canvas.toBlob((blob) => {
                                                resolve({ blob, dataUrl: canvas.toDataURL('image/png') });
                                            }, 'image/png');
                                        };
                                        logo.onerror = function() {
                                            // Fallback: use text if logo doesn't load
                                            console.warn('Logo failed to load, using text fallback');
                                            ctx.fillStyle = '#fe8d2c';
                                            ctx.font = 'bold 42px "Playfair Display", Georgia, serif';
                                            ctx.textAlign = 'center';
                                            ctx.fillText('Aurora Pets', 540, 1420);
                                            
                                            // Website URL
                                            ctx.font = '36px "Buenard", Georgia, serif';
                                            ctx.fillStyle = '#dcffd6';
                                            ctx.fillText('auroraurn.pet/plantscan', 540, 1490);
                                            
                                            // Convert to blob anyway
                                            canvas.toBlob((blob) => {
                                                resolve({ blob, dataUrl: canvas.toDataURL('image/png') });
                                            }, 'image/png');
                                        };
                                        // Load the Aurora logo (adjust path as needed)
                                        logo.src = './assets/plantscan/imgs/logo.png';
                                    };
                                    img.onerror = () => reject(new Error('Failed to load plant image'));
                                    img.src = data.image;
                                });
                            }

                            // Download image file
                            function downloadImage(dataUrl, filename) {
                                const link = document.createElement('a');
                                link.href = dataUrl;
                                link.download = filename;
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }

                            // Share with native share sheet (mobile) or download (desktop)
                            async function shareTo(platform, data) {
                                try {
                                    showToast('Generando imagen...');
                                    
                                    const { blob, dataUrl } = await generateStoryImage(data);
                                    const fileName = `${data.petName}-${data.plantName}-Aurora.png`.replace(/\s+/g, '-');

                                    // Try native share (mobile with image support)
                                    if (navigator.share && navigator.canShare) {
                                        const file = new File([blob], fileName, { type: 'image/png' });
                                        const shareData = {
                                            files: [file],
                                            title: `La planta de ${data.petName}`,
                                            text: `¡${data.plantName} es la planta perfecta para ${data.petName}! Descubre la tuya en auroraurn.pet/plantscan`
                                        };

                                        if (navigator.canShare(shareData)) {
                                            await navigator.share(shareData);
                                            showToast('¡Compartido exitosamente!');
                                            return;
                                        }
                                    }

                                    // Fallback: Download image
                                    downloadImage(dataUrl, fileName);
                                    
                                    const platformNames = {
                                        'instagram': 'Instagram',
                                        'tiktok': 'TikTok',
                                        'facebook': 'Facebook',
                                        'x': 'Twitter/X'
                                    };
                                    const platformName = platformNames[platform] || 'redes sociales';
                                    showToast(`Imagen descargada. Súbela a ${platformName} para compartir.`);
                                    
                                } catch (error) {
                                    console.error('Share error:', error);
                                    showToast('Error al generar la imagen. Inténtalo de nuevo.');
                                }
                            }

                            document.addEventListener('click', function (e) {
                                const el = e.target.closest('.share-link');
                                if (!el) return;
                                e.preventDefault();
                                const platform = el.getAttribute('data-share');
                                const data = getResultData();
                                shareTo(platform, data);
                            });

                            // Expose generateStoryImage globally so prevention.js can use it for email
                            window.generateStoryImage = generateStoryImage;
                        })();
                        </script>
            </div>            
            
            <!-- Container 2 -->
            <div class="wrapper wrapper-2" id="section-2">
                <div class="inner-container wrapper-2-flex">
                    <div class="benefits-col">
                        <div class="title" style="display: none;">
                            <h2 data-nav-title="02. Información básica">Información básica</h2>
                        </div>
                        <form class="pet-form">
                            <div class="form-group">
                                <label for="owner-name">¿Cómo te llamas?</label>
                                <input type="text" id="owner-name" name="owner-name" placeholder="Nombre del cuidador" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="pet-name">¿Cómo se llama tu mascota?</label>
                                <input type="text" id="pet-name" name="pet-name" placeholder="Nombre de tu mascota" required>
                            </div>

                            <div class="form-group">
                                <label for="pet-species">Especie de mascota:</label>
                                <select id="pet-species" name="pet-species" required>
                                    <option value="" disabled selected>Selecciona la especie</option>
                                    <option>Perro</option>
                                    <option>Gato</option>
                                    <option>Conejo</option>
                                    <option>Hámster</option>
                                    <option>Pájaro</option>
                                    <option>Tortuga</option>
                                    <option>Pez</option>
                                    <option>Otro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Género:</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="gender" value="masculino" required>
                                        <span>Masculino</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="gender" value="femenino" required>
                                        <span>Femenino</span>
                                    </label>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Mobile-only Continue Button for Section 2 -->
                        <div class="mobile-continue-wrapper">
                            <button class="btn btn-primary mobile-continue-btn" data-next-section="3">
                                Continuar
                            </button>
                        </div>
                    </div>
                    <div class="benefits-image-col">            
                    </div>
                </div>
            </div>
            
            <!-- Container 3 -->
            <div class="wrapper wrapper-3" id="section-3">
                <div class="inner-container wrapper-3-flex">
                    <div class="pet-details-col">
                        <div class="section-header">
                            <h2 data-nav-title="03. Conozcamos a tu mascota">Conozcamos más de tu mascota</h2>
                        </div>
                        
                        <form class="pet-details-form">

                            <div class="form-group">
                                <label for="pet-birthday">Fecha de cumpleaños:</label>
                                <input type="date" id="pet-birthday" name="pet-birthday" required>
                            </div>

                            <div class="form-group">
                                <label for="pet-breed">Raza:</label>
                                <input type="text" id="pet-breed" name="pet-breed" placeholder="Raza de tu mascota" required>
                            </div>

                            <div class="form-group">
                                <label for="pet-weight">Peso aproximado (en kg):</label>
                                <select id="pet-weight" name="pet-weight" required>
                                    <option value="" disabled selected>Selecciona el peso de tu mascota</option>
                                    <option value="1-5">1-5 kg</option>
                                    <option value="5-10">5-10 kg</option>
                                    <option value="10-15">10-15 kg</option>
                                    <option value="15-25">15-25 kg</option>
                                    <option value="25-35">25-35 kg</option>
                                    <option value="35+">Más de 35 kg</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label class="color-label">Color de mascota:</label>
                                <p class="sub-label">Elige hasta tres colores que mejor describan a tu mascota:</p>
                                <div class="checkbox-group color-options">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="rojo">
                                        <span>🔴 Rojo</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="azul">
                                        <span>🔵 Azul</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="amarillo">
                                        <span>🟡 Amarillo</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="verde">
                                        <span>🟢 Verde</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="naranja">
                                        <span>� Naranja</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="violeta">
                                        <span>� Violeta</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="rosa">
                                        <span>🩷 Rosa</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="marron">
                                        <span>🤎 Marrón</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="blanco">
                                        <span>⚪ Blanco</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="color" value="negro">
                                        <span>⚫ Negro</span>
                                    </label>
                                </div>
                                <!-- Mobile-only select for colors (visible on small screens) -->
                                <div class="mobile-only mobile-select-wrapper">
                                    <label for="mobile-color-select" class="sr-only">Color de mascota (móvil)</label>
                                    <select id="mobile-color-select" aria-label="Color de mascota" class="mobile-select">
                                        <option value="" disabled selected>Selecciona color</option>
                                        <option value="rojo">🔴 Rojo</option>
                                        <option value="azul">🔵 Azul</option>
                                        <option value="amarillo">🟡 Amarillo</option>
                                        <option value="verde">🟢 Verde</option>
                                        <option value="naranja">🟠 Naranja</option>
                                        <option value="violeta">🟣 Violeta</option>
                                        <option value="rosa">🩷 Rosa</option>
                                        <option value="marron">🤎 Marrón</option>
                                        <option value="blanco">⚪ Blanco</option>
                                        <option value="negro">⚫ Negro</option>
                                    </select>
                                    <div id="mobile-color-chips" class="mobile-chips" aria-hidden="false"></div>
                                </div>
                            </div>

                           
                        </form>
                        
                        <!-- Mobile-only Continue Button for Section 3 -->
                        <div class="mobile-continue-wrapper">
                            <button class="btn btn-primary mobile-continue-btn" data-next-section="4">
                                Continuar
                            </button>
                        </div>
                    </div>
                    <div class="pet-details-image-col"></div>
                </div>
            </div>            
            
            <!-- Container 4 - Light Sage Green -->
            <div class="wrapper wrapper-4" id="section-4">
                <div class="inner-container wrapper-4-flex">
                    <div class="environment-col">
                        
                        <form class="environment-form">
                            <div class="form-group full-width">
                                <label>¿Cómo es el lugar donde comparten sus días?</label>
                                <p class="sub-label">¿Viven juntos en un espacio como...?</p>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="living-space" value="casa-jardin" required>
                                        <span>🏡 Una casa con jardín</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="living-space" value="casa-sin-jardin" required>
                                        <span>🏠 Una casa sin jardín</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="living-space" value="departamento" required>
                                        <span>🏢 Un departamento</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="living-space" value="finca-terreno" required>
                                        <span>🌳 Una finca o terreno amplio</span>
                                    </label>
                                </div>
                                <!-- Mobile-only select for living-space (single select) -->
                                <div class="mobile-only mobile-select-wrapper">
                                    <label for="mobile-living-select" class="sr-only">Lugar donde viven (móvil)</label>
                                    <select id="mobile-living-select" aria-label="Lugar donde viven" class="mobile-select">
                                        <option value="" disabled selected>Selecciona el espacio</option>
                                        <option value="casa-jardin">🏡 Una casa con jardín</option>
                                        <option value="casa-sin-jardin">🏠 Una casa sin jardín</option>
                                        <option value="departamento">🏢 Un departamento</option>
                                        <option value="finca-terreno">🌳 Una finca o terreno amplio</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label>¿Qué te inspira tu mascota cada día?</label>
                                <p class="sub-label">Elige hasta tres palabras que describan lo que representa para ti:</p>
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="fortaleza">
                                        <span>💪 Fortaleza</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="dulzura">
                                        <span>🧡 Dulzura</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="libertad">
                                        <span>🕊️ Libertad</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="alegria">
                                        <span>😊 Alegría</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="nobleza">
                                        <span>🐾 Nobleza</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="independencia">
                                        <span>🐈 Independencia</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="energia">
                                        <span>⚡ Energía</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="paz">
                                        <span>🌙 Paz</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="proteccion">
                                        <span>🛡️ Protección</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="inspiration" value="belleza">
                                        <span>🌸 Belleza</span>
                                    </label>
                                </div>
                                <!-- Mobile-only multi-select for inspirations -->
                                <div class="mobile-only mobile-select-wrapper">
                                    <label for="mobile-inspiration-select" class="sr-only">Inspiración (móvil)</label>
                                    <select id="mobile-inspiration-select" aria-label="Qué te inspira tu mascota" class="mobile-select">
                                        <option value="" disabled selected>Selecciona (máx. 3)</option>
                                        <option value="fortaleza">💪 Fortaleza</option>
                                        <option value="dulzura">🧡 Dulzura</option>
                                        <option value="libertad">🕊️ Libertad</option>
                                        <option value="alegria">😊 Alegría</option>
                                        <option value="nobleza">🐾 Nobleza</option>
                                        <option value="independencia">🐈 Independencia</option>
                                        <option value="energia">⚡ Energía</option>
                                        <option value="paz">🌙 Paz</option>
                                        <option value="proteccion">🛡️ Protección</option>
                                        <option value="belleza">🌸 Belleza</option>
                                    </select>
                                    <div id="mobile-inspiration-chips" class="mobile-chips" aria-hidden="false"></div>
                                </div>
                                <!-- Mobile-only CTA placed immediately after inspiration for small screens -->
                                <div class="mobile-only mobile-cta-wrapper">
                                    <div class="mobile-email-row">
                                        <label for="mobile-result-email" class="sr-only">Correo electrónico (móvil)</label>
                                        <input id="mobile-result-email" name="mobile-result-email" type="email" placeholder="Email: correo@ejemplo.com" aria-label="Tu correo electrónico" class="mobile-email-input" />
                                    </div>
                                    <div class="mobile-email-row">
                                        <label class="email-consent-label mobile-consent">
                                            <input type="checkbox" id="mobile-send-results-email" name="mobile-send-results-email" checked />
                                            <span>Recibir resultado con imagen por correo</span>
                                        </label>
                                    </div>
                                    <a href="#" onclick="unlockAndScrollToNext(); return false;" class="btn btn-primary mobile-cta">Calcular</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="environment-image-col">
                        <!-- Email input moved here so user can enter their email before calculating -->
                        <div class="result-email-capture" aria-hidden="false">
                            <form class="contact-form" onsubmit="return false;">
                                <div class="form-row">
                                    <input id="result-email" name="result-email" type="email" placeholder="Email: correo@ejemplo.com" aria-label="Tu correo electrónico" />
                                </div>
                                <div class="form-row">
                                    <label class="email-consent-label">
                                        <input type="checkbox" id="send-results-email" name="send-results-email" checked />
                                        <span>Recibir resultado con imagen por correo electrónico</span>
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="cta-buttons">
                            <a href="#" onclick="unlockAndScrollToNext(); return false;" class="btn btn-primary">Calcular</a>
                        </div>
                    </div>
                </div>
            </div>            
            
            
            <!-- Container 5 - Pale Green -->

            <div class="wrapper wrapper-5" id="section-5">
                <div class="inner-container">
                    <div class="title" style="display: none;">
                        <h2 data-nav-title="05. Tu planta perfecta">Tu planta perfecta</h2>
                    </div>
                    <div class="loading-container">
                        <div class="loading-icon">
                            <!-- Three SVG icons arranged in equilateral triangle -->
                            <div class="triangle-container">
                                <!-- Top SVG -->
                                <div class="svg-position top">
                                    <img class="pet-icon" id="pet-svg-top" src="./assets/plantscan/imgs/icon-dog.svg" width="80" height="80" alt="Pet icon">
                                </div>
                                <!-- Bottom Left SVG -->
                                <div class="svg-position bottom-left">
                                    <img class="pet-icon" id="pet-svg-left" src="./assets/plantscan/imgs/icon-cat.svg" width="80" height="80" alt="Pet icon">
                                </div>
                                <!-- Bottom Right SVG -->
                                <div class="svg-position bottom-right">
                                    <img class="pet-icon" id="pet-svg-right" src="./assets/plantscan/imgs/icon-bunny.svg" width="80" height="80" alt="Pet icon">
                                </div>
                            </div>
                        </div>
                        <div class="loading-text" id="loading-text">
                            <span class="loading-phrase"></span>
                        </div>
                        <div class="loading-dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                    </div>

                    <!-- email capture moved to section-4; section-5 keeps only loading UI -->

                    <!-- Results are shown in section-6; section-5 holds only the loading UI -->
                </div>
            </div>

            <!-- Container 6 - Final Results -->
            <div class="wrapper wrapper-6" id="section-6">
                <div class="inner-container">
                    <div class="title" style="display: none;">
                        <h2 data-nav-title="06. Resultado final">Resultado final</h2>
                    </div>
                    <div class="results-static-container">
                        <div class="plant-result">
                            <h2 class="result-title">¡La planta perfecta para <span class="pet-name" data-pet>tu mascota</span>!</h2>
                            <div class="plant-img">
                                <img alt="Imagen de planta" data-plant-img src="./assets/plantscan/imgs/plants/schefflera.png">
                            </div>
                            <h3 class="plant-name" data-plant>Schefflera</h3>
                            <p class="plant-description" data-description>Completa el cuestionario para ver la planta que mejor representa a tu mascota. Aquí verás una descripción cuando termines.</p>

                            <!-- (Email & social moved to bottom of results container) -->
                        </div>
                    </div>
                    <!-- Bottom area: Email capture + social share (frontend-only, layout-only styles) -->
                    <div class="results-footer">
                        <div class="result-social-share" data-share-container>
                            <p class="share-label">Comparte tu resultado:</p>
                            <div class="share-links">
                                <a href="#" class="share-link share-instagram" data-share="instagram" title="Compartir en Instagram" aria-label="Compartir en Instagram">
                                    <i class="ph ph-instagram-logo" aria-hidden="true"></i>
                                </a>
                                <a href="#" class="share-link share-tiktok" data-share="tiktok" title="Compartir en TikTok" aria-label="Compartir en TikTok">
                                    <i class="ph ph-tiktok-logo" aria-hidden="true"></i>
                                </a>
                                <a href="#" class="share-link share-facebook" data-share="facebook" title="Compartir en Facebook" aria-label="Compartir en Facebook">
                                    <i class="ph ph-facebook-logo" aria-hidden="true"></i>
                                </a>
                                <a href="#" class="share-link share-x" data-share="x" title="Compartir en X (Twitter)" aria-label="Compartir en X">
                                    <i class="ph ph-twitter-logo" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="background-wrapper">
    </div>

    <script>
    // Mobile selects <-> original inputs sync helper
    (function(){
        function q(sel, ctx) { return (ctx || document).querySelector(sel); }
        function qa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

        // Colors (chip-based single-select which toggles checkboxes)
        const mobileColor = q('#mobile-color-select');
        const colorChipsContainer = q('#mobile-color-chips');
        const colorCheckboxes = qa('input[type="checkbox"][name="color"]');
        function renderColorChips() {
            if (!colorChipsContainer) return;
            const selected = colorCheckboxes.filter(c => c.checked).map(c => c.value);
            colorChipsContainer.innerHTML = '';
            selected.forEach(val => {
                const lab = document.createElement('button');
                lab.type = 'button';
                lab.className = 'chip';
                lab.textContent = val;
                lab.addEventListener('click', () => {
                    const cb = colorCheckboxes.find(c => c.value === val);
                    if (cb) { cb.checked = false; cb.dispatchEvent(new Event('change')); }
                });
                colorChipsContainer.appendChild(lab);
            });
        }
        if (mobileColor) {
            mobileColor.addEventListener('change', function(){
                const v = this.value;
                // toggle checkbox with that value
                const cb = colorCheckboxes.find(c => c.value === v);
                if (cb) { cb.checked = !cb.checked; cb.dispatchEvent(new Event('change')); }
                // reset select to placeholder
                this.selectedIndex = 0;
            });
            colorCheckboxes.forEach(cb => cb.addEventListener('change', renderColorChips));
            // initial render
            renderColorChips();
        }

        // Living space (single select -> radios)
        const mobileLiving = q('#mobile-living-select');
        const livingRadios = qa('input[type="radio"][name="living-space"]');
        if (mobileLiving) {
            // init
            const selectedRadio = livingRadios.find(r => r.checked);
            if (selectedRadio) mobileLiving.value = selectedRadio.value;

            mobileLiving.addEventListener('change', function(){
                const v = this.value;
                livingRadios.forEach(r => r.checked = (r.value === v));
            });

            livingRadios.forEach(r => r.addEventListener('change', function(){ if (this.checked) mobileLiving.value = this.value; }));
        }

        // Inspiration (select -> chips with max 3)
        const mobileInsp = q('#mobile-inspiration-select');
        const inspChipsContainer = q('#mobile-inspiration-chips');
        const inspCheckboxes = qa('input[type="checkbox"][name="inspiration"]');
        function renderInspChips() {
            if (!inspChipsContainer) return;
            const selected = inspCheckboxes.filter(c => c.checked).map(c => c.value);
            inspChipsContainer.innerHTML = '';
            selected.forEach(val => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'chip';
                b.textContent = val;
                b.addEventListener('click', () => {
                    const cb = inspCheckboxes.find(c => c.value === val);
                    if (cb) { cb.checked = false; cb.dispatchEvent(new Event('change')); }
                });
                inspChipsContainer.appendChild(b);
            });
        }
        if (mobileInsp) {
            mobileInsp.addEventListener('change', function(){
                const v = this.value;
                const cb = inspCheckboxes.find(c => c.value === v);
                if (cb) {
                    // if already selected, deselect; otherwise select if under limit
                    const currently = inspCheckboxes.filter(c => c.checked).map(c => c.value);
                    if (cb.checked) { cb.checked = false; cb.dispatchEvent(new Event('change')); }
                    else if (currently.length < 3) { cb.checked = true; cb.dispatchEvent(new Event('change')); }
                }
                this.selectedIndex = 0;
            });
            inspCheckboxes.forEach(cb => cb.addEventListener('change', renderInspChips));
            renderInspChips();
        }

        // Mobile email sync: copy between #mobile-result-email and #result-email
        const mobileEmail = q('#mobile-result-email');
        const originalEmail = q('#result-email');
        if (mobileEmail && originalEmail) {
            // init
            mobileEmail.value = originalEmail.value || '';
            // mobile -> original
            mobileEmail.addEventListener('input', function(){ originalEmail.value = this.value; });
            // original -> mobile
            originalEmail.addEventListener('input', function(){ mobileEmail.value = this.value; });
        }

        // Checkbox sync: keep desktop and mobile consent checkboxes in sync
        const desktopConsent = q('#send-results-email');
        const mobileConsent = q('#mobile-send-results-email');
        if (desktopConsent && mobileConsent) {
            // init - both start checked
            desktopConsent.checked = true;
            mobileConsent.checked = true;
            // desktop -> mobile
            desktopConsent.addEventListener('change', function(){ mobileConsent.checked = this.checked; });
            // mobile -> desktop
            mobileConsent.addEventListener('change', function(){ desktopConsent.checked = this.checked; });
        }
    })();
    </script>
@endsection

@push('scripts')
    <script src="./js/prevention.js"></script>
@endpush