// home2-gsap.js - GSAP letter animation for #page-1 h1
(function(){
  'use strict';

  // Wrap text nodes in spans but preserve existing element structure
  // Words are wrapped in word containers to prevent mid-word line breaks
  function wrapTextNodes(node) {
    const children = Array.from(node.childNodes);
    children.forEach(child => {
      if (child.nodeType === Node.TEXT_NODE) {
        const text = child.textContent || '';
        // If text node is empty, skip
        if (text.length === 0) return;
        const frag = document.createDocumentFragment();
        
        // Split by spaces to keep words together
        const words = text.split(' ');
        words.forEach((word, wordIndex) => {
          if (word.length > 0) {
            // Create a word wrapper span
            const wordSpan = document.createElement('span');
            wordSpan.className = 'gsap-word';
            wordSpan.style.display = 'inline-block';
            wordSpan.style.whiteSpace = 'nowrap';
            
            // Wrap each letter in the word
            for (let i = 0; i < word.length; i++) {
              const ch = word[i];
              const span = document.createElement('span');
              span.className = 'gsap-letter';
              span.textContent = ch;
              wordSpan.appendChild(span);
            }
            frag.appendChild(wordSpan);
          }
          
          // Add space after word (except last word)
          if (wordIndex < words.length - 1) {
            const spaceSpan = document.createElement('span');
            spaceSpan.className = 'gsap-letter gsap-space';
            spaceSpan.textContent = ' ';
            frag.appendChild(spaceSpan);
          }
        });
        
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
