'use client';

import { useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';

/**
 * PageReinit
 *
 * After every client-side <Link> navigation:
 *  1. Scrolls back to the top of the page
 *  2. Re-initialises Swiper sliders and anime.js via window.craftoReinit()
 *     (exposed by main.js) so the home page looks correct when reached
 *     via a breadcrumb or nav link instead of a full browser reload.
 */
export default function PageReinit() {
  const pathname = usePathname();
  const isFirst = useRef(true);

  useEffect(() => {
    // Skip the very first render — main.js already ran on initial load.
    if (isFirst.current) {
      isFirst.current = false;
      return;
    }

    // Scroll to top immediately on every client-side navigation.
    window.scrollTo({ top: 0, behavior: 'instant' });

    // Wait for React to finish painting the new page DOM, then reinit scripts.
    // Two rAF frames ensure we're past React's commit + browser paint.
    const raf1 = requestAnimationFrame(() => {
      const raf2 = requestAnimationFrame(() => {
        const timer = setTimeout(() => {
          if (typeof (window as any).craftoReinit === 'function') {
            (window as any).craftoReinit();
          }
        }, 300);
        return () => clearTimeout(timer);
      });
      return () => cancelAnimationFrame(raf2);
    });

    return () => cancelAnimationFrame(raf1);
  }, [pathname]);

  return null;
}
