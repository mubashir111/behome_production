import type { MetadataRoute } from 'next';
import { SERVER_API_URL, API_KEY, SITE_URL } from '@/lib/config';

const headers = { 'x-api-key': API_KEY };

async function fetchProductSlugs(): Promise<string[]> {
    try {
        const res = await fetch(`${SERVER_API_URL}/v1/products?per_page=500&fields=slug`, { headers, next: { revalidate: 3600 } });
        const json = await res.json();
        return (json?.data ?? []).map((p: any) => p.slug).filter(Boolean);
    } catch {
        return [];
    }
}

async function fetchBlogSlugs(): Promise<string[]> {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/blog-posts?per_page=500`, { headers, next: { revalidate: 3600 } });
        const json = await res.json();
        return (json?.data ?? []).map((p: any) => p.slug).filter(Boolean);
    } catch {
        return [];
    }
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
    const [productSlugs, blogSlugs] = await Promise.all([fetchProductSlugs(), fetchBlogSlugs()]);

    const staticPages: MetadataRoute.Sitemap = [
        { url: SITE_URL,                       lastModified: new Date(), changeFrequency: 'daily',   priority: 1.0 },
        { url: `${SITE_URL}/shop`,             lastModified: new Date(), changeFrequency: 'daily',   priority: 0.9 },
        { url: `${SITE_URL}/blog`,             lastModified: new Date(), changeFrequency: 'weekly',  priority: 0.8 },
        { url: `${SITE_URL}/collections`,      lastModified: new Date(), changeFrequency: 'weekly',  priority: 0.7 },
        { url: `${SITE_URL}/about`,            lastModified: new Date(), changeFrequency: 'monthly', priority: 0.5 },
        { url: `${SITE_URL}/contact`,          lastModified: new Date(), changeFrequency: 'monthly', priority: 0.5 },
        { url: `${SITE_URL}/faq`,              lastModified: new Date(), changeFrequency: 'monthly', priority: 0.4 },
    ];

    const productPages: MetadataRoute.Sitemap = productSlugs.map(slug => ({
        url: `${SITE_URL}/product/${slug}`,
        lastModified: new Date(),
        changeFrequency: 'weekly' as const,
        priority: 0.8,
    }));

    const blogPages: MetadataRoute.Sitemap = blogSlugs.map(slug => ({
        url: `${SITE_URL}/blog/${slug}`,
        lastModified: new Date(),
        changeFrequency: 'monthly' as const,
        priority: 0.6,
    }));

    return [...staticPages, ...productPages, ...blogPages];
}
