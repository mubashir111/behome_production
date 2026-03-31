import type { Metadata } from 'next';
import Link from 'next/link';

export const metadata: Metadata = {
    title: '404 — Page Not Found | Behome',
};

export default function NotFound() {
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
                        fontFamily: 'var(--font-outfit, sans-serif)',
                    }}
                >
                    404
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
                    Page not found
                </h1>
                <p style={{ color: 'rgba(255,255,255,0.55)', fontSize: '16px', marginBottom: '36px', maxWidth: '420px', margin: '0 auto 36px' }}>
                    The page you&apos;re looking for doesn&apos;t exist or has been moved.
                </p>
                <div style={{ display: 'flex', gap: '12px', justifyContent: 'center', flexWrap: 'wrap' }}>
                    <Link
                        href="/"
                        className="btn btn-white btn-medium btn-round-edge fw-600"
                    >
                        Go home
                    </Link>
                    <Link
                        href="/shop"
                        className="btn btn-dark-gray btn-medium btn-round-edge fw-600"
                    >
                        Browse shop
                    </Link>
                </div>
            </div>
        </main>
    );
}
