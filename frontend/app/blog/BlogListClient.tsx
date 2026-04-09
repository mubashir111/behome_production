'use client';

import { useState, useEffect, useRef } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useSearchParams } from 'next/navigation';


function formatDate(dateStr: string) {
    try {
        return new Date(dateStr).toLocaleDateString('en-US', { day: '2-digit', month: 'long', year: 'numeric' });
    } catch { return dateStr; }
}

interface Props {
    initialPosts: any[];
    initialMeta: any;
}

export default function BlogListClient({ initialPosts, initialMeta }: Props) {
    const searchParams = useSearchParams();
    const page         = Number(searchParams.get('page') || 1);
    const category     = searchParams.get('category') || '';

    const [posts, setPosts]     = useState<any[]>(initialPosts);
    const [meta, setMeta]       = useState<any>(initialMeta);
    const [loading, setLoading] = useState(false);

    // Track if this is the initial render so we skip refetching the SSR data
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current && page === 1 && !category) {
            isFirstRender.current = false;
            return;
        }
        isFirstRender.current = false;

        setLoading(true);
        const params = new URLSearchParams({ per_page: '9', page: String(page) });
        if (category) params.set('category', category);

        fetch(`/api/frontend/blog-posts?${params}`, {
            headers: { Accept: 'application/json' },
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
    }, [page, category]);

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
            <div className="row g-4">
                {posts.map((post: any) => (
                    <div key={post.id} className="col-lg-4 col-md-6">
                        <div className="card bg-transparent border-0 h-100">
                            <div className="blog-image position-relative overflow-hidden border-radius-4px">
                                <Link href={`/blog/${post.slug}`}>
                                    <Image
                                        alt={post.title}
                                        src={post.cover_image || '/images/demo-decor-store-blog-01.jpg'}
                                        width={640} height={420}
                                        unoptimized
                                        style={{ width: '100%', height: '240px', objectFit: 'cover' }}
                                    />
                                </Link>
                            </div>
                            <div className="card-body px-0 pt-25px pb-25px">
                                <span className="fs-13 text-uppercase d-block mb-8px fw-500">
                                    {post.category && (
                                        <Link className="text-white fw-700 categories-text me-10px" href={`/blog?category=${post.category}`}>
                                            {post.category}
                                        </Link>
                                    )}
                                    <span className="blog-date opacity-5">{formatDate(post.published_at ?? post.created_at)}</span>
                                </span>
                                <Link className="card-title fw-600 fs-17 lh-28 text-white d-block mb-10px" href={`/blog/${post.slug}`}>
                                    {post.title}
                                </Link>
                                {post.excerpt && (
                                    <p className="text-white opacity-6 fs-14 lh-26 mb-0" style={{ display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                                        {post.excerpt}
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {meta && meta.last_page > 1 && (
                <div className="col-12 mt-50px d-flex justify-content-center">
                    <ul className="pagination pagination-style-01 fs-13 fw-500 mb-0">
                        {page > 1 && (
                            <li className="page-item">
                                <Link className="page-link" href={`/blog?page=${page - 1}${category ? `&category=${category}` : ''}`}>
                                    <i className="feather icon-feather-arrow-left fs-18 d-xs-none" />
                                </Link>
                            </li>
                        )}
                        {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(p => (
                            <li key={p} className={`page-item${p === page ? ' active' : ''}`}>
                                <Link className="page-link" href={`/blog?page=${p}${category ? `&category=${category}` : ''}`}>
                                    {String(p).padStart(2, '0')}
                                </Link>
                            </li>
                        ))}
                        {page < meta.last_page && (
                            <li className="page-item">
                                <Link className="page-link" href={`/blog?page=${page + 1}${category ? `&category=${category}` : ''}`}>
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
