'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import Image from 'next/image';
import Link from 'next/link';

interface Slide {
    id: number;
    title: string;
    description: string;
    image: string;
    link: string;
    button_text: string;
    badge_text: string | null;
    status: number;
}

interface FeaturedPromotion {
    name: string;
    subtitle?: string | null;
    description?: string | null;
    badge_text?: string | null;
    link?: string | null;
    image?: string | null;
    discount_pct?: number | null;
    discounted_price?: string | null;
    currency_price?: string | null;
}

// ─── Decorative SVG: abstract chair line-art ─────────────────────────────────
function ChairIllustration() {
    return (
        <svg viewBox="0 0 160 180" fill="none" xmlns="http://www.w3.org/2000/svg"
            style={{ width: '100%', maxWidth: '160px', opacity: 0.55 }}>
            <path d="M30 90 Q80 80 130 90" stroke="var(--base-color,#c9a96e)" strokeWidth="2" strokeLinecap="round"/>
            <path d="M45 90 L40 40 Q80 30 120 40 L115 90" stroke="var(--base-color,#c9a96e)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M40 40 Q80 26 120 40" stroke="var(--base-color,#c9a96e)" strokeWidth="2" strokeLinecap="round"/>
            <line x1="45" y1="90" x2="38" y2="155" stroke="var(--base-color,#c9a96e)" strokeWidth="2" strokeLinecap="round"/>
            <line x1="115" y1="90" x2="122" y2="155" stroke="var(--base-color,#c9a96e)" strokeWidth="2" strokeLinecap="round"/>
            <line x1="40" y1="40" x2="35" y2="155" stroke="rgba(201,169,110,0.4)" strokeWidth="1.5" strokeLinecap="round" strokeDasharray="4 3"/>
            <line x1="120" y1="40" x2="125" y2="155" stroke="rgba(201,169,110,0.4)" strokeWidth="1.5" strokeLinecap="round" strokeDasharray="4 3"/>
            <path d="M38 130 Q80 124 122 130" stroke="var(--base-color,#c9a96e)" strokeWidth="1.5" strokeLinecap="round" opacity="0.6"/>
            <line x1="60" y1="42" x2="57" y2="88" stroke="rgba(201,169,110,0.3)" strokeWidth="1" strokeLinecap="round"/>
            <line x1="80" y1="38" x2="80" y2="88" stroke="rgba(201,169,110,0.3)" strokeWidth="1" strokeLinecap="round"/>
            <line x1="100" y1="42" x2="103" y2="88" stroke="rgba(201,169,110,0.3)" strokeWidth="1" strokeLinecap="round"/>
        </svg>
    );
}

const palette = [
    { hex: '#C9A96E', name: 'Sand Gold' },
    { hex: '#8B7355', name: 'Walnut' },
    { hex: '#D4C5B2', name: 'Linen' },
    { hex: '#4A5240', name: 'Forest' },
    { hex: '#1C1C1C', name: 'Onyx' },
];

interface Props {
    slides: Slide[];
    featuredPromotion?: FeaturedPromotion | null;
}

