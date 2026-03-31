'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import ProductGrid from '@/components/ProductGrid';
import LoadingSkeleton from '@/components/LoadingSkeleton';
import ProductDescriptionBlocks from '@/components/ProductDescriptionBlocks';
import { useToast } from '@/components/ToastProvider';
import { useCurrency } from '@/components/SettingsProvider';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Variation {
    id: number;
    product_attribute_name: string;
    product_attribute_option_name: string;
    price: string;
    old_price?: string;
    currency_price: string;
    sku: string;
    stock: number;
    children: Variation[];
    media?: Array<{ original_url: string; url: string }>;
}

interface VariationLeaf extends Variation {
    optionMap: Record<string, string>;
}

interface Review {
    id: number;
    name: string;
    star: number;
    review: string;
    created_at: string;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

/** Collect all unique options per attribute name (from entire tree) */
function collectOptions(vars: Variation[]): Record<string, string[]> {
    const map: Record<string, Set<string>> = {};
    const walk = (nodes: Variation[]) => {
        for (const n of nodes) {
            if (!map[n.product_attribute_name]) map[n.product_attribute_name] = new Set();
            map[n.product_attribute_name].add(n.product_attribute_option_name);
            if (n.children?.length) walk(n.children);
        }
    };
    walk(vars);
    return Object.fromEntries(Object.entries(map).map(([k, v]) => [k, Array.from(v)]));
}

function flattenVariationLeaves(vars: Variation[], trail: Record<string, string> = {}): VariationLeaf[] {
    const leaves: VariationLeaf[] = [];

    for (const variation of vars) {
        const nextTrail = variation.product_attribute_name
            ? { ...trail, [variation.product_attribute_name]: variation.product_attribute_option_name }
            : trail;

        if (!variation.children || variation.children.length === 0) {
            leaves.push({ ...variation, optionMap: nextTrail });
            continue;
        }

        leaves.push(...flattenVariationLeaves(variation.children, nextTrail));
    }

    return leaves;
}

// ─── Stars helper ─────────────────────────────────────────────────────────────

function StarDisplay({ rating, size = 'sm' }: { rating: number; size?: 'sm' | 'lg' }) {
    const cls = size === 'lg' ? 'fs-18' : 'fs-13';
    return (
        <span className={cls}>
            {[1, 2, 3, 4, 5].map(i => (
                <i key={i} className={`bi bi-star-fill ${i <= Math.round(rating) ? 'text-golden-yellow' : 'text-extra-medium-gray'}`} />
            ))}
        </span>
    );
}

// ─── Main Component ───────────────────────────────────────────────────────────

export default function ProductPageClient({ params }: { params: { slug: string } }) {
    const { slug } = params;

    const [product, setProduct] = useState<any>(null);
    const [relatedProducts, setRelatedProducts] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [quantity, setQuantity] = useState(1);
    const { showToast } = useToast();
    const { formatAmount } = useCurrency();
    const [activeImageIndex, setActiveImageIndex] = useState(0);

    // Variations
    const [allVariations, setAllVariations] = useState<Variation[]>([]);
    const [attrOptions, setAttrOptions] = useState<Record<string, string[]>>({});
    const [selected, setSelected] = useState<Record<string, string>>({});
    const [activeVariation, setActiveVariation] = useState<Variation | null>(null);
    const [colorVariantMap, setColorVariantMap] = useState<Record<string, Variation>>({});

    // Wishlist
    const [inWishlist, setInWishlist] = useState(false);
    const [wishlistLoading, setWishlistLoading] = useState(false);

    // Reviews
    const [reviews, setReviews] = useState<Review[]>([]);
    const [reviewForm, setReviewForm] = useState({ star: 5, review: '' });
    const [submittingReview, setSubmittingReview] = useState(false);
    const [reviewMessage, setReviewMessage] = useState('');
    const [activeTab, setActiveTab] = useState<'description' | 'reviews' | 'additional_info' | 'shipping'>('description');
    const [hoverStar, setHoverStar] = useState(0);
    const [showReviews, setShowReviews] = useState(true);

    // ── Fetch product & related ──────────────────────────────────────────────
    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                setError(null);
                const productResponse = await apiFetch(`/products/${slug}`);
                if (productResponse?.status) {
                    const p = productResponse.data;
                    setProduct(p);
                    setReviews(p.reviews || []);

                    // Fetch variations
                    try {
                        const varRes = await apiFetch(`/frontend/product/all-variation/${slug}`);
                        const vars: Variation[] = varRes?.data || [];
                        setAllVariations(vars);
                        const opts = collectOptions(vars);
                        setAttrOptions(opts);
                        
                        // Build color variant map for fast image lookup
                        const colorMap: Record<string, Variation> = {};
                        const buildColorMap = (variations: Variation[]) => {
                            for (const v of variations) {
                                if (v.product_attribute_name === 'Color' && v.product_attribute_option_name) {
                                    colorMap[v.product_attribute_option_name] = v;
                                }
                                if (v.children?.length) buildColorMap(v.children);
                            }
                        };
                        buildColorMap(vars);
                        setColorVariantMap(colorMap);
                        
                        const defaults: Record<string, string> = {};
                        const firstLeaf = flattenVariationLeaves(vars)[0];

                        if (firstLeaf) {
                            Object.assign(defaults, firstLeaf.optionMap);
                        } else {
                            Object.entries(opts).forEach(([attr, options]) => {
                                if (options.length > 0) defaults[attr] = options[0];
                            });
                        }

                        setSelected(defaults);
                    } catch { /* no variations */ }

                    // Fetch related
                    if (p.category?.slug) {
                        const rel = await apiFetch(`/products?category_slug=${p.category.slug}&per_page=4`);
                        setRelatedProducts((rel?.data?.data || []).filter((r: any) => r.slug !== slug));
                    }

                    // Check wishlist status
                    const token = localStorage.getItem('token');
                    if (token) {
                        try {
                            const wl = await apiFetch('/frontend/wishlist');
                            const ids = (wl?.data || []).map((w: any) => w.product_id ?? w.product?.id ?? w.id);
                            setInWishlist(ids.includes(p.id));
                        } catch { /* not logged in or error */ }
                    }
                } else {
                    setError('Product not found');
                }
            } catch (err: any) {
                setError(err.message || 'Failed to load product');
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [slug]);

