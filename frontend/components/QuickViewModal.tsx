'use client';

import { useState, useEffect, useCallback } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import { useToast } from './ToastProvider';
import { useAuthModal } from '@/context/AuthModalContext';

interface Variation {
    id: number;
    product_attribute_name: string;
    product_attribute_option_name: string;
    currency_price: string;
    stock: number;
    children: Variation[];
}

interface Product {
    id: number;
    name: string;
    slug: string;
    cover?: string;
    image?: string;   // SimpleProductDetailsResource returns 'image'
    images?: string[];
    currency_price: string;
    discounted_price?: string;
    is_offer: boolean;
    short_description?: string;
    stock?: number;
    variations?: Variation[];
    rating_star?: number;
    rating_star_count?: number;
    category?: { name: string };
}

interface QuickViewModalProps {
    slug: string | null;
    onClose: () => void;
}

function collectOptions(vars: Variation[]): Record<string, string[]> {
    const map: Record<string, Set<string>> = {};
    const walk = (nodes: Variation[]) => {
        for (const n of nodes) {
            if (n.product_attribute_name && n.product_attribute_option_name) {
                if (!map[n.product_attribute_name]) map[n.product_attribute_name] = new Set();
                map[n.product_attribute_name].add(n.product_attribute_option_name);
            }
            if (n.children?.length) walk(n.children);
        }
    };
    walk(vars);
    return Object.fromEntries(Object.entries(map).map(([k, v]) => [k, Array.from(v)]));
}

