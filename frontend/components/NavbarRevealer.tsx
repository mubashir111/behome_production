'use client';

import { useEffect } from 'react';

/**
 * NavbarRevealer
 * 
 * Hides all elements with the .navbar class by default (via CSS in layout.tsx)
 * and reveals them once the window 'load' event fires, ensuring styles
 * are fully applied before displaying the navbar.
 */
export default function NavbarRevealer() {
  useEffect(() => {
    const reveal = () => {
      document.querySelectorAll('.navbar').forEach(el => {
        el.classList.add('style-loaded');
      });
    };

    // If the document is already loaded, reveal immediately
    if (document.readyState === 'complete') {
      reveal();
    } else {
      // Otherwise wait for the load event
      window.addEventListener('load', reveal);
      
      // Safety timeout in case some resource (like a large image) is hanging
      const timer = setTimeout(reveal, 3000);
      
      return () => {
        window.removeEventListener('load', reveal);
        clearTimeout(timer);
      };
    }
  }, []);

  return null;
}