    useEffect(() => {
        setActiveImageIndex(0);
    }, [product?.id]);

    // ── Sync active variation when selection changes ─────────────────────────
    useEffect(() => {
        if (!allVariations.length) return;
        const attrNames = Object.keys(attrOptions);
        if (attrNames.length === 0) return;

        const leaves = flattenVariationLeaves(allVariations);
        const match = leaves.find((leaf) =>
            attrNames.every((attrName) => leaf.optionMap[attrName] === selected[attrName])
        );
        setActiveVariation(match || null);
    }, [selected, allVariations, attrOptions]);

    // ── Displayed price ──────────────────────────────────────────────────────
    const displayPrice = activeVariation
        ? formatAmount(parseFloat(activeVariation.price) || 0)
        : formatAmount(parseFloat(product?.price) || 0);
    const displayOldPrice = activeVariation
        ? (activeVariation.old_price ? formatAmount(parseFloat(activeVariation.old_price) || 0) : null)
        : (product?.is_offer && product?.old_price ? formatAmount(parseFloat(product.old_price) || 0) : null);
    const displaySku = activeVariation?.sku || product?.sku || '';
    const displayStock = activeVariation != null ? activeVariation.stock : (product?.stock ?? null);
    const canPurchase = product?.can_purchasable === 5;
    const stockOk = canPurchase && (displayStock === null || displayStock > 0);
    const galleryImages: string[] = product?.images?.length
        ? product.images
        : [product?.image || product?.cover || '/images/demo-decor-store-product-01.jpg'];
    const plainDescription = (product?.description || '')
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    const shortDescription = plainDescription.slice(0, 220);
    const discountPercent = displayOldPrice && product?.old_price
        ? Math.max(
            0,
            Math.round(
                (((parseFloat(product.old_price) || 0) - (parseFloat(activeVariation?.price || product.price) || 0))
                    / Math.max(parseFloat(product.old_price) || 1, 1)) * 100
            )
        )
        : 0;

    const safeActiveImageIndex = Math.min(activeImageIndex, Math.max(galleryImages.length - 1, 0));
    const activeImage = galleryImages[safeActiveImageIndex] || '/images/demo-decor-store-product-01.jpg';

    const goToPrevImage = () => {
        setActiveImageIndex((current) => (current === 0 ? galleryImages.length - 1 : current - 1));
    };

    const goToNextImage = () => {
        setActiveImageIndex((current) => (current === galleryImages.length - 1 ? 0 : current + 1));
    };

    useEffect(() => {
        if (galleryImages.length <= 1) {
            return;
        }

        const handleKeyNavigation = (event: KeyboardEvent) => {
            if (event.key === 'ArrowLeft') {
                goToPrevImage();
            }

            if (event.key === 'ArrowRight') {
                goToNextImage();
            }
        };

        window.addEventListener('keydown', handleKeyNavigation);

        return () => window.removeEventListener('keydown', handleKeyNavigation);
    }, [galleryImages.length]);

    // ── Add to cart ──────────────────────────────────────────────────────────
    const addToCart = async (redirectToCart = false) => {
        const token = localStorage.getItem('token');
        if (!token) { notify('Please login to add items to cart', 'error'); return false; }
        try {
            const res = await apiFetch('/cart', {
                method: 'POST',
                body: JSON.stringify({
                    product_id: product.id,
                    quantity,
                    variation_id: activeVariation?.id || null,
                }),
            });
            if (res.status) {
                notify(`${product.name} added to cart!`, 'success');
                window.dispatchEvent(new CustomEvent('cart:updated', {}));
                if (redirectToCart) {
                    window.location.href = '/cart';
                }
                return true;
            } else {
                notify(res.message || 'Failed to add to cart', 'error');
                return false;
            }
        } catch (err: any) {
            notify(err.message || 'An error occurred', 'error');
            return false;
        }
    };

