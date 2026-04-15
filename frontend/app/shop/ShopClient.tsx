'use client';

import { useState, useEffect, Suspense, useRef } from 'react';
import Image from 'next/image';
import { usePathname, useRouter, useSearchParams } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import WishlistButton from '@/components/WishlistButton';
import PageLoadingShell from '@/components/PageLoadingShell';
import { useToast } from '@/components/ToastProvider';
import { useCart } from '@/components/CartProvider';
import { useSettings } from '@/components/SettingsProvider';
import { useAuthModal } from '@/context/AuthModalContext';
import QuickViewModal from '@/components/QuickViewModal';


function ShopContent() {
    const router = useRouter();
    const pathname = usePathname();
    const searchParams = useSearchParams();
    const categorySlug = searchParams.get('category');
    const brandSlugParam = searchParams.get('brand');
    const searchQuery = searchParams.get('search') || '';
    const initialPage = Number.parseInt(searchParams.get('page') || '1', 10);

    const [products, setProducts] = useState<any[]>([]);
    const [quickViewSlug, setQuickViewSlug] = useState<string | null>(null);
    const { settings, formatAmount } = useSettings();

    const PRICE_RANGES = (() => {
        const filterStr = settings?.site_price_filters || '25, 50, 100, 200';
        const points = filterStr.split(',')
            .map((s: string) => parseFloat(s.trim()))
            .filter((n: number) => !isNaN(n))
            .sort((a: number, b: number) => a - b);

        if (points.length === 0) return [];
        const ranges = [];

        // Return rounded number as string, no currency formatting
        const f = (n: number) => Math.round(n).toString();

        ranges.push({ min: 0, max: points[0], label: `Under ${f(points[0])}` });
        for (let i = 0; i < points.length - 1; i++) {
            ranges.push({
                min: points[i],
                max: points[i + 1],
                label: `${f(points[i])} to ${f(points[i + 1])}`
            });
        }
        const lastPoint = points[points.length - 1];
        ranges.push({ min: lastPoint, max: Infinity, label: `${f(lastPoint)} & Above` });
        return ranges;
    })();
    const [categories, setCategories] = useState<any[]>([]);
    const [brands, setBrands] = useState<any[]>([]);
    const [latestProducts, setLatestProducts] = useState<any[]>([]);
    const [totalProducts, setTotalProducts] = useState(0);
    const [currentPage, setCurrentPage] = useState(Number.isNaN(initialPage) ? 1 : Math.max(initialPage, 1));
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [sortBy, setSortBy] = useState('default');
    const [viewMode, setViewMode] = useState<'2' | '3' | '4' | 'list'>('4');
    const { showToast } = useToast();
    const { openAuthModal } = useAuthModal();
    const [priceRange, setPriceRange] = useState<any | null>(null);
    const [searchInput, setSearchInput] = useState(searchQuery);
    const [naIndex, setNaIndex] = useState(0);
    const lastFetchedUrl = useRef<string | null>(null);
    const [showMobileFilters, setShowMobileFilters] = useState(false);
    const [suggestions, setSuggestions] = useState<any[]>([]);
    const [showSuggestions, setShowSuggestions] = useState(false);
    const suggestDebounce = useRef<ReturnType<typeof setTimeout> | null>(null);
    const suggestContainerRef = useRef<HTMLDivElement>(null);

    const { updateCart } = useCart();

    /* ── Add to cart ─────────────────────────────────────── */
    const addToCart = async (product: any) => {
        try {
            const token = localStorage.getItem('token');
            if (!token) { openAuthModal(() => addToCart(product)); return; }
            const res = await apiFetch('/cart', {
                method: 'POST',
                body: JSON.stringify({ product_id: product.id, quantity: 1, variation_id: null }),
            });
            if (res.status) {
                showToast(`Added ${product.name} to cart!`, 'success');
                updateCart();
            } else {
                showToast(res.message || 'Failed to add to cart', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'An error occurred', 'error');
        }
    };

    /* ── Sidebar static data (once) ─────────────────────── */
    useEffect(() => {
        Promise.all([
            apiFetch('/categories').catch(() => ({ data: [] })),
            apiFetch('/frontend/product-brand').catch(() => ({ data: [] })),
            apiFetch('/products?per_page=5&sort=latest').catch(() => ({ data: { data: [] } })),
        ]).then(([catData, brandData, latestData]) => {
            setCategories(catData.data || []);
            setBrands(brandData.data || []);
            setLatestProducts(latestData.data?.data || []);
        }).catch(() => { });
    }, []);

    const buildShopUrl = (updates: Record<string, string | null>) => {
        const nextParams = new URLSearchParams(searchParams.toString());

        Object.entries(updates).forEach(([key, value]) => {
            if (!value) {
                nextParams.delete(key);
                return;
            }

            nextParams.set(key, value);
        });

        if (updates.page === null || updates.page === '1') {
            nextParams.delete('page');
        }

        const query = nextParams.toString();
        return query ? `${pathname}?${query}` : pathname;
    };

    const applyShopParams = (updates: Record<string, string | null>, options?: { scroll?: boolean }) => {
        const nextUrl = buildShopUrl(updates);
        router.replace(nextUrl, { scroll: options?.scroll ?? false });
    };

    /* ── Products (re-fetch on filter change) ────────────── */
    useEffect(() => {
        const pageFromUrl = Number.parseInt(searchParams.get('page') || '1', 10);
        const searchFromUrl = searchParams.get('search') || '';

        let url = `/products?per_page=24&page=${pageFromUrl}`;
        if (categorySlug) url += `&category_slug=${categorySlug}`;
        if (brandSlugParam) url += `&brand_slug=${brandSlugParam}`;
        if (searchFromUrl) url += `&search=${encodeURIComponent(searchFromUrl)}`;
        if (priceRange) {
            url += `&min_price=${priceRange.min}`;
            if (priceRange.max !== Infinity) {
                url += `&max_price=${priceRange.max}`;
            }
        }
        if (sortBy === '1') url += '&sort=popular';
        if (sortBy === '3') url += '&sort=latest';

        // Guard: avoid double-fetching same URL (Strict Mode or redundant state changes)
        if (lastFetchedUrl.current === url) return;
        lastFetchedUrl.current = url;

        setLoading(true);
        if (currentPage !== pageFromUrl) setCurrentPage(pageFromUrl);
        if (searchInput !== searchFromUrl) setSearchInput(searchFromUrl);

        apiFetch(url)
            .then(data => {
                const pagination = data.data || {};
                let list: any[] = pagination.data || [];

                // Client-side sorts
                if (sortBy === '4') list = [...list].sort((a, b) => parseFloat(a.price || 0) - parseFloat(b.price || 0));
                if (sortBy === '5') list = [...list].sort((a, b) => parseFloat(b.price || 0) - parseFloat(a.price || 0));
                if (sortBy === '2') list = [...list].sort((a, b) => (b.average_rating || 0) - (a.average_rating || 0));

                setProducts(list);
                setTotalProducts(pagination.total || list.length);
                setLastPage(pagination.last_page || 1);
            })
            .catch(() => {
                setProducts([]);
                setTotalProducts(0);
                setLastPage(1);
            })
            .finally(() => setLoading(false));
    }, [searchParams, categorySlug, brandSlugParam, searchQuery, sortBy, priceRange]);

    const changePage = (page: number) => {
        const nextPage = Math.max(1, Math.min(page, lastPage || 1));
        setCurrentPage(nextPage);
        applyShopParams({ page: String(nextPage) });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const submitSearch = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setCurrentPage(1);
        applyShopParams({ search: searchInput.trim() || null, page: null }, { scroll: false });
    };

    const visiblePages = (() => {
        if (lastPage <= 1) return [];
        const start = Math.max(1, currentPage - 2);
        const end = Math.min(lastPage, currentPage + 2);
        const pages: number[] = [];
        for (let page = start; page <= end; page += 1) {
            pages.push(page);
        }
        return pages;
    })();

    /* ── Search suggestions (debounced) ─────────────────── */
    useEffect(() => {
        if (suggestDebounce.current) clearTimeout(suggestDebounce.current);
        const q = searchInput.trim();
        if (q.length < 2) { setSuggestions([]); setShowSuggestions(false); return; }
        suggestDebounce.current = setTimeout(() => {
            apiFetch(`/products?search=${encodeURIComponent(q)}&per_page=6`)
                .then(data => {
                    const list: any[] = data?.data?.data || [];
                    setSuggestions(list);
                    setShowSuggestions(list.length > 0);
                })
                .catch(() => { setSuggestions([]); setShowSuggestions(false); });
        }, 280);
        return () => { if (suggestDebounce.current) clearTimeout(suggestDebounce.current); };
    }, [searchInput]);

    useEffect(() => {
        const handler = (e: MouseEvent) => {
            if (suggestContainerRef.current && !suggestContainerRef.current.contains(e.target as Node)) {
                setShowSuggestions(false);
            }
        };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, []);

    /* ── New arrivals slider (1 at a time) ───────────────── */
    const naPrev = () => setNaIndex(i => (i - 1 + latestProducts.length) % latestProducts.length);
    const naNext = () => setNaIndex(i => (i + 1) % latestProducts.length);
    const currentArrival = latestProducts[naIndex] || null;

    /* ── Grid class helper ───────────────────────────────── */
    const gridClass = viewMode === '2'
        ? 'row-cols-2 row-cols-md-2 row-cols-xl-2'
        : viewMode === '3'
            ? 'row-cols-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3'
            : viewMode === 'list'
                ? 'row-cols-1'
                : /* 4 */ 'row-cols-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-4';

    return (
        <>
        <main>
            <section className="page-shell page-shell-tight ps-6 pe-6 lg-ps-3 lg-pe-3 sm-ps-0 sm-pe-0">
                <div className="container-fluid">
                    <div className="row flex-row-reverse">

                        {/* ══ Product grid ════════════════════════════════ */}
                        <div className="col-xxl-10 col-lg-9 ps-5 md-ps-15px md-mb-60px">

                            {/* ── Mobile search bar ── */}
                            <div className="d-md-none mb-15px w-100 position-relative">
                                <form className="d-flex gap-2 w-100" onSubmit={e => { setShowSuggestions(false); submitSearch(e); }} role="search">
                                    <input
                                        type="search" name="search" autoComplete="off" spellCheck={false}
                                        className="form-control form-control-sm flex-grow-1"
                                        placeholder="Search products…"
                                        value={searchInput}
                                        onChange={e => setSearchInput(e.target.value)}
                                        onFocus={() => suggestions.length > 0 && setShowSuggestions(true)}
                                        style={{ background: 'rgba(255,255,255,0.06)', color: '#fff', border: '1px solid rgba(255,255,255,0.14)' }}
                                    />
                                    <button type="submit" className="btn btn-base-color btn-small btn-rounded text-dark-gray" style={{ whiteSpace: 'nowrap', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>Search</button>
                                </form>
                                {showSuggestions && suggestions.length > 0 && (
                                    <div style={{ position: 'absolute', top: '100%', left: 0, right: 0, marginTop: 6, background: 'rgba(18,18,28,0.98)', border: '1px solid rgba(197,160,89,0.2)', borderRadius: 10, boxShadow: '0 16px 40px rgba(0,0,0,0.5)', zIndex: 9999, overflow: 'hidden' }}>
                                        {suggestions.map((s: any) => (
                                            <a key={s.id} href={`/product/${s.slug}`} onClick={() => setShowSuggestions(false)}
                                                style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 14px', textDecoration: 'none', borderBottom: '1px solid rgba(255,255,255,0.05)' }}>
                                                <div style={{ width: 36, height: 36, borderRadius: 6, overflow: 'hidden', flexShrink: 0, background: 'rgba(255,255,255,0.04)' }}>
                                                    {/* eslint-disable-next-line @next/next/no-img-element */}
                                                    <img src={s.cover || '/images/demo-decor-store-product-01.jpg'} alt={s.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                                </div>
                                                <div style={{ flex: 1, minWidth: 0 }}>
                                                    <p style={{ margin: 0, color: '#fff', fontSize: 13, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{s.name}</p>
                                                    <p style={{ margin: 0, color: 'var(--base-color)', fontSize: 12, fontWeight: 600 }}>{s.is_offer ? s.discounted_price : s.currency_price}</p>
                                                </div>
                                            </a>
                                        ))}
                                        <button onClick={() => { setShowSuggestions(false); submitSearch({ preventDefault: () => {} } as any); }}
                                            style={{ width: '100%', padding: '10px 14px', background: 'rgba(197,160,89,0.08)', border: 'none', color: 'var(--base-color)', fontSize: 12, fontWeight: 700, cursor: 'pointer', textAlign: 'left', letterSpacing: '0.04em' }}>
                                            <i className="feather icon-feather-search" style={{ marginRight: 8, fontSize: 12 }}></i>
                                            See all results for &ldquo;{searchInput}&rdquo;
                                        </button>
                                    </div>
                                )}
                            </div>

                            {/* ── Mobile filter + sort bar ── */}
                            <div className="d-flex d-md-none align-items-center gap-2 mb-20px">
                                <button
                                    type="button"
                                    className="shop-mobile-filter-btn"
                                    onClick={() => setShowMobileFilters(true)}
                                    style={{ display: 'flex', alignItems: 'center', gap: 7, flex: 1, justifyContent: 'center', padding: '9px 14px', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.12)', borderRadius: 8, color: '#fff', fontWeight: 600, fontSize: 13, cursor: 'pointer' }}
                                >
                                    <i className="bi bi-filter"></i>
                                    Filters {(categorySlug || brandSlugParam || priceRange) ? <span style={{ background: 'var(--base-color)', color: '#000', borderRadius: '50%', width: 18, height: 18, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: 10, fontWeight: 800 }}>{[categorySlug, brandSlugParam, priceRange].filter(Boolean).length}</span> : null}
                                </button>
                                <select
                                    className="form-select form-select-sm border-0"
                                    value={sortBy}
                                    onChange={e => setSortBy(e.target.value)}
                                    style={{ flex: 1, background: 'rgba(255,255,255,0.06)', color: '#fff', borderRadius: 8, border: '1px solid rgba(255,255,255,0.12)', padding: '9px 12px', fontSize: 13, fontWeight: 600 }}
                                >
                                    <option value="default">Default</option>
                                    <option value="1">Popular</option>
                                    <option value="3">Latest</option>
                                    <option value="4">Price ↑</option>
                                    <option value="5">Price ↓</option>
                                </select>
                            </div>

                            {/* ── Desktop toolbar ── */}
                            <div className="toolbar-wrapper border-bottom border-color-extra-medium-gray d-none d-md-flex flex-wrap align-items-center gap-2 w-100 mb-40px pb-15px">
                                <div className="d-flex align-items-center gap-1 me-5px" role="group" aria-label="Product grid layout">
                                    {([['2', '/images/shop-two-column.svg', '2 columns'], ['3', '/images/shop-three-column.svg', '3 columns'], ['4', '/images/shop-four-column.svg', '4 columns'], ['list', '/images/shop-list.svg', 'List view']] as const).map(([mode, src, label]) => (
                                        <button
                                            key={mode}
                                            type="button"
                                            aria-label={label}
                                            aria-pressed={viewMode === mode}
                                            title={label}
                                            onClick={() => setViewMode(mode)}
                                            style={{ background: 'transparent', border: 'none', padding: '6px', borderRadius: 4, cursor: 'pointer', opacity: viewMode === mode ? 1 : 0.4, outline: 'none', boxShadow: viewMode === mode ? '0 0 0 2px var(--base-color)' : 'none', transition: 'opacity 0.15s, box-shadow 0.15s' }}
                                        >
                                            <Image alt="" src={src} width={18} height={18} style={{ filter: 'brightness(0) invert(1)' }} />
                                        </button>
                                    ))}
                                </div>
                                <div ref={suggestContainerRef} className="d-flex flex-grow-1 gap-2 align-items-center position-relative" style={{ maxWidth: 380 }}>
                                    <form className="d-flex flex-grow-1 gap-2 align-items-center" onSubmit={e => { setShowSuggestions(false); submitSearch(e); }}>
                                        <input
                                            type="search"
                                            placeholder="Search products…"
                                            value={searchInput}
                                            autoComplete="off"
                                            onChange={e => setSearchInput(e.target.value)}
                                            onFocus={() => suggestions.length > 0 && setShowSuggestions(true)}
                                            className="form-control form-control-sm"
                                            style={{ background: 'rgba(255,255,255,0.06)', color: '#fff', border: '1px solid rgba(255,255,255,0.12)' }}
                                        />
                                        <button type="submit" className="btn btn-base-color btn-small btn-rounded text-dark-gray" style={{ whiteSpace: 'nowrap', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>Search</button>
                                    </form>
                                    {showSuggestions && suggestions.length > 0 && (
                                        <div style={{ position: 'absolute', top: '100%', left: 0, right: 0, marginTop: 6, background: 'rgba(18,18,28,0.98)', border: '1px solid rgba(197,160,89,0.2)', borderRadius: 10, boxShadow: '0 16px 40px rgba(0,0,0,0.5)', zIndex: 9999, overflow: 'hidden' }}>
                                            {suggestions.map((s: any) => (
                                                <a key={s.id} href={`/product/${s.slug}`} onClick={() => setShowSuggestions(false)}
                                                    style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 14px', textDecoration: 'none', borderBottom: '1px solid rgba(255,255,255,0.05)', transition: 'background 0.15s' }}
                                                    onMouseEnter={e => (e.currentTarget.style.background = 'rgba(255,255,255,0.05)')}
                                                    onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}
                                                >
                                                    <div style={{ width: 36, height: 36, borderRadius: 6, overflow: 'hidden', flexShrink: 0, background: 'rgba(255,255,255,0.04)' }}>
                                                        {/* eslint-disable-next-line @next/next/no-img-element */}
                                                        <img src={s.cover || '/images/demo-decor-store-product-01.jpg'} alt={s.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                                    </div>
                                                    <div style={{ flex: 1, minWidth: 0 }}>
                                                        <p style={{ margin: 0, color: '#fff', fontSize: 13, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{s.name}</p>
                                                        <p style={{ margin: 0, color: 'var(--base-color)', fontSize: 12, fontWeight: 600 }}>{s.is_offer ? s.discounted_price : s.currency_price}</p>
                                                    </div>
                                                </a>
                                            ))}
                                            <button onClick={() => { setShowSuggestions(false); submitSearch({ preventDefault: () => {} } as any); }}
                                                style={{ width: '100%', padding: '10px 14px', background: 'rgba(197,160,89,0.08)', border: 'none', color: 'var(--base-color)', fontSize: 12, fontWeight: 700, cursor: 'pointer', textAlign: 'left', letterSpacing: '0.04em' }}>
                                                <i className="feather icon-feather-search" style={{ marginRight: 8, fontSize: 12 }}></i>
                                                See all results for &ldquo;{searchInput}&rdquo;
                                            </button>
                                        </div>
                                    )}
                                </div>
                                <div className="ms-auto d-flex align-items-center gap-3">
                                    {!loading && totalProducts > 0 && <span className="text-white opacity-6 fs-13">{products.length} of {totalProducts} results</span>}
                                    <select
                                        className="form-select form-select-sm border-0"
                                        value={sortBy}
                                        onChange={e => setSortBy(e.target.value)}
                                        style={{ background: 'rgba(255,255,255,0.06)', color: '#fff', borderRadius: 6 }}
                                    >
                                        <option value="default">Default sorting</option>
                                        <option value="1">Popularity</option>
                                        <option value="2">Avg. rating</option>
                                        <option value="3">Latest</option>
                                        <option value="4">Price: low → high</option>
                                        <option value="5">Price: high → low</option>
                                    </select>
                                </div>
                            </div>

                            {/* Active filter pills */}
                            {(searchQuery || categorySlug || brandSlugParam || priceRange) && (
                                <div className="d-flex flex-wrap gap-2 mb-25px">
                                    {searchQuery && (
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => applyShopParams({ search: null, page: null })}>
                                            Search: {searchQuery} <span className="opacity-6">×</span>
                                        </button>
                                    )}
                                    {categorySlug && (
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => applyShopParams({ category: null, page: null })}>
                                            Category: {categorySlug} <span className="opacity-6">×</span>
                                        </button>
                                    )}
                                    {brandSlugParam && (
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => applyShopParams({ brand: null, page: null })}>
                                            Brand: {brandSlugParam} <span className="opacity-6">×</span>
                                        </button>
                                    )}
                                    {priceRange && (
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => setPriceRange(null)}>
                                            {priceRange.label} <span className="opacity-6">×</span>
                                        </button>
                                    )}
                                </div>
                            )}

                            {/* Product grid */}
                            {loading ? (
                                <div className={`row ${gridClass}`}>
                                    {[...Array(8)].map((_, i) => (
                                        <div key={i} className="col mb-45px">
                                            <div style={{ height: viewMode === 'list' ? 100 : 260, background: 'rgba(255,255,255,0.05)', borderRadius: 4, marginBottom: 12 }} />
                                            <div style={{ height: 14, width: '60%', background: 'rgba(255,255,255,0.05)', borderRadius: 4, marginBottom: 8 }} />
                                        </div>
                                    ))}
                                </div>
                            ) : products.length === 0 ? (
                                <div className="text-center py-60px">
                                    <i className="feather icon-feather-search fs-50 d-block mb-20px text-white" style={{ opacity: 0.25 }}></i>
                                    <p className="text-white fw-600 fs-18 mb-8px">No products found</p>
                                    <p className="text-white fs-14 mb-25px" style={{ opacity: 0.5 }}>Try adjusting your filters or search term.</p>
                                    <button className="btn btn-small btn-round-edge btn-base-color" onClick={() => window.location.href = '/shop'}>Clear all filters</button>
                                </div>
                            ) : (
                                <>
                                    <div className={`row ${gridClass}`}>
                                        {products.map(product => (
                                            <div key={product.id} className="col mb-45px">
                                                {viewMode === 'list' ? (
                                                    <div className="shop-box d-flex align-items-center gap-4 pb-20px" style={{ borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
                                                        <a href={`/product/${product.slug}`} style={{ flexShrink: 0, width: 110, position: 'relative', display: 'block' }}>
                                                            <Image alt={product.name} src={product.cover || '/images/demo-decor-store-product-01.jpg'} width={110} height={130} unoptimized style={{ width: 110, height: 130, objectFit: 'cover', borderRadius: 4 }} />
                                                            {product.stock === 0 && (
                                                                <span style={{ position: 'absolute', top: 6, left: 6, background: 'rgba(239,68,68,0.9)', color: '#fff', fontSize: 10, fontWeight: 700, padding: '2px 6px', borderRadius: 4, letterSpacing: '0.04em', textTransform: 'uppercase' }}>Out of Stock</span>
                                                            )}
                                                        </a>
                                                        <div className="flex-grow-1">
                                                            <a className="text-white fs-16 fw-600 d-block mb-5px text-truncate" href={`/product/${product.slug}`}>{product.name}</a>
                                                            <div className="fw-500 fs-15 mb-8px">
                                                                {product.is_offer ? (
                                                                    <><del className="me-5px opacity-6">{product.currency_price}</del><span style={{ color: 'var(--base-color)' }}>{product.discounted_price}</span></>
                                                                ) : <span>{product.currency_price}</span>}
                                                            </div>
                                                            {product.stock > 0 && product.stock <= 5 && (
                                                                <p style={{ margin: '0 0 10px', fontSize: 12, fontWeight: 700, color: '#f59e0b' }}>
                                                                    <i className="feather icon-feather-alert-circle" style={{ fontSize: 11, marginRight: 4 }}></i>
                                                                    Only {product.stock} left
                                                                </p>
                                                            )}
                                                            <div className="d-flex gap-2">
                                                                <button className="btn btn-base-color btn-small btn-rounded text-dark-gray" onClick={() => addToCart(product)} disabled={product.stock === 0}>{product.stock === 0 ? 'Out of Stock' : 'Add to Cart'}</button>
                                                                <WishlistButton productId={product.id} initialInWishlist={Boolean(product.wishlist)} className="bg-dark-gray w-35px h-35px text-white d-flex align-items-center justify-content-center rounded-circle border-0" onRequireAuth={() => openAuthModal()} onMessage={(m, t) => showToast(m, t)} />
                                                            </div>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="shop-box pb-25px">
                                                        <div className="shop-image" style={{ position: 'relative' }}>
                                                            <a href={`/product/${product.slug}`}>
                                                                <Image alt={product.name} src={product.cover || '/images/demo-decor-store-product-01.jpg'} width={640} height={720} unoptimized style={{ width: '100%', height: 'auto' }} />
                                                                {product.is_offer && <span className="lable hot">Offer</span>}
                                                                <div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
                                                            </a>
                                                            {product.stock === 0 && (
                                                                <span style={{ position: 'absolute', top: 10, left: 10, background: 'rgba(239,68,68,0.88)', backdropFilter: 'blur(4px)', color: '#fff', fontSize: 10, fontWeight: 700, padding: '3px 8px', borderRadius: 5, letterSpacing: '0.05em', textTransform: 'uppercase', zIndex: 2, pointerEvents: 'none' }}>Out of Stock</span>
                                                            )}
                                                            {product.stock > 0 && product.stock <= 5 && (
                                                                <span style={{ position: 'absolute', top: 10, left: 10, background: 'rgba(245,158,11,0.9)', backdropFilter: 'blur(4px)', color: '#fff', fontSize: 10, fontWeight: 700, padding: '3px 8px', borderRadius: 5, letterSpacing: '0.05em', zIndex: 2, pointerEvents: 'none' }}>Only {product.stock} left</span>
                                                            )}
                                                            <div className="shop-hover d-flex justify-content-center">
                                                                <WishlistButton productId={product.id} initialInWishlist={Boolean(product.wishlist)} className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0" onRequireAuth={() => openAuthModal()} onMessage={(m, t) => showToast(m, t)} />
                                                                <button className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0" onClick={() => addToCart(product)} disabled={product.stock === 0}>
                                                                    <i className="feather icon-feather-shopping-bag fs-15"></i>
                                                                </button>
                                                                <button className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0" onClick={() => setQuickViewSlug(product.slug)} aria-label="Quick view">
                                                                    <i className="feather icon-feather-eye fs-15"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div className="shop-footer pt-20px text-center">
                                                            <a className="text-white fs-17 fw-600 d-block mb-5px" href={`/product/${product.slug}`}>{product.name}</a>
                                                            <div className="fw-500 fs-15 lh-normal">
                                                                {product.is_offer ? (
                                                                    <><del className="me-5px opacity-6">{product.currency_price}</del><span style={{ color: 'var(--base-color)' }}>{product.discounted_price}</span></>
                                                                ) : <span>{product.currency_price}</span>}
                                                            </div>
                                                            {product.stock > 0 && product.stock <= 5 && (
                                                                <p style={{ margin: '6px 0 0', fontSize: 11, fontWeight: 700, color: '#f59e0b' }}>
                                                                    <i className="feather icon-feather-alert-circle" style={{ fontSize: 10, marginRight: 3 }}></i>
                                                                    Only {product.stock} left
                                                                </p>
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>

                                    {lastPage > 1 && (
                                        <nav className="d-flex justify-content-center mt-20px">
                                            <ul className="pagination pagination-style-01 m-0">
                                                <li className={`page-item ${currentPage <= 1 ? 'disabled' : ''}`}>
                                                    <button className="page-link bg-transparent border-0" onClick={() => changePage(currentPage - 1)}>Prev</button>
                                                </li>
                                                {visiblePages.map(page => (
                                                    <li key={page} className={`page-item ${page === currentPage ? 'active' : ''}`}>
                                                        <button className="page-link bg-transparent border-0" onClick={() => changePage(page)}>{page}</button>
                                                    </li>
                                                ))}
                                                <li className={`page-item ${currentPage >= lastPage ? 'disabled' : ''}`}>
                                                    <button className="page-link bg-transparent border-0" onClick={() => changePage(currentPage + 1)}>Next</button>
                                                </li>
                                            </ul>
                                        </nav>
                                    )}
                                </>
                            )}
                        </div>

                        {/* ══ Sidebar ══════════════════════════════════════ */}
                        <div className="col-xxl-2 col-lg-3 shop-sidebar">
                            <div className="mb-30px ui-sidebar-card">
                                <span className="ui-sidebar-title">Filter by categories</span>
                                <ul className="fs-15 shop-filter category-filter">
                                    <li>
                                        <a href={buildShopUrl({ category: null, page: null })} style={!categorySlug ? { color: 'var(--base-color)' } : {}}>All</a>
                                    </li>
                                    {categories.map((cat: any) => (
                                        <li key={cat.id}>
                                            <a href={buildShopUrl({ category: cat.slug, page: null })} style={categorySlug === cat.slug ? { color: 'var(--base-color)' } : {}}>{cat.name}</a>
                                            <span className="item-qty">{String(cat.products_count || cat.product_count || 0).padStart(2, '0')}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {brands.length > 0 && (
                                <div className="mb-30px ui-sidebar-card">
                                    <span className="ui-sidebar-title">Filter by brand</span>
                                    <ul className="fs-15 shop-filter category-filter">
                                        {brands.map((b: any) => (
                                            <li key={b.id}>
                                                <a href={buildShopUrl({ brand: b.slug, page: null })} style={brandSlugParam === b.slug ? { color: 'var(--base-color)' } : {}}>{b.name}</a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <div className="mb-30px ui-sidebar-card">
                                <span className="ui-sidebar-title">Filter by price</span>
                                <ul className="fs-15 shop-filter price-filter">
                                    {PRICE_RANGES.map(range => {
                                        const active = priceRange?.min === range.min && priceRange?.max === range.max;
                                        return (
                                            <li key={range.label}>
                                                <button onClick={() => setPriceRange(active ? null : range)} style={active ? { color: 'var(--base-color)', background: 'none', border: 'none', padding: 0 } : { background: 'none', border: 'none', padding: 0, color: 'inherit' }}>
                                                    {range.label}
                                                </button>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </div>

                            {latestProducts.length > 0 && currentArrival && (
                                <div className="mb-30px ui-sidebar-card">
                                    <div className="d-flex align-items-center justify-content-between mb-15px">
                                        <span className="ui-sidebar-title mb-0">New arrivals</span>
                                        <div className="d-flex align-items-center gap-2">
                                            <button className="new-arrivals-nav-btn" onClick={naPrev}>←</button>
                                            <button className="new-arrivals-nav-btn" onClick={naNext}>→</button>
                                        </div>
                                    </div>
                                    <div className="new-arrivals-item">
                                        <a href={`/product/${currentArrival.slug}`} className="new-arrivals-thumb">
                                            <Image alt={currentArrival.name} src={currentArrival.cover || '/images/demo-decor-store-product-01.jpg'} width={120} height={120} unoptimized />
                                        </a>
                                        <div className="new-arrivals-info">
                                            <a href={`/product/${currentArrival.slug}`}>{currentArrival.name}</a>
                                            <div className="price">{currentArrival.currency_price}</div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                    </div>
                </div>
            </section>

            {/* ── Mobile Filter Drawer ── */}
            {showMobileFilters && (
                <div style={{ position: 'fixed', inset: 0, zIndex: 9999 }}>
                    {/* Backdrop */}
                    <div onClick={() => setShowMobileFilters(false)} style={{ position: 'absolute', inset: 0, background: 'rgba(0,0,0,0.65)', backdropFilter: 'blur(4px)' }} />

                    {/* Sheet */}
                    <div style={{ position: 'absolute', bottom: 0, left: 0, right: 0, background: '#141414', borderRadius: '20px 20px 0 0', maxHeight: '88vh', display: 'flex', flexDirection: 'column', boxShadow: '0 -8px 40px rgba(0,0,0,0.6)' }}>

                        {/* Header — sticky */}
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 20px', borderBottom: '1px solid rgba(255,255,255,0.08)', flexShrink: 0 }}>
                            <span style={{ color: '#fff', fontWeight: 700, fontSize: 16 }}>
                                Filters
                                {[categorySlug, brandSlugParam, priceRange].filter(Boolean).length > 0 && (
                                    <span style={{ marginLeft: 8, background: 'var(--base-color)', color: '#000', borderRadius: '50%', width: 20, height: 20, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: 11, fontWeight: 800 }}>
                                        {[categorySlug, brandSlugParam, priceRange].filter(Boolean).length}
                                    </span>
                                )}
                            </span>
                            <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
                                {(categorySlug || brandSlugParam || priceRange) && (
                                    <button onClick={() => { applyShopParams({ category: null, brand: null, page: null }); setPriceRange(null); }} style={{ background: 'none', border: '1px solid rgba(255,255,255,0.15)', color: 'rgba(255,255,255,0.5)', fontSize: 12, padding: '4px 10px', borderRadius: 6, cursor: 'pointer', fontWeight: 600 }}>
                                        Clear all
                                    </button>
                                )}
                                <button onClick={() => setShowMobileFilters(false)} style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.5)', fontSize: 24, cursor: 'pointer', lineHeight: 1, padding: 0 }}>×</button>
                            </div>
                        </div>

                        {/* Scrollable body */}
                        <div style={{ overflowY: 'auto', flex: 1, padding: '20px 20px 8px' }}>

                            {/* Categories */}
                            <div style={{ marginBottom: 28 }}>
                                <p style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: 12 }}>Category</p>
                                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                                    {[{ id: 'all', slug: null, name: 'All' }, ...categories].map((cat: any) => {
                                        const active = cat.slug === null ? !categorySlug : categorySlug === cat.slug;
                                        return (
                                            <button key={cat.id} onClick={() => { applyShopParams({ category: cat.slug, page: null }); setShowMobileFilters(false); }}
                                                style={{ padding: '8px 16px', borderRadius: 20, border: '1px solid', borderColor: active ? 'var(--base-color)' : 'rgba(255,255,255,0.13)', background: active ? 'rgba(197,160,89,0.12)' : 'transparent', color: active ? 'var(--base-color)' : 'rgba(255,255,255,0.75)', fontSize: 13, fontWeight: 600, cursor: 'pointer' }}>
                                                {cat.name}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Brands */}
                            {brands.length > 0 && (
                                <div style={{ marginBottom: 28 }}>
                                    <p style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: 12 }}>Brand</p>
                                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                                        {brands.map((b: any) => {
                                            const active = brandSlugParam === b.slug;
                                            return (
                                                <button key={b.id} onClick={() => { applyShopParams({ brand: active ? null : b.slug, page: null }); setShowMobileFilters(false); }}
                                                    style={{ padding: '8px 16px', borderRadius: 20, border: '1px solid', borderColor: active ? 'var(--base-color)' : 'rgba(255,255,255,0.13)', background: active ? 'rgba(197,160,89,0.12)' : 'transparent', color: active ? 'var(--base-color)' : 'rgba(255,255,255,0.75)', fontSize: 13, fontWeight: 600, cursor: 'pointer' }}>
                                                    {b.name}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}

                            {/* Price */}
                            {PRICE_RANGES.length > 0 && (
                                <div style={{ marginBottom: 28 }}>
                                    <p style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: 12 }}>Price</p>
                                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                                        {PRICE_RANGES.map(range => {
                                            const active = priceRange?.min === range.min && priceRange?.max === range.max;
                                            return (
                                                <button key={range.label} onClick={() => setPriceRange(active ? null : range)}
                                                    style={{ padding: '8px 16px', borderRadius: 20, border: '1px solid', borderColor: active ? 'var(--base-color)' : 'rgba(255,255,255,0.13)', background: active ? 'rgba(197,160,89,0.12)' : 'transparent', color: active ? 'var(--base-color)' : 'rgba(255,255,255,0.75)', fontSize: 13, fontWeight: 600, cursor: 'pointer' }}>
                                                    {range.label}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Footer CTA — sticky */}
                        <div style={{ padding: '16px 20px', borderTop: '1px solid rgba(255,255,255,0.08)', flexShrink: 0 }}>
                            <button onClick={() => setShowMobileFilters(false)} style={{ width: '100%', padding: '15px', background: 'var(--base-color)', color: '#0a0a0a', fontWeight: 700, fontSize: 14, border: 'none', borderRadius: 12, cursor: 'pointer' }}>
                                Show Results {totalProducts > 0 ? `(${totalProducts})` : ''}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </main>

        {quickViewSlug && (
            <QuickViewModal slug={quickViewSlug} onClose={() => setQuickViewSlug(null)} />
        )}
    </>
    );
}

export default function ShopPageClient() {
    return (
        <Suspense fallback={<PageLoadingShell variant="grid" />}>
            <ShopContent />
        </Suspense>
    );
}
