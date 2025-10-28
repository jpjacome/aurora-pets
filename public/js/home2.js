// home2.js - Frame-based forward-only scrubber
(function(){
	'use strict';

	const FRAME_IMG_ID = 'bg-frame';
	const FRAMES = (window.HOME2_FRAMES && Array.isArray(window.HOME2_FRAMES)) ? window.HOME2_FRAMES.slice() : [];
	const SECTIONS_SELECTOR = '.section';
	const PRELOAD_AHEAD = 3; // number of frames to preload ahead of current


	const img = document.getElementById(FRAME_IMG_ID);
	if (!img) {
		console.warn('Frame image element not found:', FRAME_IMG_ID);
		return;
	}

	const sections = document.querySelectorAll(SECTIONS_SELECTOR);
	if (!sections || sections.length === 0) {
		console.warn('No sections found with selector', SECTIONS_SELECTOR);
		return;
	}

		// helper preloader - always stores/returns a Promise
		const cache = new Map();
		function preload(src) {
			if (cache.has(src)) return cache.get(src);
			const p = new Promise((resolve, reject) => {
				const i = new Image();
				i.onload = () => { resolve(i); };
				i.onerror = () => reject(new Error('Failed to load ' + src));
				i.src = src;
			});
			cache.set(src, p);
			return p;
		}

	// Progressive preload: start with first few frames
	for (let i=0;i<Math.min(PRELOAD_AHEAD+1, FRAMES.length); i++) preload(FRAMES[i]).catch(()=>{});

	// track last frame index shown; start at 0
	let lastShownIndex = 0;
	img.src = FRAMES[0];

	function clamp(v, a, b){ return Math.max(a, Math.min(b, v)); }

	// Compute scroll mapping: from top of sections container (start) to bottom of section 3 (end)
	function computeScrollRange(){
		const wrapper = document.querySelector('.sections');
		if(!wrapper) return {start:0, end: document.documentElement.scrollHeight - window.innerHeight };

		const start = wrapper.getBoundingClientRect().top + window.pageYOffset; // top of wrapper
		// find bottom of third section (or last if less than 3)
		const targetSection = sections[Math.min(2, sections.length-1)];
		const end = targetSection.getBoundingClientRect().bottom + window.pageYOffset - window.innerHeight;
		return { start, end };
	}

	let range = computeScrollRange();
	window.addEventListener('resize', () => { range = computeScrollRange(); });

	function onScroll(){
		const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
		const t = clamp((scrollY - range.start) / (Math.max(1, range.end - range.start)), 0, 1);
		const idx = Math.floor(t * (FRAMES.length - 1));

			// Update when index changes (allow forward and backward)
			if (idx !== lastShownIndex) {
				lastShownIndex = idx;
				const src = FRAMES[idx];
				img.src = src;

				// preload neighbors both behind and ahead
				for (let i=1;i<=PRELOAD_AHEAD;i++){
					const ahead = FRAMES[idx + i];
					const behind = FRAMES[idx - i];
					if (ahead) preload(ahead).catch(()=>{});
					if (behind) preload(behind).catch(()=>{});
				}
			}
	}

	// Throttle with requestAnimationFrame
	let ticking = false;
	function rafHandler(){
		if (!ticking){
			ticking = true;
			requestAnimationFrame(()=>{ onScroll(); ticking = false; });
		}
	}

	window.addEventListener('scroll', rafHandler, { passive: true });
	// run initially in case page loaded scrolled
	range = computeScrollRange();
	onScroll();

})();

	// Scroll-driven video zoom for .page-bg-video inside .wrapper-2
	(function(){
		'use strict';

		// Respect user's reduced motion preference
		const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (prefersReduced) return;

		const VIDEO_SELECTOR = '.page-bg-video';
		const WRAPPER_SELECTOR = '.wrapper-2';
		const THRESHOLD = 0.15; // fraction of viewport used as threshold to start/end
		const MIN_SCALE = 1.0;
		const MAX_SCALE = 1.7; // adjust to taste

		const video = document.querySelector(VIDEO_SELECTOR);
		const wrapper = document.querySelector(WRAPPER_SELECTOR);
		if (!video || !wrapper) return;

		// ensure transform origin and GPU acceleration
		try {
			video.style.transformOrigin = 'center center';
			video.style.willChange = 'transform';
		} catch (e) {}

		// compute start/end Y positions for the zoom mapping
		function computeRange() {
			const rect = wrapper.getBoundingClientRect();
			const docTop = window.pageYOffset || document.documentElement.scrollTop || 0;
			const wrapperTop = rect.top + docTop;
			const wrapperHeight = wrapper.offsetHeight || rect.height || 0;

			// Start when the wrapper's top is THRESHOLD into the viewport (slightly visible)
			const start = wrapperTop - window.innerHeight * (1 - THRESHOLD);
			// End when wrapper bottom has passed THRESHOLD of the viewport
			const end = wrapperTop + wrapperHeight - window.innerHeight * THRESHOLD;
			// avoid zero-length ranges
			return { start, end: Math.max(start + 1, end) };
		}

		let range = computeRange();
		window.addEventListener('resize', () => { range = computeRange(); });

		// apply transform based on scroll progress (0..1)
		function applyZoomForScroll() {
			const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
			const raw = (scrollY - range.start) / (range.end - range.start);
			const t = Math.max(0, Math.min(1, raw));
			const scale = MIN_SCALE + t * (MAX_SCALE - MIN_SCALE);
			video.style.transform = `scale(${scale})`;
		}

		// rAF-throttled handler
		let ticking = false;
		function onScrollRaf() {
			if (!ticking) {
				ticking = true;
				requestAnimationFrame(() => { applyZoomForScroll(); ticking = false; });
			}
		}

		// Use IntersectionObserver to start attaching scroll listener only when wrapper is relevant
		const io = new IntersectionObserver((entries) => {
			const e = entries[0];
			if (!e) return;
			// update computed range whenever intersection changes
			range = computeRange();

			if (e.isIntersecting) {
				// ensure initial correct value
				applyZoomForScroll();
				window.addEventListener('scroll', onScrollRaf, { passive: true });
			} else {
				// still set final clipped state when leaving viewport
				applyZoomForScroll();
				// keep listener attached so scrolling back rewinds zoom smoothly
			}
		}, {
			root: null,
			rootMargin: '0px',
			threshold: THRESHOLD
		});

		io.observe(wrapper);

		// Run once on load in case page is already scrolled
		range = computeRange();
		applyZoomForScroll();

	})();
