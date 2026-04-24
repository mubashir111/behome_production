'use client';

import { useState, useEffect } from 'react';
import ProductGrid from './ProductGrid';

const KEY = 'behom_recently_viewed';
const MAX = 6;

export interface RecentlyViewedProduct {
    id: number;
    name: string;
    slug: string;
    cover?: string;
    currency_price: string;
    discounted_price?: string;
    is_offer: boolean;
    category?: { name: string };
    wishlist?: boolean;
}

/** Call this on any product page once the product data is loaded */
export function recordRecentlyViewed(product: RecentlyViewedProduct) {
    try {
        const stored: RecentlyViewedProduct[] = JSON.parse(localStorage.getItem(KEY) || '[]');
        const updated = [product, ...stored.filter(p => p.slug !== product.slug)].slice(0, MAX);
        localStorage.setItem(KEY, JSON.stringify(updated));
    } catch {}
}

interface RecentlyViewedProps {
    currentSlug: string;
}

export default function RecentlyViewed({ currentSlug }: RecentlyViewedProps) {
    const [products, setProducts] = useState<RecentlyViewedProduct[]>([]);

    useEffect(() => {
        try {
            const stored: RecentlyViewedProduct[] = JSON.parse(localStorage.getItem(KEY) || '[]');
            const others = stored.filter(p => p.slug !== currentSlug);
            setProducts(others);
        } catch {}
    }, [currentSlug]);

    if (!products.length) return null;

    return (
        <section className="page-shell page-shell-tight pt-0">
            <div className="container">
                <div className="row justify-content-center mb-25px">
                    <div className="col-lg-5 text-center">
                        <span className="text-uppercase fs-13 ls-2px fw-600 opacity-6">Your browsing history</span>
                        <h4 className="alt-font text-white fw-700 mt-5px mb-0">Recently Viewed</h4>
                    </div>
                </div>
                <ProductGrid products={products} showCategory />
            </div>
        </section>
    );
}
