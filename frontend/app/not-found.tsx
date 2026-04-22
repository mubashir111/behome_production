import type { Metadata } from 'next';
import Link from 'next/link';

export const metadata: Metadata = {
    title: '404 — Page Not Found | Behome',
};

export default function NotFound() {
    return (
        <main style={{ minHeight: '80vh', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '80px 20px', textAlign: 'center', position: 'relative', overflow: 'hidden' }}>

            {/* Decorative background glow */}
            <div style={{ position: 'absolute', top: '30%', left: '50%', transform: 'translate(-50%, -50%)', width: 600, height: 600, background: 'radial-gradient(circle, rgba(197,160,89,0.06) 0%, transparent 70%)', pointerEvents: 'none' }} />

            <div style={{ position: 'relative', zIndex: 1, maxWidth: 560 }}>

                {/* 404 numeral */}
                <p style={{ fontSize: 'clamp(100px, 20vw, 160px)', fontWeight: 800, lineHeight: 1, color: 'rgba(255,255,255,0.04)', letterSpacing: '-6px', margin: '0 0 -30px', fontFamily: 'var(--font-body, sans-serif)', userSelect: 'none' }}>
                    404
                </p>

                {/* Gold divider line */}
                <div style={{ width: 48, height: 2, background: 'var(--base-color)', margin: '0 auto 24px', borderRadius: 2 }} />

                <h1 style={{ fontSize: 'clamp(24px, 4vw, 38px)', fontWeight: 700, color: '#fff', marginBottom: 14, letterSpacing: '-0.5px', fontFamily: 'var(--font-heading, sans-serif)' }}>
                    Page not found
                </h1>
                <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 15, marginBottom: 40, lineHeight: 1.7 }}>
                    The page you&apos;re looking for doesn&apos;t exist or may have been moved.<br />
                    Let us help you find what you need.
                </p>

                {/* Action buttons */}
                <div style={{ display: 'flex', gap: 12, justifyContent: 'center', flexWrap: 'wrap', marginBottom: 48 }}>
                    <Link href="/" className="btn btn-base-color btn-medium btn-round-edge btn-box-shadow">
                        <span><span className="btn-double-text" data-text="Go Home">Go Home</span></span>
                    </Link>
                    <Link href="/shop" className="btn btn-transparent-white btn-medium btn-round-edge">
                        <span><span className="btn-double-text" data-text="Browse Shop">Browse Shop</span></span>
                    </Link>
                </div>

                {/* Quick links */}
                <div style={{ borderTop: '1px solid rgba(255,255,255,0.07)', paddingTop: 28 }}>
                    <p style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'rgba(255,255,255,0.25)', marginBottom: 16 }}>Popular pages</p>
                    <div style={{ display: 'flex', justifyContent: 'center', flexWrap: 'wrap', gap: '8px 24px' }}>
                        {[
                            { href: '/collections', label: 'Collections' },
                            { href: '/contact',     label: 'Contact Us'  },
                            { href: '/faq',         label: 'FAQs'        },
                            { href: '/account',     label: 'My Account'  },
                            { href: '/wishlist',    label: 'Wishlist'    },
                        ].map(({ href, label }) => (
                            <Link key={href} href={href} className="notfound-quick-link">
                                {label}
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </main>
    );
}
