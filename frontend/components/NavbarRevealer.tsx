'use client';

import { useEffect } from 'react';

/**
 * NavbarRevealer
 *
 * Keeps the navbar invisible until main.js has had a chance to apply its
 * sticky/fixed positioning, then fades it in — eliminating the 1-frame jump
 * where the navbar renders in the wrong position before JS repositions it.
 *
 * Strategy: wait for DOMContentLoaded (scripts parsed) + two rAF ticks
 * (enough for main.js synchronous init to complete), then reveal.
 * Hard cap at 800 ms so it never stays hidden on slow connections.
 */
export default function NavbarRevealer() {
  useEffect(() => {
    const reveal = () => {
      document.querySelectorAll('.navbar').forEach(el => {
        el.classList.add('style-loaded');
      });
    };

    const run = () => {
      // Two rAF ticks: first gives main.js DOM mutations time to flush,
      // second lets the browser repaint with those mutations applied.
      requestAnimationFrame(() => requestAnimationFrame(reveal));
    };

    const timer = setTimeout(reveal, 800); // hard safety cap

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', run, { once: true });
    } else {
      run();
    }

    return () => clearTimeout(timer);
  }, []);

  return null;
}
