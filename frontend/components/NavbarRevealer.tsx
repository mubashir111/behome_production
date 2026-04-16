'use client';

import { useEffect } from 'react';
import { useToast } from '@/components/ToastProvider';

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
  const { showToast } = useToast();

  // Show toast when session expires (redirected with ?session=expired)
  useEffect(() => {
    if (typeof window !== 'undefined' && window.location.search.includes('session=expired')) {
      showToast('Your session has expired. Please log in again.', 'error');
      // Clean the param from URL without reloading
      const url = new URL(window.location.href);
      url.searchParams.delete('session');
      window.history.replaceState({}, '', url.toString());
    }
  }, [showToast]);

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
