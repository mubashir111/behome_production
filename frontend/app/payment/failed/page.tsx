'use client';

import { Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import PageLoadingShell from '@/components/PageLoadingShell';

function PaymentFailedContent() {
    const searchParams = useSearchParams();
    const orderId = searchParams.get('order_id');

    return (
        <main>
            <section className="top-space-padding pb-0">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-md-8 text-center">
                            <div className="bg-dark-gray border-radius-6px p-50px">
                                <i className="bi bi-x-circle-fill text-red fs-60 mb-20px d-block"></i>
                                <h4 className="text-white alt-font fw-600 mb-10px">Payment Failed</h4>
                                <p className="text-gray mb-30px">We're sorry, but your payment for order #{orderId} could not be processed. Please try again or contact support.</p>
                                <div className="d-flex justify-content-center gap-3">
                                    <Link href="/checkout" className="btn btn-base-color btn-medium btn-round-edge">Return to Checkout</Link>
                                    <Link href="/contact" className="btn btn-transparent-white btn-medium btn-round-edge">Contact Support</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}

export default function PaymentFailed() {
    return (
        <Suspense fallback={<PageLoadingShell variant="message" />}>
            <PaymentFailedContent />
        </Suspense>
    );
}
