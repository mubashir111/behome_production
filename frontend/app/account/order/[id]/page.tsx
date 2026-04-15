'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import Link from 'next/link';
import { useParams, useSearchParams, useRouter } from 'next/navigation';
import { useToast } from '@/components/ToastProvider';
import { loadStripe } from '@stripe/stripe-js';
import { Elements } from '@stripe/react-stripe-js';
import StripePaymentForm from '@/components/StripePaymentForm';

const STATUS_COLORS: Record<number, string> = {
    1: '#f59e0b',
    5: '#3b82f6',
    7: '#8b5cf6',
    10: '#10b981',
    15: '#ef4444',
    20: '#ef4444',
};

const STATUS_NAMES: Record<number, string> = {
    1: 'Pending', 5: 'Confirmed', 7: 'On the Way', 10: 'Delivered', 15: 'Cancelled', 20: 'Rejected',
};

type Message = { id: number; sender_type: string; sender_name: string; message: string; created_at: string; is_mine: boolean };

export default function OrderDetail() {
    const params = useParams();
    const searchParams = useSearchParams();
    const router = useRouter();
    const { showToast } = useToast();
    const id = params.id as string;
    const [isNewOrder, setIsNewOrder] = useState(false);

    useEffect(() => {
        if (searchParams.get('status') === 'success') {
            setIsNewOrder(true);
            // Remove ?status=success from URL so banner won't reappear on refresh
            router.replace(`/account/order/${id}`);
        }
    }, []);

    const [order, setOrder] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    // Actions
    const [cancelling, setCancelling] = useState(false);
    const [cancelReason, setCancelReason] = useState('');
    const [showCancelModal, setShowCancelModal] = useState(false);

    // Return/Refund
    const [showReturnModal, setShowReturnModal] = useState(false);
    const [returnReasons, setReturnReasons] = useState<any[]>([]);
    const [returnForm, setReturnForm] = useState({ reason_id: '', note: '' });
    const [submittingReturn, setSubmittingReturn] = useState(false);
    const [existingReturn, setExistingReturn] = useState<any>(null);

    // Messages
    const [messages, setMessages] = useState<Message[]>([]);
    const [msgText, setMsgText] = useState('');
    const [sendingMsg, setSendingMsg] = useState(false);
    const [showMessages, setShowMessages] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    // Stripe
    const [stripePromise, setStripePromise] = useState<any>(null);
    const [stripeOptions, setStripeOptions] = useState<any>(null);
    const [initiatingPayment, setInitiatingPayment] = useState(false);
    const paymentSectionRef = useRef<HTMLDivElement>(null);

    // Auto-scroll to inline payment section
    useEffect(() => {
        if (stripeOptions) {
            setTimeout(() => {
                paymentSectionRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
    }, [stripeOptions]);

    const fetchOrder = useCallback(async () => {
        try {
            const response = await apiFetch(`/orders/${id}`);
            if (response.status) {
                setOrder(response.data);
                setExistingReturn(response.data.return_and_refund ?? null);
            } else {
                setError('Order not found');
            }
        } catch (err: any) {
            setError(err.message || 'Failed to load order');
        } finally {
            setLoading(false);
        }
    }, [id]);

    const fetchMessages = useCallback(async () => {
        try {
            const res = await apiFetch(`/orders/${id}/messages`);
            if (res.status) setMessages(res.data);
        } catch { }
    }, [id]);

    const fetchReturnReasons = useCallback(async () => {
        try {
            const res = await apiFetch('/frontend/return-reason');
            const items = Array.isArray(res) ? res : Array.isArray(res?.data) ? res.data : [];
            setReturnReasons(items);
            if (items.length > 0) setReturnForm(f => ({ ...f, reason_id: String(items[0].id) }));
        } catch { }
    }, []);

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!token) { window.location.href = '/account'; return; }
        fetchOrder();
    }, [fetchOrder]);

    useEffect(() => {
        if (showMessages) { fetchMessages(); }
    }, [showMessages, fetchMessages]);

    useEffect(() => {
        if (showReturnModal && returnReasons.length === 0) fetchReturnReasons();
    }, [showReturnModal, fetchReturnReasons, returnReasons.length]);

    // Auto-open return modal when navigated from orders list with ?action=return
    useEffect(() => {
        if (order && searchParams.get('action') === 'return') {
            setShowReturnModal(true);
        }
    }, [order, searchParams]);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    const cancelOrder = async () => {
        setCancelling(true);
        try {
            const res = await apiFetch(`/orders/${id}/cancel`, {
                method: 'POST',
                body: JSON.stringify({ reason: cancelReason }),
            });
            if (res.status) {
                setShowCancelModal(false);
                if (res.data?.type === 'requested') {
                    showToast('Cancellation request submitted. Our team will review and respond within 6–7 working days.', 'success');
                    // Refresh messages so the request shows in the thread
                    if (showMessages) fetchMessages();
                } else {
                    showToast('Cancelled successfully', 'success');
                }
                fetchOrder();
            } else {
                showToast(res.message || 'Failed to cancel order.', 'error');
            }
        } catch (e: any) {
            showToast(e.message || 'Failed to cancel order.', 'error');
        } finally {
            setCancelling(false);
        }
    };

    const submitReturn = async () => {
        if (!returnForm.reason_id) return;
        setSubmittingReturn(true);
        try {
            const products = order.order_products?.map((item: any) => {
                const qty = Math.abs(item.quantity ?? item.order_quantity ?? 1);
                const price = parseFloat(item.price ?? 0);
                return {
                    product_id: item.product_id,
                    has_variation: item.has_variation ?? false,
                    variation_id: item.variation_id ?? '',
                    variation_names: item.variation_names ?? '',
                    order_quantity: qty,
                    quantity: qty,
                    price: price,
                    total: parseFloat(item.total ?? 0),
                    tax: parseFloat(item.tax ?? 0),
                    return_price: price * qty,
                };
            });

            const res = await apiFetch(`/frontend/return-order/request/${id}`, {
                method: 'POST',
                body: JSON.stringify({
                    return_reason_id: parseInt(returnForm.reason_id),
                    note: returnForm.note,
                    order_id: parseInt(id),
                    order_serial_no: order.order_serial_no,
                    products: JSON.stringify(products),
                }),
            });

            if (res.status || res.data) {
                showToast('Return request submitted. Once approved, your refund will be issued to your original payment method within 5–7 days.', 'success');
                setShowReturnModal(false);
                fetchOrder();
            } else {
                showToast(res.message || 'Failed to submit return request.', 'error');
            }
        } catch (e: any) {
            showToast(e.message || 'Failed to submit return request.', 'error');
        } finally {
            setSubmittingReturn(false);
        }
    };

    const sendMessage = async () => {
        if (!msgText.trim()) return;
        setSendingMsg(true);
        try {
            const res = await apiFetch(`/orders/${id}/messages`, {
                method: 'POST',
                body: JSON.stringify({ message: msgText.trim() }),
            });
            if (res.status) {
                setMessages(prev => [...prev, res.data]);
                setMsgText('');
            }
        } catch { }
        setSendingMsg(false);
    };

    const initiateOnlinePayment = async () => {
        setInitiatingPayment(true);
        try {
            const res = await apiFetch('/payment/initiate', {
                method: 'POST',
                body: JSON.stringify({
                    order_id: parseInt(id),
                    payment_gateway: 'stripe',
                }),
            });

            if (res.status && res.data?.client_secret) {
                setStripePromise(loadStripe(res.data.publishableKey));
                setStripeOptions({
                    clientSecret: res.data.client_secret,
                    appearance: { theme: 'night', labels: 'floating' },
                });
            } else {
                showToast(res.message || 'Failed to initiate payment. Please try again.', 'error');
            }
        } catch (e: any) {
            showToast(e.message || 'An unexpected error occurred.', 'error');
        } finally {
            setInitiatingPayment(false);
        }
    };

    if (loading) return (
        <main><section className="page-shell"><div className="container text-center">
            <div className="spinner-border text-white" role="status"></div>
            <p className="text-white mt-20px">Loading order...</p>
        </div></section></main>
    );

    if (error || !order) return (
        <main><section className="page-shell"><div className="container text-center">
            <i className="feather icon-feather-alert-circle text-white fs-50 mb-20px d-block opacity-5"></i>
            <p className="text-white">{error || 'Order not found'}</p>
            <Link href="/account?tab=orders" className="btn btn-medium btn-round-edge btn-base-color mt-15px">Back to Orders</Link>
        </div></section></main>
    );

    const statusColor = STATUS_COLORS[order.status as number] || '#888';

    const printInvoice = () => {
        const win = window.open('', '_blank');
        if (!win) return;

        // Address: API returns order_address as array
        const addr = (order.order_address ?? [])[0] ?? null;
        const addrLines = addr ? [
            addr.full_name ?? addr.name,
            addr.phone,
            addr.address,
            [addr.city, addr.state, addr.zip_code].filter(Boolean).join(', '),
            addr.country,
        ].filter(Boolean) : [];

        // Products: use pre-formatted currency_price fields from API
        const products: any[] = order.order_products ?? [];
        const rows = products.map((item: any) => {
            const name    = item.product_name || 'Product';
            const qty     = item.quantity || 1;
            const price   = item.currency_price || item.price || '';
            const total   = item.total_currency_price || item.subtotal_currency_price || '';
            const variant = item.variation_names ? `<br/><small style="color:#666">${item.variation_names}</small>` : '';
            return `<tr>
                <td style="padding:10px 12px;border-bottom:1px solid #eee">${name}${variant}</td>
                <td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:center">${qty}</td>
                <td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:right">${price}</td>
                <td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:right;font-weight:600">${total}</td>
            </tr>`;
        }).join('');

        // Totals: use pre-formatted _currency_price fields
        const totalsRows = [
            { label: 'Subtotal', value: order.subtotal_currency_price },
            order.tax > 0 && { label: 'Tax', value: order.tax_currency_price },
            order.shipping_charge > 0 && { label: 'Shipping', value: order.shipping_charge_currency_price },
            order.discount > 0 && { label: 'Discount', value: order.discount_currency_price, negative: true },
        ].filter(Boolean).map((row: any) =>
            `<tr><td style="padding:6px 12px;text-align:right;color:#555">${row.label}</td>
             <td style="padding:6px 12px;text-align:right">${row.negative ? '-' : ''}${row.value}</td></tr>`
        ).join('');

        const date = order.order_datetime || new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
        const isPaid = order.payment_status_name || (order.payment_status === 5 ? 'Paid' : 'Unpaid');
        const paidColor = isPaid === 'Paid' ? '#16a34a' : '#dc2626';

        win.document.write(`<!DOCTYPE html><html><head><title>Invoice #${order.order_serial_no}</title>
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
            <div><div class="brand">Behome</div><div style="font-size:12px;color:#777;margin-top:4px">Premium Architectural Decor &amp; Furniture</div></div>
            <div class="invoice-meta">
                <strong>INVOICE</strong>
                Order #${order.order_serial_no}<br/>
                Date: ${date}<br/>
                ${order.payment_method_name ? 'Payment: ' + order.payment_method_name + '<br/>' : ''}
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
                    <td style="padding:10px 12px;text-align:right">${order.total_currency_price}</td>
                </tr>
            </tbody>
        </table>
        <div class="footer">Thank you for shopping with Behome &nbsp;·&nbsp; For support visit behome.co.uk/contact</div>
        <script>window.onload=function(){window.print();}<\/script>
        </body></html>`);
        win.document.close();
    };

    // Instant cancel: only COD/credit, pending, and not yet paid
    const isOnlinePayment = order.payment_status === 5; // payment_status 5 = Paid (always online)
    const canInstantCancel = order.status === 1 && !isOnlinePayment && !order.cancellation_requested;
    // Request only: online-paid orders (any status 1 or 5), or confirmed COD
    const canRequestCancel = !canInstantCancel && (order.status === 1 || order.status === 5) && !order.cancellation_requested;
    const canCancel = canInstantCancel || canRequestCancel;
    const canReturn = order.status === 10 && !existingReturn && order.order_products?.some((item: any) => item.is_refundable);
    const returnSubmitted = !!existingReturn;
    const cancellationPending = !!order.cancellation_requested;

    return (
        <main className="no-layout-pad page-top-100">
            <section className="page-shell page-shell-tight">
                <div className="container">

                    {/* Order Confirmation Banner */}
                    {isNewOrder && (
                        <div className="row mb-40px" style={{ marginTop: '40px' }}>
                            <div className="col-12">
                                <div className="text-center p-40px xs-p-25px border-radius-12px" style={{ background: 'linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(197,160,89,0.08) 100%)', border: '1px solid rgba(16,185,129,0.3)', backdropFilter: 'blur(8px)' }}>
                                    {/* Icon */}
                                    <div style={{ width: 72, height: 72, borderRadius: '50%', background: 'rgba(16,185,129,0.18)', border: '2px solid rgba(16,185,129,0.4)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 20px' }}>
                                        <i className="feather icon-feather-check fs-30" style={{ color: '#10b981' }}></i>
                                    </div>
                                    <h4 className="text-white fw-700 mb-8px alt-font">Thank you for your order!</h4>
                                    <p className="mb-0 fs-15" style={{ color: 'rgba(255,255,255,0.6)' }}>
                                        Order <strong className="text-white">#{order.order_serial_no}</strong> has been placed successfully.
                                    </p>
                                    <p className="mt-5px mb-25px fs-13" style={{ color: 'rgba(255,255,255,0.45)' }}>
                                        A confirmation email will be sent to you shortly.
                                    </p>
                                    {/* Actions */}
                                    <div className="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                                        <a href="/shop" className="btn btn-small btn-round-edge btn-base-color">
                                            <i className="feather icon-feather-shopping-bag me-8px"></i>Continue Shopping
                                        </a>
                                        <a href="/account?tab=orders" className="btn btn-small btn-round-edge btn-dark-gray border border-color-extra-medium-gray text-white">
                                            <i className="feather icon-feather-package me-8px"></i>View All Orders
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}


                    {/* Header */}
                    <div className="row mb-50px">
                        <div className="col-12">
                            <Link href="/account?tab=orders" className="text-white opacity-6 fs-14 d-inline-flex align-items-center gap-2 mb-20px icon-hover-push-left breadcrumb-link">
                                <i className="feather icon-feather-arrow-left"></i> Back to Orders
                            </Link>
                            <div className="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <h4 className="text-white alt-font fw-600 mb-5px">Order #{order.order_serial_no}</h4>
                                    <p className="text-white opacity-6 mb-0 fs-14">{order.order_datetime}</p>
                                </div>
                                <div className="d-flex align-items-center gap-15px flex-wrap justify-content-lg-end">
                                    <span className="badge px-15px py-8px fs-13 fw-600 border-radius-4px text-nowrap" style={{ background: `${statusColor}22`, color: statusColor, border: `1px solid ${statusColor}44`, marginRight: '20px' }}>
                                        {order.status_name || STATUS_NAMES[order.status] || 'Unknown'}
                                    </span>
                                    <button onClick={printInvoice} className="btn btn-small btn-round-edge px-20px text-nowrap" style={{ background: 'rgba(197,160,89,0.1)', border: '1px solid rgba(197,160,89,0.3)', color: 'var(--base-color)', fontSize: 13 }}>
                                        <i className="feather icon-feather-download me-1"></i> Invoice
                                    </button>
                                    <button onClick={() => setShowMessages(m => !m)} className="btn btn-small btn-round-edge px-20px text-nowrap" style={{ background: 'rgba(99,102,241,0.12)', border: '1px solid rgba(99,102,241,0.3)', color: '#818cf8', fontSize: 13 }}>
                                        <i className="feather icon-feather-message-circle me-1"></i> {showMessages ? 'Hide' : 'Message'} Support
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Order Status Timeline */}
                    {order.status !== 15 && order.status !== 20 ? (() => {
                        const steps = [
                            { label: 'Order Placed',  icon: 'icon-feather-shopping-bag', status: 1  },
                            { label: 'Confirmed',      icon: 'icon-feather-check-circle', status: 5  },
                            { label: 'On the Way',     icon: 'icon-feather-truck',         status: 7  },
                            { label: 'Delivered',      icon: 'icon-feather-home',          status: 10 },
                        ];
                        const currentIdx = steps.reduce((acc, s, i) => order.status >= s.status ? i : acc, 0);
                        return (
                            <div className="row mb-35px">
                                <div className="col-12">
                                    <div className="p-25px border-radius-6px" style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)' }}>
                                        <div style={{ display: 'flex', alignItems: 'flex-start', position: 'relative' }}>
                                            {steps.map((step, i) => {
                                                const done = order.status >= step.status;
                                                const active = i === currentIdx;
                                                const isLast = i === steps.length - 1;
                                                return (
                                                    <div key={i} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', position: 'relative' }}>
                                                        {/* Connector line */}
                                                        {!isLast && (
                                                            <div style={{ position: 'absolute', top: 18, left: '50%', width: '100%', height: 2, background: order.status > step.status ? 'var(--base-color)' : 'rgba(255,255,255,0.1)', transition: 'background 0.4s', zIndex: 0 }} />
                                                        )}
                                                        {/* Dot */}
                                                        <div style={{ width: 36, height: 36, borderRadius: '50%', background: done ? 'var(--base-color)' : 'rgba(255,255,255,0.08)', border: `2px solid ${done ? 'var(--base-color)' : 'rgba(255,255,255,0.12)'}`, display: 'flex', alignItems: 'center', justifyContent: 'center', position: 'relative', zIndex: 1, transition: 'all 0.3s', boxShadow: active ? '0 0 0 4px rgba(197,160,89,0.2)' : 'none' }}>
                                                            <i className={`feather ${step.icon}`} style={{ fontSize: 15, color: done ? '#111' : 'rgba(255,255,255,0.3)' }} />
                                                        </div>
                                                        {/* Label */}
                                                        <p className="mb-0 mt-10px text-center" style={{ fontSize: 11, fontWeight: active ? 700 : 500, color: done ? '#fff' : 'rgba(255,255,255,0.35)', letterSpacing: '0.02em', lineHeight: 1.3, maxWidth: 70 }}>
                                                            {step.label}
                                                        </p>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })() : (
                        <div className="row mb-35px">
                            <div className="col-12">
                                <div className="p-20px border-radius-6px d-flex align-items-center gap-15px" style={{ background: 'rgba(239,68,68,0.08)', border: '1px solid rgba(239,68,68,0.25)' }}>
                                    <i className="feather icon-feather-x-circle fs-22" style={{ color: '#ef4444', flexShrink: 0 }}></i>
                                    <div>
                                        <p className="fw-600 mb-2px" style={{ color: '#f87171' }}>Order {order.status === 15 ? 'Cancelled' : 'Rejected'}</p>
                                        <p className="mb-0 fs-13" style={{ color: 'rgba(255,255,255,0.45)' }}>This order has been {order.status === 15 ? 'cancelled' : 'rejected'}. If you have questions, please contact support.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Messages Panel */}
                    {showMessages && (
                        <div className="row mb-30px">
                            <div className="col-12">
                                <div className="bg-dark-gray border-radius-6px border border-color-extra-medium-gray p-25px ui-panel ui-panel-sm">
                                    <p className="text-white fw-600 fs-15 mb-20px">
                                        <i className="feather icon-feather-message-circle me-2 opacity-7"></i>
                                        Messages with Support
                                    </p>

                                    {/* Thread */}
                                    <div style={{ maxHeight: 320, overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: 12 }} className="mb-20px">
                                        {messages.length === 0 ? (
                                            <p className="text-white opacity-4 fs-13 text-center py-20px">No messages yet. Send us a message and we'll get back to you shortly.</p>
                                        ) : messages.map(msg => (
                                            <div key={msg.id} style={{ display: 'flex', justifyContent: msg.is_mine ? 'flex-end' : 'flex-start' }}>
                                                <div style={{
                                                    maxWidth: '75%',
                                                    background: msg.is_mine ? 'rgba(99,102,241,0.18)' : 'rgba(255,255,255,0.06)',
                                                    border: `1px solid ${msg.is_mine ? 'rgba(99,102,241,0.3)' : 'rgba(255,255,255,0.1)'}`,
                                                    borderRadius: msg.is_mine ? '16px 16px 4px 16px' : '16px 16px 16px 4px',
                                                    padding: '10px 16px',
                                                }}>
                                                    <p className="fs-11 fw-600 mb-5px" style={{ color: msg.is_mine ? '#818cf8' : '#FB991C' }}>{msg.sender_name}</p>
                                                    <p className="text-white fs-13 mb-5px" style={{ whiteSpace: 'pre-wrap', lineHeight: 1.6 }}>{msg.message}</p>
                                                    <p className="fs-11 mb-0 opacity-4 text-white">{msg.created_at}</p>
                                                </div>
                                            </div>
                                        ))}
                                        <div ref={messagesEndRef} />
                                    </div>

                                    {/* Send */}
                                    <div className="d-flex gap-3 align-items-end">
                                        <textarea
                                            value={msgText}
                                            onChange={e => setMsgText(e.target.value)}
                                            onKeyDown={e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } }}
                                            rows={2}
                                            maxLength={2000}
                                            placeholder="Type your message… (Enter to send, Shift+Enter for new line)"
                                            className="border-radius-4px input-small flex-grow-1"
                                            style={{ resize: 'none', fontSize: 13 }}
                                        />
                                        <button onClick={sendMessage} disabled={sendingMsg || !msgText.trim()} className="btn btn-small btn-round-edge btn-base-color text-nowrap" style={{ alignSelf: 'flex-end' }}>
                                            {sendingMsg ? '...' : 'Send'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="row g-4 align-items-start">
                        {/* Products */}
                        <div className="col-lg-8">
                            <div className="bg-glass-card rounded-12px box-shadow-extra-large border border-color-extra-medium-gray overflow-hidden mb-25px ui-panel ui-panel-sm ui-table-shell">
                                <div className="ui-panel-header px-30px pt-30px pb-20px">
                                    <span className="text-white fw-600 fs-17 alt-font">Order Items</span>
                                </div>
                                {order.order_products?.map((item: any, idx: number) => (
                                    <div key={idx} className="px-25px py-20px d-flex align-items-center gap-20px" style={{ borderBottom: idx < order.order_products.length - 1 ? '1px solid rgba(255,255,255,0.07)' : 'none' }}>
                                        <div className="flex-shrink-0" style={{ marginRight: '20px' }}>
                                            <Image
                                                src={item.product_image || '/images/demo-decor-store-product-01.jpg'}
                                                alt={item.product_name}
                                                width={70} height={70} unoptimized
                                                style={{ width: 70, height: 70, objectFit: 'cover', borderRadius: 8 }}
                                            />
                                        </div>
                                        <div className="flex-grow-1">
                                            <a href={`/product/${item.product_slug}`} className="text-white fw-600 fs-15 d-block mb-5px breadcrumb-link">
                                                {item.product_name}
                                            </a>
                                            <p className="text-white opacity-5 fs-13 mb-0">Qty: {Math.abs(item.quantity)} × {item.currency_price}</p>
                                        </div>
                                        <div className="text-end flex-shrink-0">
                                            <span className="text-white fw-600">{item.total_currency_price}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Return Status (if submitted) */}
                            {existingReturn && (() => {
                                // Return status: 5=Pending, 10=Accepted, 15=Rejected
                                const isPending = existingReturn.status === 5;
                                const isAccepted = existingReturn.status === 10;
                                const isRejected = existingReturn.status === 15;

                                // Refund status (only active when accepted): 5=AwaitingItem, 10=ItemReceived, 15=RefundIssued
                                const rs = existingReturn.refund_status;
                                const isAwaiting = rs === 5;
                                const isItemReceived = rs === 10;
                                const isRefunded = rs === 15;

                                const refundAmt = existingReturn.total_return_price
                                    ? parseFloat(existingReturn.total_return_price).toFixed(2)
                                    : null;

                                // 4 pipeline steps for the progress bar
                                const steps = [
                                    { label: 'Request Submitted', done: true, rejected: false },
                                    { label: 'Request Accepted', done: isAccepted || isAwaiting || isItemReceived || isRefunded, rejected: isRejected },
                                    { label: 'Item Received', done: isItemReceived || isRefunded, rejected: false, skipped: isRejected },
                                    { label: 'Refund Issued', done: isRefunded, rejected: false, skipped: isRejected },
                                ];

                                return (
                                    <div className="rounded-12px mb-25px overflow-hidden" style={{ border: '1px solid rgba(255,255,255,0.1)', background: 'rgba(255,255,255,0.03)', paddingTop: '30px', paddingLeft: '30px', paddingRight: '30px', paddingBottom: '30px' }}>
                                        {/* Header */}
                                        <div className="px-30px py-20px d-flex align-items-center justify-content-between flex-wrap gap-3" style={{ borderBottom: '1px solid rgba(255,255,255,0.07)', marginBottom: '20px' }}>
                                            <div className="d-flex align-items-center gap-10px">
                                                <i className="feather icon-feather-rotate-ccw fs-16 text-white opacity-7"></i>
                                                <span className="text-white fw-600 fs-16">Return / Refund Request</span>
                                            </div>
                                            <div className="d-flex align-items-center gap-12px">
                                                {existingReturn.created_at && (
                                                    <span className="text-white opacity-4 fs-12" style={{ paddingRight: '10px' }}>Submitted {existingReturn.created_at}</span>
                                                )}
                                                {isRefunded && (
                                                    <span className="badge px-12px py-6px fs-12 fw-600 border-radius-20px" style={{ background: 'rgba(16,185,129,0.15)', color: '#10b981', border: '1px solid rgba(16,185,129,0.3)' }}>
                                                        Refund Issued
                                                    </span>
                                                )}
                                                {isRejected && (
                                                    <span className="badge px-12px py-6px fs-12 fw-600 border-radius-20px" style={{ background: 'rgba(239,68,68,0.15)', color: '#ef4444', border: '1px solid rgba(239,68,68,0.3)' }}>
                                                        Rejected
                                                    </span>
                                                )}
                                                {isPending && (
                                                    <span className="badge px-12px py-6px fs-12 fw-600 border-radius-20px" style={{ background: 'rgba(251,153,28,0.15)', color: '#FB991C', border: '1px solid rgba(251,153,28,0.3)' }}>
                                                        Under Review
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        {/* 4-step progress tracker */}
                                        <div className="px-30px pb-35px" style={{ borderBottom: '1px solid rgba(255,255,255,0.07)' }}>
                                            <div style={{ display: 'flex', alignItems: 'flex-start', position: 'relative' }}>
                                                {steps.map((step, i) => {
                                                    const isLast = i === steps.length - 1;
                                                    const dotColor = step.rejected ? '#ef4444' : step.done ? '#10b981' : step.skipped ? 'rgba(255,255,255,0.1)' : 'rgba(255,255,255,0.15)';
                                                    const lineColor = step.done && !isLast ? '#10b981' : 'rgba(255,255,255,0.1)';
                                                    return (
                                                        <div key={i} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', position: 'relative' }}>
                                                            {/* Connector line */}
                                                            {!isLast && (
                                                                <div style={{ position: 'absolute', top: 12, left: '50%', width: '100%', height: 2, background: lineColor, zIndex: 0 }} />
                                                            )}
                                                            {/* Dot */}
                                                            <div style={{ position: 'relative', zIndex: 1, width: 26, height: 26, borderRadius: '50%', background: dotColor, border: `2px solid ${step.done || step.rejected ? 'transparent' : 'rgba(255,255,255,0.2)'}`, display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 8 }}>
                                                                {step.rejected ? (
                                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="3"><path d="M18 6L6 18M6 6l12 12" /></svg>
                                                                ) : step.done ? (
                                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="3"><path d="M5 13l4 4L19 7" /></svg>
                                                                ) : (
                                                                    <span style={{ fontSize: 10, color: 'rgba(255,255,255,0.4)', fontWeight: 700 }}>{i + 1}</span>
                                                                )}
                                                            </div>
                                                            {/* Label */}
                                                            <p style={{ fontSize: 10, fontWeight: 600, textAlign: 'center', color: step.rejected ? '#ef4444' : step.done ? '#10b981' : 'rgba(255,255,255,0.3)', lineHeight: 1.3, margin: 0, padding: '0 4px' }}>
                                                                {step.label}
                                                            </p>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>

                                        <div className="px-30px py-25px">
                                            {/* Request meta */}
                                            <div className="d-flex flex-wrap gap-20px mb-16px">
                                                {existingReturn.return_reason?.title && (
                                                    <div>
                                                        <span className="text-white opacity-4 fs-11 d-block mb-3px" style={{ textTransform: 'uppercase', letterSpacing: '0.05em' }}>Return Reason</span>
                                                        <span className="text-white fs-13 fw-600">{existingReturn.return_reason.title}</span>
                                                    </div>
                                                )}
                                                {refundAmt && (
                                                    <div>
                                                        <span className="text-white opacity-4 fs-11 d-block mb-3px" style={{ textTransform: 'uppercase', letterSpacing: '0.05em' }}>Refund Amount</span>
                                                        <span className="fs-15 fw-700" style={{ color: isRefunded ? '#10b981' : 'rgba(255,255,255,0.8)' }}>{refundAmt}</span>
                                                    </div>
                                                )}
                                                {existingReturn.refund_issued_at && (
                                                    <div>
                                                        <span className="text-white opacity-4 fs-11 d-block mb-3px" style={{ textTransform: 'uppercase', letterSpacing: '0.05em' }}>Refunded On</span>
                                                        <span className="text-white fs-13 fw-600">{existingReturn.refund_issued_at}</span>
                                                    </div>
                                                )}
                                            </div>
                                            {existingReturn.note && (
                                                <p className="text-white opacity-5 fs-13 mb-16px">"{existingReturn.note}"</p>
                                            )}

                                            {/* Stage-specific message */}
                                            {isPending && (
                                                <div className="p-15px border-radius-8px" style={{ background: 'rgba(251,153,28,0.08)', border: '1px solid rgba(251,153,28,0.2)' }}>
                                                    <p className="fw-600 fs-13 mb-5px" style={{ color: '#FB991C' }}>
                                                        <i className="feather icon-feather-clock me-2"></i>Under Review
                                                    </p>
                                                    <p className="text-white opacity-6 fs-13 mb-0">Your request is being reviewed by our team. We'll update you within 1–2 business days.</p>
                                                </div>
                                            )}
                                            {isAwaiting && (
                                                <div className="p-15px border-radius-8px" style={{ background: 'rgba(99,102,241,0.08)', border: '1px solid rgba(99,102,241,0.2)' }}>
                                                    <p className="fw-600 fs-13 mb-8px" style={{ color: '#818cf8' }}>
                                                        <i className="feather icon-feather-package me-2"></i>Ship the Item Back
                                                    </p>
                                                    <p className="text-white opacity-6 fs-13 mb-8px">Your return was approved. Please pack the item securely and ship it back to our return address.</p>
                                                    <p className="text-white opacity-5 fs-12 mb-0">Once we receive and inspect it, we'll issue your refund.</p>
                                                </div>
                                            )}
                                            {isItemReceived && (
                                                <div className="p-15px border-radius-8px" style={{ background: 'rgba(99,102,241,0.08)', border: '1px solid rgba(99,102,241,0.2)' }}>
                                                    <p className="fw-600 fs-13 mb-5px" style={{ color: '#818cf8' }}>
                                                        <i className="feather icon-feather-check-circle me-2"></i>Item Received — Processing Refund
                                                    </p>
                                                    <p className="text-white opacity-6 fs-13 mb-0">We've received your item and are inspecting it. Your refund will be issued shortly.</p>
                                                </div>
                                            )}
                                            {isRefunded && (
                                                <div className="p-15px border-radius-8px" style={{ background: 'rgba(16,185,129,0.08)', border: '1px solid rgba(16,185,129,0.2)' }}>
                                                    <p className="fw-600 fs-13 mb-5px" style={{ color: '#10b981' }}>
                                                        <i className="feather icon-feather-check-circle me-2"></i>Refund Issued
                                                    </p>
                                                    <p className="text-white opacity-6 fs-13 mb-0">
                                                        {refundAmt ? (
                                                            <><strong style={{ color: '#10b981' }}>{refundAmt}</strong> has been refunded to your original payment method.</>
                                                        ) : (
                                                            'Your refund has been issued to your original payment method.'
                                                        )}
                                                    </p>
                                                </div>
                                            )}
                                            {isRejected && (
                                                <div className="p-15px border-radius-8px" style={{ background: 'rgba(239,68,68,0.08)', border: '1px solid rgba(239,68,68,0.2)' }}>
                                                    <p className="fw-600 fs-13 mb-5px" style={{ color: '#ef4444' }}>
                                                        <i className="feather icon-feather-x-circle me-2"></i>Request Not Approved
                                                    </p>
                                                    <p className="text-white opacity-6 fs-13 mb-0">
                                                        {existingReturn.reject_reason || 'Your return request was not approved. Please contact support for more information.'}
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                );
                            })()}

                            {/* Shipping Address */}
                            {(() => {
                                const addr = order.order_address?.data?.[0] ?? order.order_address?.[0];
                                if (!addr) return null;
                                return (
                                    <div className="bg-glass-card rounded-12px box-shadow-extra-large border border-color-extra-medium-gray p-30px ui-panel ui-panel-sm">
                                        <span className="text-white fw-600 fs-17 alt-font d-block mb-15px">Shipping Address</span>
                                        <p className="text-white opacity-7 fs-14 lh-26 mb-0">
                                            <strong className="text-white opacity-10">{addr.full_name}</strong><br />
                                            {addr.address}<br />
                                            {addr.city}{addr.zip_code ? `, ${addr.zip_code}` : ''}<br />
                                            {addr.country}
                                            {addr.phone && <><br />{addr.phone}</>}
                                        </p>
                                    </div>
                                );
                            })()}

                            {/* Wide Inline Stripe Payment Section below Shipping Address */}
                            <div ref={paymentSectionRef} className={`transition-all duration-700 ease-in-out overflow-hidden ${stripeOptions ? 'max-h-[1000px] opacity-100 mt-25px mb-25px' : 'max-h-0 opacity-0'}`}>
                                <div className="bg-[#111111] border border-white/10 p-40px md-p-20px border-radius-12px shadow-2xl relative overflow-hidden">
                                        <div className="absolute top-0 left-0 w-100 h-4px opacity-70" style={{ backgroundColor: '#6772e5' }}></div>
                                        <div className="text-center mb-30px">
                                            <img src="/images/stripe_fallback.svg" alt="Stripe" className="checkout-payment-logo mx-auto" />
                                            <h4 className="text-white alt-font fw-600 mb-5px w-100 text-center">Order Checkout</h4>
                                            <p className="text-white/50 fs-14 w-100 text-center">Securely pay Order #{order.order_serial_no}</p>
                                        </div>

                                        {stripePromise && stripeOptions && (
                                            <div className="animate__animated animate__fadeIn">
                                                <Elements stripe={stripePromise} options={stripeOptions}>
                                                    <StripePaymentForm 
                                                        orderId={parseInt(id)} 
                                                        onSuccess={() => {
                                                            setStripeOptions(null);
                                                            fetchOrder();
                                                            showToast('Payment successful!', 'success');
                                                        }}
                                                        onCancel={() => setStripeOptions(null)}
                                                    />
                                                </Elements>
                                            </div>
                                        )}
                                </div>
                            </div>
                        </div>

                        {/* Summary */}
                        <div className="col-lg-4">
                            <div className="bg-glass-card rounded-12px box-shadow-extra-large border border-color-extra-medium-gray p-30px ui-panel ui-panel-sm">
                                <span className="text-white fw-600 fs-17 alt-font d-block mb-20px pb-15px border-bottom border-color-extra-medium-gray">Order Summary</span>

                                <div className="d-flex justify-content-between mb-12px">
                                    <span className="text-white opacity-6 fs-14">Subtotal</span>
                                    <span className="text-white fs-14">{order.subtotal_currency_price}</span>
                                </div>
                                {order.tax_currency_price && (
                                    <div className="d-flex justify-content-between mb-15px">
                                        <span className="text-white-light fs-14">Tax</span>
                                        <span className="text-white fs-14 fw-600">{order.tax_currency_price}</span>
                                    </div>
                                )}
                                {!!order.shipping_charge_currency_price && (
                                    <div className="d-flex justify-content-between mb-15px">
                                        <span className="text-white-light fs-14">Shipping</span>
                                        <span className="text-white fs-14 fw-600">{order.shipping_charge_currency_price}</span>
                                    </div>
                                )}
                                {!!(order.discount && parseFloat(order.discount) > 0) && (
                                    <div className="d-flex justify-content-between mb-15px">
                                        <span className="text-white-light fs-14">Discount</span>
                                        <span className="fs-14 fw-600" style={{ color: '#10b981' }}>-{order.discount_currency_price}</span>
                                    </div>
                                )}
                                <div className="d-flex justify-content-between mt-20px pt-20px fw-700 border-top border-color-extra-medium-gray">
                                    <span className="text-white alt-font fs-16">Total</span>
                                    <span className="text-white alt-font fs-22">{order.total_currency_price}</span>
                                </div>

                                {order.payment_method_name && (
                                    <div className="ui-note-block mt-25px">
                                        <span className="text-white-light fs-12 d-block mb-5px">Payment Method</span>
                                        <span className="text-white fs-14 fw-600">{order.payment_method_name}</span>
                                    </div>
                                )}
                                <div className="ui-note-block">
                                    <span className="text-white-light fs-12 d-block mb-10px">Payment Status</span>
                                    <span className="badge px-12px py-6px fs-11 fw-700 border-radius-4px" style={{ background: order.payment_status === 10 ? 'rgba(239,68,68,0.12)' : 'rgba(16,185,129,0.12)', color: order.payment_status === 10 ? '#ef4444' : '#10b981', border: `1px solid ${order.payment_status === 10 ? 'rgba(239,68,68,0.25)' : 'rgba(16,185,129,0.25)'}` }}>
                                        {order.payment_status_name || (order.payment_status === 5 ? 'Paid' : 'Unpaid')}
                                    </span>
                                </div>

                                {/* Refund Completed — shown when admin has issued the refund */}
                                {order.refund_transaction && (
                                    <div className="mt-20px p-20px border-radius-10px" style={{ background: 'rgba(16,185,129,0.07)', border: '1px solid rgba(16,185,129,0.25)' }}>
                                        <div className="d-flex align-items-center gap-10px mb-10px">
                                            <div style={{ width: 32, height: 32, borderRadius: '50%', background: 'rgba(16,185,129,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <i className="feather icon-feather-check-circle fs-15" style={{ color: '#10b981' }}></i>
                                            </div>
                                            <span className="fw-700 fs-14" style={{ color: '#10b981' }}>Refund Completed</span>
                                        </div>
                                        <p className="text-white opacity-6 fs-13 mb-10px lh-20">
                                            <strong style={{ color: '#34d399' }}>{order.refund_transaction.amount}</strong> has been refunded to your original payment method.
                                        </p>
                                        <div className="d-flex flex-column gap-5px">
                                            <div className="d-flex justify-content-between">
                                                <span className="text-white opacity-4 fs-11" style={{ textTransform: 'uppercase', letterSpacing: '0.05em' }}>Refunded via</span>
                                                <span className="text-white fs-12 fw-600 text-capitalize">{order.refund_transaction.payment_method === 'stripe' ? 'Stripe → Card' : 'Store Wallet'}</span>
                                            </div>
                                            <div className="d-flex justify-content-between">
                                                <span className="text-white opacity-4 fs-11" style={{ textTransform: 'uppercase', letterSpacing: '0.05em' }}>Date</span>
                                                <span className="text-white fs-12 fw-600">{order.refund_transaction.created_at}</span>
                                            </div>
                                            {order.refund_transaction.transaction_no && (
                                                <div className="mt-5px pt-8px" style={{ borderTop: '1px solid rgba(255,255,255,0.07)' }}>
                                                    <span className="text-white opacity-4 fs-11 d-block mb-3px" style={{ textTransform: 'uppercase', letterSpacing: '0.05em' }}>Reference</span>
                                                    <span className="text-white opacity-6 fs-11 fw-500" style={{ fontFamily: 'monospace', wordBreak: 'break-all' }}>{order.refund_transaction.transaction_no}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {order.payment_status === 10 && order.status < 10 && (
                                    <div className="mt-25px p-20px border-radius-10px shadow-lg" style={{ background: 'linear-gradient(135deg, rgba(201,154,92,0.12) 0%, rgba(201,154,92,0.04) 100%)', border: '1px solid rgba(201,154,92,0.25)' }}>
                                        <div className="d-flex flex-column align-items-center text-center gap-15px mb-20px">
                                            <div className="flex-shrink-0 w-45px h-45px border-radius-50 d-flex align-items-center justify-content-center" style={{ background: 'rgba(201,154,92,0.12)', border: '1px solid rgba(201,154,92,0.2)' }}>
                                                <img src="/images/stripe_fallback.svg" alt="Stripe" className="payment-icon-small" />
                                            </div>
                                            <div>
                                                <p className="text-white fw-600 fs-15 mb-2px">Secure Online Payment</p>
                                                <p className="opacity-5 fs-12 mb-0 text-white">Fast and encrypted via Stripe</p>
                                            </div>
                                        </div>
                                        {!stripeOptions ? (
                                            <button 
                                                onClick={initiateOnlinePayment}
                                                disabled={initiatingPayment}
                                                className="btn btn-small btn-round-edge w-100 btn-base-color btn-box-shadow"
                                                style={{ height: '46px' }}
                                            >
                                                <span>
                                                    <span className="btn-double-text" data-text={initiatingPayment ? 'Initiating...' : 'Pay with Stripe'}>
                                                        {initiatingPayment ? 'Initiating...' : 'Pay with Stripe'}
                                                    </span>
                                                </span>
                                            </button>
                                        ) : (
                                            <div className="text-center">
                                                <p className="text-white opacity-5 fs-12 mb-0">Form expanded below <i className="feather icon-feather-arrow-down ms-1"></i></p>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Return Policy Info */}
                                {canReturn && (
                                    <div className="ui-note-block" style={{ background: 'rgba(251,153,28,0.06)', border: '1px solid rgba(251,153,28,0.15)', borderRadius: 8, padding: '12px 14px' }}>
                                        <p className="text-white opacity-6 fs-12 mb-5px fw-600">Return Policy</p>
                                        <p className="text-white opacity-5 fs-12 mb-0 lh-20">You may request a return or refund within 7 days of delivery for size, colour, or defect issues.</p>
                                        <button onClick={() => setShowReturnModal(true)} className="btn btn-small btn-round-edge mt-10px" style={{ fontSize: 12, padding: '6px 14px', background: 'rgba(251,153,28,0.15)', border: '1px solid rgba(251,153,28,0.3)', color: '#FB991C' }}>
                                            Request Return
                                        </button>
                                    </div>
                                )}

                                {(order.reason || order.status_reason) && (
                                    <div className="ui-note-block">
                                        {order.reason && (<>
                                            <span className="text-white opacity-5 fs-12 d-block mb-5px">Customer Note</span>
                                            <p className="text-white opacity-7 fs-14 mb-0">{order.reason}</p>
                                        </>)}
                                        {order.status_reason && (<>
                                            <span className="text-white opacity-5 fs-12 d-block mt-15px mb-5px">Status Reason</span>
                                            <p className="text-white opacity-7 fs-14 mb-0">{order.status_reason}</p>
                                        </>)}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* ── Order Actions ─────────────────────────────────────── */}
                    {(canCancel || returnSubmitted || cancellationPending) && (
                        <div className="row mt-30px">
                            <div className="col-12">
                                <div style={{ background: 'rgba(255,255,255,0.02)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 12, padding: '24px 28px', display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between', gap: 16 }}>
                                    <div>
                                        <p className="text-white fw-600 fs-15 mb-4px">
                                            {cancellationPending ? 'Cancellation Request Pending' : (canCancel ? 'Need to cancel this order?' : 'Order delivered?')}
                                        </p>
                                        <p className="text-white opacity-5 fs-13 mb-0">
                                            {cancellationPending && 'We have received your cancellation request and are reviewing it.'}
                                            {canInstantCancel && 'You can cancel this order immediately as it hasn\'t been confirmed yet.'}
                                            {canRequestCancel && 'Submit a cancellation request and our team will review it within 6–7 working days.'}
                                            {(canReturn || returnSubmitted) && 'You can request a return or refund within 7 days of delivery.'}
                                        </p>
                                    </div>
                                    <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
                                        {canCancel && (
                                            <button
                                                onClick={() => setShowCancelModal(true)}
                                                className="btn btn-medium btn-round-edge px-25px text-nowrap"
                                                style={{ background: 'rgba(239,68,68,0.12)', border: '1px solid rgba(239,68,68,0.3)', color: '#ef4444', fontSize: 14 }}
                                            >
                                                <i className="feather icon-feather-x me-2"></i>
                                                {canInstantCancel ? 'Cancel Order' : 'Request Cancellation'}
                                            </button>
                                        )}
                                        {cancellationPending && (
                                            <span className="badge px-15px py-10px fs-13 fw-600" style={{ background: 'rgba(239,68,68,0.12)', color: '#ef4444', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 8, display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                                                <i className="feather icon-feather-clock" style={{ fontSize: 14 }}></i>
                                                Request Pending
                                            </span>
                                        )}
                                        {/* Return / Refund button removed from footer as it is now in the sidebar */}
                                        {returnSubmitted && (
                                            <span className="badge px-15px py-10px fs-13 fw-600" style={{ background: 'rgba(251,153,28,0.12)', color: '#FB991C', border: '1px solid rgba(251,153,28,0.3)', borderRadius: 8, display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                                                <i className="feather icon-feather-check-circle" style={{ fontSize: 14 }}></i>
                                                Return Request Submitted
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                </div>
            </section>

            {/* ── Cancel Modal ─────────────────────────────── */}
            {showCancelModal && (
                <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.7)', zIndex: 9999, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16 }}>
                    <div className="bg-dark-gray border-radius-6px p-35px" style={{ width: '100%', maxWidth: 460, border: '1px solid rgba(255,255,255,0.1)' }}>
                        <h5 className="text-white alt-font fw-600 mb-10px">
                            {canInstantCancel ? 'Cancel Order' : 'Request Cancellation'}
                        </h5>
                        <p className="text-white opacity-6 fs-14 mb-20px">
                            {canInstantCancel
                                ? 'Your order will be cancelled immediately. Please let us know why (optional).'
                                : 'We will review your request and respond within 6–7 working days. If a refund is applicable, it will be returned to your original payment method.'}
                        </p>
                        <textarea
                            value={cancelReason}
                            onChange={e => setCancelReason(e.target.value)}
                            rows={3}
                            placeholder="Reason for cancellation…"
                            className="border-radius-4px input-small w-100 mb-20px"
                            style={{ resize: 'none', fontSize: 13 }}
                        />
                        <div className="d-flex gap-3">
                            <button onClick={cancelOrder} disabled={cancelling} className="btn btn-medium btn-round-edge flex-grow-1" style={{ background: 'rgba(239,68,68,0.15)', border: '1px solid rgba(239,68,68,0.4)', color: '#ef4444' }}>
                                {cancelling ? 'Submitting…' : canInstantCancel ? 'Yes, Cancel Order' : 'Submit Request'}
                            </button>
                            <button onClick={() => setShowCancelModal(false)} className="btn btn-medium btn-round-edge btn-transparent-white">
                                Keep Order
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* ── Return / Refund Modal ────────────────────── */}
            {showReturnModal && (
                <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.7)', zIndex: 9999, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16 }}>
                    <div className="bg-dark-gray border-radius-6px p-35px" style={{ width: '100%', maxWidth: 500, border: '1px solid rgba(255,255,255,0.1)', maxHeight: '90vh', overflowY: 'auto' }}>
                        <h5 className="text-white alt-font fw-600 mb-5px">Return / Refund Request</h5>
                        <p className="text-white opacity-6 fs-14 mb-25px">Select a reason and describe the issue. Our team will review and respond within 2–3 business days.</p>

                        <div className="mb-15px">
                            <label className="text-white fs-13 fw-600 d-block mb-8px">Reason <span style={{ color: '#ef4444' }}>*</span></label>
                            <select
                                value={returnForm.reason_id}
                                onChange={e => setReturnForm(f => ({ ...f, reason_id: e.target.value }))}
                                className="border-radius-4px input-small w-100"
                                style={{ fontSize: 13 }}
                            >
                                {returnReasons.length === 0 && <option value="">Loading reasons…</option>}
                                {returnReasons.map((r: any) => (
                                    <option key={r.id} value={r.id}>{r.title}</option>
                                ))}
                            </select>
                        </div>

                        <div className="mb-20px">
                            <label className="text-white fs-13 fw-600 d-block mb-8px">Additional Details</label>
                            <textarea
                                value={returnForm.note}
                                onChange={e => setReturnForm(f => ({ ...f, note: e.target.value }))}
                                rows={4}
                                maxLength={2000}
                                placeholder="Describe the issue — wrong size, colour mismatch, damage, etc."
                                className="border-radius-4px input-small w-100"
                                style={{ resize: 'none', fontSize: 13 }}
                            />
                        </div>

                        {/* Return Policy Summary */}
                        <div className="p-15px border-radius-4px mb-20px" style={{ background: 'rgba(251,153,28,0.06)', border: '1px solid rgba(251,153,28,0.15)' }}>
                            <p className="text-white fw-600 fs-13 mb-8px">Return Policy</p>
                            <ul style={{ paddingLeft: 18, margin: 0 }}>
                                {['Returns accepted within 7 days of delivery', 'Items must be unused and in original packaging', 'Refund issued to original payment method within 5–7 days', 'Size/colour issues, manufacturing defects, and damaged goods are eligible'].map((pt, i) => (
                                    <li key={i} className="text-white opacity-6 fs-12 mb-5px">{pt}</li>
                                ))}
                            </ul>
                        </div>

                        <div className="d-flex gap-3">
                            <button onClick={submitReturn} disabled={submittingReturn || !returnForm.reason_id} className="btn btn-medium btn-round-edge btn-base-color flex-grow-1">
                                {submittingReturn ? 'Submitting…' : 'Submit Request'}
                            </button>
                            <button onClick={() => setShowReturnModal(false)} className="btn btn-medium btn-round-edge btn-transparent-white">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            )}

        </main>
    );
}
