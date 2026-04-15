'use client';

import { useEffect, useState } from 'react';

const KEY = 'behome_recently_viewed';
const MAX = 6;

export function useRecentlyViewed(currentSlug?: string) {
    const [otherSlugs, setOtherSlugs] = useState<string[]>([]);

    useEffect(() => {
        if (typeof window === 'undefined') return;

        try {
            const stored: string[] = JSON.parse(localStorage.getItem(KEY) || '[]');

            // Record current page view
            if (currentSlug) {
                const updated = [currentSlug, ...stored.filter(s => s !== currentSlug)].slice(0, MAX);
                localStorage.setItem(KEY, JSON.stringify(updated));
                setOtherSlugs(updated.filter(s => s !== currentSlug));
            } else {
                setOtherSlugs(stored);
            }
        } catch {
            setOtherSlugs([]);
        }
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [currentSlug]);

    return { otherSlugs };
}
