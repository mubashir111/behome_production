'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import Link from 'next/link';
import { useParams, useSearchParams, useRouter } from 'next/navigation';
import { useToast } from '@/components/ToastProvider';

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
                    showToast('Cancellation request submitted. Our team will review and respond within 24 hours.', 'success');
                    // Refresh messages so the request shows in the thread
                    if (showMessages) fetchMessages();
                } else {
                    showToast('Your order has been cancelled. A refund will be issued to your original payment method within 5–7 days.', 'success');
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
    const canCancel = (order.status === 1 || order.status === 5) && !order.cancellation_requested;
    const canReturn = order.status === 10 && !existingReturn;
    const returnSubmitted = !!existingReturn;
    const cancellationPending = !!order.cancellation_requested;

    return (
        <main className="no-layout-pad page-top-100">
            <section className="page-shell page-shell-tight">
                <div className="container">

                    {/* Success Banner */}
                    {isNewOrder && (
                        <div className="row mb-30px" style={{ marginTop: '40px' }}>
                            <div className="col-12">
                                <div className="d-flex align-items-center justify-content-between gap-15px p-25px border-radius-6px" style={{ background: 'rgba(16,185,129,0.1)', border: '1px solid rgba(16,185,129,0.35)', backdropFilter: 'blur(4px)' }}>
                                    <div className="d-flex align-items-center gap-15px">
                                        <div style={{ width: 42, height: 42, borderRadius: '50%', background: 'rgba(16,185,129,0.18)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                            <i className="bi bi-check-lg fs-18" style={{ color: '#10b981' }}></i>
                                        </div>
                                        <div>
                                            <p className="text-white fw-700 mb-2px fs-16">Order placed successfully!</p>
                                            <p className="mb-0 fs-13" style={{ color: 'rgba(255,255,255,0.55)' }}>Thank you for your order. We'll send a confirmation email shortly.</p>
                                        </div>
                                    </div>
                                    <span className="px-12px py-6px border-radius-4px fs-12 fw-600 text-nowrap" style={{ background: 'rgba(16,185,129,0.18)', color: '#34d399' }}>
                                        Confirmed
                                    </span>
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
                                    <button onClick={() => setShowMessages(m => !m)} className="btn btn-small btn-round-edge px-20px text-nowrap" style={{ background: 'rgba(99,102,241,0.12)', border: '1px solid rgba(99,102,241,0.3)', color: '#818cf8', fontSize: 13 }}>
                                        <i className="feather icon-feather-message-circle me-1"></i> {showMessages ? 'Hide' : 'Message'} Support
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                    <span className="text-white-light fs-12 d-block mb-5px">Payment Status</span>
                                    <span className="text-white fs-14 fw-600">{order.payment_status_name || (order.payment_status === 5 ? 'Paid' : 'Unpaid')}</span>
                                </div>

                                {/* Return Policy Info */}
                                {order.status === 10 && !existingReturn && (
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
                    {(canCancel || canReturn || returnSubmitted || cancellationPending) && (
                        <div className="row mt-30px">
                            <div className="col-12">
                                <div style={{ background: 'rgba(255,255,255,0.02)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 12, padding: '24px 28px', display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between', gap: 16 }}>
                                    <div>
                                        <p className="text-white fw-600 fs-15 mb-4px">
                                            {cancellationPending ? 'Cancellation Request Pending' : (canCancel ? 'Need to cancel this order?' : 'Order delivered?')}
                                        </p>
                                        <p className="text-white opacity-5 fs-13 mb-0">
                                            {cancellationPending && 'We have received your cancellation request and are reviewing it.'}
                                            {canCancel && order.status === 1 && 'You can cancel this order immediately as it hasn\'t been confirmed yet.'}
                                            {canCancel && order.status === 5 && 'Your order is confirmed. Submit a cancellation request and our team will review it within 24 hours.'}
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
                                                {order.status === 1 ? 'Cancel Order' : 'Request Cancellation'}
                                            </button>
                                        )}
                                        {cancellationPending && (
                                            <span className="badge px-15px py-10px fs-13 fw-600" style={{ background: 'rgba(239,68,68,0.12)', color: '#ef4444', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 8, display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                                                <i className="feather icon-feather-clock" style={{ fontSize: 14 }}></i>
                                                Request Pending
                                            </span>
                                        )}
                                        {canReturn && (
                                            <button
                                                onClick={() => setShowReturnModal(true)}
                                                className="btn btn-medium btn-round-edge px-25px text-nowrap"
                                                style={{ background: 'rgba(251,153,28,0.12)', border: '1px solid rgba(251,153,28,0.3)', color: '#FB991C', fontSize: 14 }}
                                            >
                                                <i className="feather icon-feather-rotate-ccw me-2"></i>
                                                Return / Refund
                                            </button>
                                        )}
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
                            {order.status === 1 ? 'Cancel Order' : 'Request Cancellation'}
                        </h5>
                        <p className="text-white opacity-6 fs-14 mb-20px">
                            {order.status === 1
                                ? 'Your order will be cancelled immediately. Please let us know why (optional).'
                                : 'Your order is already confirmed and may be in preparation. We will review your request and respond within 24 hours.'}
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
                                {cancelling ? 'Submitting…' : order.status === 1 ? 'Yes, Cancel Order' : 'Submit Request'}
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
