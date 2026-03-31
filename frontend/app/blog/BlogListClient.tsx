'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useSearchParams } from 'next/navigation';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
const API_KEY  = process.env.NEXT_PUBLIC_API_KEY  || '';

function formatDate(dateStr: string) {
    try {
        return new Date(dateStr).toLocaleDateString('en-US', { day: '2-digit', month: 'long', year: 'numeric' });
    } catch {
        return dateStr;
    }
}

export default function BlogListClient() {
    const searchParams  = useSearchParams();
    const page          = Number(searchParams.get('page') ?? 1);
    const [posts, setPosts]     = useState<any[]>([]);
    const [meta, setMeta]       = useState<any>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        fetch(`${API_URL}/frontend/blog-posts?per_page=9&page=${page}`, {
            headers: { 'x-api-key': API_KEY, Accept: 'application/json' },
            cache: 'no-store',
        })
            .then(r => r.json())
            .then(json => {
                setPosts(json.data ?? []);
                setMeta(json.meta ?? null);
            })
            .catch(() => {
                setPosts([]);
                setMeta(null);
            })
            .finally(() => setLoading(false));
    }, [page]);

    if (loading) {
        return (
            <div className="text-center py-80px">
                <div className="spinner-border text-white opacity-5" role="status" />
            </div>
        );
    }

    if (posts.length === 0) {
        return (
            <div className="text-center py-80px">
                <i className="feather icon-feather-book-open text-white opacity-3" style={{ fontSize: 60 }} />
                <p className="text-white opacity-5 mt-20px fs-16">No blog posts yet. Check back soon!</p>
            </div>
        );
    }

    return (
        <>
            <ul className="blog-classic blog-wrapper grid-loading grid grid-4col xl-grid-4col lg-grid-3col md-grid-2col sm-grid-2col xs-grid-1col gutter-double-extra-large"
                data-anime='{ "el": "childs", "translateY": [50, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
                <li className="grid-sizer" />
                {posts.map((post: any) => (
                    <li key={post.id} className="grid-item">
                        <div className="card bg-transparent border-0 h-100">
                            <div className="blog-image position-relative overflow-hidden border-radius-4px">
                                <Link href={`/blog/${post.slug}`}>
                                    <Image
                                        alt={post.title}
                                        src={post.cover_image || '/images/demo-decor-store-blog-01.jpg'}
                                        width={640} height={420}
                                        unoptimized
                                        style={{ width: '100%', height: 'auto' }}
                                    />
                                </Link>
                            </div>
                            <div className="card-body px-0 pt-30px pb-30px xs-pb-15px">
                                <span className="fs-13 text-uppercase d-block mb-5px fw-500">
                                    {post.category && (
                                        <Link className="text-white fw-700 categories-text" href={`/blog?category=${post.category}`}>{post.category}</Link>
                                    )}
                                    <span className="blog-date">{formatDate(post.published_at ?? post.created_at)}</span>
                                </span>
                                <Link className="card-title fw-600 fs-17 lh-30 text-white d-inline-block w-95 xs-w-100" href={`/blog/${post.slug}`}>
                                    {post.title}
                                </Link>
                                {post.excerpt && (
                                    <p className="text-white opacity-6 fs-14 mt-10px line-clamp-2">{post.excerpt}</p>
                                )}
                            </div>
                        </div>
                    </li>
                ))}
            </ul>

            {meta && meta.last_page > 1 && (
                <div className="col-12 mt-3 sm-mb-3 d-flex justify-content-center">
                    <ul className="pagination pagination-style-01 fs-13 fw-500 mb-0">
                        {page > 1 && (
                            <li className="page-item">
                                <Link className="page-link" href={`/blog?page=${page - 1}`}>
                                    <i className="feather icon-feather-arrow-left fs-18 d-xs-none" />
                                </Link>
                            </li>
                        )}
                        {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(p => (
                            <li key={p} className={`page-item${p === page ? ' active' : ''}`}>
                                <Link className="page-link" href={`/blog?page=${p}`}>{String(p).padStart(2, '0')}</Link>
                            </li>
                        ))}
                        {page < meta.last_page && (
                            <li className="page-item">
                                <Link className="page-link" href={`/blog?page=${page + 1}`}>
                                    <i className="feather icon-feather-arrow-right fs-18 d-xs-none" />
                                </Link>
                            </li>
                        )}
                    </ul>
                </div>
            )}
        </>
    );
}
