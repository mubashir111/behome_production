'use client';

import { useCallback, useEffect, useState } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import LoadingSkeleton from '@/components/LoadingSkeleton';
import { useToast } from '@/components/ToastProvider';
import { extractCurrencySymbol, readStoredCoupon, storeCoupon } from '@/lib/checkout';
import { useCurrency, useSettings } from '@/components/SettingsProvider';

export default function Cart() {
    const [cartItems, setCartItems] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [suggestions, setSuggestions] = useState<any[]>([]);
    const [updatingItems, setUpdatingItems] = useState<Set<number>>(new Set());
    const [subtotal, setSubtotal] = useState(0);
    const { showToast } = useToast();
    const { currency: { symbol: currencySymbol }, formatAmount } = useCurrency();
    const { settings } = useSettings();
    const freeShippingThreshold = Number(settings?.site_free_delivery_threshold) || 0;
    const [couponCode, setCouponCode] = useState('');
    const [couponDiscount, setCouponDiscount] = useState(0);
    const [couponMessage, setCouponMessage] = useState('');

    const clearCouponState = useCallback((nextMessage = '') => {
        setCouponDiscount(0);
        setCouponCode('');
        if (nextMessage) setCouponMessage(nextMessage);
        storeCoupon(null);
    }, []);

    const syncCoupon = useCallback(async (nextSubtotal: number) => {
        const storedCoupon = readStoredCoupon();
        if (!storedCoupon?.code) {
            setCouponDiscount(0);
            return;
        }

        setCouponCode(storedCoupon.code);
        try {
            const response = await apiFetch('/frontend/coupon/coupon-checking', {
                method: 'POST',
                body: JSON.stringify({ code: storedCoupon.code, total: nextSubtotal }),
            });

            const couponData = response?.data ?? response;
            const nextDiscount = parseFloat(couponData?.discount || 0);
            const nextCurrencyDiscount = couponData?.currency_discount || formatAmount(nextDiscount);
            const nextSymbol = extractCurrencySymbol(nextCurrencyDiscount, currencySymbol);

            setCouponDiscount(nextDiscount);
            setCouponMessage(`Coupon applied: -${nextCurrencyDiscount}`);
            storeCoupon({
                code: storedCoupon.code,
                id: couponData?.id ?? storedCoupon.id ?? null,
                discount: nextDiscount,
                currencyDiscount: nextCurrencyDiscount,
                symbol: nextSymbol,
            });
        } catch (couponError: any) {
            clearCouponState(couponError.message || 'Saved coupon is no longer valid');
        }
    }, [clearCouponState, currencySymbol, formatAmount]);

    const calculateTotal = useCallback((items: any[]) => {
        // Subtotal = sum of pre-tax line totals (price × quantity)
        const total = items.reduce((acc, item) => acc + parseFloat(item.subtotal || (item.price * item.quantity)), 0);
        setSubtotal(total);
        return total;
    }, []);

    const fetchCart = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await apiFetch('/cart');
            if (response.status) {
                setCartItems(response.data);
                const nextSubtotal = calculateTotal(response.data);
                await syncCoupon(nextSubtotal);
                // Load suggestions for both empty cart (you may like) and filled cart (upsell)
                apiFetch('/frontend/product/popular-products')
                    .then(r => {
                        const cartProductIds = new Set((response.data as any[]).map((i: any) => i.product?.id ?? i.product_id));
                        const filtered = (r?.data ?? r ?? []).filter((p: any) => !cartProductIds.has(p.id));
                        setSuggestions(filtered.slice(0, 4));
                    })
                    .catch(() => {});
            } else {
                setError('Failed to load cart');
            }
        } catch (error: any) {
            console.error('Failed to fetch cart:', error);
            setError(error.message || 'Failed to load cart');
        } finally {
            setLoading(false);
        }
    }, [calculateTotal, syncCoupon]);

    const applyCoupon = async () => {
        if (!couponCode.trim()) return;
        setCouponMessage('');
        try {
            const response = await apiFetch('/frontend/coupon/coupon-checking', {
                method: 'POST',
                body: JSON.stringify({ code: couponCode.trim(), total: subtotal }),
            });
            const couponData = response?.data ?? response;
            const nextDiscount = parseFloat(couponData?.discount || 0);
            const nextCurrencyDiscount = couponData?.currency_discount || formatAmount(nextDiscount);
            const nextSymbol = extractCurrencySymbol(nextCurrencyDiscount, currencySymbol);

            setCouponDiscount(nextDiscount);
            setCouponMessage(`Coupon applied: -${nextCurrencyDiscount}`);
            storeCoupon({
                code: couponCode.trim(),
                id: couponData?.id ?? null,
                discount: nextDiscount,
                currencyDiscount: nextCurrencyDiscount,
                symbol: nextSymbol,
            });
        } catch (couponError: any) {
            setCouponDiscount(0);
            setCouponMessage(couponError.message || 'Failed to apply coupon');
            storeCoupon(null);
        }
    };

    const updateQuantity = async (id: number, newQty: number) => {
        if (newQty < 1) return;
        setUpdatingItems(prev => new Set(prev).add(id));
        try {
            const response = await apiFetch(`/cart/${id}`, {
                method: 'PUT',
                body: JSON.stringify({ quantity: newQty }),
            });
            if (response.status) {
                const updatedItem = response.data;
                const updatedItems = cartItems.map(item =>
                    item.id === id ? { ...item, ...updatedItem } : item
                );
                setCartItems(updatedItems);
                const nextSubtotal = calculateTotal(updatedItems);
                await syncCoupon(nextSubtotal);
                window.dispatchEvent(new CustomEvent('cart:updated'));
            } else {
                showToast('Failed to update quantity', 'error');
            }
        } catch (error: any) {
            console.error('Failed to update quantity:', error);
            showToast(error.message || 'Failed to update quantity', 'error');
        } finally {
            setUpdatingItems(prev => {
                const newSet = new Set(prev);
                newSet.delete(id);
                return newSet;
            });
        }
    };

    const removeItem = async (id: number) => {
        setUpdatingItems(prev => new Set(prev).add(id));
        try {
            const response = await apiFetch(`/cart/${id}`, {
                method: 'DELETE',
            });
            if (response.status) {
                const updatedItems = cartItems.filter(item => item.id !== id);
                setCartItems(updatedItems);
                const nextSubtotal = calculateTotal(updatedItems);
                if (updatedItems.length === 0) {
                    clearCouponState();
                } else {
                    await syncCoupon(nextSubtotal);
                }
                showToast('Item removed from cart', 'success');
                window.dispatchEvent(new CustomEvent('cart:updated', {}));
            } else {
                showToast('Failed to remove item', 'error');
            }
        } catch (error: any) {
            console.error('Failed to remove item:', error);
            showToast(error.message || 'Failed to remove item', 'error');
        } finally {
            setUpdatingItems(prev => {
                const newSet = new Set(prev);
                newSet.delete(id);
                return newSet;
            });
        }
    };

    useEffect(() => {
        const storedCoupon = readStoredCoupon();
        if (storedCoupon) {
            setCouponCode(storedCoupon.code);
            setCouponDiscount(storedCoupon.discount);
        }
        fetchCart();
    }, [fetchCart]);

    if (loading) {
        return (
            <main>
                <section className="page-shell">
                    <div className="container-fluid">
                        <div className="content-layout-wrapper">
                            <div className="row align-items-start">
                                <div className="col-lg-8 pe-50px md-pe-15px md-mb-50px xs-mb-35px">
                                    <LoadingSkeleton type="table" rows={3} />
                                </div>
                                <div className="col-lg-4">
                                    <LoadingSkeleton type="card" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (error) {
        return (
            <main>
                <section className="page-shell">
                    <div className="container-fluid">
                        <div className="content-layout-wrapper">
                            <div className="text-center">
                                <h2 className="text-white mb-4">Error Loading Cart</h2>
                                <p className="text-white/70 mb-4">{error}</p>
                                <button className="btn btn-primary me-3" onClick={fetchCart}>Try Again</button>
                                <a href="/shop" className="btn btn-outline-primary">Continue Shopping</a>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    const totalTax = cartItems.reduce((acc, item) => acc + parseFloat(item.tax || 0), 0);
    const grandTotal = Math.max(subtotal + totalTax - couponDiscount, 0);

    /* ── Shared suggestion card ── */
    const SuggestionCard = ({ product }: { product: any }) => {
        const img = (product.cover || product.image || '').trim() || '/images/demo-decor-store-product-01.jpg';
        const price = product.discounted_price || product.currency_price || '';
        return (
            <a href={`/product/${product.slug}`} style={{ textDecoration: 'none', display: 'block' }}>
                <div
                    style={{ background: 'rgba(18,16,12,0.9)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 14, overflow: 'hidden', transition: 'border-color 0.3s ease, transform 0.3s ease', cursor: 'pointer' }}
                    onMouseEnter={e => { (e.currentTarget as HTMLDivElement).style.borderColor = 'rgba(201,169,110,0.35)'; (e.currentTarget as HTMLDivElement).style.transform = 'translateY(-3px)'; }}
                    onMouseLeave={e => { (e.currentTarget as HTMLDivElement).style.borderColor = 'rgba(255,255,255,0.07)'; (e.currentTarget as HTMLDivElement).style.transform = 'translateY(0)'; }}
                >
                    <div style={{ aspectRatio: '4/3', overflow: 'hidden', background: 'rgba(255,255,255,0.03)' }}>
                        {/* eslint-disable-next-line @next/next/no-img-element */}
                        <img src={img} alt={product.name}
                            style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform 0.5s ease' }}
                            onMouseEnter={e => { (e.currentTarget as HTMLImageElement).style.transform = 'scale(1.06)'; }}
                            onMouseLeave={e => { (e.currentTarget as HTMLImageElement).style.transform = 'scale(1)'; }}
                            onError={e => { (e.currentTarget as HTMLImageElement).src = '/images/demo-decor-store-product-01.jpg'; }} />
                    </div>
                    <div style={{ padding: '14px 16px 16px' }}>
                        <p style={{ margin: '0 0 6px', color: '#fff', fontWeight: 600, fontSize: 13, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{product.name}</p>
                        {price && <p style={{ margin: 0, color: 'var(--base-color,#c9a96e)', fontWeight: 700, fontSize: 13 }}>{price}</p>}
                    </div>
                </div>
            </a>
        );
    };

    return (
        <main className="no-layout-pad" style={{ background: '#0a0906', minHeight: '100vh' }}>

            {/* ── Page Header ── */}
            <div style={{
                paddingTop: 110,
                paddingBottom: 32,
                paddingLeft: 'clamp(20px,5vw,60px)',
                paddingRight: 'clamp(20px,5vw,60px)',
                borderBottom: '1px solid rgba(255,255,255,0.06)',
            }}>
                <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
                    <div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
                            <span style={{ display: 'block', width: 28, height: 1, background: 'var(--base-color,#c9a96e)' }} />
                            <span style={{ color: 'var(--base-color,#c9a96e)', fontSize: 10, fontWeight: 700, letterSpacing: '3px', textTransform: 'uppercase' }}>Your Selection</span>
                        </div>
                        <h1 style={{ fontFamily: 'var(--font-heading, sans-serif)', color: '#fff', fontWeight: 700, fontSize: 'clamp(26px,3.5vw,42px)', margin: 0, letterSpacing: '-0.5px' }}>
                            Shopping Cart
                            {cartItems.length > 0 && (
                                <span style={{ marginLeft: 14, fontSize: 14, fontWeight: 500, color: 'rgba(255,255,255,0.35)', fontFamily: 'var(--font-body, sans-serif)', letterSpacing: 0 }}>
                                    {cartItems.length} {cartItems.length === 1 ? 'item' : 'items'}
                                </span>
                            )}
                        </h1>
                    </div>
                    <nav style={{ fontSize: 12, color: 'rgba(255,255,255,0.35)', display: 'flex', gap: 6, alignItems: 'center' }}>
                        <a href="/" style={{ color: 'rgba(255,255,255,0.45)', textDecoration: 'none' }}>Home</a>
                        <span>/</span>
                        <a href="/shop" style={{ color: 'rgba(255,255,255,0.45)', textDecoration: 'none' }}>Shop</a>
                        <span>/</span>
                        <span style={{ color: 'var(--base-color,#c9a96e)' }}>Cart</span>
                    </nav>
                </div>
            </div>

            <div style={{ padding: 'clamp(28px,4vw,52px) clamp(20px,5vw,60px)', maxWidth: 1400, margin: '0 auto' }}>
                {cartItems.length > 0 ? (
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 380px', gap: 32, alignItems: 'start' }} className="cart-layout">

                        {/* ── LEFT: Items + Coupon ── */}
                        <div>
                            {/* Column headers — desktop only */}
                            <div className="d-none d-md-grid" style={{ display: 'grid', gridTemplateColumns: '1fr 80px 120px 80px 36px', gap: 16, padding: '0 20px 12px', marginBottom: 4 }}>
                                <span style={{ fontSize: 10, fontWeight: 700, letterSpacing: '2px', textTransform: 'uppercase', color: 'rgba(255,255,255,0.25)' }}>Product</span>
                                <span style={{ fontSize: 10, fontWeight: 700, letterSpacing: '2px', textTransform: 'uppercase', color: 'rgba(255,255,255,0.25)', textAlign: 'center' }}>Price</span>
                                <span style={{ fontSize: 10, fontWeight: 700, letterSpacing: '2px', textTransform: 'uppercase', color: 'rgba(255,255,255,0.25)', textAlign: 'center' }}>Qty</span>
                                <span style={{ fontSize: 10, fontWeight: 700, letterSpacing: '2px', textTransform: 'uppercase', color: 'rgba(255,255,255,0.25)', textAlign: 'right' }}>Total</span>
                                <span />
                            </div>

                            {/* Item cards */}
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                                {cartItems.map((item) => (
                                    <div
                                        key={item.id}
                                        style={{
                                            display: 'grid',
                                            gridTemplateColumns: 'auto 1fr',
                                            gap: 16,
                                            padding: '18px 20px',
                                            background: 'rgba(255,255,255,0.03)',
                                            border: '1px solid rgba(255,255,255,0.07)',
                                            borderRadius: 14,
                                            opacity: updatingItems.has(item.id) ? 0.45 : 1,
                                            transition: 'opacity 0.25s ease, border-color 0.25s ease',
                                        }}
                                        onMouseEnter={e => { if (!updatingItems.has(item.id)) (e.currentTarget as HTMLDivElement).style.borderColor = 'rgba(201,169,110,0.2)'; }}
                                        onMouseLeave={e => { (e.currentTarget as HTMLDivElement).style.borderColor = 'rgba(255,255,255,0.07)'; }}
                                    >
                                        {/* Thumbnail */}
                                        <a href={`/product/${item.product?.slug}`} style={{ flexShrink: 0, display: 'block' }}>
                                            <div style={{ position: 'relative', width: 100, height: 100, borderRadius: 10, overflow: 'hidden', background: 'rgba(255,255,255,0.04)' }}>
                                                <Image
                                                    alt={item.product?.name || ''}
                                                    src={item.product?.cover || '/images/demo-decor-store-product-01.jpg'}
                                                    fill unoptimized
                                                    style={{ objectFit: 'cover' }}
                                                />
                                            </div>
                                        </a>

                                        {/* Right side */}
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 12, minWidth: 0, flexWrap: 'wrap' }}>
                                            {/* Name + variant */}
                                            <div style={{ flex: '1 1 160px', minWidth: 0 }}>
                                                <a href={`/product/${item.product?.slug}`}
                                                    style={{ color: '#fff', fontWeight: 600, fontSize: 15, lineHeight: 1.35, textDecoration: 'none', display: 'block', marginBottom: 5 }}>
                                                    {item.product?.name}
                                                </a>
                                                {item.variation_names && (
                                                    <span style={{ display: 'inline-block', fontSize: 11, color: 'rgba(255,255,255,0.4)', background: 'rgba(255,255,255,0.06)', borderRadius: 4, padding: '2px 8px', marginBottom: 6 }}>
                                                        {item.variation_names}
                                                    </span>
                                                )}
                                                {/* Unit price on mobile */}
                                                <div className="d-md-none" style={{ fontSize: 13, color: 'rgba(255,255,255,0.5)' }}>
                                                    {item.old_price > item.price && (
                                                        <del style={{ marginRight: 6 }}>{formatAmount(parseFloat(item.old_price))}</del>
                                                    )}
                                                    {formatAmount(parseFloat(item.price))} each
                                                </div>
                                            </div>

                                            {/* Unit price desktop */}
                                            <div className="d-none d-md-block" style={{ flex: '0 0 80px', textAlign: 'center' }}>
                                                {item.old_price > item.price && (
                                                    <del style={{ display: 'block', fontSize: 11, color: 'rgba(255,255,255,0.3)', marginBottom: 2 }}>
                                                        {formatAmount(parseFloat(item.old_price))}
                                                    </del>
                                                )}
                                                <span style={{ fontSize: 14, fontWeight: 600, color: 'rgba(255,255,255,0.7)' }}>
                                                    {formatAmount(parseFloat(item.price))}
                                                </span>
                                            </div>

                                            {/* Qty stepper */}
                                            <div style={{ flex: '0 0 120px', display: 'flex', alignItems: 'center', justifyContent: 'center', height: 40, borderRadius: 20, border: '1px solid rgba(255,255,255,0.12)', background: 'rgba(255,255,255,0.04)', overflow: 'hidden' }}>
                                                <button
                                                    onClick={() => updateQuantity(item.id, item.quantity - 1)}
                                                    disabled={updatingItems.has(item.id)}
                                                    type="button"
                                                    style={{ width: 40, height: 40, border: 'none', background: 'transparent', color: 'rgba(255,255,255,0.6)', fontSize: 18, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'color 0.2s' }}
                                                    onMouseEnter={e => (e.currentTarget.style.color = '#fff')}
                                                    onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.6)')}
                                                >−</button>
                                                <span style={{ flex: 1, textAlign: 'center', color: '#fff', fontWeight: 700, fontSize: 14 }}>
                                                    {updatingItems.has(item.id) ? '…' : item.quantity}
                                                </span>
                                                <button
                                                    onClick={() => updateQuantity(item.id, item.quantity + 1)}
                                                    disabled={updatingItems.has(item.id) || (item.product?.stock != null && item.quantity >= item.product.stock)}
                                                    type="button"
                                                    style={{ width: 40, height: 40, border: 'none', background: 'transparent', color: 'rgba(255,255,255,0.6)', fontSize: 18, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'color 0.2s' }}
                                                    onMouseEnter={e => (e.currentTarget.style.color = '#fff')}
                                                    onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.6)')}
                                                >+</button>
                                            </div>

                                            {/* Line total */}
                                            <div style={{ flex: '0 0 80px', textAlign: 'right', fontWeight: 700, fontSize: 16, color: 'var(--base-color,#c9a96e)' }}>
                                                {formatAmount(parseFloat(item.subtotal || (item.price * item.quantity)))}
                                            </div>

                                            {/* Remove */}
                                            <button
                                                onClick={() => removeItem(item.id)}
                                                disabled={updatingItems.has(item.id)}
                                                type="button"
                                                title="Remove item"
                                                style={{ flex: '0 0 36px', width: 32, height: 32, borderRadius: '50%', background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.08)', color: 'rgba(255,255,255,0.35)', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'all 0.2s ease', padding: 0 }}
                                                onMouseEnter={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(239,68,68,0.12)'; (e.currentTarget as HTMLButtonElement).style.borderColor = 'rgba(239,68,68,0.3)'; (e.currentTarget as HTMLButtonElement).style.color = '#f87171'; }}
                                                onMouseLeave={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.05)'; (e.currentTarget as HTMLButtonElement).style.borderColor = 'rgba(255,255,255,0.08)'; (e.currentTarget as HTMLButtonElement).style.color = 'rgba(255,255,255,0.35)'; }}
                                            >
                                                <i className="feather icon-feather-x" style={{ fontSize: 13 }}></i>
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* ── Coupon + Continue Shopping ── */}
                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16, marginTop: 28, flexWrap: 'wrap' }}>
                                <div style={{ flex: '1 1 280px', maxWidth: 380 }}>
                                    <div style={{ display: 'flex', height: 48, borderRadius: 10, border: '1px solid rgba(255,255,255,0.12)', background: 'rgba(255,255,255,0.04)', overflow: 'hidden' }}>
                                        <input
                                            autoComplete="off"
                                            data-form-type="other"
                                            placeholder="Have a voucher code?"
                                            type="text"
                                            value={couponCode}
                                            onChange={(e) => setCouponCode(e.target.value)}
                                            onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), applyCoupon())}
                                            style={{ flex: 1, background: 'transparent', border: 'none', outline: 'none', color: '#fff', fontSize: 13, padding: '0 16px' }}
                                        />
                                        <button
                                            type="button"
                                            onClick={applyCoupon}
                                            style={{ padding: '0 20px', background: 'rgba(201,169,110,0.12)', border: 'none', borderLeft: '1px solid rgba(255,255,255,0.08)', color: 'var(--base-color,#c9a96e)', fontWeight: 700, fontSize: 12, letterSpacing: '1px', textTransform: 'uppercase', cursor: 'pointer', transition: 'background 0.2s', whiteSpace: 'nowrap' }}
                                            onMouseEnter={e => (e.currentTarget.style.background = 'rgba(201,169,110,0.22)')}
                                            onMouseLeave={e => (e.currentTarget.style.background = 'rgba(201,169,110,0.12)')}
                                        >Apply</button>
                                    </div>
                                    {couponMessage && (
                                        <p style={{ margin: '8px 0 0', fontSize: 12, fontWeight: 500, color: couponDiscount > 0 ? 'var(--base-color,#c9a96e)' : '#f87171', display: 'flex', alignItems: 'center', gap: 5 }}>
                                            <i className={`feather ${couponDiscount > 0 ? 'icon-feather-check-circle' : 'icon-feather-alert-circle'}`} style={{ fontSize: 13 }}></i>
                                            {couponMessage}
                                        </p>
                                    )}
                                </div>
                                <a href="/shop" style={{ display: 'inline-flex', alignItems: 'center', gap: 8, color: 'rgba(255,255,255,0.5)', fontSize: 13, fontWeight: 600, textDecoration: 'none', transition: 'color 0.2s' }}
                                    onMouseEnter={e => (e.currentTarget.style.color = '#fff')}
                                    onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.5)')}>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                                    Continue Shopping
                                </a>
                            </div>
                        </div>

                        {/* ── RIGHT: Order Summary ── */}
                        <div style={{ position: 'sticky', top: 100 }}>
                            <div style={{
                                background: 'rgba(255,255,255,0.03)',
                                border: '1px solid rgba(255,255,255,0.09)',
                                borderRadius: 18,
                                overflow: 'hidden',
                            }}>
                                {/* Free shipping progress */}
                                {freeShippingThreshold > 0 && (() => {
                                    const remaining = freeShippingThreshold - subtotal;
                                    const pct = Math.min((subtotal / freeShippingThreshold) * 100, 100);
                                    return (
                                        <div style={{ padding: '18px 24px', background: remaining > 0 ? 'rgba(201,169,110,0.06)' : 'rgba(34,197,94,0.06)', borderBottom: '1px solid rgba(255,255,255,0.06)' }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 10 }}>
                                                <i className={`feather ${remaining > 0 ? 'icon-feather-truck' : 'icon-feather-check-circle'}`} style={{ fontSize: 14, color: remaining > 0 ? 'var(--base-color,#c9a96e)' : '#4ade80' }}></i>
                                                <span style={{ fontSize: 12, fontWeight: 600, color: remaining > 0 ? 'rgba(255,255,255,0.7)' : '#4ade80' }}>
                                                    {remaining > 0
                                                        ? <span>Add <strong style={{ color: 'var(--base-color,#c9a96e)' }}>{formatAmount(remaining)}</strong> more for free delivery</span>
                                                        : 'Free delivery unlocked!'
                                                    }
                                                </span>
                                            </div>
                                            <div style={{ height: 4, background: 'rgba(255,255,255,0.08)', borderRadius: 99, overflow: 'hidden' }}>
                                                <div style={{ height: '100%', width: `${pct}%`, background: remaining > 0 ? 'var(--base-color,#c9a96e)' : '#4ade80', borderRadius: 99, transition: 'width 0.5s ease' }} />
                                            </div>
                                        </div>
                                    );
                                })()}

                                <div style={{ padding: '24px 24px 0' }}>
                                    <h2 style={{ fontFamily: 'var(--font-heading, sans-serif)', color: '#fff', fontSize: 20, fontWeight: 700, margin: '0 0 20px', letterSpacing: '-0.3px' }}>Order Summary</h2>

                                    {/* Line rows */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                            <span style={{ fontSize: 13, color: 'rgba(255,255,255,0.5)' }}>Subtotal ({cartItems.length} items)</span>
                                            <span style={{ fontSize: 14, fontWeight: 600, color: '#fff' }}>{formatAmount(subtotal)}</span>
                                        </div>
                                        {totalTax > 0 && (
                                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <span style={{ fontSize: 13, color: 'rgba(255,255,255,0.5)' }}>Tax</span>
                                                <span style={{ fontSize: 14, fontWeight: 600, color: '#fff' }}>{formatAmount(totalTax)}</span>
                                            </div>
                                        )}
                                        {couponDiscount > 0 && (
                                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <span style={{ fontSize: 13, color: 'rgba(255,255,255,0.5)' }}>Discount</span>
                                                <span style={{ fontSize: 14, fontWeight: 600, color: '#4ade80' }}>−{formatAmount(couponDiscount)}</span>
                                            </div>
                                        )}
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                            <span style={{ fontSize: 13, color: 'rgba(255,255,255,0.5)' }}>Shipping</span>
                                            <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.35)', fontStyle: 'italic' }}>Calculated at checkout</span>
                                        </div>
                                    </div>

                                    {/* Divider */}
                                    <div style={{ height: 1, background: 'rgba(255,255,255,0.07)', margin: '20px 0' }} />

                                    {/* Grand total */}
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 22 }}>
                                        <span style={{ fontSize: 15, fontWeight: 700, color: '#fff', fontFamily: 'var(--font-heading, sans-serif)' }}>Total</span>
                                        <span style={{ fontSize: 22, fontWeight: 800, color: 'var(--base-color,#c9a96e)', letterSpacing: '-0.5px' }}>{formatAmount(grandTotal)}</span>
                                    </div>
                                </div>

                                {/* Checkout button */}
                                <div style={{ padding: '0 24px 24px' }}>
                                    <a
                                        href="/checkout"
                                        style={{
                                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
                                            width: '100%', height: 52,
                                            background: 'var(--base-color,#c9a96e)', color: '#0a0906',
                                            borderRadius: 10, fontWeight: 700, fontSize: 13, letterSpacing: '1.5px',
                                            textTransform: 'uppercase', textDecoration: 'none',
                                            boxShadow: '0 6px 28px rgba(201,169,110,0.30)',
                                            transition: 'background 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease',
                                        }}
                                        onMouseEnter={e => { const el = e.currentTarget as HTMLAnchorElement; el.style.background = '#b8924f'; el.style.boxShadow = '0 8px 36px rgba(201,169,110,0.45)'; el.style.transform = 'translateY(-1px)'; }}
                                        onMouseLeave={e => { const el = e.currentTarget as HTMLAnchorElement; el.style.background = 'var(--base-color,#c9a96e)'; el.style.boxShadow = '0 6px 28px rgba(201,169,110,0.30)'; el.style.transform = 'translateY(0)'; }}
                                    >
                                        Proceed to Checkout
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </a>

                                    {/* Trust badges */}
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 10, marginTop: 16 }}>
                                        {[
                                            { icon: 'icon-feather-lock', label: 'Secure Payment' },
                                            { icon: 'icon-feather-refresh-cw', label: 'Easy Returns' },
                                            { icon: 'icon-feather-headphones', label: '24/7 Support' },
                                        ].map(b => (
                                            <div key={b.label} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 5, padding: '10px 4px', background: 'rgba(255,255,255,0.03)', borderRadius: 8, border: '1px solid rgba(255,255,255,0.05)' }}>
                                                <i className={`feather ${b.icon}`} style={{ fontSize: 14, color: 'var(--base-color,#c9a96e)' }}></i>
                                                <span style={{ fontSize: 9.5, fontWeight: 600, color: 'rgba(255,255,255,0.4)', textAlign: 'center', lineHeight: 1.3, letterSpacing: '0.3px' }}>{b.label}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ) : (
                    /* ── Empty cart ── */
                    <div style={{ textAlign: 'center', paddingTop: 60, paddingBottom: 60 }}>
                        <div style={{ width: 100, height: 100, borderRadius: '50%', background: 'rgba(201,169,110,0.07)', border: '1px solid rgba(201,169,110,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 28px' }}>
                            <i className="feather icon-feather-shopping-cart" style={{ fontSize: 38, color: 'rgba(201,169,110,0.4)' }}></i>
                        </div>
                        <h2 style={{ fontFamily: 'var(--font-heading, sans-serif)', color: '#fff', fontWeight: 700, fontSize: 'clamp(22px,3vw,32px)', marginBottom: 14 }}>Your cart is empty</h2>
                        <p style={{ fontSize: 15, color: 'rgba(255,255,255,0.45)', maxWidth: 380, margin: '0 auto 32px', lineHeight: 1.6 }}>
                            Looks like you haven't added anything yet. Explore our collections to find something you'll love.
                        </p>
                        <a
                            href="/shop"
                            style={{
                                display: 'inline-flex', alignItems: 'center', gap: 10,
                                background: 'var(--base-color,#c9a96e)', color: '#0a0906',
                                padding: '14px 36px', borderRadius: 8, fontWeight: 700,
                                fontSize: 12, letterSpacing: '2px', textTransform: 'uppercase',
                                textDecoration: 'none', boxShadow: '0 4px 24px rgba(201,169,110,0.3)',
                                transition: 'all 0.25s ease',
                            }}
                            onMouseEnter={e => { const el = e.currentTarget as HTMLAnchorElement; el.style.background = '#b8924f'; el.style.transform = 'translateY(-2px)'; }}
                            onMouseLeave={e => { const el = e.currentTarget as HTMLAnchorElement; el.style.background = 'var(--base-color,#c9a96e)'; el.style.transform = 'translateY(0)'; }}
                        >
                            Explore Collection
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                )}

                {/* ── You might also like ── */}
                {suggestions.length > 0 && (
                    <div style={{ marginTop: 64, paddingTop: 48, borderTop: '1px solid rgba(255,255,255,0.06)' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 28 }}>
                            <span style={{ display: 'block', width: 24, height: 1, background: 'var(--base-color,#c9a96e)' }} />
                            <span style={{ fontSize: 10, fontWeight: 700, letterSpacing: '3px', textTransform: 'uppercase', color: 'var(--base-color,#c9a96e)' }}>You might also like</span>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 18 }}>
                            {suggestions.map((product: any) => <SuggestionCard key={product.id} product={product} />)}
                        </div>
                    </div>
                )}
            </div>

            <style>{`
                @media (max-width: 900px) {
                    .cart-layout { grid-template-columns: 1fr !important; }
                }
                @media (max-width: 640px) {
                    .d-none.d-md-grid { display: none !important; }
                    .d-md-none { display: block !important; }
                    .d-none.d-md-block { display: none !important; }
                }
            `}</style>
        </main>
    );
}
