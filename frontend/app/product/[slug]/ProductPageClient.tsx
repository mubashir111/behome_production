'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { apiFetch } from '@/lib/api';
import ProductGrid from '@/components/ProductGrid';
import LoadingSkeleton from '@/components/LoadingSkeleton';
import ProductDescriptionBlocks from '@/components/ProductDescriptionBlocks';
import { useToast } from '@/components/ToastProvider';
import DOMPurify from 'isomorphic-dompurify';
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
    const { showToast, showCartToast } = useToast();
    const { formatAmount } = useCurrency();
    const [activeImageIndex, setActiveImageIndex] = useState(0);

    // Variations
    const [allVariations, setAllVariations] = useState<Variation[]>([]);
    const [attrOptions, setAttrOptions] = useState<Record<string, string[]>>({});
    const [selected, setSelected] = useState<Record<string, string>>({});
    const [activeVariation, setActiveVariation] = useState<Variation | null>(null);
    // key: "AttrName###OptionName" → variation node (for any attribute that has media)
    const [variantMediaMap, setVariantMediaMap] = useState<Record<string, Variation>>({});
    const [hoveredSwatch, setHoveredSwatch] = useState<string | null>(null); // key of hovered swatch

    // Wishlist
    const [inWishlist, setInWishlist] = useState(false);
    const [wishlistLoading, setWishlistLoading] = useState(false);
    const [addingToCart, setAddingToCart] = useState(false);

    // Reviews
    const [reviews, setReviews] = useState<Review[]>([]);
    const [reviewForm, setReviewForm] = useState({ star: 5, review: '' });
    const [submittingReview, setSubmittingReview] = useState(false);
    const [reviewMessage, setReviewMessage] = useState('');
    const [activeTab, setActiveTab] = useState<'description' | 'reviews' | 'additional_info' | 'shipping'>('description');
    const [hoverStar, setHoverStar] = useState(0);
    const [showReviews, setShowReviews] = useState(true);
    const [showShareMenu, setShowShareMenu] = useState(false);

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

                        // Build variant media map: key = "AttrName###OptionName" for any attribute that has images
                        const mediaMap: Record<string, Variation> = {};
                        const buildMediaMap = (variations: Variation[]) => {
                            for (const v of variations) {
                                if (v.product_attribute_name && v.product_attribute_option_name) {
                                    mediaMap[`${v.product_attribute_name}###${v.product_attribute_option_name}`] = v;
                                }
                                if (v.children?.length) buildMediaMap(v.children);
                            }
                        };
                        buildMediaMap(vars);
                        setVariantMediaMap(mediaMap);

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
        setActiveImageIndex(0); // switch gallery to variant image when selection changes
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
    // Prefer the selected variant's images in the gallery; fall back to product images
    const productImages: string[] = product?.images?.length
        ? product.images
        : [product?.image || product?.cover || '/images/demo-decor-store-product-01.jpg'];

    const variantGalleryImages: string[] = (() => {
        // Priority 1: active (leaf) variation has its own uploaded images
        // Show variant image first, then product gallery images (so product photos remain visible)
        if (activeVariation?.media?.length) {
            const variantImgs = activeVariation.media.map((m: any) => m.original_url);
            return [...variantImgs, ...productImages.filter((img: string) => !variantImgs.includes(img))];
        }
        // Priority 2: any selected option node has uploaded images
        for (const [attrName, optName] of Object.entries(selected)) {
            const v = variantMediaMap[`${attrName}###${optName}`];
            if (v?.media?.length) {
                const variantImgs = v.media.map((m: any) => m.original_url);
                return [...variantImgs, ...productImages.filter((img: string) => !variantImgs.includes(img))];
            }
        }
        // Priority 3: no variant images uploaded yet — cycle through product images
        // by variant index so each color shows a different image from the gallery
        if (product?.images?.length > 1) {
            const firstAttr = Object.keys(attrOptions)[0];
            if (firstAttr && selected[firstAttr]) {
                const optIndex = attrOptions[firstAttr].indexOf(selected[firstAttr]);
                if (optIndex >= 0) {
                    // Rotate the gallery so the variant's image leads, others follow
                    const idx = optIndex % productImages.length;
                    return [...productImages.slice(idx), ...productImages.slice(0, idx)];
                }
            }
        }
        return [];
    })();
    const galleryImages: string[] = variantGalleryImages.length > 0
        ? variantGalleryImages
        : productImages;
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
        setAddingToCart(true);
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
                showCartToast({
                    name: product.name,
                    image: activeVariation?.media?.[0]?.url || product.image || product.cover,
                    price: displayPrice
                });
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
        } finally {
            setAddingToCart(false);
        }
    };

    const buyNow = async () => {
        await addToCart(true);
    };

    const shareToWhatsApp = () => {
        const url = typeof window !== 'undefined' ? window.location.href : '';
        const text = encodeURIComponent(`Check out this ${product?.name} at Behome! ${url}`);
        window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
        setShowShareMenu(false);
    };

    const shareToFacebook = () => {
        const url = typeof window !== 'undefined' ? encodeURIComponent(window.location.href) : '';
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        setShowShareMenu(false);
    };

    const copyToClipboard = async () => {
        const url = typeof window !== 'undefined' ? window.location.href : '';
        try {
            await navigator.clipboard.writeText(url);
            notify('Product link copied to clipboard! ✓', 'success');
        } catch {
            notify('Failed to copy link', 'error');
        }
        setShowShareMenu(false);
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
                        <Link href="/shop" className="btn btn-base-color btn-medium btn-round-edge">Back to Shop</Link>
                    </div>
                </section>
            </main>
        );
    }

    const attrNames = Object.keys(attrOptions);

    return (
        <main className="no-layout-pad page-top-100">
            {/* ── Breadcrumb ─────────────────────────────────────────────── */}
            {/* Breadcrumb Section — Fixed top gap by removing top-space-margin */}
            <section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
                <div className="container-fluid">
                    <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                        <ul>
                            <li><a href="/" className="breadcrumb-link">Home</a></li>
                            <li><Link href="/shop" className="breadcrumb-link">Shop</Link></li>
                            {product.category?.name && <li><Link href={`/shop?category=${product.category.slug}`} className="breadcrumb-link">{product.category.name}</Link></li>}
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
                                    <div className="d-flex d-md-grid gap-12px order-2 order-md-1 product-thumb-grid">
                                        {galleryImages.map((img: string, index: number) => {
                                            const isActive = index === safeActiveImageIndex;

                                            return (
                                                <button
                                                    key={`${img}-${index}`}
                                                    type="button"
                                                    onClick={() => setActiveImageIndex(index)}
                                                    aria-label={`Preview image ${index + 1}`}
                                                    className={`product-thumb-btn${isActive ? ' active' : ''}`}
                                                >
                                                    <Image
                                                        alt={`${product.name} thumbnail ${index + 1}`}
                                                        src={img}
                                                        width={168}
                                                        height={168}
                                                        unoptimized
                                                        className="product-thumb-img"
                                                    />
                                                </button>
                                            );
                                        })}
                                    </div>
                                )}

                                <div className="flex-grow-1 position-relative order-1 order-md-2">
                                    <div className="product-img-frame">
                                        <Image
                                            alt={`${product.name} ${safeActiveImageIndex + 1}`}
                                            src={activeImage}
                                            width={1200}
                                            height={1200}
                                            unoptimized
                                            className="product-main-img"
                                        />

                                        {galleryImages.length > 1 && (
                                            <>
                                                <button type="button" onClick={goToPrevImage} aria-label="Previous image" className="product-img-nav-btn prev">
                                                    <i className="fa fa-chevron-left" />
                                                </button>
                                                <button type="button" onClick={goToNextImage} aria-label="Next image" className="product-img-nav-btn next">
                                                    <i className="fa fa-chevron-right" />
                                                </button>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="col-xl-5 col-lg-5">
                            <div className="product-info-panel">
                                <div className="d-flex align-items-center justify-content-between gap-3 mb-15px">
                                    {product.brand?.name ? (
                                        <span className="fs-13 fw-700 text-uppercase ls-2px product-brand-label">{product.brand.name}</span>
                                    ) : (
                                        <span className="fs-13 fw-700 text-uppercase ls-2px product-brand-label">Product</span>
                                    )}
                                    
                                    <div className="position-relative">
                                        <button
                                            type="button"
                                            onClick={() => setShowShareMenu(!showShareMenu)}
                                            className="bg-transparent border-0 p-0 fs-13 fw-600 product-brand-label hover-text-white transition-all d-flex align-items-center gap-1"
                                            title="Share this product"
                                        >
                                            <i className="feather icon-feather-share-2" />
                                            <span>Share</span>
                                        </button>

                                        {showShareMenu && (
                                            <>
                                                <div className="share-menu-backdrop" onClick={() => setShowShareMenu(false)} />
                                                <div className="share-menu-dropdown animate-in fade-in slide-in-from-top-2 duration-200">
                                                    <div className="p-3 border-bottom border-white/5">
                                                        <span className="fs-12 fw-700 text-white/40 text-uppercase ls-1px">Share via</span>
                                                    </div>
                                                    <div className="d-flex flex-column">
                                                        <button onClick={shareToWhatsApp} className="share-menu-item">
                                                            <i className="bi bi-whatsapp" style={{ color: '#25D366' }} />
                                                            <span>WhatsApp</span>
                                                        </button>
                                                        <button onClick={shareToFacebook} className="share-menu-item">
                                                            <i className="bi bi-facebook" style={{ color: '#1877F2' }} />
                                                            <span>Facebook</span>
                                                        </button>
                                                        <button onClick={copyToClipboard} className="share-menu-item border-top border-white/5">
                                                            <i className="feather icon-feather-copy" />
                                                            <span>Copy Link</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                </div>

                                <h2 className="text-white fw-700 mb-12px product-title">
                                    {product.name}
                                </h2>

                                <div className="d-flex flex-wrap align-items-center gap-12px mb-20px product-meta-text">
                                    <StarDisplay rating={product.rating || 0} />
                                    <button
                                        className="bg-transparent border-0 p-0 text-decoration-underline fs-13 fw-500 product-meta-text"
                                        onClick={() => setActiveTab('reviews')}
                                    >
                                        {product.reviews_count || 0} review{product.reviews_count !== 1 ? 's' : ''}
                                    </button>
                                    {displaySku && <span className="fs-13"><span className="fw-600 text-white">SKU:</span> {displaySku}</span>}
                                </div>

                                <div className="d-flex flex-wrap align-items-end gap-10px mb-8px">
                                    <span className="text-white fw-700 product-price-main">{displayPrice}</span>
                                    {displayOldPrice && (
                                        <del className="fs-18 fw-500 product-price-old">{displayOldPrice}</del>
                                    )}
                                    {discountPercent > 0 && (
                                        <span className="fs-13 fw-700 product-price-off">({discountPercent}% off)</span>
                                    )}
                                </div>

                                {shortDescription && (
                                    <p className="mb-20px fs-14 lh-26 product-desc-text">
                                        {shortDescription}
                                        {plainDescription.length > 220 ? '...' : ''}
                                    </p>
                                )}

                                <div className="mb-20px">
                                    {!canPurchase ? (
                                        <span className="fs-13 fw-700 product-stock-low">
                                            <i className="feather icon-feather-slash me-5px" />Not available for purchase
                                        </span>
                                    ) : displayStock === null ? null : displayStock > 10 ? (
                                        <span className="fs-13 fw-700 product-stock-ok">
                                            <i className="feather icon-feather-check-circle me-5px" />In Stock
                                        </span>
                                    ) : displayStock > 0 ? (
                                        <span className="fs-13 fw-700 product-stock-out">
                                            <i className="feather icon-feather-alert-circle me-5px" />Only {displayStock} left in stock
                                        </span>
                                    ) : (
                                        <span className="fs-13 fw-700 product-stock-low">
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
                                                        <span className="ms-8px fw-400 product-brand-label">{selected[attrName]}</span>
                                                    )}
                                                </span>
                                                <div className="d-flex flex-wrap gap-2">
                                                    {attrOptions[attrName].map(opt => {
                                                        const isActive = selected[attrName] === opt;
                                                        const swatchKey = `${attrName}###${opt}`;
                                                        const variantNode = variantMediaMap[swatchKey];
                                                        const hasImage = (variantNode?.media?.length ?? 0) > 0;
                                                        const imageUrl = hasImage ? variantNode!.media![0].original_url : null;
                                                        const isHovered = hoveredSwatch === swatchKey;

                                                        if (hasImage) {
                                                            // Image swatch — works for any attribute (Color, Material, Style, etc.)
                                                            return (
                                                                <div
                                                                    key={opt}
                                                                    className="variation-swatch-wrap"
                                                                    onMouseEnter={() => setHoveredSwatch(swatchKey)}
                                                                    onMouseLeave={() => setHoveredSwatch(null)}
                                                                >
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => setSelected(prev => ({ ...prev, [attrName]: opt }))}
                                                                        className={`var-swatch-btn${isActive ? ' active' : ''}`}
                                                                    >
                                                                        <img src={imageUrl!} alt={opt} className="var-swatch-img" />
                                                                    </button>
                                                                    <span className={`var-swatch-label${isActive ? ' active' : ''}`}>{opt}</span>
                                                                    {isHovered && (
                                                                        <div className="var-swatch-popup">
                                                                            <img src={imageUrl!} alt={opt} className="var-swatch-popup-img" />
                                                                            <p className="var-swatch-popup-name">{opt}</p>
                                                                            <div className="var-swatch-popup-arrow" />
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            );
                                                        }

                                                        // Text pill — for options without images
                                                        return (
                                                            <button
                                                                key={opt}
                                                                type="button"
                                                                onClick={() => setSelected(prev => ({ ...prev, [attrName]: opt }))}
                                                                className={`var-pill-btn${isActive ? ' active' : ''}`}
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

                                <div className="mb-20px product-actions-divider">
                                    <div className="d-flex flex-column gap-3">
                                        {/* Row 1: Quantity + Add to Cart + Wishlist */}
                                        <div className="d-flex align-items-center gap-2">
                                            {/* Quantity Selector */}
                                            <div className="d-flex align-items-center product-qty-wrap">
                                                <button className="bg-transparent border-0 text-white product-qty-btn" onClick={() => setQuantity(q => Math.max(1, q - 1))}>-</button>
                                                <span className="d-flex align-items-center justify-content-center fw-700 text-white mx-1 product-qty-val">{quantity}</span>
                                                <button className="bg-transparent border-0 text-white product-qty-btn" onClick={() => setQuantity(q => q + 1)}>+</button>
                                            </div>

                                            {/* Add to Cart */}
                                            <button
                                                className="flex-grow-1 d-flex align-items-center justify-content-center gap-2 border-radius-6px fw-600 fs-15 transition-all product-atc-btn"
                                                onClick={() => addToCart()}
                                                disabled={!stockOk || addingToCart}
                                                style={{ opacity: stockOk && !addingToCart ? 1 : 0.6 }}
                                            >
                                                {addingToCart
                                                    ? <><span className="spinner-border spinner-border-sm" role="status" /><span>Adding…</span></>
                                                    : <><i className="bi bi-bag fs-18" /><span>Add to cart</span></>
                                                }
                                            </button>

                                            {/* Wishlist */}
                                            <button
                                                type="button"
                                                onClick={toggleWishlist}
                                                disabled={wishlistLoading}
                                                aria-label={inWishlist ? 'Remove from wishlist' : 'Add to wishlist'}
                                                className="d-flex align-items-center justify-content-center border-radius-6px transition-all product-wishlist-btn"
                                            >
                                                <i className={`${inWishlist ? 'bi bi-heart-fill' : 'bi bi-heart'} product-wishlist-icon`} />
                                            </button>
                                        </div>

                                        {/* Row 2: Buy Now (Full Width) */}
                                        <button
                                            className="btn btn-extra-large btn-switch-text btn-box-shadow btn-none-transform border-radius-6px w-100 fw-600 transition-all product-buy-now-btn"
                                            onClick={buyNow}
                                            disabled={!stockOk}
                                            style={{ opacity: stockOk ? 1 : 0.5 }}
                                        >
                                            Buy Now
                                        </button>
                                    </div>
                                </div>

                                <div className="d-flex flex-column gap-15px mb-20px">
                                    <div className="product-detail-card">
                                        <span className="d-block text-white fw-600 mb-5px">Delivery & Assembly Details</span>
                                        <span className="d-block fs-13 product-delivery-note">Estimated delivery in 5-8 business days. Add pincode integration when shipping zones are available.</span>
                                    </div>
                                    <div className="product-detail-card">
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
                                            <Link href={`/shop?category=${product.category.slug}`} className="text-white product-opacity-link">{product.category.name}</Link>
                                        </div>
                                    )}
                                    {product.brand?.name && (
                                        <div>
                                            <span className="text-white fw-600">Brand: </span>
                                            <span className="text-white product-opacity-link">{product.brand.name}</span>
                                        </div>
                                    )}
                                </div>

                                <button
                                    type="button"
                                    className="product-write-review-btn"
                                    onClick={() => {
                                        setActiveTab('reviews');
                                        setTimeout(() => {
                                            document.getElementById('review-form-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }, 100);
                                    }}
                                >
                                    <i className="feather icon-feather-edit-2 fs-14" />
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
                                className={`product-tab-btn${activeTab === tab.id ? ' active' : ''}`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {activeTab === 'description' && (
                        <div
                            className="fs-15 lh-28 text-white opacity-8 product-tab-desc"
                            dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(product.description || '<p>No description available.</p>') }}
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
                                                    <th className="fw-600 border-color-transparent-white-light py-15px ps-0 product-info-table-label">{info.label}</th>
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
                        <div className="fs-15 lh-28 text-white opacity-8 product-tab-desc">
                            {product.shipping_and_return ? (
                                <div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(product.shipping_and_return.replace(/\n/g, '<br />')) }} />
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
                                            
                                            className="bg-transparent border-0 p-0 fs-13 fw-500 product-review-toggle-btn"
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
                                                        className="product-review-star-btn"
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
                                                
                                                placeholder="Share your experience with this product..."
                                                value={reviewForm.review}
                                                onChange={e => setReviewForm(f => ({ ...f, review: e.target.value }))}
                                                required
                                                className="border-radius-4px w-100 product-review-textarea"
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
