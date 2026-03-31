'use client';

import { useEffect } from 'react';

export default function GlobalError({
    error,
    reset,
}: {
    error: Error & { digest?: string };
    reset: () => void;
}) {
    useEffect(() => {
        console.error(error);
    }, [error]);

    // global-error must include <html> and <body> — it replaces the root layout
    return (
        <html lang="en">
            <body style={{ background: '#0f0f18', margin: 0, fontFamily: 'sans-serif' }}>
                <main
                    style={{
                        minHeight: '100vh',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        padding: '60px 20px',
                        textAlign: 'center',
                    }}
                >
                    <div>
                        <h1 style={{ fontSize: '48px', fontWeight: 800, color: '#fff', marginBottom: '12px' }}>
                            Critical Error
                        </h1>
                        <p style={{ color: 'rgba(255,255,255,0.55)', fontSize: '16px', maxWidth: '400px', margin: '0 auto 32px' }}>
                            A critical error occurred. Please refresh the page or contact support.
                        </p>
                        <button
                            onClick={reset}
                            style={{
                                background: '#fff',
                                color: '#0f0f18',
                                border: 'none',
                                padding: '12px 28px',
                                borderRadius: '999px',
                                fontWeight: 700,
                                fontSize: '15px',
                                cursor: 'pointer',
                            }}
                        >
                            Reload page
                        </button>
                    </div>
                </main>
            </body>
        </html>
    );
}
