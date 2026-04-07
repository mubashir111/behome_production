import type { Metadata } from 'next';
import { Suspense } from 'react';
import BlogListClient from './BlogListClient';
import { SERVER_API_URL, API_KEY } from '@/lib/config';
import { constructMetadata } from '@/lib/metadata';

async function getBlogPageData() {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/static-pages/blog`, {
            cache: 'no-store',
            headers: { 'x-api-key': API_KEY, 'Content-Type': 'application/json' },
        });
        if (!res.ok) return null;
        const json = await res.json();
        return json.data ?? null;
    } catch {
        return null;
    }
}

async function getInitialPosts() {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/blog-posts?per_page=9&page=1`, {
            cache: 'no-store',
            headers: { 'x-api-key': API_KEY, Accept: 'application/json' },
        });
        if (!res.ok) return { posts: [], meta: null };
        const json = await res.json();
        return { posts: json.data ?? [], meta: json.meta ?? null };
    } catch {
        return { posts: [], meta: null };
    }
}

export async function generateMetadata(): Promise<Metadata> {
    const data = await getBlogPageData();
    return constructMetadata({
        title: data?.meta_title || 'Blog | Behome',
        description: data?.meta_description || 'Explore interior design inspiration, home decor trends, styling guides, and expert tips from the Behome team.',
    });
}

export default async function Blog() {
    const [pageData, { posts, meta }] = await Promise.all([
        getBlogPageData(),
        getInitialPosts(),
    ]);

    const title = pageData?.title   || 'Our Blog';
    const intro = pageData?.content || '';

    return (
        <main>
            <section className="top-space-padding pt-50px pb-100px xs-pb-60px">
                <div className="container">

                    <div className="row justify-content-center mb-60px xs-mb-40px text-center">
                        <div className="col-lg-7">
                            <span className="fs-13 fw-600 text-uppercase ls-3px text-base-color d-block mb-15px">Behome Journal</span>
                            <h1 className="alt-font fw-600 text-white ls-minus-1px mb-0">{title}</h1>
                            {intro && <p className="text-white opacity-7 fs-16 lh-30 mt-20px mb-0">{intro}</p>}
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-12">
                            <Suspense fallback={<div className="text-center py-80px"><div className="spinner-border text-white opacity-5" role="status" /></div>}>
                                <BlogListClient initialPosts={posts} initialMeta={meta} />
                            </Suspense>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    );
}
