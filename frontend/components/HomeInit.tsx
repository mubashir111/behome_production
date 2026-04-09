'use client';

import { useEffect } from 'react';

export default function HomeInit() {
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'instant' });

    // Poll until craftoReinit is available (main.js may still be loading)
    let attempts = 0;
    const tryReinit = () => {
      if (typeof (window as any).craftoReinit === 'function') {
        (window as any).craftoReinit();
      } else if (attempts < 15) {
        attempts++;
        setTimeout(tryReinit, 200);
      }
    };

    const timer = setTimeout(tryReinit, 100);
    return () => clearTimeout(timer);
  }, []);

  return null;
}
