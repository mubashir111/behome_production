'use client';

import { Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import PageLoadingShell from '@/components/PageLoadingShell';

function PaymentSuccessContent() {
    const searchParams = useSearchParams();
    const orderId = searchParams.get('order_id');

    return (
        <main>
            <section className="top-space-padding pb-0">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-md-8 text-center">
                            <div className="bg-dark-gray border-radius-6px p-50px">
                                <i className="bi bi-check-circle-fill text-base-color fs-60 mb-20px d-block"></i>
                                <h4 className="text-white alt-font fw-600 mb-10px">Thank you for your order!</h4>
                                <p className="text-gray mb-30px">Your order #{orderId} has been placed successfully. We will send you an email confirmation shortly.</p>
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
