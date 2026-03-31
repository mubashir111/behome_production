import type { Metadata } from 'next';
import ProductPageClient from './ProductPageClient';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
const API_KEY  = process.env.NEXT_PUBLIC_API_KEY  || '';

export async function generateMetadata(
    { params }: { params: { slug: string } }
): Promise<Metadata> {
    try {
        const res = await fetch(`${API_URL}/v1/products/${params.slug}`, {
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
                type: 'website',
                ...(image && { images: [{ url: image, width: 800, height: 900 }] }),
            },
            twitter: {
                card: 'summary_large_image',
                title,
                description,
                ...(image && { images: [image] }),
            },
        };
    } catch {
        return { title: 'Product' };
    }
}

export default function ProductPage({ params }: { params: { slug: string } }) {
    return <ProductPageClient params={params} />;
}
