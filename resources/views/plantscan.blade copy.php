<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Plant Scan</title>   
    
    <!-- Basic SEO -->
    <meta name="description" content="Responde estas simples preguntas y descubre la planta que representa a tu mascota.">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Aurora">
    <meta name="theme-color" content="#ffffff">

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="./assets/favicon.png">
    <link rel="apple-touch-icon" href="./assets/favicon.png">

        <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://auroraurn.pet/" />
    <meta property="og:title" content="Aurora Pets - Plant Scan" />
    <meta property="og:description" content="Cada mascota tiene una planta que la representa. ¿Cuál es la tuya?" />
    <meta property="og:image" content="https://auroraurn.pet/assets/plantscan/imgs/11.png" />

    <!-- X (Twitter) -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://auroraurn.pet/" />
    <meta property="twitter:title" content="Aurora Pets - Plant Scan" />
    <meta property="twitter:description" content="Cada mascota tiene una planta que la representa. ¿Cuál es la tuya?" />
    <meta property="twitter:image" content="https://auroraurn.pet/assets/plantscan/imgs/11.png" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&family=Buenard:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollSmoother.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/SplitText.min.js"></script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link rel="stylesheet" href="./css/aurora-general.css">
    <link rel="stylesheet" href="./css/prevention-style.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
     
</head>
<body>
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
                            <h1 data-nav-title="01. Plan de prevención">PlantScan</h1>
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
                                if (existing) { existing.textContent = msg; existing.style.opacity = 1; setTimeout(()=> existing.style.opacity = 0, 2500); return; }
                                const d = document.createElement('div');
                                d.id = 'plantscan-share-toast';
                                d.textContent = msg;
                                Object.assign(d.style, {
                                    position: 'fixed', bottom: '20px', left: '50%', transform: 'translateX(-50%)',
                                    background: '#333', color: '#fff', padding: '10px 14px', borderRadius: '6px', zIndex: 9999, opacity: 1
                                });
                                document.body.appendChild(d);
                                setTimeout(()=> d.style.opacity = 0, 2500);
                            }

                            function shareTo(platform, data) {
                                const text = `${data.plantName} — ${data.description} \nPara ${data.petName}`.trim();
                                const encodedURL = encodeURIComponent(data.url);
                                const encodedText = encodeURIComponent(text);

                                if (navigator.share) {
                                    navigator.share({
                                        title: `La planta de ${data.petName} - ${data.plantName}`,
                                        text: text,
                                        url: data.url
                                    }).catch((err) => { console.log('Share canceled or failed', err); });
                                    return;
                                }

                                switch (platform) {
                                    case 'x':
                                        openPopup(`https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedURL}`);
                                        break;
                                    case 'whatsapp':
                                        // WhatsApp web/mobile
                                        window.location.href = `https://wa.me/?text=${encodedText}%20${encodedURL}`;
                                        break;
                                    case 'facebook':
                                        openPopup(`https://www.facebook.com/sharer/sharer.php?u=${encodedURL}`);
                                        break;
                                    case 'linkedin':
                                        openPopup(`https://www.linkedin.com/sharing/share-offsite/?url=${encodedURL}`);
                                        break;
                                    case 'instagram':
                                    case 'tiktok':
                                        copyToClipboard(data.url);
                                        showToast('Enlace copiado. Pega en Instagram o TikTok para compartir.');
                                        break;
                                    default:
                                        copyToClipboard(data.url);
                                        showToast('Enlace copiado al portapapeles');
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

    <script src="./js/prevention.js"></script>
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
    })();
    </script>

</body>
</html>