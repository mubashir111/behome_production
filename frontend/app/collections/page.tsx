'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { usePathname, useRouter, useSearchParams } from 'next/navigation';
import { apiFetch } from '@/lib/api';

const FALLBACK_IMAGES = [
    '/images/demo-decor-store-main-banner-01.jpg',
    '/images/demo-decor-store-main-banner-02.jpg',
    '/images/demo-decor-store-main-banner-03.jpg',
];

export default function Collections() {
    const router = useRouter();
    const pathname = usePathname();
    const searchParams = useSearchParams();
    const initialPage = Number.parseInt(searchParams.get('page') || '1', 10);
    const [categories, setCategories] = useState<any[]>([]);
    const [currentPage, setCurrentPage] = useState(Number.isNaN(initialPage) ? 1 : Math.max(initialPage, 1));
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const nextPage = Number.parseInt(searchParams.get('page') || '1', 10);
        setCurrentPage(Number.isNaN(nextPage) ? 1 : Math.max(nextPage, 1));
    }, [searchParams]);

    useEffect(() => {
        const fetchCategories = async () => {
            setLoading(true);
            try {
                const response = await apiFetch(`/frontend/product-category?paginate=1&per_page=12&page=${currentPage}`);
                const list = Array.isArray(response?.data) ? response.data : [];
                const meta = response?.meta || {};
                setCategories(list);
                setCurrentPage(meta.current_page || currentPage);
                setLastPage(meta.last_page || 1);
            } catch (error) {
                console.error('Error fetching categories:', error);
                setCategories([]);
                setLastPage(1);
            } finally {
                setLoading(false);
            }
        };
        fetchCategories();
    }, [currentPage]);

    const changePage = (page: number) => {
        const nextPage = Math.max(1, Math.min(page, lastPage || 1));
        setCurrentPage(nextPage);
        const nextParams = new URLSearchParams(searchParams.toString());
        if (nextPage <= 1) {
            nextParams.delete('page');
        } else {
            nextParams.set('page', String(nextPage));
        }
        const nextUrl = nextParams.toString() ? `${pathname}?${nextParams.toString()}` : pathname;
        router.replace(nextUrl, { scroll: false });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const visiblePages = (() => {
        if (lastPage <= 1) return [];
        const start = Math.max(1, currentPage - 2);
        const end = Math.min(lastPage, currentPage + 2);
        const pages: number[] = [];
        for (let p = start; p <= end; p++) pages.push(p);
        return pages;
    })();

    return (
        <main className="no-layout-pad" style={{ paddingTop: '50px' }}>

            {/* Hero */}
            <section className="page-title-center-alignment cover-background bg-dark-gray" style={{
                backgroundImage: "linear-gradient(rgba(10,10,10,0.72),rgba(10,10,10,0.72)),url('/images/new/bg/bg1.webp')",
                paddingBottom: '70px',
            }}>
                <div className="container">
                    <div className="row">
                        <div className="col-12 text-center position-relative page-title-extra-large">
                            <h1 className="alt-font d-inline-block fw-700 ls-minus-05px text-white mb-10px">Collections</h1>
                            <p className="text-white opacity-6 mb-0" style={{ fontSize: 15, letterSpacing: '0.3px' }}>
                                Discover our curated range of premium categories
                            </p>
                        </div>
                        <div className="col-12 breadcrumb breadcrumb-style-01 d-flex justify-content-center mt-2">
                            <ul>
                                <li><a className="text-white" href="/">Home</a></li>
                                <li className="text-white opacity-7">Collections</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            {/* Grid */}
            <section className="py-5" style={{ background: '#0b0b0f' }}>
                <div className="container">

                    {loading ? (
                        <div className="row g-4">
                            {Array.from({ length: 5 }).map((_, i) => (
                                <div key={i} className="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div className="shop-shimmer" style={{ height: 340, borderRadius: 12 }} />
                                </div>
                            ))}
                        </div>
                    ) : categories.length === 0 ? (
                        <div className="text-center py-5">
                            <div style={{
                                width: 72, height: 72, borderRadius: '50%',
                                background: 'rgba(201,169,110,0.10)', border: '1px solid rgba(201,169,110,0.2)',
                                display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 20px',
                            }}>
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--base-color,#c9a96e)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                                    <rect x="2" y="3" width="7" height="7" rx="1" />
                                    <rect x="15" y="3" width="7" height="7" rx="1" />
                                    <rect x="2" y="14" width="7" height="7" rx="1" />
                                    <rect x="15" y="14" width="7" height="7" rx="1" />
                                </svg>
                            </div>
                            <p className="text-white opacity-5 mb-0">No collections found</p>
                        </div>
                    ) : (
                        <div className="row g-4">
                            {categories.map((cat, idx) => {
                                const img = cat.cover || cat.thumb || FALLBACK_IMAGES[idx % FALLBACK_IMAGES.length];
                                const count = cat.products_count ?? 0;
                                return (
                                    <div key={cat.id} className="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <a href={`/shop?category=${cat.slug}`} className="collection-card d-block text-decoration-none" style={{
                                            position: 'relative', display: 'block',
                                            borderRadius: 12, overflow: 'hidden',
                                            height: 340,
                                            background: '#111',
                                        }}>
                                            {/* Image */}
                                            <Image
                                                src={img}
                                                alt={cat.name}
                                                fill
                                                sizes="(max-width: 576px) 100vw, (max-width: 768px) 50vw, (max-width: 992px) 33vw, (max-width: 1200px) 25vw, 20vw"
                                                style={{ objectFit: 'cover', objectPosition: 'center', transition: 'transform 0.6s ease' }}
                                                className="collection-card-img"
                                            />

                                            {/* Overlay */}
                                            <div style={{
                                                position: 'absolute', inset: 0,
                                                background: 'linear-gradient(to top, rgba(5,5,8,0.88) 0%, rgba(5,5,8,0.30) 50%, rgba(5,5,8,0.10) 100%)',
                                                transition: 'background 0.4s ease',
                                            }} className="collection-card-overlay" />

                                            {/* Count badge */}
                                            {count > 0 && (
                                                <div style={{
                                                    position: 'absolute', top: 16, right: 16,
                                                    background: 'rgba(8,8,12,0.72)',
                                                    backdropFilter: 'blur(10px)',
                                                    WebkitBackdropFilter: 'blur(10px)',
                                                    border: '1px solid rgba(201,169,110,0.28)',
                                                    borderRadius: 20,
                                                    padding: '4px 12px',
                                                    fontSize: 11,
                                                    fontWeight: 700,
                                                    color: 'var(--base-color,#c9a96e)',
                                                    letterSpacing: '0.5px',
                                                    zIndex: 2,
                                                }}>
                                                    {count} {count === 1 ? 'item' : 'items'}
                                                </div>
                                            )}

                                            {/* Bottom text */}
                                            <div style={{
                                                position: 'absolute', bottom: 0, left: 0, right: 0,
                                                padding: '28px 24px 24px',
                                                zIndex: 2,
                                            }}>
                                                <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: 12 }}>
                                                    <div style={{ flex: 1, minWidth: 0 }}>
                                                        <div style={{
                                                            display: 'inline-flex', alignItems: 'center', gap: 6,
                                                            marginBottom: 6,
                                                        }}>
                                                            <span style={{ display: 'block', width: 18, height: 1, background: 'var(--base-color,#c9a96e)' }} />
                                                            <span style={{ color: 'var(--base-color,#c9a96e)', fontSize: 9, fontWeight: 700, letterSpacing: '2.5px', textTransform: 'uppercase' }}>
                                                                Collection
                                                            </span>
                                                        </div>
                                                        <div style={{
                                                            fontFamily: 'var(--font-heading, sans-serif)',
                                                            color: '#fff',
                                                            fontSize: 'clamp(18px,2vw,22px)',
                                                            fontWeight: 700,
                                                            letterSpacing: '-0.3px',
                                                            lineHeight: 1.25,
                                                            display: '-webkit-box',
                                                            WebkitLineClamp: 2,
                                                            WebkitBoxOrient: 'vertical',
                                                            overflow: 'hidden',
                                                            textOverflow: 'ellipsis',
                                                        }}>
                                                            {cat.name}
                                                        </div>
                                                    </div>

                                                    {/* Arrow CTA */}
                                                    <div className="collection-card-arrow" style={{
                                                        width: 42, height: 42, borderRadius: '50%',
                                                        background: 'var(--base-color,#c9a96e)',
                                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                        flexShrink: 0,
                                                        opacity: 0,
                                                        transform: 'translateY(8px)',
                                                        transition: 'opacity 0.3s ease, transform 0.3s ease',
                                                    }}>
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0d0d0d" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                                            <path d="M5 12h14M12 5l7 7-7 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                );
                            })}
                        </div>
                    )}

                    {/* Pagination */}
                    {lastPage > 1 && (
                        <nav aria-label="Collections pagination" className="d-flex justify-content-center mt-5">
                            <ul className="pagination pagination-style-01 m-0">
                                <li className={`page-item ${currentPage <= 1 ? 'disabled' : ''}`}>
                                    <button className="page-link bg-transparent border-0 text-white" onClick={() => changePage(currentPage - 1)} disabled={currentPage <= 1}>
                                        Prev
                                    </button>
                                </li>
                                {visiblePages.map((page) => (
                                    <li key={page} className={`page-item ${page === currentPage ? 'active' : ''}`}>
                                        <button className="page-link bg-transparent border-0 text-white" onClick={() => changePage(page)}>
                                            {page}
                                        </button>
                                    </li>
                                ))}
                                <li className={`page-item ${currentPage >= lastPage ? 'disabled' : ''}`}>
                                    <button className="page-link bg-transparent border-0 text-white" onClick={() => changePage(currentPage + 1)} disabled={currentPage >= lastPage}>
                                        Next
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    )}
                </div>
            </section>

            <style>{`

                .collection-card:hover .collection-card-img {
                    transform: scale(1.06);
                }
                .collection-card:hover .collection-card-overlay {
                    background: linear-gradient(to top, rgba(5,5,8,0.94) 0%, rgba(5,5,8,0.45) 55%, rgba(5,5,8,0.15) 100%);
                }
                .collection-card:hover .collection-card-arrow {
                    opacity: 1 !important;
                    transform: translateY(0) !important;
                }
            `}</style>

        </main>
    );
}
