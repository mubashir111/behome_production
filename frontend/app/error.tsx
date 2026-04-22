'use client';

import { useEffect } from 'react';

export default function Error({
    error,
    reset,
}: {
    error: Error & { digest?: string };
    reset: () => void;
}) {
    useEffect(() => {
        // Log to error reporting service in production
        console.error(error);
    }, [error]);

    return (
        <main
            style={{
                minHeight: '70vh',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '60px 20px',
                textAlign: 'center',
            }}
        >
            <div>
                <p
                    style={{
                        fontSize: '120px',
                        fontWeight: 800,
                        lineHeight: 1,
                        color: 'rgba(255,255,255,0.06)',
                        letterSpacing: '-4px',
                        marginBottom: '0',
                        fontFamily: 'var(--font-body, sans-serif)',
                    }}
                >
                    500
                </p>
                <h1
                    style={{
                        fontSize: 'clamp(28px, 4vw, 48px)',
                        fontWeight: 700,
                        color: '#fff',
                        marginTop: '-20px',
                        marginBottom: '16px',
                        letterSpacing: '-1px',
                    }}
                >
                    Something went wrong
                </h1>
                <p style={{ color: 'rgba(255,255,255,0.55)', fontSize: '16px', maxWidth: '420px', margin: '0 auto 36px' }}>
                    An unexpected error occurred. Please try again, or contact support if the problem persists.
                </p>
                <div style={{ display: 'flex', gap: '12px', justifyContent: 'center', flexWrap: 'wrap' }}>
                    <button
                        onClick={reset}
                        className="btn btn-white btn-medium btn-round-edge fw-600"
                    >
                        Try again
                    </button>
                    <a href="/" className="btn btn-dark-gray btn-medium btn-round-edge fw-600">
                        Go home
                    </a>
                </div>
            </div>
        </main>
    );
}