    const buyNow = async () => {
        await addToCart(true);
    };

    const shareProduct = async () => {
        const shareData = {
            title: product.name,
            text: shortDescription || product.name,
            url: typeof window !== 'undefined' ? window.location.href : '',
        };

        try {
            if (navigator.share) {
                await navigator.share(shareData);
                return;
            }

            if (shareData.url) {
                await navigator.clipboard.writeText(shareData.url);
                notify('Product link copied to clipboard', 'success');
            }
        } catch {
            // ignore cancelled shares
        }
    };

    // ── Wishlist toggle ──────────────────────────────────────────────────────
    const toggleWishlist = async () => {
        const token = localStorage.getItem('token');
        if (!token) { notify('Please login to save to wishlist', 'error'); return; }
        setWishlistLoading(true);
        // Optimistically flip the heart immediately so UI feels instant
        const wasInWishlist = inWishlist;
        setInWishlist(!wasInWishlist);
        try {
            await apiFetch('/frontend/wishlist/toggle', {
                method: 'POST',
                body: JSON.stringify({ product_id: product.id, toggle: !wasInWishlist }),
            });
            notify(wasInWishlist ? 'Removed from wishlist' : 'Added to wishlist', 'success');
        } catch (err: any) {
            // Revert on failure
            setInWishlist(wasInWishlist);
            notify(err.message || 'Failed to update wishlist', 'error');
        } finally {
            setWishlistLoading(false);
        }
    };

    // ── Submit review ────────────────────────────────────────────────────────
    const submitReview = async (e: React.FormEvent) => {
        e.preventDefault();
        const token = localStorage.getItem('token');
        if (!token) { setReviewMessage('Please login to leave a review'); return; }
        if (!reviewForm.review.trim()) { setReviewMessage('Please write a review'); return; }
        setSubmittingReview(true);
        setReviewMessage('');
        try {
            const res = await apiFetch('/frontend/product-review', {
                method: 'POST',
                body: JSON.stringify({ product_id: product.id, star: reviewForm.star, review: reviewForm.review }),
            });
            const reviewData = res.data || res;
            if (reviewData?.id) {
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                setReviews(prev => [{
                    id: reviewData.id,
                    name: user.name || 'You',
                    star: reviewForm.star,
                    review: reviewForm.review,
                    created_at: 'Just now',
                }, ...prev]);
                setReviewForm({ star: 5, review: '' });
                setReviewMessage('Review submitted successfully!');
                setActiveTab('reviews');
            } else {
                setReviewMessage(res.message || 'Failed to submit review');
            }
        } catch (err: any) {
            setReviewMessage(err.message || 'An error occurred');
        } finally {
            setSubmittingReview(false);
        }
    };

    const notify = (msg: string, type: 'success' | 'error') => showToast(msg, type);

