// home2-gsap.js - GSAP letter animation for #page-1 h1
(function(){
  'use strict';

  // Wrap text nodes in spans but preserve existing element structure
  function wrapTextNodes(node) {
    const children = Array.from(node.childNodes);
    children.forEach(child => {
      if (child.nodeType === Node.TEXT_NODE) {
        const text = child.textContent || '';
        // If text node is empty, skip
        if (text.length === 0) return;
        const frag = document.createDocumentFragment();
        for (let i = 0; i < text.length; i++) {
          const ch = text[i];
          const span = document.createElement('span');
          span.className = 'gsap-letter';
          // keep exact character (including spaces) so spacing remains
          span.textContent = ch;
          frag.appendChild(span);
        }
        child.parentNode.replaceChild(frag, child);
      } else if (child.nodeType === Node.ELEMENT_NODE) {
        wrapTextNodes(child);
      }
    });
  }

  function init() {
    if (!window.gsap) return;
    try { if (window.TextPlugin) gsap.registerPlugin(TextPlugin); } catch(e){}
    const h = document.querySelector('#page-1 h1');
    if (!h) return;

    wrapTextNodes(h);

    const letters = Array.from(document.querySelectorAll('#page-1 .gsap-letter'));
    if (!letters.length) return;

    gsap.set(letters, {display: 'inline-block', opacity: 0, y: 56});
    gsap.to(letters, {
      opacity: 1,
      y: 1,
      duration: 1.55,
      stagger: 0.1,
      ease: 'power3.out',
      // slight random skew for liveliness
      onComplete: function(){ /* noop */ }
    });
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(init, 10);
  } else {
    document.addEventListener('DOMContentLoaded', init);
  }
})();
