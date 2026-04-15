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

export default async function ProductPage({ params }: { params: { slug: string } }) {
    let jsonLd: object | null = null;
    try {
        const res = await fetch(`${SERVER_API_URL}/v1/products/${params.slug}`, {
            headers: { 'x-api-key': API_KEY, Accept: 'application/json' },
            next: { revalidate: 60 },
        });
        if (res.ok) {
            const json = await res.json();
            const p = json?.data;
            if (p) {
                const price    = p.sale_price ?? p.price ?? 0;
                const currency = p.currency_code ?? 'AED';
                const inStock  = (p.stock ?? 0) > 0;
                jsonLd = {
                    '@context': 'https://schema.org/',
                    '@type': 'Product',
                    name: p.name,
                    description: (p.details ?? p.description ?? '').replace(/<[^>]*>/g, '').slice(0, 5000),
                    image: p.cover ? [p.cover] : undefined,
                    sku: p.sku ?? undefined,
                    brand: p.brand ? { '@type': 'Brand', name: p.brand } : undefined,
                    offers: {
                        '@type': 'Offer',
                        url: `${SITE_URL}/product/${params.slug}`,
                        priceCurrency: currency,
                        price: parseFloat(price),
                        availability: inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                        itemCondition: 'https://schema.org/NewCondition',
                    },
                };
            }
        }
    } catch { /* non-critical — skip */ }

    return (
        <>
            {jsonLd && (
                <script
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
                />
            )}
            <ProductPageClient params={params} />
        </>
    );
}
