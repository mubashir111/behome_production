'use client';

import { useEffect } from 'react';
import { useToast } from '@/components/ToastProvider';

export default function NavbarRevealer() {
  const { showToast } = useToast();

  // Show toast when session expires
  useEffect(() => {
    if (typeof window !== 'undefined' && window.location.search.includes('session=expired')) {
      showToast('Your session has expired. Please log in again.', 'error');
      const url = new URL(window.location.href);
      url.searchParams.delete('session');
      window.history.replaceState({}, '', url.toString());
    }
  }, [showToast]);

  // Reveal navbar after hydration (removes FOUC from main.js repositioning)
  useEffect(() => {
    const reveal = () => {
      document.querySelectorAll('.navbar').forEach(el => el.classList.add('style-loaded'));
    };
    const run = () => requestAnimationFrame(() => requestAnimationFrame(reveal));
    const timer = setTimeout(reveal, 800);
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', run, { once: true });
    } else {
      run();
    }
    return () => clearTimeout(timer);
  }, []);

  // Scroll-driven glass effect — replaces main.js .sticky logic (disabled by no-sticky class)
  useEffect(() => {
    const header = document.querySelector('header.header-with-topbar') as HTMLElement | null;
    if (!header) return;

    const THRESHOLD = 60; // px scrolled before glass kicks in

    const onScroll = () => {
      if (window.scrollY > THRESHOLD) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // run once on mount in case page loads mid-scroll
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  return null;
}