export default function QuickViewModal({ slug, onClose }: QuickViewModalProps) {
    const [product, setProduct] = useState<Product | null>(null);
    const [loading, setLoading] = useState(false);
    const [qty, setQty] = useState(1);
    const [selectedOptions, setSelectedOptions] = useState<Record<string, string>>({});
    const [addingToCart, setAddingToCart] = useState(false);
    const { showToast, showCartToast } = useToast();
    const { openAuthModal } = useAuthModal();

    useEffect(() => {
        if (!slug) return;
        setLoading(true);
        setProduct(null);
        setQty(1);
        setSelectedOptions({});
        apiFetch(`/products/${slug}`)
            .then(res => setProduct(res?.data ?? null))
            .catch(() => setProduct(null))
            .finally(() => setLoading(false));
    }, [slug]);

    // Close on Escape
    useEffect(() => {
        const handler = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, [onClose]);

    // Lock body scroll
    useEffect(() => {
        document.body.style.overflow = 'hidden';
        return () => { document.body.style.overflow = ''; };
    }, []);

    const options = product?.variations?.length ? collectOptions(product.variations) : {};

    const handleAddToCart = useCallback(async () => {
        if (!product) return;
        const token = localStorage.getItem('token');
        if (!token) { openAuthModal(() => handleAddToCart()); return; }
        setAddingToCart(true);
        try {
            const res = await apiFetch('/cart', {
                method: 'POST',
                body: JSON.stringify({ product_id: product.id, quantity: qty, variation_id: null }),
            });
            if (res.status) {
                const img = product.image || product.cover || '';
                showCartToast({ name: product.name, image: img.includes('/images/default/') ? undefined : img, price: product.discounted_price || product.currency_price });
                onClose();
            } else {
                showToast(res.message || 'Failed to add to cart', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'An error occurred', 'error');
        } finally {
            setAddingToCart(false);
        }
    }, [product, qty, openAuthModal, showCartToast, showToast, onClose]);

    if (!slug) return null;

    return (
        <div
            onClick={e => { if (e.target === e.currentTarget) onClose(); }}
            style={{
                position: 'fixed', inset: 0, zIndex: 99999,
                background: 'rgba(0,0,0,0.75)', backdropFilter: 'blur(6px)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                padding: '12px',
            }}
        >
            <div style={{
                background: 'rgba(15,15,25,0.98)',
                border: '1px solid rgba(197,160,89,0.2)',
                borderRadius: 16,
                width: '100%', maxWidth: 860,
                maxHeight: '95vh',
                overflow: 'hidden',
                display: 'flex', flexDirection: 'column',
                boxShadow: '0 32px 80px rgba(0,0,0,0.7)',
            }}>
                {/* Header */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 24px', borderBottom: '1px solid rgba(255,255,255,0.07)' }}>
                    <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.1em' }}>Quick View</span>
                    <button onClick={onClose} style={{ background: 'none', border: '1px solid rgba(255,255,255,0.12)', color: 'rgba(255,255,255,0.6)', width: 32, height: 32, borderRadius: '50%', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18, lineHeight: 1 }}>×</button>
                </div>

                {/* Body */}
                <div style={{ overflow: 'auto', flex: 1, padding: '16px' }}>
                    {loading && (
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 300, color: 'rgba(255,255,255,0.3)', fontSize: 14 }}>
                            <span>Loading…</span>
                        </div>
                    )}

                    {!loading && !product && (
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 300, color: 'rgba(255,255,255,0.3)', fontSize: 14 }}>
                            <span>Product not found.</span>
                        </div>
                    )}

                    {!loading && product && (
                        <>
                        <style>{`
                            @media (max-width: 600px) {
                                .qv-grid { grid-template-columns: 1fr !important; }
                                .qv-img { max-height: 220px !important; aspect-ratio: 16/9 !important; }
                            }
                        `}</style>
                        <div className="qv-grid" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }}>
                            {/* Image */}
                            <div className="qv-img" style={{ borderRadius: 12, overflow: 'hidden', background: 'rgba(255,255,255,0.03)', aspectRatio: '4/5', position: 'relative' }}>
                                {/* eslint-disable-next-line @next/next/no-img-element */}
                                <img
                                    src={product.image || product.cover || (product.images?.[0]) || '/images/demo-decor-store-product-01.jpg'}
                                    alt={product.name}
                                    style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }}
                                />
                            </div>

                            {/* Details */}
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                                {product.category && (
                                    <span style={{ fontSize: 11, fontWeight: 600, color: 'var(--base-color)', textTransform: 'uppercase', letterSpacing: '0.1em' }}>{product.category.name}</span>
                                )}
                                <h2 style={{ margin: 0, color: '#fff', fontSize: 'clamp(16px, 4vw, 22px)', fontWeight: 700, lineHeight: 1.3 }}>{product.name}</h2>

                                {/* Rating */}
                                {(product.rating_star_count ?? 0) > 0 && (
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                        {[1,2,3,4,5].map(i => (
                                            <i key={i} className={`bi ${i <= Math.round(product.rating_star ?? 0) ? 'bi-star-fill' : 'bi-star'}`}
                                                style={{ fontSize: 13, color: i <= Math.round(product.rating_star ?? 0) ? '#f59e0b' : 'rgba(255,255,255,0.2)' }} />
                                        ))}
                                        <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)', marginLeft: 4 }}>({product.rating_star_count})</span>
                                    </div>
                                )}

                                {/* Price */}
                                <div style={{ fontSize: 22, fontWeight: 700 }}>
                                    {product.is_offer ? (
                                        <span>
                                            <del style={{ fontSize: 14, color: 'rgba(255,255,255,0.35)', marginRight: 8 }}>{product.currency_price}</del>
                                            <span style={{ color: 'var(--base-color)' }}>{product.discounted_price}</span>
                                        </span>
                                    ) : (
                                        <span style={{ color: '#fff' }}>{product.currency_price}</span>
                                    )}
                                </div>

                                {/* Short description */}
                                {product.short_description && (
                                    <p style={{ margin: 0, fontSize: 13, color: 'rgba(255,255,255,0.5)', lineHeight: 1.6 }}>{product.short_description}</p>
                                )}

                                {/* Variations */}
                                {Object.entries(options).map(([attr, vals]) => (
                                    <div key={attr}>
                                        <p style={{ margin: '0 0 8px', fontSize: 12, fontWeight: 600, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase', letterSpacing: '0.06em' }}>{attr}</p>
                                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                                            {vals.map(val => {
                                                const selected = selectedOptions[attr] === val;
                                                return (
                                                    <button key={val} onClick={() => setSelectedOptions(prev => ({ ...prev, [attr]: val }))}
                                                        style={{ padding: '6px 14px', borderRadius: 6, fontSize: 12, fontWeight: 600, cursor: 'pointer', border: selected ? '1px solid var(--base-color)' : '1px solid rgba(255,255,255,0.12)', background: selected ? 'rgba(197,160,89,0.15)' : 'transparent', color: selected ? 'var(--base-color)' : 'rgba(255,255,255,0.6)', transition: 'all 0.2s' }}>
                                                        {val}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ))}

                                {/* Qty — hidden when out of stock */}
                                {product.stock !== 0 && (
                                <div>
                                    <p style={{ margin: '0 0 8px', fontSize: 12, fontWeight: 600, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase', letterSpacing: '0.06em' }}>Quantity</p>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 0, border: '1px solid rgba(255,255,255,0.12)', borderRadius: 8, width: 'fit-content', overflow: 'hidden' }}>
                                        <button onClick={() => setQty(q => Math.max(1, q - 1))} style={{ width: 38, height: 38, background: 'rgba(255,255,255,0.05)', border: 'none', color: '#fff', cursor: 'pointer', fontSize: 18, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>−</button>
                                        <span style={{ width: 42, textAlign: 'center', color: '#fff', fontSize: 14, fontWeight: 600 }}>{qty}</span>
                                        <button onClick={() => setQty(q => Math.min(product.stock ?? 99, q + 1))} style={{ width: 38, height: 38, background: 'rgba(255,255,255,0.05)', border: 'none', color: '#fff', cursor: 'pointer', fontSize: 18, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>+</button>
                                    </div>
                                </div>
                                )}

                                {/* Actions */}
                                <div style={{ display: 'flex', gap: 10, marginTop: 4 }}>
                                    <button onClick={handleAddToCart} disabled={addingToCart || product.stock === 0}
                                        style={{ flex: 1, padding: '12px 20px', background: product.stock === 0 ? 'rgba(255,255,255,0.08)' : 'var(--base-color)', border: product.stock === 0 ? '1px solid rgba(255,255,255,0.1)' : 'none', borderRadius: 8, color: product.stock === 0 ? 'rgba(255,255,255,0.3)' : '#000', fontSize: 13, fontWeight: 700, cursor: (addingToCart || product.stock === 0) ? 'not-allowed' : 'pointer', opacity: addingToCart ? 0.7 : 1, textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                                        {product.stock === 0 ? 'Out of Stock' : addingToCart ? 'Adding…' : 'Add to Cart'}
                                    </button>
                                    <a href={`/product/${product.slug}`}
                                        style={{ padding: '12px 16px', border: '1px solid rgba(255,255,255,0.15)', borderRadius: 8, color: 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 600, textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 6, whiteSpace: 'nowrap' }}>
                                        <i className="feather icon-feather-external-link" style={{ fontSize: 13 }} />
                                        Full Page
                                    </a>
                                </div>

                                {/* Stock */}
                                {product.stock !== undefined && (
                                    <p style={{ margin: 0, fontSize: 12, color: product.stock === 0 ? '#f87171' : product.stock <= 3 ? '#f59e0b' : '#4ade80' }}>
                                        <i className={`feather ${product.stock === 0 ? 'icon-feather-x-circle' : product.stock <= 3 ? 'icon-feather-alert-circle' : 'icon-feather-check-circle'}`} style={{ marginRight: 5 }} />
                                        {product.stock === 0 ? 'Out of stock' : product.stock <= 3 ? `Only ${product.stock} left!` : 'In stock'}
                                    </p>
                                )}
                            </div>
                        </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
