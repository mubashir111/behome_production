'use client';

import { useState, useEffect, useCallback } from 'react';
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
    image?: string;
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

    useEffect(() => {
        const handler = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, [onClose]);

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
                background: 'rgba(5, 5, 10, 0.85)', backdropFilter: 'blur(10px)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                padding: '16px',
            }}
        >
            <style>{`
                @keyframes qvAppear {
                    from { opacity: 0; transform: scale(0.96) translateY(20px); }
                    to { opacity: 1; transform: scale(1) translateY(0); }
                }
                .qv-modal {
                    animation: qvAppear 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                    background: linear-gradient(145deg, rgba(20, 20, 25, 0.98), rgba(28, 28, 35, 0.98));
                    border: 1px solid rgba(197, 160, 89, 0.25);
                    border-radius: 20px;
                    width: 100%; 
                    max-width: 940px;
                    max-height: 90vh;
                    overflow: hidden;
                    display: flex; 
                    flex-direction: column;
                    box-shadow: 0 40px 100px rgba(0,0,0,0.8);
                }
                .qv-body {
                    display: grid;
                    grid-template-columns: 1.1fr 1fr;
                    overflow: auto;
                    flex: 1;
                }
                .qv-img-container {
                    padding: 24px;
                    background: rgba(255, 255, 255, 0.02);
                }
                .qv-img-box {
                    border-radius: 12px;
                    overflow: hidden;
                    aspect-ratio: 1/1;
                    position: relative;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                }
                .qv-details-wrap {
                    padding: 40px 48px;
                    display: flex;
                    flex-direction: column;
                    gap: 24px;
                }
                .qv-kicker {
                    font-size: 11px;
                    font-weight: 700;
                    color: #C5A059;
                    text-transform: uppercase;
                    letter-spacing: 0.2em;
                }
                .qv-title {
                    margin: 0;
                    color: #fff;
                    font-family: serif;
                    font-size: clamp(24px, 5vw, 36px);
                    font-weight: 400;
                    line-height: 1.1;
                }
                .qv-price {
                    font-size: 24px;
                    font-weight: 600;
                    color: #fff;
                    display: flex;
                    align-items: baseline;
                    gap: 12px;
                }
                .qv-btn-primary {
                    flex: 1;
                    padding: 14px 24px;
                    background: linear-gradient(to right, #C5A059, #b38d45);
                    border: none;
                    border-radius: 10px;
                    color: #000;
                    font-size: 13px;
                    font-weight: 700;
                    cursor: pointer;
                    text-transform: uppercase;
                    letter-spacing: 0.1em;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    white-space: nowrap;
                    box-shadow: 0 4px 15px rgba(197, 160, 89, 0.2);
                }
                .qv-btn-primary:hover:not(:disabled) {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(197, 160, 89, 0.3);
                    filter: brightness(1.1);
                }
                .qv-btn-secondary {
                    padding: 14px 20px;
                    border: 1px solid rgba(255,255,255,0.15);
                    border-radius: 10px;
                    color: rgba(255,255,255,0.8);
                    font-size: 12px;
                    font-weight: 600;
                    text-decoration: none;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                }
                .qv-btn-secondary:hover {
                    background: rgba(255,255,255,0.05);
                    border-color: rgba(255,255,255,0.3);
                    color: #fff;
                }
                .qv-chip {
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-size: 12px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                }
                @media (max-width: 768px) {
                    .qv-body { grid-template-columns: 1fr; }
                    .qv-details-wrap { padding: 24px; gap: 20px; }
                    .qv-action-row { flex-direction: column !important; align-items: stretch !important; }
                    .qv-btn-primary { width: 100%; justify-content: center; }
                    .qv-qty-selector { width: 100% !important; justify-content: center; }
                    .qv-footer-row { flex-direction: column; gap: 16px; align-items: flex-start !important; }
                }
            `}</style>

            <div className="qv-modal">
                {/* Header */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 24px', borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
                    <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.15em' }}>Product Discovery</span>
                    <button onClick={onClose} style={{ background: 'rgba(255,255,255,0.05)', border: 'none', color: '#fff', width: 32, height: 32, borderRadius: '50%', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20 }}>×</button>
                </div>

                {/* Body */}
                <div className="qv-body">
                    {loading && (
                        <div style={{ gridColumn: 'span 2', display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 400, color: 'rgba(255,255,255,0.3)' }}>
                            <div style={{ width: 40, height: 40, border: '3px solid rgba(197,160,89,0.2)', borderTopColor: '#C5A059', borderRadius: '50%', animation: 'spin 1s linear infinite' }} />
                            <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
                        </div>
                    )}

                    {!loading && product && (
                        <>
                            {/* Image Section */}
                            <div className="qv-img-container">
                                <div className="qv-img-box">
                                    <img
                                        src={product.image || product.cover || (product.images?.[0]) || '/images/demo-decor-store-product-01.jpg'}
                                        alt={product.name}
                                        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                    />
                                    {product.is_offer && (
                                        <span style={{ position: 'absolute', top: 16, left: 16, background: '#C5A059', color: '#000', fontSize: 10, fontWeight: 800, padding: '4px 10px', borderRadius: 4, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Special Offer</span>
                                    )}
                                </div>
                            </div>

                            {/* Details Section */}
                            <div className="qv-details-wrap">
                                <div>
                                    {product.category && <div className="qv-kicker">{product.category.name}</div>}
                                    <h2 className="qv-title">{product.name}</h2>
                                    
                                    {(product.rating_star_count ?? 0) > 0 && (
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginTop: 12 }}>
                                            <div style={{ display: 'flex', gap: 3 }}>
                                                {[1,2,3,4,5].map(i => (
                                                    <i key={i} className={`bi ${i <= Math.round(product.rating_star ?? 0) ? 'bi-star-fill' : 'bi-star'}`}
                                                        style={{ fontSize: 12, color: i <= Math.round(product.rating_star ?? 0) ? '#C5A059' : 'rgba(255,255,255,0.1)' }} />
                                                ))}
                                            </div>
                                            <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)' }}>({product.rating_star_count} reviews)</span>
                                        </div>
                                    )}
                                </div>

                                <div className="qv-price">
                                    {product.is_offer ? (
                                        <>
                                            <span style={{ color: '#C5A059' }}>{product.discounted_price}</span>
                                            <del style={{ fontSize: 16, color: 'rgba(255,255,255,0.3)', fontWeight: 400 }}>{product.currency_price}</del>
                                        </>
                                    ) : (
                                        <span>{product.currency_price}</span>
                                    )}
                                </div>

                                {product.short_description && (
                                    <p style={{ margin: 0, fontSize: 14, color: 'rgba(255,255,255,0.5)', lineHeight: 1.6, fontWeight: 300 }}>
                                        {product.short_description}
                                    </p>
                                )}

                                {/* Variations */}
                                {Object.entries(options).map(([attr, vals]) => (
                                    <div key={attr}>
                                        <p style={{ margin: '0 0 12px', fontSize: 11, fontWeight: 700, color: 'rgba(255,255,255,0.4)', textTransform: 'uppercase', letterSpacing: '0.1em' }}>{attr}</p>
                                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 10 }}>
                                            {vals.map(val => {
                                                const selected = selectedOptions[attr] === val;
                                                return (
                                                    <button key={val} onClick={() => setSelectedOptions(prev => ({ ...prev, [attr]: val }))}
                                                        className="qv-chip"
                                                        style={{
                                                            border: selected ? '2px solid #C5A059' : '1px solid rgba(255,255,255,0.1)',
                                                            background: selected ? 'rgba(197, 160, 89, 0.1)' : 'rgba(255,255,255,0.03)',
                                                            color: selected ? '#C5A059' : 'rgba(255,255,255,0.7)',
                                                        }}>
                                                        {val}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ))}

                                {/* Add to Cart Row */}
                                <div style={{ marginTop: 'auto', display: 'flex', flexDirection: 'column', gap: 20 }}>
                                    <div className="qv-action-row" style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                                        {product.stock !== 0 && (
                                            <div className="qv-qty-selector" style={{ display: 'flex', alignItems: 'center', background: 'rgba(255,255,255,0.03)', borderRadius: 10, border: '1px solid rgba(255,255,255,0.1)' }}>
                                                <button onClick={() => setQty(q => Math.max(1, q - 1))} style={{ width: 44, height: 44, background: 'none', border: 'none', color: '#fff', cursor: 'pointer', fontSize: 20 }}>−</button>
                                                <span style={{ minWidth: 40, textAlign: 'center', color: '#fff', fontSize: 15, fontWeight: 700 }}>{qty}</span>
                                                <button onClick={() => setQty(q => Math.min(product.stock ?? 99, q + 1))} style={{ width: 44, height: 44, background: 'none', border: 'none', color: '#fff', cursor: 'pointer', fontSize: 20 }}>+</button>
                                            </div>
                                        )}
                                        
                                        <button onClick={handleAddToCart} disabled={addingToCart || product.stock === 0} className="qv-btn-primary">
                                            <i className="feather icon-feather-shopping-bag" style={{ fontSize: 16 }} />
                                            {product.stock === 0 ? 'Out of Stock' : addingToCart ? 'Wait...' : 'Add to Collection'}
                                        </button>
                                    </div>

                                    <div className="qv-footer-row" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                        <a href={`/product/${product.slug}`} className="qv-btn-secondary">
                                            <i className="feather icon-feather-external-link" />
                                            Explore Full Details
                                        </a>

                                        {product.stock !== undefined && (
                                            <div style={{ fontSize: 12, fontWeight: 600, color: product.stock <= 0 ? '#ff5555' : product.stock <= 3 ? '#ffaa00' : '#4ade80', display: 'flex', alignItems: 'center', gap: 6 }}>
                                                <div style={{ width: 6, height: 6, borderRadius: '50%', background: 'currentColor', boxShadow: '0 0 8px currentColor' }} />
                                                {product.stock <= 0 ? 'Unavailable' : product.stock <= 3 ? `Limited Stock` : 'Stock Available'}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
