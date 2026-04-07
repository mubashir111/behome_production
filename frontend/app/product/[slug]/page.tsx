import type { Metadata } from 'next';
import ProductPageClient from './ProductPageClient';
import { SERVER_API_URL, API_KEY, SITE_URL } from '@/lib/config';
import { constructMetadata } from '@/lib/metadata';


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
        const image       = p.cover ?? '/images/og-default.png';

        return constructMetadata({
            title,
            description,
            image,
        });
    } catch {
        return constructMetadata({ title: 'Product' });
    }
}

export default function ProductPage({ params }: { params: { slug: string } }) {
    return <ProductPageClient params={params} />;
}