    // ── Loading / Error states ───────────────────────────────────────────────
    if (loading) {
        return (
            <main>
                <section className="page-shell page-shell-tight">
                    <div className="container">
                        <div className="row">
                            <div className="col-lg-6 md-mb-40px"><LoadingSkeleton type="card" /></div>
                            <div className="col-lg-5 offset-lg-1"><LoadingSkeleton type="card" /></div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (error || !product) {
        return (
            <main>
                <section className="page-shell page-shell-tight">
                    <div className="container text-center ui-panel ui-empty-state">
                        <h2 className="text-white mb-20px">Product Not Found</h2>
                        <p className="mb-20px">{error}</p>
                        <a href="/shop" className="btn btn-base-color btn-medium btn-round-edge">Back to Shop</a>
                    </div>
                </section>
            </main>
        );
    }

    const attrNames = Object.keys(attrOptions);

    return (
        <main className="no-layout-pad" style={{ paddingTop: '100px' }}>
            {/* ── Breadcrumb ─────────────────────────────────────────────── */}
            {/* Breadcrumb Section — Fixed top gap by removing top-space-margin */}
            <section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
                <div className="container-fluid">
                    <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                        <ul>
                            <li><a href="/" style={{ textDecoration: 'none' }}>Home</a></li>
                            <li><a href="/shop" style={{ textDecoration: 'none' }}>Shop</a></li>
                            {product.category?.name && <li><a href={`/shop?category=${product.category.slug}`} style={{ textDecoration: 'none' }}>{product.category.name}</a></li>}
                            <li>{product.name}</li>
                        </ul>
                    </div>
                </div>
            </section>


            {/* ── Product detail ─────────────────────────────────────────── */}
            <section className="page-shell page-shell-tight">
                <div className="container">
                    <div className="row g-5 align-items-start">
                        <div className="col-xl-7 col-lg-7">
                            <div className="d-flex flex-column flex-md-row gap-20px">
                                {galleryImages.length > 1 && (
                                    <div
                                        className="d-flex d-md-grid gap-12px order-2 order-md-1"
                                        style={{ gridAutoRows: '84px', width: '84px', minWidth: '84px', overflowX: 'auto' }}
                                    >
                                        {galleryImages.map((img: string, index: number) => {
                                            const isActive = index === safeActiveImageIndex;

                                            return (
                                                <button
                                                    key={`${img}-${index}`}
                                                    type="button"
                                                    onClick={() => setActiveImageIndex(index)}
                                                    aria-label={`Preview image ${index + 1}`}
                                                    style={{
                                                        width: '84px',
                                                        height: '84px',
                                                        minWidth: '84px',
                                                        borderRadius: '10px',
                                                        overflow: 'hidden',
                                                        padding: 0,
                                                        border: isActive ? '2px solid #ff7a1a' : '1px solid rgba(255,255,255,0.12)',
                                                        background: '#0f0f0f',
                                                        boxShadow: isActive ? '0 0 0 3px rgba(255,122,26,0.16)' : 'none',
                                                        opacity: isActive ? 1 : 0.7,
                                                    }}
                                                >
                                                    <Image
                                                        alt={`${product.name} thumbnail ${index + 1}`}
                                                        src={img}
                                                        width={168}
                                                        height={168}
                                                        unoptimized
                                                        style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}
                                                    />
                                                </button>
                                            );
                                        })}
                                    </div>
                                )}

                                <div className="flex-grow-1 position-relative order-1 order-md-2">
                                    <div
                                        style={{
                                            position: 'relative',
                                            borderRadius: '18px',
                                            overflow: 'hidden',
                                            background: '#141414',
                                            border: '1px solid rgba(255,255,255,0.08)',
                                            minHeight: '620px',
                                        }}
                                    >
                                        <Image
                                            alt={`${product.name} ${safeActiveImageIndex + 1}`}
                                            src={activeImage}
                                            width={1200}
                                            height={1200}
                                            unoptimized
                                            className="w-100"
                                            style={{ display: 'block', width: '100%', height: '100%', minHeight: '620px', objectFit: 'cover' }}
                                        />

                                        {galleryImages.length > 1 && (
                                            <>
                                                <button
                                                    type="button"
                                                    onClick={goToPrevImage}
                                                    aria-label="Previous image"
                                                    style={{
                                                        position: 'absolute',
                                                        top: '50%',
                                                        left: '22px',
                                                        transform: 'translateY(-50%)',
                                                        width: '48px',
                                                        height: '48px',
                                                        borderRadius: '999px',
                                                        border: '1px solid rgba(255,255,255,0.2)',
                                                        background: 'rgba(10,10,10,0.62)',
                                                        color: '#fff',
                                                    }}
                                                >
                                                    <i className="fa fa-chevron-left" />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={goToNextImage}
                                                    aria-label="Next image"
                                                    style={{
                                                        position: 'absolute',
                                                        top: '50%',
                                                        right: '22px',
                                                        transform: 'translateY(-50%)',
                                                        width: '48px',
                                                        height: '48px',
                                                        borderRadius: '999px',
                                                        border: '1px solid rgba(255,255,255,0.2)',
                                                        background: 'rgba(10,10,10,0.62)',
                                                        color: '#fff',
                                                    }}
                                                >
                                                    <i className="fa fa-chevron-right" />
                                                </button>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="col-xl-5 col-lg-5">
                            <div
                                style={{
                                    border: '1px solid rgba(255,255,255,0.08)',
                                    borderRadius: '18px',
                                    background: 'rgba(255,255,255,0.02)',
                                    padding: '32px',
                                }}
                            >
                                {product.brand?.name && (
                                    <div className="d-flex align-items-center justify-content-between gap-3 mb-10px">
                                        <span className="fs-13 fw-700 text-uppercase ls-2px" style={{ color: '#ff7a1a' }}>{product.brand.name}</span>
                                        <button
                                            type="button"
                                            onClick={shareProduct}
                                            className="bg-transparent border-0 p-0 fs-13 fw-600"
                                            style={{ color: '#ff7a1a' }}
                                        >
                                            <i className="feather icon-feather-share-2 me-5px" />
                                            Share
                                        </button>
                                    </div>
                                )}

                                <h2 className="text-white fw-700 mb-12px" style={{ fontSize: '34px', lineHeight: 1.15 }}>
                                    {product.name}
                                </h2>

                                <div className="d-flex flex-wrap align-items-center gap-12px mb-20px" style={{ color: 'rgba(255,255,255,0.72)' }}>
                                    <StarDisplay rating={product.rating || 0} />
                                    <button
                                        className="bg-transparent border-0 p-0 text-decoration-underline fs-13 fw-500"
                                        style={{ color: 'rgba(255,255,255,0.72)' }}
                                        onClick={() => setActiveTab('reviews')}
                                    >
                                        {product.reviews_count || 0} review{product.reviews_count !== 1 ? 's' : ''}
                                    </button>
                                    {displaySku && <span className="fs-13"><span className="fw-600 text-white">SKU:</span> {displaySku}</span>}
                                </div>

                                <div className="d-flex flex-wrap align-items-end gap-10px mb-8px">
                                    <span className="text-white fw-700" style={{ fontSize: '38px', lineHeight: 1 }}>{displayPrice}</span>
                                    {displayOldPrice && (
                                        <del className="fs-18 fw-500" style={{ color: 'rgba(255,255,255,0.42)' }}>{displayOldPrice}</del>
                                    )}
                                    {discountPercent > 0 && (
                                        <span className="fs-13 fw-700" style={{ color: '#67e8a5' }}>({discountPercent}% off)</span>
                                    )}
                                </div>

                                <p className="fs-13 mb-20px" style={{ color: 'rgba(255,255,255,0.58)' }}>
                                    EMI and promotional offers can be attached here once those fields exist in admin.
                                </p>

                                <div
                                    className="mb-20px"
                                    style={{
                                        border: '1px solid rgba(255,122,26,0.35)',
                                        background: 'rgba(255,122,26,0.08)',
                                        borderRadius: '12px',
                                        padding: '14px 16px',
                                    }}
                                >
                                    <span className="d-block fs-12 fw-700 text-uppercase mb-5px" style={{ color: '#ff7a1a' }}>Offer</span>
                                    <span className="d-block text-white fw-600">Apply coupon or campaign messaging in this area when promo data is available.</span>
                                </div>

                                {shortDescription && (
                                    <p className="mb-20px fs-14 lh-26" style={{ color: 'rgba(255,255,255,0.74)' }}>
                                        {shortDescription}
                                        {product.description && product.description.length > 220 ? '...' : ''}
                                    </p>
                                )}

                                <div className="mb-20px">
                                    {!canPurchase ? (
                                        <span className="fs-13 fw-700" style={{ color: '#f59e0b' }}>
                                            <i className="feather icon-feather-slash me-5px" />Not available for purchase
                                        </span>
                                    ) : displayStock === null ? null : displayStock > 10 ? (
                                        <span className="fs-13 fw-700" style={{ color: '#4ade80' }}>
                                            <i className="feather icon-feather-check-circle me-5px" />In Stock
                                        </span>
                                    ) : displayStock > 0 ? (
                                        <span className="fs-13 fw-700" style={{ color: '#fb923c' }}>
                                            <i className="feather icon-feather-alert-circle me-5px" />Only {displayStock} left in stock
                                        </span>
                                    ) : (
                                        <span className="fs-13 fw-700" style={{ color: '#f59e0b' }}>
                                            <i className="feather icon-feather-x-circle me-5px" />Out of Stock
                                        </span>
                                    )}
                                </div>

                                {attrNames.length > 0 && (
                                    <div className="mb-24px">
                                        {attrNames.map(attrName => (
                                            <div key={attrName} className="mb-15px">
                                                <span className="d-block text-white fw-600 fs-14 mb-10px">
                                                    Select {attrName}
                                                    {selected[attrName] && (
                                                        <span className="ms-8px fw-400" style={{ color: '#ff7a1a' }}>{selected[attrName]}</span>
                                                    )}
                                                </span>
                                                <div className="d-flex flex-wrap gap-2">
                                                    {attrOptions[attrName].map(opt => {
                                                        const isActive = selected[attrName] === opt;
                                                        const colorVariant = colorVariantMap[opt];
                                                        const hasImage = colorVariant?.media && colorVariant.media.length > 0;
                                                        const imageUrl = hasImage ? colorVariant.media?.[0].original_url : null;

                                                        // Render image swatch for Color attribute, text button for others
                                                        if (attrName === 'Color' && hasImage) {
                                                            return (
                                                                <button
                                                                    key={opt}
                                                                    type="button"
                                                                    onClick={() => setSelected(prev => ({ ...prev, [attrName]: opt }))}
                                                                    style={{
                                                                        width: '70px',
                                                                        height: '70px',
                                                                        padding: '2px',
                                                                        borderRadius: '10px',
                                                                        border: isActive ? '3px solid #ff7a1a' : '1px solid rgba(255,255,255,0.14)',
                                                                        background: 'transparent',
                                                                        overflow: 'hidden',
                                                                    }}
                                                                    title={opt}
                                                                >
                                                                    <img
                                                                        src={imageUrl!}
                                                                        alt={opt}
                                                                        style={{
                                                                            width: '100%',
                                                                            height: '100%',
                                                                            objectFit: 'cover',
                                                                            borderRadius: '8px',
                                                                        }}
                                                                    />
                                                                </button>
                                                            );
                                                        }

                                                        // Text button for other attributes or colors without images
                                                        return (
                                                            <button
                                                                key={opt}
                                                                type="button"
                                                                onClick={() => setSelected(prev => ({ ...prev, [attrName]: opt }))}
                                                                style={{
                                                                    minWidth: '56px',
                                                                    padding: '9px 14px',
                                                                    borderRadius: '10px',
                                                                    border: isActive ? '1.5px solid #ff7a1a' : '1px solid rgba(255,255,255,0.14)',
                                                                    background: isActive ? 'rgba(255,122,26,0.12)' : 'rgba(255,255,255,0.02)',
                                                                    color: '#fff',
                                                                    fontSize: '13px',
                                                                    fontWeight: 600,
                                                                }}
                                                            >
                                                                {opt}
                                                            </button>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <div
                                    className="mb-20px"
                                    style={{
                                        borderTop: '1px solid rgba(255,255,255,0.08)',
                                        borderBottom: '1px solid rgba(255,255,255,0.08)',
                                        padding: '18px 0',
                                    }}
                                >
                                    <div className="d-flex flex-column gap-3">
                                        {/* Row 1: Quantity + Add to Cart + Wishlist */}
                                        <div className="d-flex align-items-center gap-2">
                                            {/* Quantity Selector */}
                                            <div className="d-flex align-items-center" style={{ height: '50px', border: '1px solid rgba(255,255,255,0.15)', borderRadius: '6px', background: '#0a0a0a' }}>
                                                <button 
                                                    className="bg-transparent border-0 text-white" 
                                                    onClick={() => setQuantity(q => Math.max(1, q - 1))}
                                                    style={{ width: '45px', height: '100%', fontSize: '20px', cursor: 'pointer' }}
                                                >-</button>
                                                <span 
                                                    className="d-flex align-items-center justify-content-center fw-700 text-white mx-1" 
                                                    style={{ width: '40px', height: '100%', fontSize: '18px', cursor: 'default' }}
                                                >
                                                    {quantity}
                                                </span>
                                                <button 
                                                    className="bg-transparent border-0 text-white" 
                                                    onClick={() => setQuantity(q => q + 1)}
                                                    style={{ width: '45px', height: '100%', fontSize: '18px', cursor: 'pointer' }}
                                                >+</button>
                                            </div>

                                            {/* Add to Cart */}
                                            <button
                                                className="flex-grow-1 d-flex align-items-center justify-content-center gap-2 border-radius-6px fw-600 fs-15 transition-all"
                                                onClick={() => addToCart()}
                                                disabled={!stockOk}
                                                style={{
                                                    height: '50px',
                                                    background: '#0a0a0a',
                                                    color: '#fff',
                                                    border: '1px solid rgba(255,255,255,0.15)',
                                                    opacity: stockOk ? 1 : 0.5,
                                                    padding: '0 20px',
                                                }}
                                            >
                                                <i className="bi bi-bag fs-18" />
                                                <span>Add to cart</span>
                                            </button>

                                            {/* Wishlist */}
                                            <button
                                                type="button"
                                                onClick={toggleWishlist}
                                                disabled={wishlistLoading}
                                                className="d-flex align-items-center justify-content-center border-radius-6px transition-all"
                                                style={{
                                                    width: '50px',
                                                    height: '50px',
                                                    background: '#0a0a0a',
                                                    border: '1px solid rgba(255,255,255,0.15)',
                                                    color: '#d1b06b',
                                                    flexShrink: 0,
                                                }}
                                            >
                                                <i className={inWishlist ? 'bi bi-heart-fill' : 'bi bi-heart'} style={{ fontSize: '20px' }} />
                                            </button>
                                        </div>

                                        {/* Row 2: Buy Now (Full Width) */}
                                        <button
                                            className="btn btn-extra-large btn-switch-text btn-box-shadow btn-none-transform border-radius-6px w-100 fw-600 transition-all"
                                            onClick={buyNow}
                                            disabled={!stockOk}
                                            style={{
                                                height: '50px',
                                                background: '#ff7a1a',
                                                color: '#fff',
                                                border: 'none',
                                                opacity: stockOk ? 1 : 0.5,
                                                fontSize: '15px',
                                            }}
                                        >
                                            Buy Now
                                        </button>
                                    </div>
                                </div>

                                <div className="d-flex flex-column gap-15px mb-20px">
                                    <div
                                        style={{
                                            border: '1px solid rgba(255,255,255,0.08)',
                                            borderRadius: '12px',
                                            padding: '14px 16px',
                                            background: 'rgba(255,255,255,0.02)',
                                        }}
                                    >
                                        <span className="d-block text-white fw-600 mb-5px">Delivery & Assembly Details</span>
                                        <span className="d-block fs-13" style={{ color: 'rgba(255,255,255,0.64)' }}>Estimated delivery in 5-8 business days. Add pincode integration when shipping zones are available.</span>
                                    </div>

                                    <div
                                        style={{
                                            border: '1px solid rgba(255,255,255,0.08)',
                                            borderRadius: '12px',
                                            padding: '14px 16px',
                                            background: 'rgba(255,255,255,0.02)',
                                        }}
                                    >
                                        <span className="d-block text-white fw-600 mb-5px">Secure Checkout</span>
                                        <div className="d-flex flex-wrap gap-2">
                                            {['visa', 'mastercard', 'american-express', 'discover'].map((logo) => (
                                                <Image
                                                    key={logo}
                                                    alt={logo}
                                                    src={`/images/${logo}.svg`}
                                                    width={42}
                                                    height={26}
                                                    unoptimized
                                                />
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="d-flex flex-column gap-2 fs-14 mb-20px">
                                    {product.category?.name && (
                                        <div>
                                            <span className="text-white fw-600">Category: </span>
                                            <a href={`/shop?category=${product.category.slug}`} className="text-white" style={{ opacity: 0.72 }}>{product.category.name}</a>
                                        </div>
                                    )}
                                    {product.brand?.name && (
                                        <div>
                                            <span className="text-white fw-600">Brand: </span>
                                            <span className="text-white" style={{ opacity: 0.72 }}>{product.brand.name}</span>
                                        </div>
                                    )}
                                </div>

                                <button
                                    type="button"
                                    onClick={() => {
                                        setActiveTab('reviews');
                                        setTimeout(() => {
                                            document.getElementById('review-form-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }, 100);
                                    }}
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: 8,
                                        background: 'transparent',
                                        border: '1px solid rgba(255,255,255,0.12)',
                                        borderRadius: 10,
                                        color: 'rgba(255,255,255,0.76)',
                                        fontSize: 13,
                                        fontWeight: 600,
                                        padding: '11px 18px',
                                    }}
                                >
                                    <i className="feather icon-feather-edit-2" style={{ fontSize: 14 }} />
                                    Write a Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* ── Product detail tabs ───────────────────────────────────── */}
            <section className="py-40px lg-py-20px">
                <div className="container">
                    <div className="d-flex gap-0 border-bottom border-color-extra-medium-gray mb-40px overflow-x-auto">
                        {([
                            { id: 'description', label: 'Description' },
                            { id: 'additional_info', label: 'Additional Information' },
                            { id: 'shipping', label: 'Shipping and Return' },
                            { id: 'reviews', label: `Reviews (${reviews.length})` },
                        ] as const).map(tab => (
                            <button
                                key={tab.id}
                                type="button"
                                onClick={() => setActiveTab(tab.id)}
                                className="bg-transparent border-0 pb-15px px-25px fs-15 fw-600 text-capitalize whitespace-nowrap"
                                style={{
                                    color: activeTab === tab.id ? '#d1b06b' : 'rgba(255,255,255,0.5)',
                                    borderBottom: activeTab === tab.id ? '2px solid #d1b06b' : '2px solid transparent',
                                    marginBottom: '-1px',
                                    cursor: 'pointer',
                                    transition: 'all 0.2s',
                                }}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {activeTab === 'description' && (
                        <div
                            className="fs-15 lh-28 text-white opacity-8"
                            style={{ maxWidth: '900px' }}
                            dangerouslySetInnerHTML={{ __html: product.description || '<p>No description available.</p>' }}
                        />
                    )}

                    {activeTab === 'additional_info' && (
                        <div className="row">
                            <div className="col-lg-6">
                                <table className="table border-color-transparent-white-light text-white">
                                    <tbody>
                                        {product.additional_info && Array.isArray(product.additional_info) && product.additional_info.length > 0 ? (
                                            product.additional_info.map((info: any, i: number) => (
                                                <tr key={i}>
                                                    <th className="fw-600 border-color-transparent-white-light py-15px ps-0" style={{ width: '150px' }}>{info.label}</th>
                                                    <td className="border-color-transparent-white-light py-15px opacity-7">{info.value}</td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td className="border-0 ps-0 opacity-5">No additional information available.</td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    {activeTab === 'shipping' && (
                        <div className="fs-15 lh-28 text-white opacity-8" style={{ maxWidth: '900px' }}>
                            {product.shipping_and_return ? (
                                <div dangerouslySetInnerHTML={{ __html: product.shipping_and_return.replace(/\n/g, '<br />') }} />
                            ) : (
                                <p>No shipping and return information available.</p>
                            )}
                        </div>
                    )}

                    {activeTab === 'reviews' && (
                        <div className="row">
                            <div className="col-lg-7 md-mb-50px">
                                <div className="d-flex align-items-center justify-content-between mb-20px">
                                    <span className="text-white fw-600 fs-15">
                                        {reviews.length} Review{reviews.length !== 1 ? 's' : ''}
                                    </span>
                                    {reviews.length > 0 && (
                                        <button
                                            type="button"
                                            onClick={() => setShowReviews(v => !v)}
                                            className="bg-transparent border-0 p-0 fs-13 fw-500"
                                            style={{ color: 'rgba(255,255,255,0.5)', cursor: 'pointer' }}
                                        >
                                            <i className={`feather ${showReviews ? 'icon-feather-eye-off' : 'icon-feather-eye'} me-5px`} />
                                            {showReviews ? 'Hide reviews' : 'Show reviews'}
                                        </button>
                                    )}
                                </div>

                                {reviews.length === 0 ? (
                                    <div className="ui-panel ui-empty-state opacity-5">
                                        <i className="feather icon-feather-message-square fs-40 d-block mb-15px" />
                                        <p className="fs-15">No reviews yet. Be the first!</p>
                                    </div>
                                ) : showReviews ? (
                                    <div className="d-flex flex-column gap-4">
                                        {reviews.map((r, i) => (
                                            <div key={r.id ?? i} className="ui-panel ui-panel-sm">
                                                <div className="d-flex justify-content-between align-items-start mb-10px flex-wrap gap-2">
                                                    <div>
                                                        <span className="text-white fw-700 fs-15 d-block">{r.name}</span>
                                                        <span className="fs-12 opacity-5">{r.created_at}</span>
                                                    </div>
                                                    <StarDisplay rating={r.star} />
                                                </div>
                                                <p className="mb-0 fs-14 lh-24 opacity-8">{r.review}</p>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="ui-panel opacity-5 text-center py-30px">
                                        <p className="fs-14 mb-0">Reviews hidden</p>
                                    </div>
                                )}
                            </div>

                            <div className="col-lg-4 offset-lg-1" id="review-form-section">
                                <div className="ui-panel ui-panel-lg">
                                    <h6 className="text-white fw-700 mb-20px">Write a Review</h6>

                                    {reviewMessage && (
                                        <div className={`fs-13 mb-15px fw-500 ${reviewMessage.includes('success') ? 'text-base-color' : 'text-red'}`}>
                                            {reviewMessage}
                                        </div>
                                    )}

                                    <form onSubmit={submitReview}>
                                        {/* Star picker */}
                                        <div className="mb-20px">
                                            <label className="text-white fs-14 fw-600 d-block mb-10px">Your Rating</label>
                                            <div className="d-flex gap-1">
                                                {[1, 2, 3, 4, 5].map(star => (
                                                    <button
                                                        key={star}
                                                        type="button"
                                                        onMouseEnter={() => setHoverStar(star)}
                                                        onMouseLeave={() => setHoverStar(0)}
                                                        onClick={() => setReviewForm(f => ({ ...f, star }))}
                                                        style={{ background: 'none', border: 'none', padding: '2px', cursor: 'pointer', fontSize: '22px' }}
                                                    >
                                                        <i className={`bi bi-star-fill ${star <= (hoverStar || reviewForm.star) ? 'text-golden-yellow' : 'text-extra-medium-gray'}`} />
                                                    </button>
                                                ))}
                                            </div>
                                        </div>

                                        <div className="mb-20px">
                                            <label className="text-white fs-14 fw-600 d-block mb-10px">Your Review <span className="text-red">*</span></label>
                                            <textarea
                                                rows={4}
                                                className="border-radius-4px w-100 text-white"
                                                placeholder="Share your experience with this product..."
                                                value={reviewForm.review}
                                                onChange={e => setReviewForm(f => ({ ...f, review: e.target.value }))}
                                                required
                                                style={{ background: 'rgba(255,255,255,0.04)', border: '1px solid rgba(255,255,255,0.12)', color: '#fff', padding: '12px', resize: 'vertical' }}
                                            />
                                        </div>

                                        <button
                                            type="submit"
                                            disabled={submittingReview}
                                            className="btn btn-base-color btn-medium btn-round-edge w-100 text-transform-none fw-600"
                                        >
                                            {submittingReview ? 'Submitting...' : 'Submit Review'}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </section>

            {/* ── Rich Description Blocks ──────────────────────────────── */}
            {product.details && <ProductDescriptionBlocks blocks={product.details} />}

            {/* ── Related Products ───────────────────────────────────────── */}
            {relatedProducts.length > 0 && (
                <section className="page-shell page-shell-tight pt-0">
                    <div className="container">
                        <div className="row justify-content-center mb-25px">
                            <div className="col-lg-5 text-center">
                                <span className="text-uppercase fs-13 ls-2px fw-600 opacity-6">You may also like</span>
                                <h4 className="alt-font text-white fw-700 mt-5px mb-0">Related products</h4>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-12 px-0">
                                <ProductGrid products={relatedProducts} showCategory />
                            </div>
                        </div>
                    </div>
                </section>
            )}
        </main>
    );
}
