import type { MetadataRoute } from 'next';
import { SITE_URL } from '@/lib/config';

export default function robots(): MetadataRoute.Robots {
    const siteUrl = SITE_URL;

    return {
        rules: [
            {
                userAgent: '*',
                allow: '/',
                disallow: ['/account', '/checkout', '/cart', '/payment'],
            },
        ],
        sitemap: `${siteUrl}/sitemap.xml`,
    };
}
