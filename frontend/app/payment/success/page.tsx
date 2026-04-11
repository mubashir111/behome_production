'use client';

import { Suspense, useEffect, useState } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import PageLoadingShell from '@/components/PageLoadingShell';
import { apiFetch } from '@/lib/api';

type VerifyState = 'verifying' | 'success' | 'failed';

function PaymentSuccessContent() {
    const searchParams    = useSearchParams();
    const orderId         = searchParams.get('order_id');
    const paymentIntent   = searchParams.get('payment_intent');
    const redirectStatus  = searchParams.get('redirect_status');

    const [state, setState]       = useState<VerifyState>('verifying');
    const [errorMsg, setErrorMsg] = useState('');

    useEffect(() => {
        if (!orderId) {
            setState('failed');
            setErrorMsg('Missing order ID.');
            return;
        }

        if (paymentIntent) {
            // ── Stripe payment: verify server-side ────────────────────────
            apiFetch(`/v1/payment/verify/${orderId}`, {
                method: 'POST',
                body: JSON.stringify({
                    payment_gateway:  'stripe',
                    payment_intent:   paymentIntent,
                    redirect_status:  redirectStatus,
                }),
            })
                .then((res) => {
                    if (res.status) {
                        setState('success');
                        window.dispatchEvent(new Event('cart:updated'));
                    } else {
                        setState('failed');
                        setErrorMsg(res.message || 'Payment verification failed.');
                    }
                })
                .catch((err) => {
                    setState('failed');
                    setErrorMsg(err.message || 'Could not reach the server.');
                });
        } else {
            // ── No payment_intent: COD or already confirmed ───────────────
            // Always check the real order status from the API — never assume success.
            apiFetch(`/v1/orders/${orderId}`)
                .then((res) => {
                    if (res.status && res.data) {
                        // payment_status 5 = Paid, or COD/credit orders are always OK
                        const isPaid    = res.data.payment_status === 5;
                        const isCod     = ['cashondelivery', 'credit'].some((s: string) =>
                            (res.data.payment_method_name ?? '').toLowerCase().includes(s.replace('cashondelivery', 'cash'))
                        );
                        const isActive  = res.data.active === 5;
                        if (isPaid || isCod || isActive) {
                            setState('success');
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
                .catch((err) => {
                    setState('failed');
                    setErrorMsg(err.message || 'Could not reach the server.');
                });
        }
    }, [orderId, paymentIntent, redirectStatus]);

    if (state === 'verifying') {
        return (
            <main>
                <section className="top-space-padding pb-0">
                    <div className="container">
                        <div className="row justify-content-center">
                            <div className="col-md-8 text-center">
                                <div className="bg-dark-gray border-radius-6px p-50px">
                                    <div className="spinner-border text-base-color mb-20px" role="status" style={{ width: 48, height: 48 }}></div>
                                    <h4 className="text-white alt-font fw-600 mb-10px">Confirming your order…</h4>
                                    <p className="text-gray">Please wait a moment.</p>
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
                <section className="top-space-padding pb-0">
                    <div className="container">
                        <div className="row justify-content-center">
                            <div className="col-md-8 text-center">
                                <div className="bg-dark-gray border-radius-6px p-50px">
                                    <i className="bi bi-x-circle-fill text-red fs-60 mb-20px d-block"></i>
                                    <h4 className="text-white alt-font fw-600 mb-10px">Payment Not Confirmed</h4>
                                    <p className="text-gray mb-30px">{errorMsg || 'Something went wrong. Please try again or contact support.'}</p>
                                    <div className="d-flex justify-content-center gap-3">
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

    // success
    return (
        <main>
            <section className="top-space-padding pb-0">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-md-8 text-center">
                            <div className="bg-dark-gray border-radius-6px p-50px">
                                <i className="bi bi-check-circle-fill text-base-color fs-60 mb-20px d-block"></i>
                                <h4 className="text-white alt-font fw-600 mb-10px">Thank you for your order!</h4>
                                <p className="text-gray mb-30px">
                                    Your order #{orderId} has been placed and confirmed. We will send you an email shortly.
                                </p>
                                <div className="d-flex justify-content-center gap-3">
                                    <Link href="/shop" className="btn btn-base-color btn-medium btn-round-edge">Continue Shopping</Link>
                                    <Link href={`/account/order/${orderId}`} className="btn btn-transparent-white btn-medium btn-round-edge">View Order</Link>
                                </div>
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
