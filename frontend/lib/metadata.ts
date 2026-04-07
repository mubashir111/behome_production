import { Metadata } from 'next';
import { SITE_URL } from './config';

export function constructMetadata({
    title = 'Behome - Premium Architectural Decor & Luxury Furniture',
    description = 'Behome - Premium E-commerce experience for architectural decor, luxury furniture, and high-end interior design.',
    image = '/images/og-default.png',
    icons = '/images/favicon.png',
    noIndex = false,
}: {
    title?: string;
    description?: string;
    image?: string;
    icons?: string;
    noIndex?: boolean;
} = {}): Metadata {
    return {
        title: {
            default: title,
            template: '%s | Behome',
        },
        description,
        openGraph: {
            title,
            description,
            siteName: 'Behome',
            images: [
                {
                    url: image,
                    width: 1200,
                    height: 630,
                },
            ],
            type: 'website',
        },
        twitter: {
            card: 'summary_large_image',
            title,
            description,
            images: [image],
        },
        icons,
        metadataBase: new URL(SITE_URL),
        ...(noIndex && {
            robots: {
                index: false,
                follow: false,
            },
        }),
    };
}