export default function HeroSliderAlt({ slides, featuredPromotion }: Props) {
    const activeSlides = slides.filter(s => s.status === 5);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isAnimating, setIsAnimating] = useState(false);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);

    const goToSlide = useCallback((index: number) => {
        if (isAnimating) return;
        setIsAnimating(true);
        setCurrentIndex(index);
        setTimeout(() => setIsAnimating(false), 800);
    }, [isAnimating]);

    const goToNext = useCallback(() => {
        goToSlide((currentIndex + 1) % activeSlides.length);
    }, [activeSlides.length, currentIndex, goToSlide]);

    const goToPrev = useCallback(() => {
        goToSlide((currentIndex - 1 + activeSlides.length) % activeSlides.length);
    }, [activeSlides.length, currentIndex, goToSlide]);

    useEffect(() => {
        if (activeSlides.length <= 1) return;
        intervalRef.current = setInterval(goToNext, 7000);
        return () => { if (intervalRef.current) clearInterval(intervalRef.current); };
    }, [activeSlides.length, currentIndex, goToNext]);

    if (activeSlides.length === 0) {
        return (
            <section className="p-0" style={{ minHeight: '100vh', background: '#0d0d0d', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <div className="text-center text-white">
                    <h1 className="alt-font fw-700 fs-80 mb-15px">Elevate your living</h1>
                    <p className="mb-30px opacity-7">Premium modern homes & furniture</p>
                    <Link href="/shop" className="btn btn-glass btn-large btn-round-edge">SHOP NOW</Link>
                </div>
            </section>
        );
    }

    const padNum = (n: number) => String(n).padStart(2, '0');

    return (
        <section
            className="p-0 overflow-hidden hero-slider-section"
            style={{ position: 'relative', width: '100%', height: '100svh', minHeight: 'clamp(560px, 90vh, 960px)', background: '#0d0d0d' }}
        >
            {/* ── Slides ── */}
            {activeSlides.map((slide, index) => (
                <div
                    key={slide.id}
                    style={{
                        position: 'absolute',
                        inset: 0,
                        transition: 'opacity 0.9s cubic-bezier(0.4,0,0.2,1)',
                        opacity: index === currentIndex ? 1 : 0,
                        zIndex: index === currentIndex ? 2 : 1,
                    }}
                >
                    {/* BG image */}
                    <Image src={slide.image} alt={slide.title} fill unoptimized
                        style={{ objectFit: 'cover', objectPosition: 'center' }}
                        priority={index === 0}
                    />

                    {/* Gradient — heavier on left, clears on right */}
                    <div style={{
                        position: 'absolute', inset: 0,
                        background: 'linear-gradient(105deg, rgba(10,8,6,0.82) 0%, rgba(10,8,6,0.58) 42%, rgba(10,8,6,0.22) 68%, rgba(10,8,6,0.08) 100%)',
                        zIndex: 1,
                    }} />
                    {/* Bottom vignette */}
                    <div style={{
                        position: 'absolute', inset: 0,
                        background: 'linear-gradient(to top, rgba(0,0,0,0.50) 0%, transparent 28%)',
                        zIndex: 1,
                    }} />

                    {/* Content Grid */}
                    <div
                        className="hero-slider-grid"
                        style={{
                            position: 'absolute', inset: 0, zIndex: 2,
                            display: 'grid',
                            gridTemplateColumns: '1fr 1fr',
                            alignItems: 'center',
                            padding: 'clamp(80px,8vw,120px) clamp(24px,6vw,80px)',
                            gap: '40px',
                        }}
                    >
                        {/* ── Left: Text ── */}
                        <div
                            className="hero-slider-text-block"
                            style={{
                                transition: 'all 0.85s cubic-bezier(0.4,0,0.2,1)',
                                transform: index === currentIndex ? 'translateY(0)' : 'translateY(40px)',
                                opacity: index === currentIndex ? 1 : 0,
                            }}
                        >
                            {slide.badge_text && (
                                <div style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', marginBottom: '24px' }}>
                                    <span style={{ display: 'block', width: '32px', height: '1px', background: 'var(--base-color,#c9a96e)' }} />
                                    <span style={{ color: 'var(--base-color,#c9a96e)', fontSize: '11px', fontWeight: '700', letterSpacing: '4px', textTransform: 'uppercase' }}>
                                        {slide.badge_text}
                                    </span>
                                </div>
                            )}

                            <h1 style={{
                                fontFamily: 'var(--primary-font,serif)', color: '#ffffff', fontWeight: '700',
                                fontSize: 'clamp(38px,5.5vw,96px)', lineHeight: '1.04',
                                letterSpacing: 'clamp(-1px,-0.025em,-2.5px)', marginBottom: '20px', maxWidth: '580px',
                            }}>
                                {slide.title}
                            </h1>

                            {slide.description && (
                                <p style={{
                                    color: 'rgba(255,255,255,0.68)',
                                    fontSize: 'clamp(14px,1.4vw,18px)', letterSpacing: '0.3px',
                                    marginBottom: '44px', lineHeight: '1.7', maxWidth: '440px',
                                }}>
                                    {slide.description}
                                </p>
                            )}

                            <div style={{ display: 'flex', alignItems: 'center', gap: '20px', flexWrap: 'wrap' }}>
                                <Link
                                    href={slide.link || '/shop'}
                                    style={{
                                        display: 'inline-flex', alignItems: 'center', gap: '12px',
                                        background: 'var(--base-color,#c9a96e)', color: '#0d0d0d',
                                        padding: 'clamp(13px,1.5vw,17px) clamp(28px,3.5vw,44px)',
                                        borderRadius: '3px', fontWeight: '700', fontSize: '12px',
                                        letterSpacing: '2px', textTransform: 'uppercase', textDecoration: 'none',
                                        transition: 'all 0.3s ease', boxShadow: '0 4px 24px rgba(201,169,110,0.35)',
                                    }}
                                    onMouseEnter={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.background = '#b8924f';
                                        el.style.boxShadow = '0 6px 32px rgba(201,169,110,0.5)';
                                        el.style.transform = 'translateY(-2px)';
                                    }}
                                    onMouseLeave={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.background = 'var(--base-color,#c9a96e)';
                                        el.style.boxShadow = '0 4px 24px rgba(201,169,110,0.35)';
                                        el.style.transform = 'translateY(0)';
                                    }}
                                >
                                    {slide.button_text || 'Shop Collection'}
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </Link>

                                <Link
                                    href="/collections"
                                    style={{
                                        display: 'inline-flex', alignItems: 'center', gap: '8px',
                                        color: 'rgba(255,255,255,0.75)', fontSize: '12px', fontWeight: '600',
                                        letterSpacing: '1.5px', textTransform: 'uppercase', textDecoration: 'none',
                                        borderBottom: '1px solid rgba(255,255,255,0.3)', paddingBottom: '2px',
                                        transition: 'all 0.3s ease',
                                    }}
                                    onMouseEnter={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.color = '#ffffff';
                                        el.style.borderBottomColor = 'rgba(255,255,255,0.8)';
                                    }}
                                    onMouseLeave={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.color = 'rgba(255,255,255,0.75)';
                                        el.style.borderBottomColor = 'rgba(255,255,255,0.3)';
                                    }}
                                >
                                    View All
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            ))}

            {/* ── Persistent Overlay: Right-side Editorial card ── */}
            <div
                style={{
                    position: 'absolute', inset: 0, zIndex: 5,
                    pointerEvents: 'none',
                    display: 'grid',
                    gridTemplateColumns: '1fr 1fr',
                    alignItems: 'center',
                    padding: 'clamp(80px,8vw,120px) clamp(24px,6vw,80px)',
                    gap: '40px',
                }}
            >
                <div /> {/* Empty Left half to match slider grid */}
                
                <div
                    className="hero-slider-right-panel"
                    style={{
                        display: 'flex',
                        justifyContent: 'flex-end',
                        pointerEvents: 'auto',
                        transition: 'all 1.2s cubic-bezier(0.4,0,0.2,1)',
                    }}
                >
                    <div style={{
                        width: '100%',
                        maxWidth: '340px',
                        background: 'rgba(13,11,9,0.55)',
                        backdropFilter: 'blur(24px)',
                        WebkitBackdropFilter: 'blur(24px)',
                        border: '1px solid rgba(201,169,110,0.18)',
                        borderRadius: '16px',
                        overflow: 'hidden',
                        boxShadow: '0 32px 80px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.08)',
                    }}>
                        {/* Top area */}
                        <div style={{ padding: '32px 32px 8px', position: 'relative', display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                            {featuredPromotion?.discounted_price && featuredPromotion?.discount_pct ? (
                                <div style={{
                                    position: 'absolute', top: '16px', right: '16px',
                                    background: 'var(--base-color,#c9a96e)', color: '#0d0d0d',
                                    fontWeight: '800', fontSize: '11px', letterSpacing: '1px',
                                    padding: '5px 10px', borderRadius: '20px', zIndex: 2
                                }}>
                                    {featuredPromotion.discount_pct}% OFF
                                </div>
                            ) : null}

                            <div style={{ position: 'absolute', top: '10px', right: '24px', fontFamily: 'var(--primary-font,serif)', fontSize: '80px', fontWeight: '800', color: 'rgba(201,169,110,0.07)', lineHeight: '1', userSelect: 'none', pointerEvents: 'none' }}>26</div>

                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px', alignSelf: 'flex-start' }}>
                                <span style={{ width: '20px', height: '1px', background: 'var(--base-color,#c9a96e)', display: 'block' }} />
                                <span style={{ color: 'var(--base-color,#c9a96e)', fontSize: '10px', fontWeight: '700', letterSpacing: '3px', textTransform: 'uppercase' }}>
                                    {featuredPromotion?.badge_text || featuredPromotion?.subtitle || 'Spring 2026'}
                                </span>
                            </div>

                            {featuredPromotion?.image ? (
                                <div style={{ width: '100%', height: '150px', position: 'relative', borderRadius: '10px', overflow: 'hidden', marginBottom: '4px' }}>
                                    <Image src={featuredPromotion.image} alt={featuredPromotion.name} fill unoptimized style={{ objectFit: 'cover' }} />
                                </div>
                            ) : <ChairIllustration />}

                            <div style={{ fontFamily: 'var(--primary-font,serif)', color: '#ffffff', fontSize: 'clamp(18px,1.6vw,22px)', fontWeight: '700', letterSpacing: '-0.3px', textAlign: 'center', marginTop: '12px', marginBottom: '4px' }}>
                                {featuredPromotion?.name || 'The New Collection'}
                            </div>

                            {featuredPromotion?.discounted_price ? (
                                <div style={{ textAlign: 'center', marginBottom: '16px' }}>
                                    <span style={{ color: 'var(--base-color,#c9a96e)', fontSize: '20px', fontWeight: '800', letterSpacing: '-0.5px' }}>{featuredPromotion.discounted_price}</span>
                                    {featuredPromotion.currency_price && <span style={{ color: 'rgba(255,255,255,0.35)', fontSize: '13px', textDecoration: 'line-through', marginLeft: '8px' }}>{featuredPromotion.currency_price}</span>}
                                </div>
                            ) : (
                                <div style={{ color: 'rgba(255,255,255,0.45)', fontSize: '12px', textAlign: 'center', marginBottom: '24px' }}>
                                    {featuredPromotion?.description || 'Crafted for the modern home'}
                                </div>
                            )}
                        </div>

                        <div style={{ height: '1px', background: 'rgba(255,255,255,0.07)', margin: '0 24px' }} />

                        {!featuredPromotion?.discounted_price && (
                            <>
                                <div style={{ padding: '20px 28px 0' }}>
                                    <div style={{ color: 'rgba(255,255,255,0.35)', fontSize: '9px', fontWeight: '700', letterSpacing: '2.5px', textTransform: 'uppercase', marginBottom: '12px' }}>Season Palette</div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                                        {palette.map((c) => (
                                            <div key={c.hex} title={c.name} style={{ width: '26px', height: '26px', borderRadius: '50%', background: c.hex, border: '2px solid rgba(255,255,255,0.12)' }} />
                                        ))}
                                    </div>
                                </div>
                                <div style={{ padding: '16px 28px 0', display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                                    {['Minimalist', 'Japandi', 'Artisan'].map(tag => (
                                        <span key={tag} style={{ padding: '4px 12px', borderRadius: '20px', background: 'rgba(201,169,110,0.10)', border: '1px solid rgba(201,169,110,0.22)', color: 'rgba(201,169,110,0.85)', fontSize: '10px', fontWeight: '600', letterSpacing: '1px', textTransform: 'uppercase' }}>{tag}</span>
                                    ))}
                                </div>
                            </>
                        )}

                        <div style={{ padding: '20px 28px 24px' }}>
                            <Link href={featuredPromotion?.link || '/collections'} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: 'rgba(201,169,110,0.10)', border: '1px solid rgba(201,169,110,0.25)', borderRadius: '8px', padding: '13px 18px', textDecoration: 'none' }}>
                                <span style={{ color: '#ffffff', fontSize: '12px', fontWeight: '600' }}>Discover the Collection</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--base-color,#c9a96e)" strokeWidth="2"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            {/* ── Bottom Bar: Left (arrows + dots) | Center (scroll) | Right (counter) ── */}
            <div style={{
                position: 'absolute', bottom: 0, left: 0, right: 0, zIndex: 10,
                display: 'grid', gridTemplateColumns: '1fr auto 1fr',
                alignItems: 'end',
                padding: '0 clamp(24px,6vw,80px) clamp(24px,3vw,40px)',
            }}>
                {/* Left: arrows + dots */}
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                    {activeSlides.length > 1 && (
                        <>
                            <button onClick={goToPrev} aria-label="Previous slide"
                                style={{
                                    width: '46px', height: '46px', borderRadius: '50%',
                                    background: 'rgba(255,255,255,0.10)', backdropFilter: 'blur(10px)',
                                    border: '1px solid rgba(255,255,255,0.18)', color: '#fff',
                                    cursor: 'pointer', display: 'flex', alignItems: 'center',
                                    justifyContent: 'center', transition: 'all 0.3s ease', padding: 0, flexShrink: 0,
                                }}
                                onMouseEnter={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.22)'; }}
                                onMouseLeave={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.10)'; }}
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                    <path d="M15 18l-6-6 6-6" />
                                </svg>
                            </button>
                            <button onClick={goToNext} aria-label="Next slide"
                                style={{
                                    width: '46px', height: '46px', borderRadius: '50%',
                                    background: 'rgba(255,255,255,0.10)', backdropFilter: 'blur(10px)',
                                    border: '1px solid rgba(255,255,255,0.18)', color: '#fff',
                                    cursor: 'pointer', display: 'flex', alignItems: 'center',
                                    justifyContent: 'center', transition: 'all 0.3s ease', padding: 0, flexShrink: 0,
                                }}
                                onMouseEnter={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.22)'; }}
                                onMouseLeave={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.10)'; }}
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                    <path d="M9 18l6-6-6-6" />
                                </svg>
                            </button>

                            {/* Dots — next to arrows */}
                            <div style={{ display: 'flex', gap: '7px', alignItems: 'center', marginLeft: '4px' }}>
                                {activeSlides.map((_, i) => (
                                    <button key={i} onClick={() => goToSlide(i)} aria-label={`Go to slide ${i + 1}`}
                                        style={{
                                            width: i === currentIndex ? '26px' : '7px', height: '7px',
                                            borderRadius: '4px',
                                            background: i === currentIndex ? 'var(--base-color,#c9a96e)' : 'rgba(255,255,255,0.3)',
                                            border: 'none', cursor: 'pointer', padding: 0, transition: 'all 0.4s ease',
                                        }}
                                    />
                                ))}
                            </div>
                        </>
                    )}
                </div>

                {/* Center: scroll indicator */}
                <div
                    style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '6px', cursor: 'pointer', paddingBottom: '4px' }}
                    onClick={() => {
                        const next = document.querySelector('.hero-slider-section + *') as HTMLElement | null;
                        if (next) next.scrollIntoView({ behavior: 'smooth' });
                    }}
                >
                    <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: '9px', fontWeight: '600', letterSpacing: '3px', textTransform: 'uppercase' }}>
                        Scroll
                    </span>
                    <div style={{
                        width: '22px', height: '36px', borderRadius: '11px',
                        border: '1.5px solid rgba(255,255,255,0.25)',
                        display: 'flex', justifyContent: 'center', paddingTop: '6px',
                    }}>
                        <div style={{
                            width: '3px', height: '8px', borderRadius: '2px',
                            background: 'rgba(255,255,255,0.55)',
                            animation: 'heroScrollBounce 1.6s ease-in-out infinite',
                        }} />
                    </div>
                </div>

                {/* Right: counter */}
                <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                    {activeSlides.length > 1 && (
                        <div style={{ display: 'flex', alignItems: 'baseline', gap: '6px', fontFamily: 'var(--primary-font,serif)' }}>
                            <span style={{ color: '#ffffff', fontSize: 'clamp(18px,2vw,26px)', fontWeight: '700', letterSpacing: '-0.5px' }}>
                                {padNum(currentIndex + 1)}
                            </span>
                            <span style={{ color: 'rgba(255,255,255,0.35)', fontSize: '12px' }}>/</span>
                            <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 'clamp(13px,1.3vw,17px)', fontWeight: '400' }}>
                                {padNum(activeSlides.length)}
                            </span>
                        </div>
                    )}
                </div>
            </div>

            <style>{`
                @keyframes heroScrollBounce {
                    0%, 100% { transform: translateY(0); opacity: 1; }
                    60% { transform: translateY(8px); opacity: 0.3; }
                }
                @media (max-width: 768px) {
                    .hero-slider-grid { grid-template-columns: 1fr !important; }
                    .hero-slider-right-panel { display: none !important; }
                }
            `}</style>
        </section>
    );
}
