'use client';

import { useCallback, useEffect, useState } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import LoadingSkeleton from '@/components/LoadingSkeleton';
import { useToast } from '@/components/ToastProvider';
import { extractCurrencySymbol, readStoredCoupon, storeCoupon } from '@/lib/checkout';
import { useCurrency } from '@/components/SettingsProvider';

export default function Cart() {
    const [cartItems, setCartItems] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [updatingItems, setUpdatingItems] = useState<Set<number>>(new Set());
    const [subtotal, setSubtotal] = useState(0);
    const { showToast } = useToast();
    const { currency: { symbol: currencySymbol }, formatAmount } = useCurrency();
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
    }, [clearCouponState, currencySymbol]);

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
                showToast('Cart updated successfully', 'success');
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

    return (
        <main className="no-layout-pad page-top-100">
            <section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
                <div className="container-fluid">
                    <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                        <ul>
                            <li><a href="/" className="breadcrumb-link">Home</a></li>
                            <li><a href="/shop" className="breadcrumb-link">Shop</a></li>
                            <li>Cart</li>
                        </ul>
                    </div>
                </div>
            </section>
            <section className="page-shell page-shell-tight min-h-600px d-flex align-items-center">
                <div className="container-fluid">
                    <div className="content-layout-wrapper">
                        {cartItems.length > 0 ? (
                            <div className="row align-items-start">
                                <div className="col-lg-8 pe-50px md-pe-15px md-mb-50px xs-mb-35px">

                                    {/* ── Desktop table (md and up) ── */}
                                    <div className="d-none d-md-block">
                                        <table className="table cart-products border-color-transparent-white-light !border-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col"></th>
                                                    <th className="alt-font fw-600 text-white" scope="col">Product</th>
                                                    <th scope="col"></th>
                                                    <th className="alt-font fw-600 text-white" scope="col">Price</th>
                                                    <th className="alt-font fw-600 text-white" scope="col">Quantity</th>
                                                    <th className="alt-font fw-600 text-white" scope="col">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {cartItems.map((item) => (
                                                    <tr key={item.id}>
                                                        <td className="product-remove">
                                                            <a className="fs-20 fw-500 cursor-pointer" onClick={() => removeItem(item.id)}>×</a>
                                                        </td>
                                                        <td className="product-thumbnail">
                                                            <a href={`/product/${item.product?.slug}`}>
                                                                <Image alt={item.product?.name} className="cart-product-image" src={item.product?.cover || "/images/demo-decor-store-product-01.jpg"} width={140} height={140} unoptimized />
                                                            </a>
                                                        </td>
                                                        <td className="product-name">
                                                            <a className="text-white fw-500 d-block lh-initial" href={`/product/${item.product?.slug}`}>{item.product?.name}</a>
                                                            {item.variation_names && <span className="fs-14">{item.variation_names}</span>}
                                                        </td>
                                                        <td className="product-price" data-title="Price">{formatAmount(parseFloat(item.price))}</td>
                                                        <td className="product-quantity" data-title="Quantity">
                                                            <div className="quantity" style={{ opacity: updatingItems.has(item.id) ? 0.4 : 1, transition: 'opacity 0.2s ease' }}>
                                                                <button className="qty-minus" onClick={() => updateQuantity(item.id, item.quantity - 1)} type="button" disabled={updatingItems.has(item.id)}>-</button>
                                                                <input aria-label="qty-text" className="qty-text bg-transparent text-white" readOnly type="text" value={item.quantity} />
                                                                <button className="qty-plus" onClick={() => updateQuantity(item.id, item.quantity + 1)} type="button" disabled={updatingItems.has(item.id)}>+</button>
                                                            </div>
                                                        </td>
                                                        <td className="product-subtotal" data-title="Total">{formatAmount(parseFloat(item.subtotal || (item.price * item.quantity)))}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* ── Mobile card list (xs and sm only) ── */}
                                    <div className="d-md-none" style={{ borderTop: '1px solid rgba(255,255,255,0.1)', paddingBottom: 120 }}>
                                        {cartItems.map((item) => (
                                            <div key={item.id} style={{
                                                display: 'flex',
                                                gap: '14px',
                                                padding: '16px 0',
                                                borderBottom: '1px solid rgba(255,255,255,0.08)',
                                                opacity: updatingItems.has(item.id) ? 0.5 : 1,
                                                transition: 'opacity 0.2s ease',
                                            }}>
                                                {/* Product image */}
                                                <a href={`/product/${item.product?.slug}`} style={{ flexShrink: 0 }}>
                                                    <Image
                                                        alt={item.product?.name}
                                                        src={item.product?.cover || '/images/demo-decor-store-product-01.jpg'}
                                                        width={80} height={80} unoptimized
                                                        style={{ width: 80, height: 80, objectFit: 'cover', borderRadius: 8, display: 'block' }}
                                                    />
                                                </a>

                                                {/* Details */}
                                                <div style={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', gap: 4 }}>
                                                    {/* Name + remove */}
                                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 8 }}>
                                                        <a href={`/product/${item.product?.slug}`} style={{ color: '#fff', fontWeight: 600, fontSize: 14, lineHeight: 1.3, textDecoration: 'none' }}>
                                                            {item.product?.name}
                                                        </a>
                                                        <button onClick={() => removeItem(item.id)} disabled={updatingItems.has(item.id)}
                                                            style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.4)', fontSize: 22, lineHeight: 1, cursor: 'pointer', padding: '0 2px', flexShrink: 0 }}>
                                                            ×
                                                        </button>
                                                    </div>

                                                    {/* Variant */}
                                                    {item.variation_names && (
                                                        <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.5)' }}>{item.variation_names}</span>
                                                    )}

                                                    {/* Unit price */}
                                                    <span style={{ fontSize: 13, color: 'rgba(255,255,255,0.6)' }}>
                                                        {formatAmount(parseFloat(item.price))} each
                                                    </span>

                                                    {/* Qty controls + line total */}
                                                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 6 }}>
                                                        <div className="quantity" style={{ display: 'flex', alignItems: 'center' }}>
                                                            <button className="qty-minus" onClick={() => updateQuantity(item.id, item.quantity - 1)} type="button" disabled={updatingItems.has(item.id)} style={{ minWidth: 40, minHeight: 40 }}>-</button>
                                                            <input aria-label="qty-text" className="qty-text bg-transparent text-white" readOnly type="text" value={item.quantity} style={{ textAlign: 'center', minWidth: 36 }} />
                                                            <button className="qty-plus" onClick={() => updateQuantity(item.id, item.quantity + 1)} type="button" disabled={updatingItems.has(item.id)} style={{ minWidth: 40, minHeight: 40 }}>+</button>
                                                        </div>
                                                        <span style={{ fontSize: 16, fontWeight: 700, color: '#fff' }}>
                                                            {formatAmount(parseFloat(item.subtotal || (item.price * item.quantity)))}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {/* ── Coupon + Continue Shopping ── */}
                                    <div className="row mt-20px">
                                        <div className="col-12 col-md-7 col-xl-7">
                                            <div className="coupon-code-panel">
                                                <input
                                                    className="bg-dark-gray border-radius-4px text-white border-color-transparent-white-light"
                                                    placeholder="Coupon code"
                                                    type="text"
                                                    value={couponCode}
                                                    onChange={(e) => setCouponCode(e.target.value)}
                                                    onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), applyCoupon())}
                                                />
                                                <button type="button" className="btn apply-coupon-btn fs-13 fw-600 text-uppercase text-white" onClick={applyCoupon}>Apply</button>
                                            </div>
                                            {couponMessage && <p className={`fs-13 mt-10px mb-0 ${couponDiscount > 0 ? 'text-base-color' : 'text-red'}`}>{couponMessage}</p>}
                                        </div>
                                        <div className="col-12 col-md-5 col-xl-5 text-start text-md-end mt-15px mt-md-0">
                                            <a className="btn btn-small border-1 btn-round-edge btn-transparent-white-light text-white text-transform-none" href="/shop">Continue Shopping</a>
                                        </div>
                                    </div>
                                </div>

                                {/* ── Mobile sticky checkout bar (hidden on lg+) ── */}
                                <div className="d-lg-none" style={{
                                    position: 'sticky', bottom: 0, zIndex: 50,
                                    background: 'linear-gradient(to top, rgba(20,20,20,0.98) 80%, transparent)',
                                    padding: '16px 20px 20px',
                                    marginLeft: -12, marginRight: -12,
                                }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 }}>
                                        <span style={{ color: 'rgba(255,255,255,0.6)', fontSize: 13 }}>Total</span>
                                        <span style={{ color: '#fff', fontWeight: 700, fontSize: 18 }}>
                                            {formatAmount(Math.max(
                                                subtotal
                                                + cartItems.reduce((acc, item) => acc + parseFloat(item.tax || 0), 0)
                                                - couponDiscount,
                                                0
                                            ))}
                                        </span>
                                    </div>
                                    <a href="/checkout" className="btn btn-base-color btn-large btn-round-edge btn-box-shadow w-100 text-transform-none">
                                        Proceed to Checkout
                                    </a>
                                </div>

                                <div className="col-lg-4">
                                    <div className="dark-card-bg border-radius-6px p-50px xl-p-30px lg-p-25px xs-p-20px ui-panel ui-panel-lg">
                                        <span className="fs-26 alt-font fw-600 text-white mb-5px d-block">Cart totals</span>
                                        <table className="w-100 total-price-table">
                                            <tbody>
                                                <tr>
                                                    <th className="w-45 fw-600 text-white alt-font">Subtotal</th>
                                                    <td className="text-white fw-600">{formatAmount(subtotal)}</td>
                                                </tr>
                                                {(() => {
                                                    const totalTax = cartItems.reduce((acc, item) => acc + parseFloat(item.tax || 0), 0);
                                                    return totalTax > 0 ? (
                                                        <tr>
                                                            <th className="w-45 fw-600 text-white alt-font">Tax</th>
                                                            <td className="text-white fw-600">{formatAmount(totalTax)}</td>
                                                        </tr>
                                                    ) : null;
                                                })()}
                                                {couponDiscount > 0 && (
                                                    <tr>
                                                        <th className="w-45 fw-600 text-white alt-font">Discount</th>
                                                        <td className="text-base-color fw-600">-{formatAmount(couponDiscount)}</td>
                                                    </tr>
                                                )}
                                                <tr>
                                                    <th className="w-45 fw-600 text-white alt-font">Shipping</th>
                                                    <td className="text-white fw-600">Calculated at checkout</td>
                                                </tr>
                                                <tr className="total-amount">
                                                    <th className="fw-600 text-white alt-font pb-0">Total</th>
                                                    <td className="pb-0" data-title="Total">
                                                        <h6 className="d-block fw-700 mb-0 text-white alt-font">
                                                            {formatAmount(Math.max(
                                                                subtotal
                                                                + cartItems.reduce((acc, item) => acc + parseFloat(item.tax || 0), 0)
                                                                - couponDiscount,
                                                                0
                                                            ))}
                                                        </h6>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <a className="btn btn-base-color btn-extra-large btn-switch-text btn-round-edge btn-box-shadow w-100 text-transform-none mt-25px" href="/checkout">
                                            <span>
                                                <span className="btn-double-text" data-text="Proceed to checkout">Proceed to checkout</span>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="row justify-content-center">
                                <div className="col-md-8 col-lg-6">
                                    <div className="text-center py-80px md-py-50px text-white">
                                        <div className="mb-30px">
                                            <i className="bi bi-cart-x fs-80 text-white/20"></i>
                                        </div>
                                        <h2 className="alt-font fw-600 mb-15px">Your cart is empty</h2>
                                        <p className="fs-18 text-white/60 mb-35px mx-auto max-w-400px">Looks like you haven't added anything to your cart yet. Explore our collections to find something you'll love.</p>
                                        <a href="/shop" className="btn btn-primary btn-large btn-round-edge px-45px btn-box-shadow">
                                            <span>
                                                <span className="btn-double-text" data-text="Start Shopping">Start Shopping</span>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </section>
        </main>
    );
}
