// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger, ScrollSmoother, SplitText);

// Fix mobile viewport height issue (address bar shows/hides)
// Calculate and set custom --vh property
function setVhProperty() {
    // Prefer the Visual Viewport height when available (handles address-bar show/hide)
    const vv = (window.visualViewport && typeof window.visualViewport.height === 'number') ? window.visualViewport.height : window.innerHeight;
    const vh = vv * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

// Set on load
setVhProperty();

// Prevent browsers from restoring previous scroll position on navigation (mobile only)
try {
    if (window.innerWidth <= 600 && window.history && 'scrollRestoration' in window.history) {
        window.history.scrollRestoration = 'manual';
    }
} catch (e) {}

// Update on resize and orientation change
window.addEventListener('resize', setVhProperty);
window.addEventListener('orientationchange', () => {
    setTimeout(setVhProperty, 100); // Small delay for orientation change
});

// Debounced refresh helper (mobile-only) to re-sync GSAP/ScrollTrigger when viewport changes
// Uses gsap.utils.debounce which is available because GSAP is registered above
// Provide a local debounce fallback because some GSAP builds may not include utils.debounce
function localDebounce(fn, wait) {
    let t = null;
    return function(...args) {
        if (t) clearTimeout(t);
        t = setTimeout(() => { fn.apply(this, args); t = null; }, wait);
    };
}

const debouncedRefresh = (gsap && gsap.utils && typeof gsap.utils.debounce === 'function' ? gsap.utils.debounce : localDebounce)(function() {
    // If a user is actively interacting with a form control, skip refresh to avoid stealing focus/scroll
    if (window.__ps_inputFocused) return;
    // Only run for mobile phones (not tablets/desktop)
    if (window.innerWidth > 600) return;

    try {
        // Refresh ScrollTrigger so pins and start/end recalculations use current viewport
        if (window.ScrollTrigger) ScrollTrigger.refresh();
    } catch (e) {
        // ignore
    }

    // If a smoother exists and a scroll lock is active, recompute lock position
    try {
        const smoother = window.ScrollSmoother ? ScrollSmoother.get() : null;
        if (typeof scrollLockActive !== 'undefined' && scrollLockActive) {
            if (smoother && typeof smoother.scrollTop === 'function') {
                lockScrollPosition = smoother.scrollTop();
            } else {
                lockScrollPosition = window.pageYOffset || document.documentElement.scrollTop || 0;
            }
        }
    } catch (e) {}

}, 80);

// If VisualViewport API exists, listen for its resize/scroll events to detect address-bar toggles
if (window.visualViewport) {
    const onVVChange = function() {
        setVhProperty();
        debouncedRefresh();
    };
    try {
        visualViewport.addEventListener('resize', onVVChange);
        visualViewport.addEventListener('scroll', onVVChange);
    } catch (e) {}
}

// Also trigger debounced refresh on global resize/orientationchange
window.addEventListener('resize', debouncedRefresh);
window.addEventListener('orientationchange', function(){ setTimeout(debouncedRefresh, 140); });

function scrollToSection(sectionNumber) {
    const section = document.getElementById(`section-${sectionNumber}`);
    // Use ScrollSmoother's scrollTo method for smooth scrolling
    let smoother = ScrollSmoother.get();
    if (smoother) {
        smoother.scrollTo(section, true, "top top");
    } else {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}        

// === NUMERIC SYSTEM: Gematria + Mappings + Reducers ===
// Jewish Gematria mapping
const GEMATRIA = {
    A: 1, B: 2, C: 3, D: 4, E: 5, F: 6, G: 7, H: 8, I: 9,
    J: 600, K: 10, L: 20, M: 30, N: 40, O: 50, P: 60, Q: 70, R: 80,
    S: 90, T: 100, U: 200, V: 700, W: 900, X: 300, Y: 400, Z: 500
};

// Final plant set (19 items) with official numbers from plant list fix.md
const ALLOWED_PLANTS = [
    { num: 1,  name: "Pensamientos (Viola tricolor)" },
    { num: 3,  name: "San Pedro" },
    { num: 4,  name: "Limonero" },
    { num: 5,  name: "Schefflera" },
    { num: 6,  name: "Monstera Deliciosa" },
    { num: 7,  name: "Buganvilla" },
    { num: 9,  name: "Zamioculca" },
    { num: 10, name: "Syngonium Neon Pink" },
    { num: 12, name: "Sanseviera" },
    { num: 13, name: "Cala" },
    { num: 14, name: "Syngonium Three Kings" },
    { num: 15, name: "Anturio" },
    { num: 17, name: "Calathea Triostar" },
    { num: 18, name: "Monstera Adansonii" },
    { num: 20, name: "Helecho nativo" },
    { num: 21, name: "Capulí" },
    { num: 22, name: "Jade" },
    { num: 23, name: "Syngonium Confettii" },
    { num: 27, name: "Cholán" }
];

// Lookup tables for non-gematria answers
const SPECIES_MAP = {
    "perro": 13, "gato": 6, "conejo": 15, "hámster": 14, "hamster": 14,
    "pájaro": 11, "pajaro": 11, "tortuga": 18, "pez": 29, "otro": 17
};
const GENDER_MAP = { "masculino": 2, "femenino": 3 };
const WEIGHT_MAP = { "1-5": 3, "5-10": 8, "10-15": 13, "15-25": 20, "25-35": 30, "35+": 35 };
const COLOR_MAP = {
    "rojo": 15, "azul": 13, "amarillo": 10, "verde": 14, "naranja": 5,
    "violeta": 21, "rosa": 6, "marron": 7, "marrón": 7, "blanco": 1, "negro": 0
};
const LIVING_MAP = { "casa-jardin": 2, "casa-sin-jardin": 4, "departamento": 20, "finca-terreno": 7 };
const VIRTUE_MAP = {
    "fortaleza": 9, "dulzura": 6, "libertad": 0, "alegria": 4, "alegría": 4,
    "nobleza": 8, "independencia": 1, "energia": 9, "energía": 9,
    "paz": 7, "proteccion": 2, "protección": 2, "belleza": 6
};

// Map pet species to existing background images in assets/imgs/pets
// Note: Only species with available images are mapped; others keep CSS default
const PET_SPECIES_IMAGE_MAP = {
    // available files: bunny.png, cat.png, colibri.png, fish.png, hamster.png, horse.png, turtle.png
    gato: './assets/plantscan/imgs/pets/cat.png',
    conejo: './assets/plantscan/imgs/pets/bunny.png',
    hamster: './assets/plantscan/imgs/pets/hamster.png', // also covers "hámster" via normalizeKey
    pajaro: './assets/plantscan/imgs/pets/colibri.png',  // bird
    pez: './assets/plantscan/imgs/pets/fish.png',
    tortuga: './assets/plantscan/imgs/pets/turtle.png',
    otro: './assets/plantscan/imgs/pets/horse.png'
    // perro image not present – will fall back to CSS default
};

function getSpeciesImageUrl(val) {
    if (!val) return null;
    const key = normalizeKey(val);
    return PET_SPECIES_IMAGE_MAP[key] || null;
}

function applySpeciesBackgroundToSection3(speciesVal) {
    const col3 = document.querySelector('.wrapper-3 .pet-details-image-col');
    if (!col3) return;
    const imgUrl = getSpeciesImageUrl(speciesVal);
    if (imgUrl) {
        col3.style.background = `url("${imgUrl}") center center / cover no-repeat`;
    } else {
        // Remove inline override to keep whatever CSS background is defined
        col3.style.removeProperty('background');
    }
}

// Helpers
function normalizeStr(input) {
    if (!input) return "";
    return input
        .toString()
        .trim()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
}
function normalizeToAtoZ(input) {
    return normalizeStr(input).toUpperCase().replace(/[^A-Z]/g, "");
}
function normalizeKey(input) {
    return normalizeStr(input).toLowerCase();
}
function gematriaSum(input) {
    const s = normalizeToAtoZ(input);
    let sum = 0;
    for (const ch of s) sum += GEMATRIA[ch] || 0;
    return sum;
}
function sumDigitsOnce(n) {
    return String(n).split("").reduce((a, d) => a + Number(d), 0);
}
function birthdayToSingleDigit(dateStr) {
    if (!dateStr) return 0;
    const digits = String(dateStr).replace(/\D/g, "");
    if (!digits) return 0;
    let n = digits.split("").reduce((a, d) => a + Number(d), 0);
    while (n > 9) n = sumDigitsOnce(n);
    return n;
}
function getCheckedValues(name) {
    return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`)).map(el => el.value);
}
function mapSpecies(val) {
    if (!val) return 0;
    const key = normalizeKey(val);
    return SPECIES_MAP[key] ?? 0;
}
function mapGender() {
    const el = document.querySelector('input[name="gender"]:checked');
    return el ? (GENDER_MAP[normalizeKey(el.value)] ?? 0) : 0;
}
function mapWeight(val) {
    return val ? (WEIGHT_MAP[val] ?? 0) : 0;
}
function mapColors(values) {
    return values.reduce((sum, v) => sum + (COLOR_MAP[normalizeKey(v)] ?? 0), 0);
}
function mapLiving() {
    const el = document.querySelector('input[name="living-space"]:checked');
    return el ? (LIVING_MAP[normalizeKey(el.value)] ?? 0) : 0;
}
function mapVirtues(values) {
    const sel = values.slice(0, 3);
    return sel.reduce((sum, v) => sum + (VIRTUE_MAP[normalizeKey(v)] ?? 0), 0);
}

function computeAllValues() {
    const ownerName = document.getElementById('owner-name')?.value || "";
    const petName = document.getElementById('pet-name')?.value || "";
    const petSpecies = document.getElementById('pet-species')?.value || "";
    const petBirthday = document.getElementById('pet-birthday')?.value || "";
    const petBreed = document.getElementById('pet-breed')?.value || "";
    const petWeight = document.getElementById('pet-weight')?.value || "";
    const colors = getCheckedValues('color');
    const virtues = getCheckedValues('inspiration');

    const v1_owner = gematriaSum(ownerName);
    const v2_pet = gematriaSum(petName);
    const v3_species = mapSpecies(petSpecies);
    const v4_gender = mapGender();
    const v5_birthday = birthdayToSingleDigit(petBirthday);
    const v6_breed = gematriaSum(petBreed);
    const v7_weight = mapWeight(petWeight);
    const v8_colors = mapColors(colors);
    const v9_living = mapLiving();
    const v10_virtues = mapVirtues(virtues);

        // Exclude owner name from the final calculation per request
        const partsForTotal = [v2_pet, v3_species, v4_gender, v5_birthday, v6_breed, v7_weight, v8_colors, v9_living, v10_virtues];
        const total = partsForTotal.reduce((a, b) => a + b, 0);
    // Fold into the 19-item allowed list
    const idx = total % ALLOWED_PLANTS.length; // 0..18
    const chosen = ALLOWED_PLANTS[idx];
    const plantNumber = chosen.num;

    return {
        inputs: { ownerName, petName, petSpecies },
            parts: { v1_owner, v2_pet, v3_species, v4_gender, v5_birthday, v6_breed, v7_weight, v8_colors, v9_living, v10_virtues },
        total,
    plantNumber,
    plantName: chosen.name
    };
}

function buildPlantDescription(result) {
    const pet = result?.inputs?.petName?.trim() || "tu mascota";
    return `Basado en tus respuestas, ${result.plantName} refleja la energía de ${pet}.`;
}

// Post generated result image to server for emailing
async function postResultImageToServer(blob, filename, email, petName, plantName) {
    try {
        const ownerName = document.getElementById('owner-name')?.value || '';
        const form = new FormData();
        form.append('email', email);
        form.append('image', blob, filename);
        form.append('owner_name', ownerName);
        form.append('pet_name', petName || '');
        form.append('plant_name', plantName || '');

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        const response = await fetch('/plantscan/email', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            body: form
        });

        if (!response.ok) {
            throw new Error('Email upload failed');
        }

        // Optional: show success toast
        console.log('✅ Result image sent to email successfully');
        return true;
    } catch (err) {
        console.error('❌ Failed to send result image to email:', err);
        // Don't block the user flow on email failure
        return false;
    }
}

// Expose for quick console checks during dev
if (typeof window !== 'undefined') {
    window.gematriaSum = gematriaSum;
    window.computeAllValues = computeAllValues;
    window.postResultImageToServer = postResultImageToServer;
}

// **SCROLL LOCK SYSTEM** - Prevent scrolling past certain sections until unlocked
let maxUnlockedSection = 4; // Initially allow scrolling up to wrapper-4 (sections 5 and 6 locked)
let isScrollLocked = false;

function unlockNextSection() {
    maxUnlockedSection = Math.min(maxUnlockedSection + 1, 6); // Max section is 6 (results are in section-6)
    // debug removed
    
    // Release the GSAP scroll lock
    if (maxUnlockedSection >= 5) {
        releaseGSAPScrollLock();
        // Temporarily disable snap to allow smooth transition to new section
        if (window.scrollSnapControl) {
            window.scrollSnapControl.temporaryDisable(1500);
        }
    }
    
    // Trigger event to update bullet states
    window.dispatchEvent(new CustomEvent('sectionUnlocked'));
}

// Function to validate all required form fields
function validateAllForms() {
    const errors = [];
    
    // Section 2 - Basic Information
    const ownerName = document.getElementById('owner-name');
    const petName = document.getElementById('pet-name');
    const petSpecies = document.getElementById('pet-species');
    const genderRadios = document.querySelectorAll('input[name="gender"]');
    
    if (!ownerName || !ownerName.value.trim()) {
        errors.push('Nombre del cuidador');
    }
    if (!petName || !petName.value.trim()) {
        errors.push('Nombre de la mascota');
    }
    if (!petSpecies || !petSpecies.value) {
        errors.push('Especie de mascota');
    }
    
    let genderSelected = false;
    genderRadios.forEach(radio => {
        if (radio.checked) genderSelected = true;
    });
    if (!genderSelected) {
        errors.push('Género de la mascota');
    }
    
    // Section 3 - Pet Details
    const petBirthday = document.getElementById('pet-birthday');
    const petBreed = document.getElementById('pet-breed');
    const petWeight = document.getElementById('pet-weight');
    const colorCheckboxes = document.querySelectorAll('input[name="color"]');
    
    if (!petBirthday || !petBirthday.value) {
        errors.push('Fecha de cumpleaños');
    }
    if (!petBreed || !petBreed.value.trim()) {
        errors.push('Raza de la mascota');
    }
    if (!petWeight || !petWeight.value) {
        errors.push('Peso de la mascota');
    }
    
    let colorSelected = false;
    colorCheckboxes.forEach(checkbox => {
        if (checkbox.checked) colorSelected = true;
    });
    if (!colorSelected) {
        errors.push('Color de la mascota');
    }
    
    // Section 4 - Environment
    const livingSpaceRadios = document.querySelectorAll('input[name="living-space"]');
    const inspirationCheckboxes = document.querySelectorAll('input[name="inspiration"]');
    
    let livingSpaceSelected = false;
    livingSpaceRadios.forEach(radio => {
        if (radio.checked) livingSpaceSelected = true;
    });
    if (!livingSpaceSelected) {
        errors.push('Tipo de vivienda');
    }
    
    let inspirationSelected = false;
    inspirationCheckboxes.forEach(checkbox => {
        if (checkbox.checked) inspirationSelected = true;
    });
    if (!inspirationSelected) {
        errors.push('Al menos una palabra de inspiración');
    }

    // Section 4.5 - Email (moved to wrapper-4 near the Calcular button)
    const emailInput = document.getElementById('result-email');
    if (!emailInput || !emailInput.value.trim()) {
        errors.push('Correo electrónico');
    } else {
        // Basic email format check
        const emailVal = emailInput.value.trim();
        const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRe.test(emailVal)) {
            errors.push('Correo electrónico (formato inválido)');
        }
    }
    
    return errors;
}

// Function to show validation errors
function showValidationErrors(errors) {
    const errorMessage = `Por favor completa los siguientes campos:\n\n• ${errors.join('\n• ')}`;
    
    // Create a more detailed notification
    const notification = document.createElement('div');
    notification.className = 'scroll-lock-notification validation-error';
    notification.innerHTML = `
        <div class="">⚠️</div>
        <div class="main-text">Formulario incompleto</div>
        <div class="sub-text">Por favor, completa todos los campos requeridos</div>
        <div class="error-list">${errors.map(error => `• ${error}`).join('<br>')}</div>
    `;
    
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remove after 5 seconds (longer for error messages)
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 400);
    }, 5000);
}

// Validate Section 2 - Basic Information
function validateSection2() {
    const errors = [];
    
    const ownerName = document.getElementById('owner-name');
    const petName = document.getElementById('pet-name');
    const petSpecies = document.getElementById('pet-species');
    const genderRadios = document.querySelectorAll('input[name="gender"]');
    
    if (!ownerName || !ownerName.value.trim()) {
        errors.push('Nombre del cuidador');
    }
    if (!petName || !petName.value.trim()) {
        errors.push('Nombre de la mascota');
    }
    if (!petSpecies || !petSpecies.value) {
        errors.push('Especie de mascota');
    }
    
    let genderSelected = false;
    genderRadios.forEach(radio => {
        if (radio.checked) genderSelected = true;
    });
    if (!genderSelected) {
        errors.push('Género de la mascota');
    }
    
    return errors;
}

// Validate Section 3 - Pet Details
function validateSection3() {
    const errors = [];
    
    const petBirthday = document.getElementById('pet-birthday');
    const petBreed = document.getElementById('pet-breed');
    const petWeight = document.getElementById('pet-weight');
    const colorCheckboxes = document.querySelectorAll('input[name="color"]');
    
    if (!petBirthday || !petBirthday.value) {
        errors.push('Fecha de cumpleaños');
    }
    if (!petBreed || !petBreed.value.trim()) {
        errors.push('Raza de la mascota');
    }
    if (!petWeight || !petWeight.value) {
        errors.push('Peso de la mascota');
    }
    
    let colorSelected = false;
    colorCheckboxes.forEach(checkbox => {
        if (checkbox.checked) colorSelected = true;
    });
    if (!colorSelected) {
        errors.push('Color de la mascota (selecciona al menos uno)');
    }
    
    return errors;
}

// Handle mobile continue button clicks
function handleMobileContinue(nextSection) {
    let validationErrors = [];
    
    // Validate based on which section we're leaving
    if (nextSection === 3) {
        // Validate section 2
        validationErrors = validateSection2();
    } else if (nextSection === 4) {
        // Validate section 3
        validationErrors = validateSection3();
    }
    
    if (validationErrors.length > 0) {
        showValidationErrors(validationErrors);
        return;
    }
    
    // If section 2 is complete and moving to section 3, apply species background
    if (nextSection === 3) {
        const petSpecies = document.getElementById('pet-species');
        if (petSpecies && petSpecies.value) {
            applySpeciesBackgroundToSection3(petSpecies.value);
        }
    }
    
    // All validations passed, scroll to next section
    scrollToSection(nextSection);
}

// Function called by the "Calcular" button to unlock wrapper-5
function unlockAndScrollToNext() {
    // debug removed
    
    // First, validate all required fields
    const validationErrors = validateAllForms();
    
    if (validationErrors.length > 0) {
    // debug removed
        showValidationErrors(validationErrors);
        return; // Don't proceed if validation fails
    }
    
    // debug removed
    
    // Compute result now and cache it for the result renderer
    const result = computeAllValues();
    window.petPlantResult = result;
    // Prefill the static results container immediately so the final page shows content during the loading timelapse
    (function prefillResultsNow(res) {
        try {
            const resultsContainer = document.querySelector('.results-static-container');
            if (!resultsContainer) return;

            const pet = (res?.inputs?.petName?.trim() || 'tu mascota');
            const petNameEls = resultsContainer.querySelectorAll('[data-pet]');
            petNameEls.forEach(el => el.textContent = pet);

            const plantNameEl = resultsContainer.querySelector('[data-plant]');
            if (plantNameEl) plantNameEl.textContent = res.plantName;

            // Keep the server/blade-provided description in the DOM untouched.
            // Do not overwrite the <p data-description> here so it can display
            // the static message rendered by the Blade template.
            // const descEl = resultsContainer.querySelector('[data-description]');
            // if (descEl) descEl.textContent = buildPlantDescription(res);

            const imgEl = resultsContainer.querySelector('[data-plant-img]');
            if (imgEl) setImageWithFallback(imgEl, getPlantImageSrc(res.plantName));

            // Ensure results container is hidden until animation reveals it later
            resultsContainer.style.display = 'none';
        } catch (e) {
            // don't block flow on prefill errors
            console.warn('Prefill results failed', e);
        }
    })(result);
    // debug removed
    
    // Generate and send image via email if user consented
    (async function generateAndEmailImage(res) {
        try {
            const consentCheckbox = document.getElementById('send-results-email');
            const emailInput = document.getElementById('result-email');
            const email = emailInput ? emailInput.value.trim() : '';
            
            // Only proceed if checkbox is checked and email is valid
            if (!consentCheckbox || !consentCheckbox.checked || !email) {
                console.log('⏭️ Skipping email image send (not consented or no email)');
                return;
            }
            
            console.log('📧 User consented to receive email, generating image...');
            
            // Prepare data for image generation (description must come from DB)
            const imageData = {
                petName: res.inputs.petName || 'tu mascota',
                plantName: res.plantName || 'tu planta',
                description: null, // will be fetched from server
                image: getPlantImageSrc(res.plantName),
                url: window.lastShareUrl || window.location.href
            };

            // Try to fetch the plant description from the server (do not embed descriptions in the page)
            try {
                const descResp = await fetch(`/plants/description?name=${encodeURIComponent(res.plantName)}`);
                if (descResp && descResp.ok) {
                    const descJson = await descResp.json();
                    if (descJson && descJson.description) {
                        imageData.description = descJson.description;
                    }
                }
            } catch (e) {
                console.warn('Failed to fetch plant description from server, will fall back to generated text.', e);
            }

            // Fallback to computed description if server didn't provide one
            if (!imageData.description) {
                imageData.description = buildPlantDescription(res);
            }

            // Generate the story image (same function used for social sharing)
            const { blob, dataUrl } = await generateStoryImage(imageData);
            const fileName = `${res.inputs.petName}-${res.plantName}-Aurora.png`.replace(/\s+/g, '-');
            
            // Upload to server for email attachment
            await postResultImageToServer(blob, fileName, email, res.inputs.petName, res.plantName);
            
            console.log('✅ Image sent to server for email delivery');
        } catch (err) {
            console.warn('⚠️ Failed to generate/send image for email:', err);
            // Don't block user flow on email failure
        }
    })(result);
    
    // Remove the pulsing animation from the button
    const calcularButton = document.querySelector('.wrapper-4 .btn-primary');
    if (calcularButton) {
        calcularButton.classList.add('clicked');
        gsap.killTweensOf(calcularButton); // Stop any running animations
        gsap.set(calcularButton, { scale: 1 }); // Reset scale
    // debug removed
    }
    
    // Mark section-5 as unlocked internally so programmatic navigation to it will stick,
    // but do NOT release the GSAP scroll lock yet — keep the lock active to prevent
    // the user or snap logic from advancing to section-6 until the loading timer completes.
    // This avoids releasing GSAP/ScrollTrigger here which can trigger internal refreshes
    // and programmatic scrolls that jump to section-6 prematurely.
    maxUnlockedSection = Math.max(maxUnlockedSection, 5);
    window.dispatchEvent(new CustomEvent('sectionUnlocked'));
    // Temporarily disable scroll snap to allow the smooth scroll to section-5 to finish
    if (window.scrollSnapControl) {
        window.scrollSnapControl.temporaryDisable(1500);
    }
    
    // Scroll to the newly unlocked section (start loading UI quickly)
    setTimeout(() => {
        scrollToSection(5);
        // Ensure loading phrases and svgs are visible for a full 6 seconds,
        // then unlock & show result in section-6. This is intentionally
        // independent of how fast image generation or server calls complete.
        setTimeout(() => {
            const desc = buildPlantDescription(result);
            // Before showing, unlock and scroll to section-6 so results are shown there
            maxUnlockedSection = 6;
            releaseGSAPScrollLock();
            window.dispatchEvent(new CustomEvent('sectionUnlocked'));
            scrollToSection(6);
            // Small delay so the scroll has time to settle before animating
            setTimeout(() => showPlantResult(result.plantName, desc), 300);
        }, 6000); // Keep loading UI visible for 6 seconds
    }, 300); // Show section-5 quickly (300ms) after pressing Calcular

    // After computing the result, send the test run to the server and wait for confirmation
    (async function sendTestToServer(res) {
        try {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : null;

            const payload = {
                client: res.inputs.ownerName || null,
                email: document.getElementById('result-email')?.value || null,
                pet_name: res.inputs.petName || null,
                pet_species: document.getElementById('pet-species')?.value || null,
                gender: document.querySelector('input[name="gender"]:checked')?.value || null,
                pet_birthday: document.getElementById('pet-birthday')?.value || null,
                pet_breed: document.getElementById('pet-breed')?.value || null,
                pet_weight: document.getElementById('pet-weight')?.value || null,
                pet_color: getCheckedValues('color'),
                living_space: document.querySelector('input[name="living-space"]:checked')?.value || null,
                pet_characteristics: getCheckedValues('inspiration'),
                plant_test: res.plantName || null,
                plant: res.plantName || null,
                plant_description: buildPlantDescription(res) || null,
                plant_number: res.plantNumber || null,
                metadata: res.parts || null,
            };

            const response = await fetch('/tests', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const err = await response.json().catch(() => ({ message: 'Server error' }));
                console.warn('Test save failed', err);
                // Optionally show a small non-blocking notification
            } else {
                const data = await response.json();
                console.log('Test saved', data);
                // Store share_url returned by server for use by the share buttons
                if (data.share_url) {
                    window.lastShareUrl = data.share_url;
                }
                // You could also store the test id for later use: window.lastTestId = data.test_id;
            }
        } catch (e) {
            console.warn('Failed to send test to server', e);
        }
    })(result);
    
    // debug removed
}

// Initialize ScrollSmoother
document.addEventListener('DOMContentLoaded', function() {            // Create ScrollSmoother instance
    const smoother = ScrollSmoother.create({
        wrapper: '#smooth-wrapper',
        content: '#smooth-content',
        
        // **SMOOTH SCROLL CONTROL VALUES**
        smooth: 2,                   // Main smoothing: 0 = no smoothing, higher = more smoothing
                                    // Try values: 0.5 (fast), 1 (normal), 2 (smooth), 3+ (very smooth)
        
        smoothTouch: 1,           // Touch device smoothing (mobile/tablet)
                                    // false = no touch smoothing, 0.1-1 = smoothing amount
        
        speed: .8,                   // Overall scroll speed multiplier
                                    // 0.5 = half speed, 1 = normal, 2 = double speed
        
        effects: false,             // Disable effects to avoid conflicts with pinning
        normalizeScroll: true,      // Force scroll on JS thread for consistency
        
        // **EASING CONTROL**
        ease: "expo.out",           // Easing function for smooth scroll
                                    // Options: "expo", "power2", "elastic", "back", "sine"
        
        // **CALLBACKS FOR FINE CONTROL**
        onUpdate: function() {
            // Called on every scroll update - simplified for performance
        },
        
    });    // **SOLUTION: True deck-of-cards stacking effect**
    // All sections stack on top of each other at the top of the screen
    let sections = gsap.utils.toArray('.wrapper');
    
    sections.forEach((section, i) => {
        // Set z-index so first section is on bottom, last section is on top
        gsap.set(section, { zIndex: i + 1 });
        
        // Create the main stacking ScrollTrigger
        ScrollTrigger.create({
            trigger: section,
            start: 'top top',
            end: `+=${window.innerHeight}`,
            pin: true,
            pinSpacing: false,
            scroller: '#smooth-wrapper',
            onUpdate: (self) => {
                // As we scroll through this section, scale down all previous sections
                const progress = self.progress;
                
                // Scale down and move previous sections
                for (let j = 0; j < i; j++) {
                    const scale = 1 - (progress * 0.05 * (i - j)); // Gradual scale down
                    const y = progress * -20 * (i - j); // Move up slightly
                    
                    gsap.set(sections[j], {
                        scale: scale,
                        y: y,
                        transformOrigin: 'center top'
                    });
                }
            }
        });
    });    
    
    // **ELEGANT POP ANIMATIONS FOR INNER CONTAINERS**
    // Create beautiful entrance animations for each inner container (EXCEPT wrapper-1)
    const innerContainers = gsap.utils.toArray('.inner-container').filter((container, index) => {
        // Exclude wrapper-1's inner-container (index 0) since it has its own page load animation
        return index !== 0;
    });
    
    innerContainers.forEach((container, i) => {
        // Set initial state - hidden and smaller
        gsap.set(container, {
            opacity: 0,
            scale: 0.8,
            y: 60,
            rotationX: 15,
            transformOrigin: "center center"
        });
        
        // Create the pop animation timeline - one-time entrance only
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: container.closest('.wrapper'), // Trigger on parent wrapper instead
                start: 'top 80%', // Start when container is 80% in view
                end: 'top 20%', // End when container reaches 20% from top
                toggleActions: 'play none none reverse', // Play on enter, reverse on leave
                scroller: '#smooth-wrapper', // Use same scroller as ScrollSmoother
                // Optional: Add markers for debugging (remove in production)
                // markers: true,
                onToggle: self => {
                    if (self.isActive) {
                    } else {
                    }
                }
            }
        });
        
        // Animate container only
        tl.to(container, {
            opacity: 1,
            scale: 1,
            y: 0,
            rotationX: 0,
            duration: 1.2,
            ease: "back.out(1.4)", // Bouncy ease for pop effect
            delay: i * 0.15 // Stagger animation for each container
        });
        
        container.addEventListener('mouseleave', () => {
            gsap.to(container, {
                boxShadow: "0 0 0px rgba(255, 255, 255, 0)",
                scale: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });
    });    // Refresh ScrollTrigger on window resize
    window.addEventListener('resize', () => {
        ScrollTrigger.refresh();
    });
    
    // Initialize scroll lock system - GSAP-native approach
    setTimeout(() => {
        createGSAPScrollLock();
    }, 1000); // Delay to ensure all other ScrollTriggers are created first

    // **SCROLL SNAP SYSTEM** - GSAP ScrollTrigger-based implementation
    // Initialize scroll snapping after ScrollSmoother is ready
    setTimeout(() => {
        initializeScrollSnap();
    }, 1200); // Slight delay after scroll lock to ensure proper initialization// **DYNAMIC CONTROL METHODS** - Call these from browser console to test
    window.controlSmoothScroll = {
        // Change smoothing amount (0-5)
        setSmooth: (value) => {
            smoother.smooth(value);
        },
        
        // Get current smooth value
        getSmooth: () => {
            return smoother.smooth();
        },
        
        // Pause/resume smooth scrolling
        pause: () => {
            smoother.paused(true);
        },
        
        resume: () => {
            smoother.paused(false);
        },
        
        // Get current scroll progress (0-1)
        getProgress: () => {
            return smoother.progress;
        },
        
        // Get current scroll velocity
        getVelocity: () => {
            return smoother.getVelocity();
        }
    };
    // **ANIMATION CONTROL METHODS** - Control the pop animations
    window.controlAnimations = {
        // Replay all inner container animations
        replayAnimations: () => {
            ScrollTrigger.refresh();
        },        // Kill all animations
        killAnimations: () => {
            gsap.killTweensOf('.inner-container');
        },
        
        // Reset all inner containers to initial state
        resetContainers: () => {
            gsap.set('.inner-container', {
                opacity: 0,
                scale: 0.8,
                y: 60,
                rotationX: 15,
                clearProps: "boxShadow"
            });
        },
        
        // Check which containers are currently visible
        getVisibleContainers: () => {
            const triggers = ScrollTrigger.getAll();
            const visibleContainers = triggers.filter(trigger => trigger.isActive).length;
            return { visible: visibleContainers };
        }
    };    // **NAVBAR BULLET SYSTEM** - ScrollTrigger-based implementation
    (function() {
      const bullets = document.querySelectorAll('.bullet-navbar .bullet');
      const wrappers = Array.from(document.querySelectorAll('.wrapper'));
      const bulletLabel = document.querySelector('.bullet-navbar .bullet-label');
      
      if (!bullets.length || !wrappers.length) return;
      
      // Extract titles from each wrapper's data-nav-title attribute
      const wrapperTitles = wrappers.map(wrapper => {
        const titleElement = wrapper.querySelector('.title h1, .title h2');
        if (titleElement && titleElement.hasAttribute('data-nav-title')) {
          return titleElement.getAttribute('data-nav-title').trim();
        }
        // Fallback to text content if no data attribute
        return titleElement ? titleElement.textContent.trim() : `Sección ${wrappers.indexOf(wrapper) + 1}`;
      });
      
      // Function to activate a specific bullet
      function activateBullet(index) {
        // Clear all bullets
        bullets.forEach(bullet => bullet.classList.remove('bullet-active'));
        // Activate the specified bullet
        if (bullets[index]) {
          bullets[index].classList.add('bullet-active');
          // Update label
          if (bulletLabel && wrapperTitles[index]) {
            bulletLabel.textContent = wrapperTitles[index];
          }
        }
      }
      
      // Create ScrollTrigger for each wrapper
      wrappers.forEach((wrapper, index) => {
        ScrollTrigger.create({
          trigger: wrapper,
          start: 'top 50%', // When wrapper top hits middle of viewport
          end: 'bottom 50%', // When wrapper bottom hits middle of viewport
          scroller: '#smooth-wrapper', // Use ScrollSmoother scroller
          
          onEnter: () => {
            // Activate this bullet when entering from above
            activateBullet(index);
          },
          
          onEnterBack: () => {
            // Activate this bullet when entering from below (scrolling up)
            activateBullet(index);
          },
          
          // Optional: Add markers for debugging (remove in production)
          // markers: true,
          // id: `navbar-${index}`
        });
      });
      
      // BULLET CLICK HANDLERS
      bullets.forEach((bullet, index) => {
        bullet.addEventListener('click', function() {
          const wrapper = wrappers[index];
          const sectionNumber = index + 1; // Convert to 1-based section number
          
          if (wrapper) {
            // Check if section is unlocked before navigating
            if (sectionNumber <= maxUnlockedSection) {
              // Immediately update bullet states and label BEFORE scrolling
              activateBullet(index);
              
              // Scroll to the wrapper
              let smoother = ScrollSmoother.get();
              if (smoother) {
                // For first wrapper, scroll to very top (0)
                if (index === 0) {
                  smoother.scrollTo(0, true);
                } else {
                  smoother.scrollTo(wrapper, true, 'top top');
                }
              } else {
                if (index === 0) {
                  window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                  wrapper.scrollIntoView({ behavior: 'smooth' });
                }
              }
            } else {
              // Section is locked, show notification
              // debug removed
              showLockNotification();
            }
          }
        });
      });
      
      // BULLET HOVER: Show respective wrapper title in label on hover
      bullets.forEach((bullet, index) => {
        bullet.addEventListener('mouseenter', function() {
          if (bulletLabel && wrapperTitles[index]) {
            bulletLabel.textContent = wrapperTitles[index];
          }
        });
        bullet.addEventListener('mouseleave', function() {
          // Restore the label to the currently active bullet
          const activeIndex = Array.from(bullets).findIndex(b => b.classList.contains('bullet-active'));
          if (bulletLabel && wrapperTitles[activeIndex]) {
            bulletLabel.textContent = wrapperTitles[activeIndex];
          }
        });
      });
      
      // **UPDATE BULLET STATES BASED ON LOCK STATUS**
      function updateBulletLockStates() {
        bullets.forEach((bullet, index) => {
          const sectionNumber = index + 1;
          if (sectionNumber > maxUnlockedSection) {
            bullet.classList.add('bullet-locked');
            bullet.classList.remove('bullet-active');
          } else {
            bullet.classList.remove('bullet-locked');
          }
        });
      }
      
      // Initial update of bullet states
      updateBulletLockStates();
      
      // Update bullet states whenever a section is unlocked
      window.addEventListener('sectionUnlocked', updateBulletLockStates);
      
      // Initialize - Force first bullet active
      activateBullet(0);
      
    })();
});

// FAQ accordion interaction
(function() {
  const faqs = document.querySelectorAll('.faq-item');
  faqs.forEach(item => {
    const btn = item.querySelector('.faq-question');
    btn.addEventListener('click', function() {
      // Close others
      faqs.forEach(i => { if(i !== item) i.classList.remove('open'); });
      // Toggle this one
      item.classList.toggle('open');
    });
  });
})();    


// **PAGE LOAD ENTRANCE ANIMATION** - Relaxing and smooth for wrapper 1
    // Force scroll to top on page load
    window.scrollTo(0, 0);
    
    // First, set wrapper-1's inner-container to hidden for smooth entrance
    const wrapper1InnerContainer = document.querySelector('.wrapper-1 .inner-container');
    if (wrapper1InnerContainer) {
        gsap.set(wrapper1InnerContainer, {
            opacity: 0, // Start hidden for smooth entrance
            scale: 0.85,
            y: 30,
            rotationX: 10,
            transformOrigin: "center center"
        });
    }
    
    // Select wrapper 1 elements for entrance animation
    const wrapper1Elements = [
        '.wrapper-1 .logo',
        '.wrapper-1 .title',
        '.wrapper-1 .paragraph',
        '.wrapper-1 .cta-buttons'
    ];
    
    // Only fade in the elements (no movement, scale, or rotation)
    gsap.set(wrapper1Elements, {
        opacity: 0
    });
    
    // Create beautiful entrance timeline with stagger
    const entranceTl = gsap.timeline({ 
        delay: .2, // Longer delay for more elegant entrance
        onComplete: () => {
            // debug removed
        }
    });
    
    // Animate the inner container first, slow and smooth
    entranceTl.to(wrapper1InnerContainer, {
        opacity: 1,
        scale: 1,
        y: 0,
        rotationX: 0,
        duration: 2.2,
        ease: "power2.out"
    })
    // Fade in the children after the container is mostly visible
    .to(wrapper1Elements, {
        opacity: 1,
        duration: 2,
        ease: "power2.out",
        stagger: {
            each: 0.35,
            from: "start",
            ease: "power2.out"
        }
    }, "-=0.5"); // Start children after most of the container is visible

    // **BULLET NAVBAR ENTRANCE ANIMATION**
    // Hide navbar and its children initially
    const bulletNavbar = document.querySelector('.bullet-navbar');
    const bulletNavbarLabel = bulletNavbar?.querySelector('.bullet-label');
    const bulletNavbarList = bulletNavbar?.querySelector('ul');
    if (bulletNavbar) {
        gsap.set(bulletNavbar, { opacity: 0 });
    }
    if (bulletNavbarLabel) {
        gsap.set(bulletNavbarLabel, { opacity: 0 });
    }
    if (bulletNavbarList) {
        gsap.set(bulletNavbarList, { opacity: 0 });
    }
    // Timeline for navbar entrance
    const navbarTl = gsap.timeline({ delay: 2 });
    // Fade in navbar background (parent)
    navbarTl.to(bulletNavbar, {
        opacity: 1,
        duration: 1.2,
        ease: "power2.out"
    });
    // Fade in label and ul children one by one
    navbarTl.to([bulletNavbarLabel, bulletNavbarList], {
        opacity: 1,
        duration: 1.1,
        ease: "power2.out",
        stagger: {
            each: 0.25,
            from: "start"
        }
    });
    
    // **GSAP-NATIVE SCROLL LOCK SYSTEM** - Using ScrollTrigger pin to create hard stop
    let scrollLockTrigger = null;
    let scrollLockActive = false;
    let lockScrollPosition = null;
    
    function createGSAPScrollLock() {
        // Remove existing lock if any
        if (scrollLockTrigger) {
            scrollLockTrigger.kill();
            scrollLockTrigger = null;
        }
        
        // Only create lock if wrapper-5 is locked
        if (maxUnlockedSection < 5) {
            const wrapper4 = document.getElementById('section-4');
            
            if (wrapper4) {
                // debug removed
                
                scrollLockTrigger = ScrollTrigger.create({
                    trigger: wrapper4,
                    start: 'bottom bottom',
                    end: '+=9999', // Large end value to keep it active
                    scroller: '#smooth-wrapper',
                    
                    onEnter: () => {
                        if (maxUnlockedSection < 5) {
                            const smoother = ScrollSmoother.get();
                            if (smoother) {
                                // Store the current scroll position as the lock point
                                lockScrollPosition = smoother.scrollTop();
                                scrollLockActive = true;
                                
                                // debug removed
                                // Only show notification on desktop (not mobile with buttons)
                                const isMobile = window.innerWidth <= 600;
                                if (!isMobile) {
                                    showLockNotification();
                                    pulseCalcularButton();
                                
                                    // Create a continuous monitoring system (desktop only)
                                    const lockMonitor = () => {
                                        if (!scrollLockActive || maxUnlockedSection >= 5) {
                                            return; // Exit if lock is released
                                        }
                                        
                                        const currentPosition = smoother.scrollTop();
                                        
                                        // If user tries to scroll past the lock point
                                        if (currentPosition > lockScrollPosition + 10) { // 10px tolerance
                                            smoother.scrollTo(lockScrollPosition, false); // Snap back immediately
                                            showLockNotification();
                                            pulseCalcularButton();
                                        }
                                        
                                        // Schedule next check
                                        requestAnimationFrame(lockMonitor);
                                    };
                                    
                                    // Start the monitoring loop
                                    requestAnimationFrame(lockMonitor);
                                }
                            }
                        }
                    },
                    
                    onLeave: (self) => {
                        // Only allow leaving if the section is unlocked
                        if (maxUnlockedSection < 5) {
                            // Force back to the locked position
                            const smoother = ScrollSmoother.get();
                            if (smoother && lockScrollPosition !== null) {
                                smoother.scrollTo(lockScrollPosition, true);
                            }
                        }
                    }
                });
            }
        }
    }
    
    function releaseGSAPScrollLock() {
        if (scrollLockTrigger) {
            // debug removed
            
            // Deactivate lock monitoring
            scrollLockActive = false;
            lockScrollPosition = null;
            
            // Kill the ScrollTrigger
            scrollLockTrigger.kill();
            scrollLockTrigger = null;
            
            // Clear any pulsing animations
            const calcularButton = document.querySelector('.wrapper-4 .btn-primary');
            if (calcularButton) {
                gsap.killTweensOf(calcularButton);
                gsap.set(calcularButton, { scale: 1 });
            }
            
            // Refresh ScrollTrigger system
            ScrollTrigger.refresh();
        }
    }
    
    function pulseCalcularButton() {
        const calcularButton = document.querySelector('.wrapper-4 .btn-primary');
        if (calcularButton && !calcularButton.classList.contains('clicked')) {
            gsap.to(calcularButton, {
                scale: 1.05,
                duration: 0.8,
                ease: "power2.inOut",
                yoyo: true,
                repeat: -1
            });
        }
    }

// Enhanced scrollToSection function with lock checking
function scrollToSectionWithLock(sectionNumber) {
    // Always allow navigation to unlocked sections or backwards
    if (sectionNumber <= maxUnlockedSection) {
        scrollToSection(sectionNumber);
    } else {
    // debug removed
        // Optional: Show a notification to the user
        showLockNotification();
    }
}

function showLockNotification() {
    // Prevent multiple notifications from stacking
    const existingNotification = document.querySelector('.scroll-lock-notification');
    if (existingNotification) {
        return; // Already showing a notification
    }
    
    // Create a temporary notification
    const notification = document.createElement('div');
    notification.className = 'scroll-lock-notification'; // Add additional style classes here if desired
    
    // Create structured content
    notification.innerHTML = `
        <div class="lock-icon">🔒</div>
        <div class="main-text">Completa el formulario para continuar</div>
        <div class="sub-text">Haz clic en "Calcular" para desbloquear</div>
    `;
    
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remove after 3.5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 400); // Wait for fade out animation
    }, 2000);
}

    // **DEBUG INFORMATION** - Show current scroll status (remove in production)
    if (typeof window !== 'undefined') {
        window.debugScrollLock = {
            getCurrentSection: () => {
                const smoother = ScrollSmoother.get();
                if (smoother) {
                    const progress = smoother.progress();
                    return Math.floor(progress * 6) + 1;
                }
                return 1;
            },
            getMaxUnlocked: () => maxUnlockedSection,
            unlockNext: () => unlockNextSection(),
            createLock: () => createGSAPScrollLock(),
            releaseLock: () => releaseGSAPScrollLock(),
            showCurrentStatus: () => {
                // debug removed
            }
        };
    }
// **FALLBACK SCROLL PREVENTION** - Additional methods to ensure lock works on all devices
    let preventScrollEvents = false;
    
    function enableScrollPrevention() {
        preventScrollEvents = true;
        
        // Prevent wheel events
        const wheelHandler = (e) => {
            if (preventScrollEvents && maxUnlockedSection < 5) {
                const smoother = ScrollSmoother.get();
                if (smoother && lockScrollPosition !== null) {
                    const currentPosition = smoother.scrollTop();
                    if (e.deltaY > 0 && currentPosition >= lockScrollPosition - 10) {
                        e.preventDefault();
                        e.stopPropagation();
                        showLockNotification();
                        pulseCalcularButton();
                        return false;
                    }
                }
            }
        };
        
        // Prevent touch events on mobile
        let lastTouchY = null;
        
        const touchStartHandler = (e) => {
            // Store initial touch position
            lastTouchY = e.touches ? e.touches[0].clientY : null;
        };
        
        const touchMoveHandler = (e) => {
            if (preventScrollEvents && maxUnlockedSection < 5) {
                // Allow touches on form elements and their containers
                const target = e.target;
                if (target.matches('input, select, textarea, label, button, .radio-label, .checkbox-label, .mobile-select, .mobile-chips, .environment-col, .environment-form, .form-group, span')) {
                    return; // Allow interaction with form elements and their containers
                }
                
                const smoother = ScrollSmoother.get();
                if (smoother && lockScrollPosition !== null) {
                    const currentPosition = smoother.scrollTop();
                    
                    // Only prevent if user is at lock position AND trying to scroll down
                    if (currentPosition >= lockScrollPosition - 10) {
                        const currentTouchY = e.touches ? e.touches[0].clientY : null;
                        const isScrollingDown = lastTouchY !== null && currentTouchY < lastTouchY;
                        
                        if (isScrollingDown) {
                            e.preventDefault();
                            showLockNotification();
                            pulseCalcularButton();
                            return false;
                        }
                    }
                    
                    // Update last touch position
                    if (e.touches) {
                        lastTouchY = e.touches[0].clientY;
                    }
                }
            }
        };
        
        // Add event listeners
        document.addEventListener('wheel', wheelHandler, { passive: false });
        document.addEventListener('touchstart', touchStartHandler, { passive: false });
        document.addEventListener('touchmove', touchMoveHandler, { passive: false });
        
        // Store handlers for removal later
        window.scrollLockHandlers = { wheelHandler, touchStartHandler, touchMoveHandler };
    }
    
    function disableScrollPrevention() {
        preventScrollEvents = false;
        
        if (window.scrollLockHandlers) {
            document.removeEventListener('wheel', window.scrollLockHandlers.wheelHandler);
            document.removeEventListener('touchstart', window.scrollLockHandlers.touchStartHandler);
            document.removeEventListener('touchmove', window.scrollLockHandlers.touchMoveHandler);
            window.scrollLockHandlers = null;
        }
    }
    
    // Update the main lock creation function to include fallback prevention
    const originalCreateGSAPScrollLock = createGSAPScrollLock;
    createGSAPScrollLock = function() {
        originalCreateGSAPScrollLock();
        if (maxUnlockedSection < 5) {
            enableScrollPrevention();
        }
    };
    
    // Update the release function to disable fallback prevention
    const originalReleaseGSAPScrollLock = releaseGSAPScrollLock;
    releaseGSAPScrollLock = function() {
        disableScrollPrevention();
        originalReleaseGSAPScrollLock();
    };
    
    // **END FALLBACK SCROLL PREVENTION**

// **SCROLL SNAP SYSTEM** - Integrated with existing bullet navbar system
let scrollSnapEnabled = true;
let isSnapping = false;
let currentSnapSection = 1;

function initializeScrollSnap() {
    if (!scrollSnapEnabled) return;
    
    const smoother = ScrollSmoother.get();
    if (!smoother) {
        console.warn('ScrollSmoother not found, scroll snap disabled');
        return;
    }
    
    // **INTEGRATION WITH BULLET NAVBAR** - Use existing bullet system for snap points
    const bullets = document.querySelectorAll('.bullet-navbar .bullet');
    const wrappers = Array.from(document.querySelectorAll('.wrapper'));
    
    if (!bullets.length || !wrappers.length) {
        console.warn('Bullet navbar or wrappers not found, scroll snap disabled');
        return;
    }
    
    // **GENTLE SCROLL END DETECTION** - Only snap when scrolling naturally stops
    let scrollEndTimer = null;
    let isUserScrolling = false;
    let lastScrollTime = 0;
    
    // Main scroll listener that doesn't interfere with existing animations
    ScrollTrigger.create({
        trigger: document.body,
        start: 0,
        end: 'max',
        scroller: '#smooth-wrapper',
        
        onUpdate: function(self) {
            if (isSnapping) return; // Don't interfere with active snapping
            
            const now = Date.now();
            lastScrollTime = now;
            isUserScrolling = true;
            
            // Clear existing timer
            if (scrollEndTimer) {
                clearTimeout(scrollEndTimer);
            }
            
            // Set timer to detect when scrolling stops (longer delay for gentleness)
            scrollEndTimer = setTimeout(() => {
                const timeSinceLastScroll = Date.now() - lastScrollTime;
                if (timeSinceLastScroll >= 300 && isUserScrolling) { // Only after 300ms of no scrolling
                    isUserScrolling = false;
                    performGentleSnapCheck();
                }
            }, 350);
        }
    });
    
    // **GENTLE SNAP CHECK** - Uses bullet navbar logic to determine closest section
    function performGentleSnapCheck() {
        if (isSnapping || !scrollSnapEnabled) return;
        
        const currentScrollTop = smoother.scrollTop();
        const viewportHeight = window.innerHeight;
        const scrollPosition = currentScrollTop + (viewportHeight * 0.5); // Middle of viewport
        
        // Find which wrapper section we're closest to using similar logic as bullet navbar
        let targetSection = 1;
        let minDistance = Infinity;
        
        wrappers.forEach((wrapper, index) => {
            const sectionNumber = index + 1;
            
            // Respect scroll lock - don't snap to locked sections
            if (sectionNumber > maxUnlockedSection) return;
            
            const rect = wrapper.getBoundingClientRect();
            const wrapperTop = rect.top + currentScrollTop;
            const wrapperMiddle = wrapperTop + (rect.height * 0.5);
            
            const distance = Math.abs(scrollPosition - wrapperMiddle);
            
            if (distance < minDistance) {
                minDistance = distance;
                targetSection = sectionNumber;
            }
        });
        
        // Only snap if we're not already very close to the target
        const targetWrapper = wrappers[targetSection - 1];
        if (targetWrapper) {
            const targetRect = targetWrapper.getBoundingClientRect();
            const targetTop = targetRect.top + currentScrollTop;
            const currentDistance = Math.abs(currentScrollTop - targetTop);
            
            // Snap threshold - only snap if we're reasonably far from perfect alignment
            const snapThreshold = viewportHeight * 0.15; // 15% of viewport height
            
            if (currentDistance > 20 && currentDistance < snapThreshold) {
                performGentleSnap(targetSection, targetTop);
            }
        }
    }
    
    // **GENTLE SNAP ANIMATION** - Smooth, non-jarring snap
    function performGentleSnap(targetSection, targetPosition) {
        if (isSnapping) return;
        
        isSnapping = true;
        currentSnapSection = targetSection;
        
        // Use ScrollSmoother's smooth scrollTo (this won't interfere with your animations)
        smoother.scrollTo(targetPosition, true, "top top");
        
        // Set a reasonable timeout to reset snapping state
        setTimeout(() => {
            isSnapping = false;
            
            // Trigger custom event for other systems
            window.dispatchEvent(new CustomEvent('scrollSnapComplete', {
                detail: { 
                    section: targetSection, 
                    position: targetPosition
                }
            }));
        }, 800); // Shorter timeout for responsiveness
    }
    
    // **KEYBOARD NAVIGATION** - Enhanced arrow key support
    document.addEventListener('keydown', function(e) {
        // Only handle if no input is focused
        if (document.activeElement && 
            (document.activeElement.tagName === 'INPUT' || 
             document.activeElement.tagName === 'TEXTAREA' || 
             document.activeElement.tagName === 'SELECT')) {
            return;
        }
        
        let targetSection = null;
        
        switch(e.key) {
            case 'ArrowDown':
            case 'PageDown':
                e.preventDefault();
                targetSection = Math.min(currentSnapSection + 1, maxUnlockedSection);
                break;
                
            case 'ArrowUp':
            case 'PageUp':
                e.preventDefault();
                targetSection = Math.max(currentSnapSection - 1, 1);
                break;
                
            case 'Home':
                e.preventDefault();
                targetSection = 1;
                break;
                
            case 'End':
                e.preventDefault();
                targetSection = maxUnlockedSection;
                break;
        }
        
        if (targetSection && targetSection !== currentSnapSection) {
            // Use the existing scrollToSection function to maintain consistency
            scrollToSection(targetSection);
        }
    });
    
    // **ENHANCED BULLET CLICKS** - Make bullet navbar snappier
    bullets.forEach((bullet, index) => {
        const sectionNumber = index + 1;
        
        bullet.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (sectionNumber <= maxUnlockedSection) {
                // Temporarily disable snap detection during manual navigation
                isSnapping = true;
                scrollToSection(sectionNumber);
                
                // Re-enable after navigation completes
                setTimeout(() => {
                    isSnapping = false;
                    currentSnapSection = sectionNumber;
                }, 1000);
            }
        });
    });
    
    // **WINDOW RESIZE HANDLER** - Refresh on resize
    window.addEventListener('resize', localDebounce(() => {
        ScrollTrigger.refresh();
    }, 250));
    
    console.log('✅ Gentle scroll snap integrated with bullet navbar system');
}

// **SIMPLIFIED SNAP CONTROL API** - Lighter control interface
window.scrollSnapControl = {
    // Enable/disable scroll snapping
    toggle: (enabled = !scrollSnapEnabled) => {
        scrollSnapEnabled = enabled;
        console.log('Scroll snap', enabled ? 'enabled' : 'disabled');
    },
    
    // Check if snapping is currently active
    isActive: () => scrollSnapEnabled && !isSnapping,
    
    // Get current snap section
    getCurrentSection: () => currentSnapSection,
    
    // Temporarily disable snapping
    temporaryDisable: (durationMs = 2000) => {
        const wasEnabled = scrollSnapEnabled;
        scrollSnapEnabled = false;
        setTimeout(() => {
            scrollSnapEnabled = wasEnabled;
        }, durationMs);
    }
};

// **END SCROLL SNAP SYSTEM**

// **ANIMATED LOADING PHRASES SYSTEM** - Enhanced with SplitText for wrapper-5
    const loadingPhrases = [
        "Consultando el oráculo de raíces...",
        "Midiendo el aura botánica de tu mascota...",
        "Comparando bigotes con pétalos...",
        "Reuniendo semillas de posibilidades...",
        "Escaneando ladridos, maullidos y latidos...",
        "Desempolvando el herbario místico...",
        "Calculando la flor que mejor encarna su esencia...",
        "Buscando en el jardín secreto de los vínculos eternos...",
    ];
    
    // Fisher–Yates shuffle (pure, returns a new array)
    function shuffleArray(arr) {
        const a = arr.slice();
        for (let i = a.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [a[i], a[j]] = [a[j], a[i]];
        }
        return a;
    }
    
    let phraseTimeline = null;
    let currentPhraseIndex = 0;
    let currentSplit = null;

    // Ensure fonts are loaded before creating SplitText instances (SplitText warns otherwise)
    function ensureFontsLoaded(timeoutMs = 1500) {
        if (document.fonts && document.fonts.ready) {
            // Race fonts.ready with a timeout so we don't hang forever
            return Promise.race([
                document.fonts.ready,
                new Promise(resolve => setTimeout(resolve, timeoutMs))
            ]);
        }
        // Fallback: resolve after a short delay
        return new Promise(resolve => setTimeout(resolve, 200));
    }
    
    async function startLoadingAnimation() {
        const phraseElement = document.querySelector('.loading-phrase');
        if (!phraseElement) return;
        
        // Kill any previous timeline before rebuilding
        if (phraseTimeline) {
            phraseTimeline.kill();
            phraseTimeline = null;
        }

    // Wait for fonts to load before building SplitText-based animations
    await ensureFontsLoaded();

    // Create a non-repeating timeline that will rebuild with a new shuffle on complete
        phraseTimeline = gsap.timeline({ repeat: 0, onComplete: startLoadingAnimation });

        // Build a randomized order for this cycle
        const randomized = shuffleArray(loadingPhrases);

        // Function to animate each phrase with character-by-character effects
        function animatePhrase(phrase) {
            let localSplit = null;

            // Create a timeline for this specific phrase
            const tl = gsap.timeline();

            // Step 1: Setup the phrase
            tl.call(() => {
                // Clean up global split first
                if (currentSplit) {
                    currentSplit.revert();
                    currentSplit = null;
                }

                // Set the text content
                phraseElement.textContent = phrase;

                // Create new SplitText instance
                localSplit = new SplitText(phraseElement, {
                    type: "chars,words",
                    charsClass: "char",
                    wordsClass: "word"
                });

                // Update global reference
                currentSplit = localSplit;
            });

            // Step 2: Set initial state and animate in
            tl.call(() => {
                if (localSplit && localSplit.chars) {
                    // Set initial state for all characters
                    gsap.set(localSplit.chars, {
                        opacity: 0,
                        y: 50,
                        rotationX: 90,
                        scale: 0.3,
                        transformOrigin: "50% 50%"
                    });

                    // Animate characters in
                    gsap.to(localSplit.chars, {
                        opacity: 1,
                        y: 0,
                        rotationX: 0,
                        scale: 1,
                        duration: 0.8,
                        ease: "back.out(1.4)",
                        stagger: {
                            each: 0.03,
                            from: "start"
                        }
                    });
                }
            }, null, null, "+=0.1");

            // Step 3: Hold the phrase
            tl.to({}, { duration: 3.5 });

            // Step 4: Animate characters out
            tl.call(() => {
                if (localSplit && localSplit.chars) {
                    gsap.to(localSplit.chars, {
                        opacity: 0,
                        y: -30,
                        rotationX: -45,
                        scale: 1.2,
                        duration: 0.6,
                        ease: "back.in(1.2)",
                        stagger: {
                            each: 0.02,
                            from: "end"
                        }
                    });
                }
            });

            // Step 5: Wait for animation to complete
            tl.to({}, { duration: 0.6 });

            return tl;
        }

        // Add all phrases to the main timeline in randomized order
        randomized.forEach((phrase) => {
            phraseTimeline.add(animatePhrase(phrase));
        });

        // Start the animation
        phraseTimeline.play();
    }
    
    function stopLoadingAnimation() {
        if (phraseTimeline) {
            // debug removed
            phraseTimeline.kill();
            phraseTimeline = null;
        }
        
        // Clean up SplitText
        if (currentSplit) {
            currentSplit.revert();
            currentSplit = null;
        }
    }
    
    // === Plant image resolution (based on assets/imgs/plants contents) ===
    const BASE_PLANT_IMG_PATH = "./assets/plantscan/imgs/plants/";
    
    function slugifyForFilename(name) {
        if (!name) return "";
        const noParens = String(name).replace(/\(.*?\)/g, "");
        return normalizeStr(noParens)
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/-+/g, "-")
            .replace(/^-|-$/g, "");
    }
    
    // Map plant name slugs -> actual filenames found in assets/imgs/plants
    const PLANT_IMAGE_MAP = {
        "pensamientos-viola-tricolor": "pensamiento.png",
        "san-pedro": "san-pedro.png",
        "limonero": "citrus-lemon.png",
        "schefflera": "schefflera.png",
        "monstera-deliciosa": "monstera-deliciosa.png",
        "buganvilla": "buganvilla.png",
        "zamioculca": "zamioculca.png",
        "syngonium-neon-pink": "syngonium-neon-pink.png",
        "sanseviera": "sanseviera.png",
        "cala-blanca": "cala-roja.png",
        "syngonium-three-kings": "syngonium-three-kings.png",
        "anturio-rojo": "anturio.png",
        "calathea-triostar": "calathea-triostar.png",
        "monstera-adansonii": "monstera-adasonii.png",
        "helecho-nativo": "helecho-nativo.png",
        "capuli": "capuli.png",
        "jade": "jade.png",
        "syngonium-confettii": "syngonium-confetti.png",
        "cholan": "cholan.png"
    };
    
    function getPlantImageSrc(plantName) {
        const slug = slugifyForFilename(plantName);
        const filename = PLANT_IMAGE_MAP[slug] || `${slug}.png`;
        return BASE_PLANT_IMG_PATH + filename;
    }
    
    function setImageWithFallback(imgEl, src) {
        if (!imgEl) return;
        imgEl.onerror = () => {
            imgEl.onerror = null;
            imgEl.src = BASE_PLANT_IMG_PATH + "schefflera.png";
        };
        // encode spaces/accents in path (e.g., "buganvilla .png")
        imgEl.src = encodeURI(src);
    }
    
    // Function to show final result (can be called later when calculation is complete)
    function showPlantResult(plantName, plantDescription) {
        stopLoadingAnimation();

        const loadingContainer = document.querySelector('.loading-container');
        const resultsContainer = document.querySelector('.results-static-container');
        if (!loadingContainer || !resultsContainer) return;

        // Fill static fields
        const pet = (window.petPlantResult?.inputs?.petName?.trim() || 'tu mascota');
        const petNameEls = resultsContainer.querySelectorAll('[data-pet]');
        petNameEls.forEach(el => el.textContent = pet);

        const plantNameEl = resultsContainer.querySelector('[data-plant]');
        if (plantNameEl) plantNameEl.textContent = plantName;

    // Intentionally do NOT modify the <p data-description> element here.
    // The paragraph should display the static Blade-rendered message
    // (e.g., 'Se ha enviado la imagen...'). Keeping JS from overwriting
    // it ensures consistent UX between email and share flows.
    // const descEl = resultsContainer.querySelector('[data-description]');
    // if (descEl) descEl.textContent = plantDescription;

        const imgEl = resultsContainer.querySelector('[data-plant-img]');
        if (imgEl) {
            const imgSrc = getPlantImageSrc(plantName);
            setImageWithFallback(imgEl, imgSrc);
            imgEl.alt = plantName;
        }

        // Animate out the loading content and reveal the static results container
        setTimeout(() => {
            gsap.timeline()
                .to(loadingContainer.children, {
                    opacity: 0,
                    y: -30,
                    stagger: 0.1,
                    duration: 0.6,
                    ease: "power2.in"
                })
                .call(() => {
                    // Hide loading and show results
                    loadingContainer.style.display = 'none';
                    resultsContainer.style.display = '';
                })
                .fromTo('.results-static-container .plant-result > *', 
                    {
                        opacity: 0,
                        y: 50,
                        scale: 0.8
                    },
                    {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        stagger: 0.2,
                        duration: 0.8,
                        ease: "back.out(1.4)"
                    }
                );
        }, 500); // Quick delay for loading animation
    }
    
    // Start loading animation when wrapper-5 becomes visible
    ScrollTrigger.create({
        trigger: '#section-5',
        start: 'top 80%',
        scroller: '#smooth-wrapper',
        onEnter: () => {
            setTimeout(startLoadingAnimation, 500); // Small delay to ensure smooth transition
        },
        once: true // Only trigger once
    });
    // **END ANIMATED LOADING PHRASES SYSTEM**

    // **TEMPORARY TESTING BYPASS** - Remove in production
    // Press Ctrl+T to bypass form validation and unlock wrapper-5
    document.addEventListener('keydown', function(e) {
        // (Ctrl+T shortcut removed to avoid conflicts; use Ctrl+Alt+T or Ctrl+5)
        
        // (Ctrl+5 shortcut removed)
        
            // Press Ctrl+Alt+T to bypass validation and jump to wrapper-5 (no result injection)
            if (e.ctrlKey && e.altKey && (e.key === 'T' || e.key === 't')) {
                e.preventDefault();
                try {
                    // Unlock section and release locks (do not compute or inject results)
                                    maxUnlockedSection = 6; // fully unlock including final results
                                    releaseGSAPScrollLock();
                                    window.dispatchEvent(new CustomEvent('sectionUnlocked'));

                                    // Scroll to the final results section (section-6)
                                    scrollToSection(6);

                    // Small UI notification to indicate bypass (auto-remove)
                    const notification = document.createElement('div');
                    notification.className = 'scroll-lock-notification';
                    notification.innerHTML = `
                        <div class="lock-icon">🚀</div>
                        <div class="main-text">Testing Bypass</div>
                        <div class="sub-text">Unlocked and scrolled to results</div>
                    `;
                    document.body.appendChild(notification);
                    setTimeout(() => notification.classList.add('show'), 10);
                    setTimeout(() => {
                        notification.classList.remove('show');
                        setTimeout(() => document.body.contains(notification) && document.body.removeChild(notification), 400);
                    }, 2000);
                } catch (err) {
                    console.error('Test bypass (Ctrl+Alt+T) failed:', err);
                }
            }
    });

    // Removed temporary Test Mode button (was used for testing)

    // **END TEMPORARY TESTING BYPASS**

    // Initialize UI state when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // **MOBILE SCROLL LOCK** - Disable ScrollSmoother on mobile devices
        function isMobileDevice() {
            return window.innerWidth <= 600;
        }

        if (isMobileDevice()) {
            // Disable ScrollSmoother on mobile to prevent scrolling
            const smoother = ScrollSmoother.get();
            if (smoother) {
                smoother.paused(true);
                console.log('✅ ScrollSmoother paused for mobile');
            }

            // Override scrollToSection to use simple window scroll on mobile
            window.originalScrollToSection = window.scrollToSection;
            window.scrollToSection = function(sectionNumber) {
                const section = document.getElementById(`section-${sectionNumber}`);
                if (section) {
                    const smoother = ScrollSmoother.get();
                    if (smoother) {
                        // Unpause smoother for scrolling
                        smoother.paused(false);
                        
                        // Scroll to section with explicit top alignment
                        smoother.scrollTo(section, true, 'top top');
                        
                        // Wait longer for scroll animation to complete before re-pausing
                        // Using 1500ms to ensure smooth scroll completes on slower devices
                        setTimeout(() => {
                            smoother.paused(true);
                            // Force scroll position to exact top if needed
                            const rect = section.getBoundingClientRect();
                            if (Math.abs(rect.top) > 5) { // If not at top within 5px tolerance
                                window.scrollTo({
                                    top: section.offsetTop,
                                    behavior: 'smooth'
                                });
                            }
                        }, 1500);
                    } else {
                        // Fallback if smoother doesn't exist
                        window.scrollTo({
                            top: section.offsetTop,
                            behavior: 'smooth'
                        });
                    }
                }
            };
        }

        // Attach mobile continue button handlers
        const mobileContinueButtons = document.querySelectorAll('.mobile-continue-btn');
        mobileContinueButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const nextSection = parseInt(this.getAttribute('data-next-section'));
                if (nextSection) {
                    handleMobileContinue(nextSection);
                }
            });
        });

        // Enforce max 3 inspirations selections
        const inspirationBoxes = Array.from(document.querySelectorAll('input[name="inspiration"]'));
        if (inspirationBoxes.length) {
            inspirationBoxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const checked = inspirationBoxes.filter(x => x.checked);
                    if (checked.length > 3) {
                        cb.checked = false; // revert the last change
                        showValidationErrors(['Máximo 3 inspiraciones']);
                    }
                });
            });
        }
    // debug removed

        // Update section-3 image when species changes (and apply initial)
        const speciesSelect = document.getElementById('pet-species');
        if (speciesSelect) {
            const updateBg = () => applySpeciesBackgroundToSection3(speciesSelect.value);
            speciesSelect.addEventListener('change', updateBg);
            updateBg();
        }
        
        // Array of available animal SVGs
        const animalSvgs = [
            './assets/plantscan/imgs/icon-dog.svg',
            './assets/plantscan/imgs/icon-cat.svg',
            './assets/plantscan/imgs/icon-bunny.svg',
            './assets/plantscan/imgs/icon-hamster.svg'
        ];
        
        // Get references to the three SVG positions
        const svgElements = {
            top: document.getElementById('pet-svg-top'),
            left: document.getElementById('pet-svg-left'),
            right: document.getElementById('pet-svg-right')
        };
        
        // Function to get a random SVG that's different from the current one
        function getRandomSvg(currentSrc) {
            const availableOptions = animalSvgs.filter(svg => svg !== currentSrc);
            return availableOptions[Math.floor(Math.random() * availableOptions.length)];
        }
        
        // Function to change SVG with smooth transition
        function changeSvgWithTransition(element) {
            if (!element) return;
            
            // Get current source
            const currentSrc = element.src;
            
            // Fade out - only animate opacity and scale, preserve rotation
            gsap.to(element, {
                opacity: 0.3,
                scale: 0.8,
                duration: 0.3,
                ease: "power2.inOut",
                onComplete: function() {
                    // Change the source to a random different one
                    const newSrc = getRandomSvg(currentSrc);
                    element.src = newSrc;
                    
                    // Fade back in - only animate opacity and scale, preserve rotation
                    gsap.to(element, {
                        opacity: 1,
                        scale: 1,
                        duration: 0.3,
                        ease: "power2.inOut"
                    });
                }
            });
        }
        
        // Function to randomly change one of the three SVGs
        function randomlyChangeSvg() {
            const positions = ['top', 'left', 'right'];
            const randomPosition = positions[Math.floor(Math.random() * positions.length)];
            const selectedElement = svgElements[randomPosition];
            
            changeSvgWithTransition(selectedElement);
            // debug removed
        }
        
        // Function to start continuous triangle rotation with better control
        function startContinuousRotation() {
            const triangleContainer = document.querySelector('.triangle-container');
            const allIcons = document.querySelectorAll('.pet-icon');
            
            if (triangleContainer) {
                // debug removed
                
                // Kill any existing animations on these elements
                gsap.killTweensOf(triangleContainer);
                allIcons.forEach(icon => {
                    gsap.killTweensOf(icon);
                });
                
                // Create master timeline for better control
                const masterTimeline = gsap.timeline({ repeat: -1 });
                
                // Force reset all elements to clean state
                gsap.set(triangleContainer, { 
                    rotation: 0,
                    clearProps: "all",
                    transformOrigin: "center center"
                });
                
                allIcons.forEach((icon, index) => {
                    gsap.set(icon, { 
                        rotation: 0,
                        clearProps: "all",
                        transformOrigin: "center center"
                    });
                    
                    // debug removed
                });
                
                // Small delay before starting animations
                setTimeout(() => {
                    // Add triangle rotation to timeline
                    masterTimeline.to(triangleContainer, {
                        rotation: 360,
                        duration: 5, // Faster rotation: 5 seconds for full rotation
                        ease: "none",
                        transformOrigin: "center center"
                    }, 0); // Start at time 0
                    
                    // Add counter-rotation for each icon to the same timeline
                    allIcons.forEach((icon, index) => {
                        masterTimeline.to(icon, {
                            rotation: -360,
                            duration: 5, // Same duration as triangle rotation
                            ease: "none",
                            transformOrigin: "center center"
                        }, 0); // Also start at time 0 (parallel with triangle)
                        
                        // debug removed
                    });
                    
                    // Store timeline globally for debugging
                    window.rotationTimeline = masterTimeline;
                    
                    // debug removed
                }, 50);
            }
        }
        
        // Verify all elements exist
        Object.keys(svgElements).forEach(position => {
            if (svgElements[position]) {
                // debug removed
            }
        });
        
        // Set up all icons with proper initial state
        const petIcons = document.querySelectorAll('.pet-icon');
    // debug removed
        
        // Set initial transform properties for all icons
        petIcons.forEach((icon, index) => {
            // Clear any existing transforms and set initial state
            gsap.set(icon, { 
                rotation: 0,
                y: 0,
                x: 0,
                scale: 1,
                transform: "none",
                transformOrigin: "center center"
            });
            
            // debug removed
        });
        
        // Start continuous rotation FIRST
        startContinuousRotation();
        
        // THEN add floating animations with a delay to avoid conflicts
        setTimeout(() => {
            petIcons.forEach((icon, index) => {
                // Create individual floating animation for each icon with stagger
                // Only animate Y position to avoid rotation conflicts
                gsap.to(icon, {
                    y: '+=10',
                    duration: 3,
                    ease: "sine.inOut",
                    yoyo: true,
                    repeat: -1,
                    delay: index * 0.5, // Stagger the floating animations
                    // Make sure this doesn't override rotation
                    transformOrigin: "center center"
                });
                // debug removed
            });
        }, 200); // Wait for rotation to start
        
        // Start the random SVG cycling every 1.5 seconds (no triangle rotation here)
        const cyclingInterval = setInterval(() => {
            randomlyChangeSvg(); // Only change a random SVG
        }, 500);
        
        // Store interval ID globally so it can be cleared if needed
        window.svgCyclingInterval = cyclingInterval;
        
    // debug removed
    });
    
    // DEBUG: Function to monitor icon rotations
        function debugIconRotations() {
            const triangleContainer = document.querySelector('.triangle-container');
            const allIcons = document.querySelectorAll('.pet-icon');
            
            // debug removed
            
            if (triangleContainer) {
                const triangleStyle = window.getComputedStyle(triangleContainer);
                // debug removed
            }
            
            allIcons.forEach((icon, index) => {
                const iconStyle = window.getComputedStyle(icon);
                // debug removed
            });
            
            // debug removed
        }
        
        // Add debug function to window for console access
        window.debugRotations = debugIconRotations;
        
        // Auto-debug every 5 seconds for monitoring
        setInterval(debugIconRotations, 5000);
        
        // Debug controls for rotation
        window.rotationControls = {
            stop: () => {
                if (window.rotationTimeline) {
                    window.rotationTimeline.pause();
                    // debug removed
                }
            },
            start: () => {
                if (window.rotationTimeline) {
                    window.rotationTimeline.play();
                    // debug removed
                }
            },
            restart: () => {
                if (window.rotationTimeline) {
                    window.rotationTimeline.kill();
                }
                startContinuousRotation();
                // debug removed
            },
            reset: () => {
                const triangleContainer = document.querySelector('.triangle-container');
                const allIcons = document.querySelectorAll('.pet-icon');
                
                if (window.rotationTimeline) {
                    window.rotationTimeline.kill();
                }
                
                gsap.set(triangleContainer, { rotation: 0, clearProps: "all" });
                allIcons.forEach(icon => {
                    gsap.set(icon, { rotation: 0, clearProps: "all" });
                });
                
                // debug removed
            }
        };

// -------------------------
// Mobile email modal handlers
// -------------------------
// Mobile email modal init (runs immediately if DOM already loaded)
function initMobileEmailModal() {
    try {
        const openBtn = document.getElementById('mobile-open-email-modal');
        const modal = document.getElementById('mobile-email-modal');
        const modalClose = modal ? modal.querySelector('.mobile-email-modal-close') : null;
        const modalCancel = document.getElementById('mobile-email-cancel');
        const modalEmail = document.getElementById('modal-email');
        const modalConsent = document.getElementById('modal-send-results-email');
        const modalSubmit = document.getElementById('mobile-email-submit');
        const originalEmail = document.getElementById('result-email');
        const desktopConsent = document.getElementById('send-results-email');

        if (!modal) return;

    // Ensure modal is hidden by default (safety in case markup/CSS not applied yet)
    // Use unconditional assignment to avoid situations where CSS or prior scripts
    // may have toggled display before this init runs.
    try { modal.style.display = 'none'; modal.setAttribute('aria-hidden', 'true'); } catch(e) {}

        function openModal() {
            if (!modal) return;
            modal.style.display = 'block';
            // Allow next paint then remove aria-hidden
            requestAnimationFrame(() => {
                modal.setAttribute('aria-hidden', 'false');
                modal.classList.add('open');
                // prevent background scroll
                document.documentElement.style.overflow = 'hidden';
                // focus input after paint without causing browser to scroll unexpectedly.
                // Use preventScroll:true where supported and fall back to silent focus.
                setTimeout(() => {
                    if (!modalEmail) return;
                    try {
                        // prefer preventScroll to avoid jumping the viewport on open
                        modalEmail.focus({ preventScroll: true });
                    } catch (e) {
                        // If preventScroll not supported, call focus but try to restore scroll position immediately
                        const prevScroll = window.scrollY || document.documentElement.scrollTop || 0;
                        modalEmail.focus();
                        window.scrollTo(0, prevScroll);
                    }
                }, 50);
            });
        }

        function closeModal() {
            if (!modal) return;
            try {
                // If any element inside the modal has focus, move focus back to the opener before hiding
                if (document.activeElement && modal.contains(document.activeElement)) {
                    try { document.activeElement.blur(); } catch (e) { /* ignore */ }
                    if (openBtn) {
                        try { openBtn.focus(); } catch (e) { /* ignore */ }
                    } else {
                        // fallback focus to body
                        try { document.body.focus(); } catch (e) {}
                    }
                }
            } catch (e) {}

            modal.classList.remove('open');
            // hide from assistive tech only after focus moved
            modal.setAttribute('aria-hidden', 'true');
            // restore scroll
            document.documentElement.style.overflow = '';
            setTimeout(() => { try { modal.style.display = 'none'; } catch(e){} }, 220);
        }

        if (openBtn) {
            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }

        if (modalClose) modalClose.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });
        if (modalCancel) modalCancel.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

        // backdrop click closes
        modal.addEventListener('click', function(e){
            if (e.target === modal || e.target.classList.contains('mobile-email-modal-backdrop')) {
                closeModal();
            }
        });

        if (modalSubmit) {
            modalSubmit.addEventListener('click', function(e) {
                e.preventDefault();
                const email = modalEmail ? modalEmail.value.trim() : '';
                const consent = modalConsent ? modalConsent.checked : true;

                // Basic email validation: must be present and look like an email
                if (!email) {
                    showValidationErrors(['Correo electrónico']);
                    if (modalEmail) modalEmail.focus();
                    return;
                }
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!re.test(email)) {
                    showValidationErrors(['Correo electrónico (formato inválido)']);
                    if (modalEmail) modalEmail.focus();
                    return;
                }

                // Copy values to the original desktop fields (so the rest of the flow can use them)
                if (originalEmail) originalEmail.value = email;
                if (desktopConsent) desktopConsent.checked = !!consent;

                // Close modal and proceed
                closeModal();

                // Proceed with existing flow
                // Small delay to allow focusout and smoother restore to complete
                try {
                    setTimeout(function() {
                        try { unlockAndScrollToNext(); } catch (err) { console.warn('unlockAndScrollToNext not available', err); }
                    }, 150);
                } catch (err) {
                    console.warn('Failed to schedule unlockAndScrollToNext', err);
                }
            });
        }
    } catch (err) {
        console.warn('Mobile email modal init failed', err);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileEmailModal);
} else {
    // DOM already loaded — init immediately
    initMobileEmailModal();
}

// Prevent unwanted automatic scrolling to internal sections on page load (mobile)
// Some browsers or 3rd-party scripts may trigger a scroll during initialization.
// If there's no explicit hash in the URL, force the viewport to the top after load
// to avoid landing in the middle of the flow (e.g., section-4) unexpectedly.
window.addEventListener('load', function() {
    try {
        if (location.hash && location.hash.length > 1) return; // respect explicit anchors
        if (window.innerWidth > 600) return; // only apply on phones as reported

        // Small timeout to let other onload handlers finish
        setTimeout(() => {
            const smoother = (window.ScrollSmoother && typeof ScrollSmoother.get === 'function') ? ScrollSmoother.get() : null;
            if (smoother) {
                try {
                    // Use smoother.scrollTo if available (no animation)
                    smoother.scrollTo(0, false);
                    // ensure it's paused on mobile as intended
                    smoother.paused(true);
                } catch (e) {
                    // fallback to window scroll
                    window.scrollTo(0, 0);
                }
            } else {
                window.scrollTo(0, 0);
            }
        }, 120);
    } catch (e) {
        // swallow errors - not critical
    }
});

// -------------------------
// Plantscan mobile debug & soft-mitigation
// Opt-in debugging: enable by adding `?plantscanDebug=1` to the URL or
// by setting `localStorage.setItem('plantscan-debug', '1')` in the console.
// This block does two things:
// 1) Tracks focusin/focusout on form controls and optionally logs calls to
//    window.scrollTo / element.scrollIntoView / ScrollSmoother.scrollTo so
//    you can see what code runs immediately after focus (likely culprit).
// 2) As a temporary mitigation, while an input is focused it temporarily
//    replaces ScrollSmoother.scrollTo with a no-op to prevent programmatic
//    smooth-scrolling from stealing focus and dismissing the mobile keyboard.
//    This is intentionally conservative and restored shortly after blur.
(function(){
    const debug = /[?&]plantscanDebug=1/.test(location.search) || localStorage.getItem('plantscan-debug') === '1';

    // Public flag for other scripts/tests
    window.__ps_inputFocused = false;

    let origSmootherScrollTo = null;
    let smootherBlocked = false;

    function tryGetSmoother() {
        try { return (window.ScrollSmoother && typeof ScrollSmoother.get === 'function') ? ScrollSmoother.get() : null; } catch (e) { return null; }
    }

    function blockSmootherScrollTo() {
        try {
            const s = tryGetSmoother();
            if (!s || smootherBlocked) return;
            if (typeof s.scrollTo === 'function') {
                origSmootherScrollTo = s.scrollTo.bind(s);
                s.scrollTo = function() { if (debug) console.log('[PS DEBUG] blocked ScrollSmoother.scrollTo while input focused', arguments); };
                s.__ps_blocked = true;
                smootherBlocked = true;
                if (debug) console.log('[PS DEBUG] ScrollSmoother.scrollTo blocked');
            }
        } catch (e) { if (debug) console.warn('[PS DEBUG] blockSmootherScrollTo failed', e); }
    }

    function restoreSmootherScrollTo() {
        try {
            const s = tryGetSmoother();
            if (!s || !s.__ps_blocked) return;
            if (origSmootherScrollTo) {
                s.scrollTo = origSmootherScrollTo;
            }
            s.__ps_blocked = false;
            origSmootherScrollTo = null;
            smootherBlocked = false;
            if (debug) console.log('[PS DEBUG] ScrollSmoother.scrollTo restored');
        } catch (e) { if (debug) console.warn('[PS DEBUG] restoreSmootherScrollTo failed', e); }
    }

    // Track focus state and apply temporary mitigation
    document.addEventListener('focusin', function(e) {
        try {
            const t = e.target;
            if (!t) return;
            const tag = (t.tagName || '').toUpperCase();
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || t.isContentEditable) {
                window.__ps_inputFocused = true;
                if (debug) console.log('[PS DEBUG] focusin on', t, { tag });
                // Block smoother scroll actions while user is interacting
                blockSmootherScrollTo();
            }
        } catch (err) { if (debug) console.warn('[PS DEBUG] focusin handler error', err); }
    }, true);

    document.addEventListener('focusout', function(e) {
        try {
            const t = e.target;
            if (!t) return;
            const tag = (t.tagName || '').toUpperCase();
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || t.isContentEditable) {
                // Delay restore slightly to avoid race with immediate programmatic scrolls
                window.__ps_inputFocused = false;
                if (debug) console.log('[PS DEBUG] focusout on', t, { tag });
                setTimeout(restoreSmootherScrollTo, 50);
            }
        } catch (err) { if (debug) console.warn('[PS DEBUG] focusout handler error', err); }
    }, true);

    if (debug) {
        // Lightweight logging wrappers to capture calls that can blur inputs
        try {
            const origWindowScrollTo = window.scrollTo.bind(window);
            window.scrollTo = function() {
                console.log('[PS DEBUG] window.scrollTo called', arguments, new Error().stack);
                return origWindowScrollTo.apply(this, arguments);
            };
        } catch (e) { console.warn('[PS DEBUG] failed to wrap window.scrollTo', e); }

        try {
            const origScrollIntoView = Element.prototype.scrollIntoView;
            Element.prototype.scrollIntoView = function() {
                console.log('[PS DEBUG] element.scrollIntoView called', this, arguments, new Error().stack);
                return origScrollIntoView.apply(this, arguments);
            };
        } catch (e) { console.warn('[PS DEBUG] failed to wrap Element.prototype.scrollIntoView', e); }

        // Attempt to wrap ScrollSmoother.scrollTo to log calls too
        function wrapSmootherLogger() {
            try {
                const s = tryGetSmoother();
                if (s && typeof s.scrollTo === 'function' && !s.__ps_logged) {
                    const original = s.scrollTo.bind(s);
                    s.scrollTo = function() { console.log('[PS DEBUG] ScrollSmoother.scrollTo called', arguments, new Error().stack); return original.apply(this, arguments); };
                    s.__ps_logged = true;
                }
            } catch (e) { console.warn('[PS DEBUG] wrapSmootherLogger failed', e); }
        }

        window.addEventListener('load', wrapSmootherLogger);
        setTimeout(wrapSmootherLogger, 800);

        console.log('[PS DEBUG] plantscan debug enabled — use ?plantscanDebug=1 or localStorage.plantscan-debug=1');
    }
})();