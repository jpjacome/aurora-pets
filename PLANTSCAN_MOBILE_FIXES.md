# PlantScan — Mobile-only CSS fixes (avoid affecting desktop)

This document lists the changes we applied during debugging, explains which rules caused the desktop scrolling regressions, and gives a safe, step-by-step plan and exact CSS snippets to re-apply mobile fixes without touching desktop.

## Summary of changes made earlier (that helped mobile)
- Forced email placeholder color to black using placeholder vendor-prefixed selectors.
- Hid two specific `.sub-label` paragraphs on phones using `.hide-on-mobile` and a mobile media query.
- Hid the bullet navbar on mobile by disabling pointer events in a mobile media query.
- Changed mobile-selected chip text color to `var(--color-1)`.
- Introduced mobile-only selects and `.mobile-only` UI wrappers (display toggles in `@media (max-width: 700px)`).
- Used dynamic viewport units via a `--vh` CSS variable set by `public/js/prevention.js` to address mobile address-bar issues.

These fixes are fine for mobile, but some rules (notably unconditional `height:100vh` / `100dvh` usage and forced element heights/overflow) ended up being applied on desktop and caused ScrollTrigger/ScrollSmoother mis-measurements and an effective page "lock" at `section-4`.

## Root causes (why desktop scrolling broke)
1. `.wrapper { height: 100vh; height: 100dvh; height: calc(var(--vh,1vh) * 100); }` applied unconditionally. Forcing exact viewport heights on desktop can prevent normal document flow and confuse scroll measurement code.
2. `background-wrapper` or other fixed-position elements with large sizes can intercept pointer events or change stacking contexts.
3. Many inner elements use `overflow: hidden` and fixed heights (e.g., `.inner-container { overflow: hidden; height: 80%; }`). When combined with #1, this leads to content being clipped or the scroll system thinking the page is shorter.
4. CSS relying on `--vh` which is set by JS — if measurements are taken before the JS runs (or run in a different order in the fresh environment), ScrollTrigger may create pins with wrong start/end positions.

## High-level strategy (safe approach)
- Revert blade and CSS to the 'before' baseline.
- Re-apply only the mobile-specific rules inside mobile media queries (max-width: 600px or 700px depending on the rule). Avoid unconditional `height: 100vh` / `100dvh` on desktop.
- Prefer `min-height: 100vh` or `height:auto` on desktop to allow natural page flow and correct GSAP/ScrollTrigger measurements.
- If CSS depending on `--vh` is necessary, scope it to mobile only and ensure `setVhProperty()` in `prevention.js` runs early (it already runs on load and on resize/orientationchange).

## Exact CSS snippets to add (recommended placement)
Add these near the end of `public/css/prevention-style.css` (or in a new file loaded after the main CSS) so they override previous rules. Use the mobile block first, then explicit desktop-safe rules.

### 1) Mobile-only dvh/100vh behavior (phones)
```css
/* Mobile-only: keep dvh/100vh behavior for phones (<=600px) */
@media (max-width: 600px) {
  html, body, #smooth-wrapper, #smooth-content {
    height: auto !important;
    min-height: calc(var(--vh, 1vh) * 100) !important; /* use --vh on mobile */
    overflow: visible !important;
  }

  .wrapper {
    width: 100vw;
    height: calc(var(--vh, 1vh) * 100) !important; /* full viewport height on phones only */
    min-height: calc(var(--vh, 1vh) * 100) !important;
  }

  .background-wrapper {
    height: calc(var(--vh, 1vh) * 100) !important;
  }
}
```

### 2) Desktop-safe fallbacks (prevents forcing exact heights on desktop)
```css
/* Desktop: avoid forcing exact viewport heights so ScrollTrigger computes correctly */
@media (min-width: 601px) {
  html, body, #smooth-wrapper, #smooth-content {
    height: auto !important;
    overflow: visible !important;
  }
  .wrapper {
    height: auto !important;
    min-height: 100vh; /* allow natural page height while ensuring at least viewport tall */
  }
  .background-wrapper {
    pointer-events: none; /* ensure fixed background doesn't intercept input */
  }
}
```

### 3) Mobile-only UI rules (examples we previously added)
```css
@media (max-width: 600px) {
  /* Hide the two targeted sub-labels on phones */
  #section-3 .pet-details-form .form-group.full-width > .sub-label,
  #section-4 .environment-form .form-group.full-width > .sub-label {
    display: none !important;
  }

  /* Keep bullet navbar non-interactive on phones */
  .bullet-navbar { pointer-events: none; }
  .bullet-navbar .bullet, .bullet-navbar a { pointer-events: none; cursor: default; }

  /* Ensure mobile email placeholder remains black */
  .mobile-email-input::placeholder { color: #000000; opacity: 1; }
}
```

## Diagnostics to run (copy/paste into DevTools console)
1) Inspect ScrollTrigger pins and triggers:
```js
if (window.ScrollTrigger) {
  console.log('ScrollTrigger triggers:', ScrollTrigger.getAll().map(t => ({id: t.trigger?.id, pin: !!t.pin, start: t.start, end: t.end})));
} else console.log('ScrollTrigger not present');
```

2) Temporary CSS override test (reversible):
```js
(function(){
  const s=document.createElement('style');
  s.id='tmp-scroll-test';
  s.textContent=`html,body,#smooth-wrapper,#smooth-content{height:auto !important;overflow:visible !important;} .wrapper{height:auto !important;min-height:100vh !important;} .background-wrapper{pointer-events:none !important;}`;
  document.head.appendChild(s);
  console.log('Temporary CSS override applied (id=tmp-scroll-test). To remove: document.getElementById("tmp-scroll-test").remove();');
})();
```
If this restores desktop scrolling, it's a CSS issue and the snippets above will fix it.

3) Check computed heights and `--vh`:
```js
const w=document.querySelector('.wrapper');
const s4=document.getElementById('section-4');
console.log('wrapper computed height:', getComputedStyle(w).height, 'box', w.getBoundingClientRect());
console.log('section-4 computed height:', getComputedStyle(s4).height, 'box', s4.getBoundingClientRect());
console.log('--vh var:', getComputedStyle(document.documentElement).getPropertyValue('--vh'));
```

## How to apply (step-by-step)
1. Revert to the previously working versions of `resources/views/plantscan.blade.php` and `public/css/prevention-style.css` (you indicated you'll do this). Keep a backup of your current modified files.
2. Add the CSS blocks above at the end of `public/css/prevention-style.css`, or create a new file `public/css/prevention-mobile-overrides.css` and include it after `prevention-style.css` in the Blade `@push('styles')` so the rules override earlier definitions.
3. Clear browser cache or open an Incognito window and load the page on desktop. Confirm you can scroll past `section-4`.
4. Test mobile (real device or device emulation). Confirm the mobile behaviors (dvh-based sizing, hidden sublabels, black placeholders) remain correct.
5. If any mismatch occurs, run the diagnostics above and paste the console output for further analysis.

## Notes and cautions
- Keep `--vh` code in `public/js/prevention.js` — it’s useful for mobile. But ensure any CSS that depends on `--vh` is wrapped in mobile-only media queries.
- Prefer `min-height:100vh` on desktop rather than forcing `height:100vh`.
- Avoid using `!important` unless necessary — we used it in mobile overrides to guarantee behavior but try to keep it minimal.

## If you want me to apply the changes
Reply with `apply CSS patch` and I will add the minimal override file or modify `public/css/prevention-style.css` and save the changes. I will keep the change small and desktop-safe, and report back with the exact file edit.

---

Last updated: October 28, 2025
