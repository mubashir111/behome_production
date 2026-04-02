'use client';

import { useState, useEffect, Suspense } from 'react';
import Image from 'next/image';
import { usePathname, useRouter, useSearchParams } from 'next/navigation';
import { apiFetch } from '@/lib/api';
import WishlistButton from '@/components/WishlistButton';
import PageLoadingShell from '@/components/PageLoadingShell';
import { useToast } from '@/components/ToastProvider';

const PRICE_RANGES = [
    { label: 'Under $25', min: 0, max: 25 },
    { label: '$25 to $50', min: 25, max: 50 },
    { label: '$50 to $100', min: 50, max: 100 },
    { label: '$100 to $200', min: 100, max: 200 },
    { label: '$200 & Above', min: 200, max: Infinity },
];

function ShopContent() {
    const router = useRouter();
    const pathname = usePathname();
    const searchParams = useSearchParams();
    const categorySlug = searchParams.get('category');
    const brandSlugParam = searchParams.get('brand');
    const searchQuery = searchParams.get('search') || '';
    const initialPage = Number.parseInt(searchParams.get('page') || '1', 10);

    const [products, setProducts] = useState<any[]>([]);
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
    const [priceRange, setPriceRange] = useState<typeof PRICE_RANGES[0] | null>(null);
    const [searchInput, setSearchInput] = useState(searchQuery);
    const [naIndex, setNaIndex] = useState(0);
    const [sidebarOpen, setSidebarOpen] = useState(false);

    /* ── Add to cart ─────────────────────────────────────── */
    const addToCart = async (product: any) => {
        try {
            const token = localStorage.getItem('token');
            if (!token) { showToast('Please login to add items to cart', 'error'); return; }
            const res = await apiFetch('/cart', {
                method: 'POST',
                body: JSON.stringify({ product_id: product.id, quantity: 1, variation_id: null }),
            });
            if (res.status) {
                showToast(`Added ${product.name} to cart!`, 'success');
                window.dispatchEvent(new CustomEvent('cart:updated', {}));
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

    useEffect(() => {
        const nextPage = Number.parseInt(searchParams.get('page') || '1', 10);
        setCurrentPage(Number.isNaN(nextPage) ? 1 : Math.max(nextPage, 1));
        setSearchInput(searchParams.get('search') || '');
    }, [searchParams]);

    useEffect(() => {
        setCurrentPage(1);
    }, [categorySlug, brandSlugParam, searchQuery, sortBy, priceRange]);

    // Lock body scroll when mobile sidebar is open
    useEffect(() => {
        document.body.style.overflow = sidebarOpen ? 'hidden' : '';
        return () => { document.body.style.overflow = ''; };
    }, [sidebarOpen]);

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
        setSidebarOpen(false); // close mobile drawer on filter apply
    };

    /* ── Products (re-fetch on filter change) ────────────── */
    useEffect(() => {
        setLoading(true);
        let url = `/products?per_page=24&page=${currentPage}`;
        if (categorySlug) url += `&category_slug=${categorySlug}`;
        if (brandSlugParam) url += `&brand_slug=${brandSlugParam}`;
        if (searchQuery) url += `&search=${encodeURIComponent(searchQuery)}`;
        if (priceRange) {
            url += `&min_price=${priceRange.min}`;
            if (priceRange.max !== Infinity) {
                url += `&max_price=${priceRange.max}`;
            }
        }
        if (sortBy === '1') url += '&sort=popular';
        if (sortBy === '3') url += '&sort=latest';

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
                setCurrentPage(pagination.current_page || currentPage);
                setLastPage(pagination.last_page || 1);
            })
            .catch(() => {
                setProducts([]);
                setTotalProducts(0);
                setLastPage(1);
            })
            .finally(() => setLoading(false));
    }, [categorySlug, brandSlugParam, searchQuery, sortBy, priceRange, currentPage]);

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

    /* ── New arrivals slider (1 at a time) ───────────────── */
    const naPrev = () => setNaIndex(i => (i - 1 + latestProducts.length) % latestProducts.length);
    const naNext = () => setNaIndex(i => (i + 1) % latestProducts.length);
    const currentArrival = latestProducts[naIndex] || null;

    /* ── Grid class helper ───────────────────────────────── */
    const gridClass = viewMode === '2'
        ? 'row-cols-1 row-cols-md-2 row-cols-xl-2'
        : viewMode === '3'
            ? 'row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-3'
            : viewMode === 'list'
                ? 'row-cols-1'
                : /* 4 */ 'row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4';

    return (
        <main className="no-layout-pad shop-main-container">
            <section className="shop-breadcrumb-section ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
                <div className="container-fluid">
                    <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                        <ul>
                            <li><a href="/" className="shop-nav-link-no-decor">Home</a></li>
                            <li>Shop</li>
                        </ul>
                    </div>
                </div>
            </section>
            <section className="page-shell page-shell-tight shop-main-section ps-6 pe-6 lg-ps-3 lg-pe-3 sm-ps-0 sm-pe-0">
                <div className="container-fluid">
                    <div className="row flex-row-reverse">

                        {/* ══ Product grid ════════════════════════════════ */}
                        <div className="shop-products-col col-xxl-10 col-lg-9 col-12 ps-5 md-ps-15px md-mb-60px">




                            {/* Toolbar: search + grid icons + sort */}
                            <div className="toolbar-wrapper border-bottom border-color-extra-medium-gray d-flex flex-wrap align-items-center gap-2 w-100 mb-40px md-mb-30px pb-15px">
                                {/* Mobile filter toggle */}
                                <button type="button" className="shop-mobile-filter-btn" onClick={() => setSidebarOpen(true)} aria-label="Open filters">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M3 6h18M6 12h12M9 18h6" /></svg>
                                    Filters
                                </button>
                                {/* Grid/list toggle icons */}
                                <div className="d-none d-md-flex align-items-center gap-1 me-5px" role="group" aria-label="Product grid layout">
                                    {([['2', '/images/shop-two-column.svg', '2 columns'], ['3', '/images/shop-three-column.svg', '3 columns'], ['4', '/images/shop-four-column.svg', '4 columns'], ['list', '/images/shop-list.svg', 'List view']] as const).map(([mode, src, label]) => (
                                        <button
                                            key={mode}
                                            type="button"
                                            aria-label={label}
                                            aria-pressed={viewMode === mode}
                                            title={label}
                                            onClick={() => setViewMode(mode)}
                                            className={`shop-view-mode-btn ${viewMode === mode ? 'active' : ''}`}
                                        >
                                            <Image alt="" aria-hidden="true" className="shop-view-mode-icon" src={src} width={18} height={18} />
                                        </button>
                                    ))}
                                </div>
                                {/* Inline search */}
                                <form className="d-flex flex-grow-1 gap-2 align-items-center shop-search-form" onSubmit={submitSearch} role="search">
                                    <label htmlFor="shop-search" className="visually-hidden">Search products</label>
                                    <input
                                        id="shop-search"
                                        type="search"
                                        name="search"
                                        autoComplete="off"
                                        spellCheck={false}
                                        placeholder="Search products…"
                                        value={searchInput}
                                        onChange={event => setSearchInput(event.target.value)}
                                        className="form-control form-control-sm shop-search-input"
                                    />
                                    <button type="submit" className="btn btn-base-color btn-small btn-rounded text-dark-gray shop-btn-nowrap">Search</button>
                                    {searchQuery && (
                                        <button type="button" className="btn btn-transparent-white btn-small btn-rounded shop-btn-nowrap" onClick={() => { setSearchInput(''); setCurrentPage(1); applyShopParams({ search: null, page: null }, { scroll: false }); }}>Clear</button>
                                    )}
                                </form>
                                {/* Results count + Sort */}
                                <div className="ms-auto d-flex align-items-center gap-3">
                                    {!loading && totalProducts > 0 && (
                                        <span className="text-white opacity-6 fs-13 shop-results-count">
                                            {products.length} of {totalProducts} results
                                        </span>
                                    )}
                                    <label htmlFor="shop-sort" className="visually-hidden">Sort products</label>
                                    <select
                                        id="shop-sort"
                                        value={sortBy}
                                        onChange={e => setSortBy(e.target.value)}
                                        className="form-select form-select-sm border-0 shop-sort-select"
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
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => {
                                            setSearchInput('');
                                            applyShopParams({ search: null, page: null }, { scroll: false });
                                        }}>
                                            Search: {searchQuery} <span className="shop-filter-pill-opacity">×</span>
                                        </button>
                                    )}
                                    {categorySlug && (
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => applyShopParams({ category: null, page: null }, { scroll: false })}>
                                            Category: {categorySlug} <span className="shop-filter-pill-opacity">×</span>
                                        </button>
                                    )}
                                    {brandSlugParam && (
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => applyShopParams({ brand: null, page: null }, { scroll: false })}>
                                            Brand: {brandSlugParam} <span className="shop-filter-pill-opacity">×</span>
                                        </button>
                                    )}
                                    {priceRange && (
                                        <button className="filter-chip ui-filter-chip text-white border-0" onClick={() => setPriceRange(null)}>
                                            {priceRange.label} <span className="shop-filter-pill-opacity">×</span>
                                        </button>
                                    )}
                                </div>
                            )}

                            {/* Product grid */}
                            {loading ? (
                                <div className={`row ${gridClass}`}>
                                    {[...Array(8)].map((_, i) => (
                                        <div key={i} className="col mb-45px">
                                            <div className="shop-loading-placeholder-img" style={{ height: viewMode === 'list' ? 100 : 260 }} />
                                            <div className="shop-loading-placeholder-title" />
                                            <div className="shop-loading-placeholder-sub" />
                                        </div>
                                    ))}
                                </div>
                            ) : products.length === 0 ? (
                                <div className="ui-panel ui-empty-state text-white opacity-6 fs-17">No products found.</div>
                            ) : (
                                <>
                                    <div className={`row ${gridClass} justify-content-center`}>
                                        {products.map(product => (
                                            <div key={product.id} className="col mb-45px">
                                                {viewMode === 'list' ? (
                                                    /* ── List layout ── */
                                                    <div className="shop-box d-flex align-items-center gap-4 pb-20px shop-list-item-border">
                                                        <a href={`/product/${product.slug}`} className="shop-list-thumb-wrapper">
                                                            <Image alt={product.name} src={product.cover || '/images/demo-decor-store-product-01.jpg'} width={110} height={130} unoptimized className="shop-list-thumb-img" />
                                                        </a>
                                                        <div className="flex-grow-1 shop-flex-min-width-0">
                                                            <a className="text-white fs-16 fw-600 d-block mb-5px text-truncate" href={`/product/${product.slug}`}>{product.name}</a>
                                                            <div className="fw-500 fs-15 mb-15px">
                                                                {product.is_offer ? (
                                                                    <><del className="me-5px opacity-6">{product.currency_price}</del><span className="shop-price-accent">{product.discounted_price}</span></>
                                                                ) : <span>{product.currency_price}</span>}
                                                            </div>
                                                            <div className="d-flex gap-2">
                                                                <button
                                                                    aria-label={`Add ${product.name} to cart`}
                                                                    className="btn btn-base-color btn-small btn-rounded text-dark-gray shop-touch-manipulation"
                                                                    onClick={e => { e.preventDefault(); addToCart(product); }}>
                                                                    Add to Cart
                                                                </button>
                                                                <WishlistButton
                                                                    productId={product.id}
                                                                    initialInWishlist={Boolean(product.wishlist)}
                                                                    className="bg-dark-gray w-35px h-35px text-white d-flex align-items-center justify-content-center rounded-circle border-0"
                                                                    onRequireAuth={() => showToast('Please login to save items to wishlist', 'error')}
                                                                    onMessage={(msg, type) => showToast(msg, type)}
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    /* ── Grid layout ── */
                                                    <div className="shop-box pb-25px">
                                                        <div className="shop-image">
                                                            <a href={`/product/${product.slug}`}>
                                                                <Image alt={product.name} src={product.cover || '/images/demo-decor-store-product-01.jpg'} width={640} height={720} unoptimized className="shop-grid-img" />
                                                                {product.is_offer && <span className="lable hot">Offer</span>}
                                                                <div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
                                                            </a>
                                                            <div className="shop-hover d-flex justify-content-center">
                                                                <WishlistButton
                                                                    productId={product.id}
                                                                    initialInWishlist={Boolean(product.wishlist)}
                                                                    className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0"
                                                                    onRequireAuth={() => showToast('Please login to save items to wishlist', 'error')}
                                                                    onMessage={(msg, type) => showToast(msg, type)}
                                                                />
                                                                <button
                                                                    aria-label={`Add ${product.name} to cart`}
                                                                    className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0 shop-touch-manipulation"
                                                                    onClick={e => { e.preventDefault(); addToCart(product); }}>
                                                                    <i className="feather icon-feather-shopping-bag fs-15" aria-hidden="true"></i>
                                                                </button>
                                                                <a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom"
                                                                    href={`/product/${product.slug}`} aria-label={`Quick view ${product.name}`}>
                                                                    <i className="feather icon-feather-eye fs-15" aria-hidden="true"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div className="shop-footer text-center pt-20px">
                                                            <a className="text-white fs-17 fw-600 d-block mb-5px" href={`/product/${product.slug}`}>{product.name}</a>
                                                            <div className="fw-500 fs-15 lh-normal">
                                                                {product.is_offer ? (
                                                                    <><del className="me-5px opacity-6">{product.currency_price}</del><span className="shop-price-accent">{product.discounted_price}</span></>
                                                                ) : <span>{product.currency_price}</span>}
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>

                                    {lastPage > 1 && (
                                        <nav aria-label="Shop pagination" className="d-flex justify-content-center mt-20px">
                                            <ul className="pagination pagination-style-01 m-0">
                                                <li className={`page-item ${currentPage <= 1 ? 'disabled' : ''}`}>
                                                    <button className="page-link bg-transparent border-0" onClick={() => changePage(currentPage - 1)} disabled={currentPage <= 1}>
                                                        Prev
                                                    </button>
                                                </li>
                                                {visiblePages.map(page => (
                                                    <li key={page} className={`page-item ${page === currentPage ? 'active' : ''}`}>
                                                        <button className="page-link bg-transparent border-0" onClick={() => changePage(page)}>
                                                            {page}
                                                        </button>
                                                    </li>
                                                ))}
                                                <li className={`page-item ${currentPage >= lastPage ? 'disabled' : ''}`}>
                                                    <button className="page-link bg-transparent border-0" onClick={() => changePage(currentPage + 1)} disabled={currentPage >= lastPage}>
                                                        Next
                                                    </button>
                                                </li>
                                            </ul>
                                        </nav>
                                    )}
                                </>
                            )}
                        </div>

                        {/* ══ Sidebar backdrop (mobile only) ══════════════ */}
                        <div className={`shop-sidebar-backdrop${sidebarOpen ? ' is-open' : ''}`} onClick={() => setSidebarOpen(false)} aria-hidden="true" />

                        {/* ══ Sidebar ══════════════════════════════════════ */}
                        <div className={`col-xxl-2 col-lg-3 shop-sidebar${sidebarOpen ? ' is-open' : ''}`}>
                            {/* Mobile sidebar header (close button) */}
                            <div className="shop-sidebar-close d-lg-none">
                                <span className="text-white fw-600 fs-16">Filters</span>
                                <button type="button" className="shop-sidebar-close-btn" onClick={() => setSidebarOpen(false)} aria-label="Close filters">×</button>
                            </div>

                            {/* Categories */}
                            <div className="mb-30px ui-sidebar-card">
                                <span className="ui-sidebar-title">Filter by categories</span>
                                <ul className="fs-15 shop-filter category-filter">
                                    <li>
                                        <a href={buildShopUrl({ category: null, page: null })} className={!categorySlug ? 'active' : ''}>
                                            <span className="product-cb product-category-cb"></span>All
                                        </a>
                                    </li>
                                    {categories.map((cat: any) => (
                                        <li key={cat.id}>
                                            <a href={buildShopUrl({ category: cat.slug, page: null })}
                                                className={categorySlug === cat.slug ? 'active' : ''}>
                                                <span className="product-cb product-category-cb"></span>{cat.name}
                                            </a>
                                            {(cat.products_count || cat.product_count) > 0 && (
                                                <span className="item-qty">{String(cat.products_count || cat.product_count).padStart(2, '0')}</span>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {/* Brands */}
                            {brands.length > 0 && (
                                <div className="mb-30px ui-sidebar-card">
                                    <span className="ui-sidebar-title">Filter by brand</span>
                                    <ul className="fs-15 shop-filter category-filter">
                                        {brands.map((b: any) => (
                                            <li key={b.id}>
                                                <a href={buildShopUrl({ brand: b.slug, page: null })}
                                                    className={brandSlugParam === b.slug ? 'active' : ''}>
                                                    <span className="product-cb product-category-cb"></span>{b.name}
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {/* Price range */}
                            <div className="mb-30px ui-sidebar-card">
                                <span className="ui-sidebar-title">Filter by price</span>
                                <ul className="fs-15 shop-filter price-filter">
                                    {PRICE_RANGES.map(range => {
                                        const active = priceRange?.min === range.min && priceRange?.max === range.max;
                                        return (
                                            <li key={range.label}>
                                                <button
                                                    className={`shop-filter-btn ${active ? 'active' : ''}`}
                                                    onClick={() => { setPriceRange(active ? null : range); setSidebarOpen(false); }}
                                                >
                                                    <span className="product-cb product-category-cb"></span>
                                                    {range.label}
                                                </button>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </div>

                            {/* New arrivals slider — 1 product at a time */}
                            {latestProducts.length > 0 && currentArrival && (
                                <div className="mb-30px ui-sidebar-card">
                                    <div className="d-flex align-items-center justify-content-between mb-15px">
                                        <span className="ui-sidebar-title mb-0">New arrivals</span>
                                        <div className="d-flex align-items-center gap-2">
                                            <button className="new-arrivals-nav-btn" onClick={naPrev} aria-label="Previous">←</button>
                                            <button className="new-arrivals-nav-btn" onClick={naNext} aria-label="Next">→</button>
                                        </div>
                                    </div>

                                    {/* Single product display */}
                                    <div className="new-arrivals-item">
                                        <a href={`/product/${currentArrival.slug}`} className="new-arrivals-thumb">
                                            <Image
                                                alt={currentArrival.name}
                                                src={currentArrival.cover || '/images/demo-decor-store-product-01.jpg'}
                                                width={120}
                                                height={120}
                                                unoptimized
                                            />
                                        </a>
                                        <div className="new-arrivals-info">
                                            <a href={`/product/${currentArrival.slug}`}>{currentArrival.name}</a>
                                            <div className="price">
                                                {currentArrival.is_offer ? (
                                                    <>
                                                        <del className="shop-arrival-del">{currentArrival.currency_price}</del>
                                                        {currentArrival.discounted_price}
                                                    </>
                                                ) : currentArrival.currency_price}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Dot indicators */}
                                    {latestProducts.length > 1 && (
                                        <div className="new-arrivals-dots mt-15px">
                                            {latestProducts.map((_, i) => (
                                                <button
                                                    key={i}
                                                    className={`new-arrivals-dot${i === naIndex ? ' active' : ''}`}
                                                    onClick={() => setNaIndex(i)}
                                                    aria-label={`Go to product ${i + 1}`}
                                                />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}

                        </div>{/* /sidebar */}

                    </div>
                </div>
            </section>

            <div className="cookie-message bg-dark-gray border-radius-8px" id="cookies-model">
                <div className="cookie-description fs-14 text-white mb-20px lh-22">We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking "Allow cookies" you consent to our use of cookies.</div>
                <div className="cookie-btn">
                    <a aria-label="btn" className="btn btn-transparent-white border-1 border-color-transparent-white-light btn-very-small btn-switch-text btn-rounded w-100 mb-15px" href="/shop#">
                        <span><span className="btn-double-text" data-text="Cookie policy">Cookie policy</span></span>
                    </a>
                    <a aria-label="text" className="btn btn-white btn-very-small btn-switch-text btn-box-shadow accept_cookies_btn btn-rounded w-100" data-accept-btn="" href="/shop#">
                        <span><span className="btn-double-text" data-text="Allow cookies">Allow cookies</span></span>
                    </a>
                </div>
            </div>
            <div className="scroll-progress d-none d-xxl-block">
                <a aria-label="scroll" className="scroll-top" href="/shop#">
                    <span className="scroll-text">Scroll</span><span className="scroll-line"><span className="scroll-point"></span></span>
                </a>
            </div>
        </main>
    );
}

export default function ShopPageClient() {
    return (
        <Suspense fallback={<PageLoadingShell variant="grid" />}>
            <ShopContent />
        </Suspense>
    );
}
