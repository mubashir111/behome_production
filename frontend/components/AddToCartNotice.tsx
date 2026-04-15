'use client';

import React from 'react';
import Link from 'next/link';

interface AddToCartNoticeProps {
    product: {
        name: string;
        image?: string;
        price?: string | number;
    };
    onClose: () => void;
}

export default function AddToCartNotice({ product, onClose }: AddToCartNoticeProps) {
    return (
        <div style={{
            width: 340,
            background: 'rgba(12,12,18,0.96)',
            backdropFilter: 'blur(24px)',
            WebkitBackdropFilter: 'blur(24px)',
            borderRadius: 16,
            border: '1px solid rgba(197,160,89,0.25)',
            boxShadow: '0 24px 60px rgba(0,0,0,0.6)',
            overflow: 'hidden',
            pointerEvents: 'auto',
        }}>
            {/* Gold top bar */}
            <div style={{ height: 3, background: 'linear-gradient(90deg, var(--base-color), rgba(197,160,89,0.3))' }} />

            <div style={{ padding: '16px 18px', display: 'flex', flexDirection: 'column', gap: 14 }}>
                {/* Header */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <div style={{ width: 20, height: 20, borderRadius: '50%', background: '#22c55e', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                            <i className="feather icon-feather-check" style={{ fontSize: 11, color: '#fff' }} />
                        </div>
                        <span style={{ color: '#fff', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em' }}>Added to Bag</span>
                    </div>
                    <button onClick={onClose} style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.35)', cursor: 'pointer', padding: 0, fontSize: 16, lineHeight: 1, display: 'flex', alignItems: 'center' }}>×</button>
                </div>

                {/* Product row */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    {product.image && (
                        <div style={{ width: 60, height: 60, borderRadius: 10, overflow: 'hidden', flexShrink: 0, border: '1px solid rgba(255,255,255,0.08)', background: 'rgba(255,255,255,0.04)' }}>
                            {/* eslint-disable-next-line @next/next/no-img-element */}
                            <img src={product.image} alt={product.name}
                                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }} />
                        </div>
                    )}
                    <div style={{ minWidth: 0, flex: 1 }}>
                        <p style={{ margin: 0, color: '#fff', fontSize: 13, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', lineHeight: 1.4 }}>{product.name}</p>
                        {product.price && (
                            <p style={{ margin: '3px 0 0', color: 'var(--base-color)', fontSize: 13, fontWeight: 700 }}>{product.price}</p>
                        )}
                    </div>
                </div>

                {/* Divider */}
                <div style={{ height: 1, background: 'rgba(255,255,255,0.06)' }} />

                {/* Actions */}
                <div style={{ display: 'flex', gap: 10 }}>
                    <Link href="/cart" onClick={onClose} style={{
                        flex: 1, padding: '9px 12px', textAlign: 'center', textDecoration: 'none',
                        border: '1px solid rgba(255,255,255,0.15)', borderRadius: 8,
                        color: 'rgba(255,255,255,0.8)', fontSize: 11, fontWeight: 700,
                        textTransform: 'uppercase', letterSpacing: '0.08em',
                    }}>
                        View Cart
                    </Link>
                    <Link href="/checkout" onClick={onClose} style={{
                        flex: 1, padding: '9px 12px', textAlign: 'center', textDecoration: 'none',
                        background: 'var(--base-color)', borderRadius: 8,
                        color: '#000', fontSize: 11, fontWeight: 700,
                        textTransform: 'uppercase', letterSpacing: '0.08em',
                        border: 'none',
                    }}>
                        Checkout
                    </Link>
                </div>
            </div>
        </div>
    );
}
