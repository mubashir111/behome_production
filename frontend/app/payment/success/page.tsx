'use client';

import { Suspense, useEffect, useState } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import PageLoadingShell from '@/components/PageLoadingShell';
import { apiFetch } from '@/lib/api';
import { useCurrency } from '@/components/SettingsProvider';

type VerifyState = 'verifying' | 'success' | 'failed';

function PaymentSuccessContent() {
    const searchParams   = useSearchParams();
    const orderId        = searchParams.get('order_id');
    const paymentIntent  = searchParams.get('payment_intent');
    const redirectStatus = searchParams.get('redirect_status');

    const [state, setState]             = useState<VerifyState>('verifying');
    const [errorMsg, setErrorMsg]       = useState('');
    const [timedOut, setTimedOut]       = useState(false);
    const [orderDetails, setOrderDetails] = useState<any>(null);
    const { formatAmount } = useCurrency();

    // Safety timeout
    useEffect(() => {
        const t = setTimeout(() => setTimedOut(true), 15000);
        return () => clearTimeout(t);
    }, []);

    const fetchOrderDetails = (id: string) => {
        apiFetch(`/v1/orders/${id}`)
            .then(res => { if (res?.status && res.data) setOrderDetails(res.data); })
            .catch(() => {});
    };

    useEffect(() => {
        if (!orderId) { setState('failed'); setErrorMsg('Missing order ID.'); return; }

        // Abort the request after 20 seconds to prevent hanging forever
        const controller = new AbortController();
        const timeoutId  = setTimeout(() => {
            controller.abort();
            setState('failed');
            setErrorMsg('Verification timed out. Please check your orders page or contact support.');
        }, 20000);

        const cleanup = () => clearTimeout(timeoutId);

        if (paymentIntent) {
            apiFetch(`/v1/payment/verify/${orderId}`, {
                method: 'POST',
                body: JSON.stringify({ payment_gateway: 'stripe', payment_intent: paymentIntent, redirect_status: redirectStatus }),
                signal: controller.signal,
            } as RequestInit)
                .then(res => {
                    cleanup();
                    if (res?.status) {
                        setState('success');
                        window.dispatchEvent(new Event('cart:updated'));
                        fetchOrderDetails(orderId);
                    } else {
                        setState('failed');
                        setErrorMsg(res?.message || 'Payment verification failed.');
                    }
                })
                .catch(err => {
                    cleanup();
                    if (err?.name !== 'AbortError') {
                        setState('failed');
                        setErrorMsg(err.message || 'Could not reach the server.');
                    }
                });
        } else {
            apiFetch(`/v1/orders/${orderId}`, { signal: controller.signal } as RequestInit)
                .then(res => {
                    cleanup();
                    if (res?.status && res.data) {
                        const isPaid   = res.data.payment_status === 5;
                        const isCod    = ['cashondelivery', 'credit'].some((s: string) =>
                            (res.data.payment_method_name ?? '').toLowerCase().includes(s.replace('cashondelivery', 'cash'))
                        );
                        const isActive = res.data.active === 5;
                        if (isPaid || isCod || isActive) {
                            setState('success');
                            setOrderDetails(res.data);
                            window.dispatchEvent(new Event('cart:updated'));
                        } else {
                            setState('failed');
                            setErrorMsg('Your payment could not be confirmed. Please contact support or try again.');
                        }
                    } else {
                        setState('failed');
                        setErrorMsg('Could not find your order. Please contact support.');
                    }
                })
                .catch(err => {
                    cleanup();
                    if (err?.name !== 'AbortError') {
                        setState('failed');
                        setErrorMsg(err.message || 'Could not reach the server.');
                    }
                });
        }

        return cleanup;
    }, [orderId, paymentIntent, redirectStatus]);

    if (state === 'verifying') {
        return (
            <main>
                <section className="top-space-padding pb-0">
                    <div className="container">
                        <div className="row justify-content-center">
                            <div className="col-md-7 text-center">
                                <div style={{ background: 'rgba(15,15,25,0.95)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: '60px 40px' }}>
                                    <div className="spinner-border mb-20px" role="status" style={{ width: 48, height: 48, color: 'var(--base-color)' }}></div>
                                    <h4 className="text-white alt-font fw-600 mb-10px">Confirming your order…</h4>
                                    {timedOut ? (
                                        <p style={{ color: 'rgba(255,255,255,0.5)', marginBottom: 0 }}>
                                            This is taking longer than expected.{' '}
                                            <Link href="/account" style={{ color: 'var(--base-color)' }}>Check My Orders</Link>
                                        </p>
                                    ) : (
                                        <p style={{ color: 'rgba(255,255,255,0.4)', marginBottom: 0 }}>Please wait a moment.</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (state === 'failed') {
        return (
            <main>
                <section className="top-space-padding pb-50px">
                    <div className="container">
                        <div className="row justify-content-center">
                            <div className="col-md-7 text-center">
                                <div style={{ background: 'rgba(15,15,25,0.95)', border: '1px solid rgba(248,113,113,0.25)', borderRadius: 16, padding: '60px 40px' }}>
                                    <div style={{ width: 72, height: 72, borderRadius: '50%', background: 'rgba(248,113,113,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 24px' }}>
                                        <i className="bi bi-x-lg" style={{ fontSize: 28, color: '#f87171' }}></i>
                                    </div>
                                    <h4 className="text-white alt-font fw-600 mb-10px">Payment Not Confirmed</h4>
                                    <p style={{ color: 'rgba(255,255,255,0.5)', marginBottom: 32 }}>{errorMsg || 'Something went wrong. Please try again or contact support.'}</p>
                                    <div className="d-flex flex-column flex-sm-row justify-content-center gap-3">
                                        <Link href="/checkout" className="btn btn-base-color btn-medium btn-round-edge">Try Again</Link>
                                        <Link href="/account" className="btn btn-transparent-white btn-medium btn-round-edge">My Orders</Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    // ── SUCCESS STATE ─────────────────────────────────────────────────────────
    const products: any[] = orderDetails?.order_products ?? orderDetails?.products ?? [];
    const shippingAddress = (orderDetails?.order_address ?? [])[0] ?? null;

    const printInvoice = () => {
        const win = window.open('', '_blank');
        if (!win) return;

        // Escape HTML to prevent XSS in invoice
        const esc = (s: any) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        // Address from order_address array
        const addr = (orderDetails?.order_address ?? [])[0] ?? shippingAddress ?? null;
        const addrLines = addr ? [
            addr.full_name ?? addr.name,
            addr.phone,
            addr.address,
            [addr.city, addr.state, addr.zip_code].filter(Boolean).join(', '),
            addr.country,
        ].filter(Boolean).map(esc) : [];

        // Items — show actual price paid: (price × qty) - discount
        // Uses formatAmount from useCurrency for consistent rounding with order totals
        const rows = products.map((item: any) => {
            const name        = esc(item.product_name || item.product?.name || 'Product');
            const qty         = Math.abs(item.quantity || 1);
            const priceRaw    = parseFloat(item.price || 0);
            const discountRaw = parseFloat(item.discount || 0);
            const lineTotal   = (priceRaw * qty) - discountRaw;
            const unitEff     = qty > 0 ? lineTotal / qty : lineTotal;
            const variant     = item.variation_names ? `<br/><small style="color:#666">${esc(item.variation_names)}</small>` : '';
            const origHtml    = discountRaw > 0 ? `<br/><small style="color:#aaa;text-decoration:line-through">${esc(item.currency_price)}</small>` : '';
            return `<tr>
                <td style="padding:10px 12px;border-bottom:1px solid #eee">${name}${variant}</td>
                <td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:center">${qty}</td>
                <td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:right">${esc(formatAmount(unitEff))}${origHtml}</td>
                <td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:right;font-weight:600">${esc(formatAmount(lineTotal))}</td>
            </tr>`;
        }).join('');

        // Totals — use raw numbers and format them locally for consistency
        const totalsRows = [
            { label: 'Subtotal', value: formatAmount(orderDetails?.subtotal || 0) },
            (orderDetails?.tax > 0) && { label: 'Tax', value: formatAmount(orderDetails.tax) },
            (orderDetails?.shipping_charge > 0) && { label: 'Shipping', value: formatAmount(orderDetails.shipping_charge) },
            (orderDetails?.discount > 0) && { label: 'Discount', value: formatAmount(orderDetails.discount), negative: true },
        ].filter(Boolean).map((row: any) =>
            `<tr><td style="padding:6px 12px;text-align:right;color:#555">${row.label}</td>
             <td style="padding:6px 12px;text-align:right">${row.negative ? '-' : ''}${row.value}</td></tr>`
        ).join('');

        const date = orderDetails?.order_datetime
            || (orderDetails?.created_at ? new Date(orderDetails.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) : new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }));

        const isPaid = orderDetails?.payment_status_name || (orderDetails?.payment_status === 5 ? 'Paid' : 'Unpaid');
        const paidColor = isPaid === 'Paid' ? '#16a34a' : '#dc2626';

        win.document.write(`<!DOCTYPE html><html><head><title>Invoice #${orderId}</title>
        <style>
            *{box-sizing:border-box;margin:0;padding:0}
            body{font-family:Arial,sans-serif;font-size:14px;color:#222;padding:40px;max-width:780px;margin:0 auto}
            .header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:40px;padding-bottom:24px;border-bottom:2px solid #111}
            .brand{font-size:24px;font-weight:700;letter-spacing:-0.5px}
            .invoice-meta{text-align:right;color:#555;font-size:13px;line-height:1.8}
            .invoice-meta strong{color:#111;font-size:16px;display:block;margin-bottom:4px}
            .paid-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-top:4px}
            .addresses{display:flex;gap:40px;margin-bottom:32px}
            .address-block{flex:1}
            .address-block h4{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#999;margin-bottom:8px}
            .address-block p{color:#333;line-height:1.7;font-size:13px}
            table{width:100%;border-collapse:collapse;margin-bottom:0}
            thead th{background:#111;color:#fff;padding:10px 12px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:0.06em}
            thead th:nth-child(2){text-align:center}thead th:nth-child(3),thead th:nth-child(4){text-align:right}
            .totals-table{margin-left:auto;width:280px;margin-top:0;border-top:2px solid #111}
            .totals-table td{font-size:13px;color:#333}
            .grand-total td{font-size:15px;font-weight:700;color:#111;padding-top:10px!important;border-top:1px solid #ddd}
            .footer{margin-top:48px;padding-top:20px;border-top:1px solid #eee;font-size:12px;color:#999;text-align:center;line-height:1.8}
            @media print{body{padding:20px}button{display:none!important}}
        </style></head><body>
        <div class="header">
            <div><div class="brand">Behom</div><div style="font-size:12px;color:#777;margin-top:4px">Premium Architectural Decor &amp; Furniture</div></div>
            <div class="invoice-meta">
                <strong>INVOICE</strong>
                Order #${esc(orderId)}<br/>
                Date: ${esc(date)}<br/>
                ${orderDetails?.payment_method_name ? 'Payment: ' + esc(orderDetails.payment_method_name) + '<br/>' : ''}
                <span class="paid-badge" style="background:${paidColor}22;color:${paidColor};border:1px solid ${paidColor}44">${isPaid}</span>
            </div>
        </div>
        ${addrLines.length ? `<div class="addresses"><div class="address-block"><h4>Deliver to</h4><p>${addrLines.join('<br/>')}</p></div></div>` : ''}
        <table>
            <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>${rows}</tbody>
        </table>
        <table class="totals-table">
            <tbody>
                ${totalsRows}
                <tr class="grand-total">
                    <td style="padding:10px 12px;text-align:right">Total</td>
                    <td style="padding:10px 12px;text-align:right">${formatAmount(orderDetails?.total || 0)}</td>
                </tr>
            </tbody>
        </table>
        <div class="footer">Thank you for shopping with Behom &nbsp;·&nbsp; For support visit behom.co.uk/contact</div>
        <script>window.onload=function(){window.print();}<\/script>
        </body></html>`);
        win.document.close();
    };

    return (
        <main className="no-layout-pad page-top-100">
            {/* Breadcrumb */}
            <section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
                <div className="container-fluid">
                    <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                        <ul>
                            <li><a href="/" className="breadcrumb-link">Home</a></li>
                            <li>Order Confirmation</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section className="pb-80px md-pb-50px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
                <div className="container-fluid">
                    <div className="row justify-content-center">
                        <div className="col-lg-9 col-xl-8">

                            {/* ── Header card ── */}
                            <div style={{
                                background: 'rgba(15,15,25,0.97)',
                                border: '1px solid rgba(197,160,89,0.2)',
                                borderRadius: 16,
                                overflow: 'hidden',
                                marginBottom: 20,
                            }}>
                                {/* Gold top bar */}
                                <div style={{ height: 4, background: 'linear-gradient(90deg, var(--base-color), rgba(197,160,89,0.2))' }} />
                                <div style={{ padding: '40px 40px 36px', display: 'flex', alignItems: 'center', gap: 24, flexWrap: 'wrap' }}>
                                    <div style={{ width: 72, height: 72, borderRadius: '50%', background: 'rgba(74,222,128,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, border: '2px solid rgba(74,222,128,0.3)' }}>
                                        <i className="bi bi-check-lg" style={{ fontSize: 32, color: '#4ade80' }}></i>
                                    </div>
                                    <div style={{ flex: 1, minWidth: 0 }}>
                                        <p style={{ margin: '0 0 4px', fontSize: 12, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: '#4ade80' }}>Order Confirmed</p>
                                        <h2 style={{ margin: '0 0 6px', color: '#fff', fontSize: 'clamp(20px, 4vw, 28px)', fontWeight: 700 }}>Thank you for your order!</h2>
                                        <p style={{ margin: 0, color: 'rgba(255,255,255,0.5)', fontSize: 14 }}>
                                            Order <strong style={{ color: 'var(--base-color)' }}>#{orderId}</strong> has been placed. We'll send you a confirmation email shortly.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }} className="order-confirm-grid">
                                <style>{`@media(max-width:640px){.order-confirm-grid{grid-template-columns:1fr !important;}}`}</style>

                                {/* ── What happens next ── */}
                                <div style={{ background: 'rgba(15,15,25,0.97)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 16, padding: '28px 28px' }}>
                                    <p style={{ margin: '0 0 20px', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'rgba(255,255,255,0.4)' }}>What happens next</p>
                                    {[
                                        { icon: 'icon-feather-mail', label: 'Confirmation email sent to your inbox' },
                                        { icon: 'icon-feather-package', label: 'We prepare and pack your items' },
                                        { icon: 'icon-feather-truck', label: 'Order shipped with tracking details' },
                                        { icon: 'icon-feather-home', label: 'Delivered to your address' },
                                    ].map((step, i) => (
                                        <div key={i} style={{ display: 'flex', alignItems: 'flex-start', gap: 14, marginBottom: i < 3 ? 18 : 0 }}>
                                            <div style={{ width: 32, height: 32, borderRadius: '50%', border: '1px solid rgba(197,160,89,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <i className={`feather ${step.icon}`} style={{ fontSize: 13, color: 'var(--base-color)' }}></i>
                                            </div>
                                            <div>
                                                <p style={{ margin: '6px 0 0', fontSize: 13, color: 'rgba(255,255,255,0.65)', lineHeight: 1.5 }}>{step.label}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* ── Shipping address ── */}
                                <div style={{ background: 'rgba(15,15,25,0.97)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 16, padding: '28px 28px' }}>
                                    <p style={{ margin: '0 0 16px', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'rgba(255,255,255,0.4)' }}>Shipping to</p>
                                    {shippingAddress ? (
                                        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                                            <p style={{ margin: 0, color: '#fff', fontWeight: 600, fontSize: 15 }}>{shippingAddress.full_name}</p>
                                            {shippingAddress.phone && <p style={{ margin: 0, color: 'rgba(255,255,255,0.5)', fontSize: 13 }}>{shippingAddress.phone}</p>}
                                            {shippingAddress.address && <p style={{ margin: 0, color: 'rgba(255,255,255,0.5)', fontSize: 13 }}>{shippingAddress.address}</p>}
                                            <p style={{ margin: 0, color: 'rgba(255,255,255,0.5)', fontSize: 13 }}>
                                                {[shippingAddress.city, shippingAddress.state, shippingAddress.zip_code].filter(Boolean).join(', ')}
                                            </p>
                                            {shippingAddress.country && <p style={{ margin: 0, color: 'rgba(255,255,255,0.5)', fontSize: 13 }}>{shippingAddress.country}</p>}
                                        </div>
                                    ) : (
                                        <p style={{ margin: 0, color: 'rgba(255,255,255,0.3)', fontSize: 13 }}>Address details will appear in your email.</p>
                                    )}

                                    {/* Payment method */}
                                    {orderDetails?.payment_method_name && (
                                        <div style={{ marginTop: 20, paddingTop: 20, borderTop: '1px solid rgba(255,255,255,0.06)' }}>
                                            <p style={{ margin: '0 0 6px', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'rgba(255,255,255,0.4)' }}>Payment</p>
                                            <p style={{ margin: 0, color: '#fff', fontSize: 13, display: 'flex', alignItems: 'center', gap: 8 }}>
                                                <i className="feather icon-feather-credit-card" style={{ color: 'var(--base-color)', fontSize: 14 }}></i>
                                                {orderDetails.payment_method_name}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* ── Order items ── */}
                            {products.length > 0 && (
                                <div style={{ background: 'rgba(15,15,25,0.97)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 16, overflow: 'hidden', marginTop: 20 }}>
                                    <div style={{ padding: '20px 28px', borderBottom: '1px solid rgba(255,255,255,0.06)' }}>
                                        <p style={{ margin: 0, fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'rgba(255,255,255,0.4)' }}>
                                            Items ordered ({products.length})
                                        </p>
                                    </div>
                                    <div style={{ padding: '8px 0' }}>
                                        {products.map((item: any, i: number) => {
                                            const itemPrice    = parseFloat(item.price || item.unit_price || 0);
                                            const itemQty      = Math.abs(parseInt(item.quantity || 1));
                                            const itemDiscount = parseFloat(item.discount || 0);
                                            const originalTotal = itemPrice * itemQty;
                                            const imgSrc       = item.product?.cover || item.cover || '';
                                            return (
                                                <div key={i} style={{
                                                    display: 'flex', alignItems: 'center', gap: 16,
                                                    padding: '16px 28px',
                                                    borderBottom: i < products.length - 1 ? '1px solid rgba(255,255,255,0.04)' : 'none',
                                                }}>
                                                    {/* Image */}
                                                    <div style={{ width: 56, height: 56, borderRadius: 10, overflow: 'hidden', flexShrink: 0, background: 'rgba(255,255,255,0.04)', border: '1px solid rgba(255,255,255,0.06)' }}>
                                                        {imgSrc ? (
                                                            /* eslint-disable-next-line @next/next/no-img-element */
                                                            <img src={imgSrc} alt={item.product?.name || item.product_name || ''} style={{ width: '100%', height: '100%', objectFit: 'cover' }} onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }} />
                                                        ) : (
                                                            <div style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                                <i className="feather icon-feather-package" style={{ color: 'rgba(255,255,255,0.2)', fontSize: 20 }}></i>
                                                            </div>
                                                        )}
                                                    </div>
                                                    {/* Name + variant */}
                                                    <div style={{ flex: 1, minWidth: 0 }}>
                                                        <p style={{ margin: 0, color: '#fff', fontWeight: 600, fontSize: 14, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                                            {item.product?.name || item.product_name || 'Product'}
                                                        </p>
                                                        {item.variation_names && (
                                                            <p style={{ margin: '2px 0 0', color: 'rgba(255,255,255,0.4)', fontSize: 12 }}>{item.variation_names}</p>
                                                        )}
                                                        <p style={{ margin: '2px 0 0', color: 'rgba(255,255,255,0.35)', fontSize: 12 }}>Qty: {itemQty}</p>
                                                    </div>
                                                    {/* Price — show original, with effective price below if discounted */}
                                                    <div style={{ textAlign: 'right', flexShrink: 0 }}>
                                                        <p style={{ margin: 0, color: 'var(--base-color)', fontWeight: 700, fontSize: 14 }}>
                                                            {formatAmount(originalTotal)}
                                                        </p>
                                                        {itemDiscount > 0 && (
                                                            <p style={{ margin: '2px 0 0', color: '#4ade80', fontSize: 11, fontWeight: 600 }}>
                                                                -{formatAmount(itemDiscount)} off
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>

                                    {/* Totals */}
                                    {orderDetails && (
                                        <div style={{ padding: '16px 28px', borderTop: '1px solid rgba(255,255,255,0.06)', display: 'flex', flexDirection: 'column', gap: 10 }}>
                                            {[
                                                { label: 'Subtotal', value: orderDetails.subtotal },
                                                orderDetails.tax > 0 && { label: 'Tax', value: orderDetails.tax },
                                                orderDetails.shipping_charge > 0 && { label: 'Shipping', value: orderDetails.shipping_charge },
                                                orderDetails.discount > 0 && { label: 'Discount', value: -orderDetails.discount, negative: true },
                                            ].filter(Boolean).map((row: any, i) => (
                                                <div key={i} style={{ display: 'flex', justifyContent: 'space-between' }}>
                                                    <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 13 }}>{row.label}</span>
                                                    <span style={{ color: row.negative ? '#4ade80' : 'rgba(255,255,255,0.7)', fontSize: 13, fontWeight: 600 }}>
                                                        {row.negative ? '-' : ''}{formatAmount(Math.abs(parseFloat(row.value || 0)))}
                                                    </span>
                                                </div>
                                            ))}
                                            <div style={{ display: 'flex', justifyContent: 'space-between', paddingTop: 12, borderTop: '1px solid rgba(255,255,255,0.08)' }}>
                                                <span style={{ color: '#fff', fontSize: 15, fontWeight: 700 }}>Total</span>
                                                <span style={{ color: 'var(--base-color)', fontSize: 17, fontWeight: 700 }}>{formatAmount(parseFloat(orderDetails.total || 0))}</span>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* ── Actions ── */}
                            <div style={{ display: 'flex', gap: 12, marginTop: 24, flexWrap: 'wrap' }}>
                                <Link href="/shop" className="btn btn-base-color btn-medium btn-round-edge" style={{ flex: 1, textAlign: 'center', minWidth: 160 }}>
                                    Continue Shopping
                                </Link>
                                <Link href="/account?tab=orders" className="btn btn-transparent-white btn-medium btn-round-edge" style={{ flex: 1, textAlign: 'center', minWidth: 160 }}>
                                    View My Orders
                                </Link>
                                {orderDetails && (
                                    <button
                                        onClick={printInvoice}
                                        className="btn btn-transparent-white btn-medium btn-round-edge"
                                        style={{ flex: 1, textAlign: 'center', minWidth: 160 }}
                                    >
                                        <i className="feather icon-feather-download me-5px"></i> Download Invoice
                                    </button>
                                )}
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}

export default function PaymentSuccess() {
    return (
        <Suspense fallback={<PageLoadingShell variant="message" />}>
            <PaymentSuccessContent />
        </Suspense>
    );
}
