import type { Metadata } from 'next';
import ShopClient from './ShopClient';

export const metadata: Metadata = {
    title: 'Shop Premium Architectural Decor & Furniture | Behom',
    description: 'Explore our curated collection of luxury furniture, architectural decor, and high-end interior design pieces. Find the perfect addition to your home.',
    openGraph: {
        title: 'Shop Premium Architectural Decor & Furniture | Behom',
        description: 'Explore our curated collection of luxury furniture, architectural decor, and high-end interior design pieces.',
        type: 'website',
        images: [{ url: '/images/og-default.png', width: 1200, height: 630 }],
    },
    twitter: {
        card: 'summary_large_image',
        title: 'Shop Premium Architectural Decor & Furniture | Behom',
        description: 'Explore our curated collection of luxury furniture, architectural decor, and high-end interior design pieces.',
        images: ['/images/og-default.png'],
    },
};

export default function ShopPage() {
    return <ShopClient />;
}
