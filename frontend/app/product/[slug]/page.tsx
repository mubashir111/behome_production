import type { Metadata } from 'next';
import ProductPageClient from './ProductPageClient';
import { SERVER_API_URL, API_KEY, SITE_URL } from '@/lib/config';


export async function generateMetadata(
    { params }: { params: { slug: string } }
): Promise<Metadata> {
    try {
        const res = await fetch(`${SERVER_API_URL}/v1/products/${params.slug}`, {
            headers: { 'x-api-key': API_KEY, Accept: 'application/json' },
            next: { revalidate: 60 },
        });
        if (!res.ok) throw new Error();
        const json = await res.json();
        const p    = json?.data;
        if (!p) throw new Error();

        const title       = p.name ?? 'Product';
        const description = (p.details ?? p.description ?? '').replace(/<[^>]*>/g, '').slice(0, 160);
        const image       = p.cover ?? undefined;

        return {
            title,
            description,
            openGraph: {
                title,
                description,
                type: 'article',
                url: `${SITE_URL}/product/${params.slug}`,
                images: image ? [{ url: image, width: 800, height: 900 }] : [{ url: '/images/og-default.png', width: 1200, height: 630 }],
            },
            twitter: {
                card: 'summary_large_image',
                title,
                description,
                images: image ? [image] : ['/images/og-default.png'],
            },
        };
    } catch {
        return { title: 'Product' };
    }
}

export default function ProductPage({ params }: { params: { slug: string } }) {
    return <ProductPageClient params={params} />;
}
