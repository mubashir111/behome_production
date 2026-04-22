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

interface PromoCard {
    name: string;
    subtitle: string | null;
    badge_text: string | null;
    description: string | null;
    link: string;
    image: string | null;
    discount_pct: number | null;
    discounted_price: string | null;
    currency_price: string | null;
}

interface Props {
    slides: Slide[];
    featuredPromotions?: PromoCard[];
}

export default function HeroSlider({ slides, featuredPromotions = [] }: Props) {
    const activeSlides = slides.filter(s => s.status === 5);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isAnimating, setIsAnimating] = useState(false);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);

    // Promo card auto-slide
    const [promoIndex, setPromoIndex] = useState(0);
    const promoRef = useRef<NodeJS.Timeout | null>(null);

    useEffect(() => {
        if (featuredPromotions.length <= 1) return;
        promoRef.current = setInterval(() => {
            setPromoIndex(i => (i + 1) % featuredPromotions.length);
        }, 4000);
        return () => { if (promoRef.current) clearInterval(promoRef.current); };
    }, [featuredPromotions.length]);

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
        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current);
        };
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
            style={{
                position: 'relative',
                width: '100%',
                height: '100svh',
                minHeight: 'clamp(560px, 90vh, 960px)',
                background: '#0d0d0d',
            }}
        >
            {/* Slides */}
            {activeSlides.map((slide, index) => (
                <div
                    key={slide.id}
                    style={{
                        position: 'absolute',
                        inset: 0,
                        transition: 'opacity 0.9s cubic-bezier(0.4, 0, 0.2, 1)',
                        opacity: index === currentIndex ? 1 : 0,
                        zIndex: index === currentIndex ? 2 : 1,
                    }}
                >
                    {/* Background Image */}
                    <Image
                        src={slide.image}
                        alt={slide.title}
                        fill
                        unoptimized
                        style={{ objectFit: 'cover', objectPosition: 'center' }}
                        priority={index === 0}
                    />

                    {/* Layered Gradient Overlay */}
                    <div style={{
                        position: 'absolute',
                        inset: 0,
                        background: 'linear-gradient(105deg, rgba(10,8,6,0.80) 0%, rgba(10,8,6,0.55) 45%, rgba(10,8,6,0.15) 75%, rgba(10,8,6,0.05) 100%)',
                        zIndex: 1,
                    }} />
                    {/* Bottom vignette for controls readability */}
                    <div style={{
                        position: 'absolute',
                        inset: 0,
                        background: 'linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 30%)',
                        zIndex: 1,
                    }} />

                    {/* Main Content Grid */}
                    <div style={{
                        position: 'absolute',
                        inset: 0,
                        zIndex: 2,
                        display: 'grid',
                        gridTemplateColumns: '1fr 1fr',
                        alignItems: 'center',
                        padding: 'clamp(80px, 8vw, 120px) clamp(24px, 6vw, 80px)',
                        gap: '40px',
                    }}
                    className="hero-slider-grid"
                    >
                        {/* Left — Text Content */}
                        <div
                            className="hero-slider-text-block"
                            style={{
                                transition: 'all 0.85s cubic-bezier(0.4, 0, 0.2, 1)',
                                transform: index === currentIndex ? 'translateY(0)' : 'translateY(40px)',
                                opacity: index === currentIndex ? 1 : 0,
                            }}
                        >
                            {/* Badge */}
                            {slide.badge_text && (
                                <div style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: '8px',
                                    marginBottom: '24px',
                                }}>
                                    <span style={{
                                        display: 'block',
                                        width: '32px',
                                        height: '1px',
                                        background: 'var(--base-color, #c9a96e)',
                                    }} />
                                    <span style={{
                                        fontFamily: 'var(--font-body, sans-serif)',
                                        color: 'var(--base-color, #c9a96e)',
                                        fontSize: '11px',
                                        fontWeight: '700',
                                        letterSpacing: '4px',
                                        textTransform: 'uppercase',
                                    }}>
                                        {slide.badge_text}
                                    </span>
                                </div>
                            )}

                            {/* Title */}
                            <h1 style={{
                                fontFamily: 'var(--font-heading, sans-serif)',
                                color: '#ffffff',
                                fontWeight: '700',
                                fontSize: 'clamp(38px, 5.5vw, 96px)',
                                lineHeight: '1.04',
                                letterSpacing: 'clamp(-1px, -0.025em, -2.5px)',
                                marginBottom: '20px',
                                maxWidth: '580px',
                            }}>
                                {slide.title}
                            </h1>

                            {/* Description */}
                            {slide.description && (
                                <p style={{
                                    color: 'rgba(255,255,255,0.68)',
                                    fontSize: 'clamp(14px, 1.4vw, 18px)',
                                    letterSpacing: '0.3px',
                                    marginBottom: '44px',
                                    lineHeight: '1.7',
                                    maxWidth: '440px',
                                }}>
                                    {slide.description}
                                </p>
                            )}

                            {/* CTA Button — Gold accent */}
                            <div style={{ display: 'flex', alignItems: 'center', gap: '20px', flexWrap: 'wrap' }}>
                                <Link
                                    href={slide.link || '/shop'}
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: '12px',
                                        background: 'var(--base-color, #c9a96e)',
                                        color: '#0d0d0d',
                                        padding: 'clamp(13px, 1.5vw, 17px) clamp(28px, 3.5vw, 44px)',
                                        borderRadius: '3px',
                                        fontWeight: '700',
                                        fontSize: '12px',
                                        letterSpacing: '2px',
                                        textTransform: 'uppercase',
                                        textDecoration: 'none',
                                        transition: 'all 0.3s ease',
                                        boxShadow: '0 4px 24px rgba(201,169,110,0.35)',
                                    }}
                                    onMouseEnter={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.background = '#b8924f';
                                        el.style.boxShadow = '0 6px 32px rgba(201,169,110,0.5)';
                                        el.style.transform = 'translateY(-2px)';
                                    }}
                                    onMouseLeave={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.background = 'var(--base-color, #c9a96e)';
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
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: '8px',
                                        color: 'rgba(255,255,255,0.75)',
                                        fontSize: '12px',
                                        fontWeight: '600',
                                        letterSpacing: '1.5px',
                                        textTransform: 'uppercase',
                                        textDecoration: 'none',
                                        borderBottom: '1px solid rgba(255,255,255,0.3)',
                                        paddingBottom: '2px',
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

                        {/* Right — Promo Cards (offer products) or Shop by Room fallback */}
                        <div
                            className="hero-slider-right-panel"
                            style={{
                                transition: 'all 0.9s cubic-bezier(0.4, 0, 0.2, 1) 0.15s',
                                transform: index === currentIndex ? 'translateX(0)' : 'translateX(40px)',
                                opacity: index === currentIndex ? 1 : 0,
                                borderRadius: '12px',
                                overflow: 'hidden',
                                position: 'relative',
                            }}
                        >
                            {featuredPromotions.length > 0 ? (
                                /* ── Promo slider panel ── */
                                (() => {
                                    const promo = featuredPromotions[promoIndex];
                                    return (
                                        <div style={{
                                            borderRadius: '12px', overflow: 'hidden',
                                            border: '1px solid rgba(255,255,255,0.10)',
                                            background: '#111',
                                            display: 'flex', flexDirection: 'column',
                                            minHeight: '320px',
                                        }}>
                                            {/* Image area */}
                                            <div style={{ position: 'relative', flex: 1, minHeight: '220px' }}>
                                                {promo.image ? (
                                                    <Image
                                                        key={promo.link}
                                                        src={promo.image}
                                                        alt={promo.name}
                                                        fill unoptimized
                                                        style={{ objectFit: 'cover', objectPosition: 'center', transition: 'opacity 0.6s ease' }}
                                                    />
                                                ) : (
                                                    <div style={{ width: '100%', height: '100%', background: 'rgba(255,255,255,0.04)' }} />
                                                )}
                                                <div style={{
                                                    position: 'absolute', inset: 0,
                                                    background: 'linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 60%)',
                                                }} />
                                                {promo.discount_pct && (
                                                    <div style={{
                                                        position: 'absolute', top: 12, right: 12,
                                                        background: 'var(--base-color,#c9a96e)', color: '#0d0d0d',
                                                        fontSize: '10px', fontWeight: '800', letterSpacing: '0.5px',
                                                        padding: '4px 9px', borderRadius: '4px',
                                                    }}>
                                                        {promo.discount_pct}% OFF
                                                    </div>
                                                )}
                                            </div>
                                            {/* Info area */}
                                            <div style={{ padding: '16px 18px 14px', background: 'rgba(0,0,0,0.6)', backdropFilter: 'blur(12px)' }}>
                                                {promo.badge_text && (
                                                    <span style={{
                                                        fontSize: '9px', fontWeight: '700', letterSpacing: '3px',
                                                        textTransform: 'uppercase', color: 'var(--base-color,#c9a96e)',
                                                        display: 'block', marginBottom: '4px',
                                                    }}>{promo.badge_text}</span>
                                                )}
                                                <a href={promo.link} style={{
                                                    color: '#fff', fontWeight: '700', fontSize: '15px',
                                                    textDecoration: 'none', display: 'block', marginBottom: '6px',
                                                    lineHeight: 1.3,
                                                }}>{promo.name}</a>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                                    {promo.discounted_price && (
                                                        <span style={{ color: 'var(--base-color,#c9a96e)', fontWeight: '800', fontSize: '16px' }}>
                                                            {promo.discounted_price}
                                                        </span>
                                                    )}
                                                    {promo.currency_price && promo.discounted_price && (
                                                        <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: '12px', textDecoration: 'line-through' }}>
                                                            {promo.currency_price}
                                                        </span>
                                                    )}
                                                </div>
                                                {/* Dots */}
                                                {featuredPromotions.length > 1 && (
                                                    <div style={{ display: 'flex', gap: 5, marginTop: 10 }}>
                                                        {featuredPromotions.map((_, di) => (
                                                            <button key={di}
                                                                onClick={() => setPromoIndex(di)}
                                                                style={{
                                                                    width: di === promoIndex ? '18px' : '6px', height: '6px',
                                                                    borderRadius: '3px', border: 'none', padding: 0, cursor: 'pointer',
                                                                    background: di === promoIndex ? 'var(--base-color,#c9a96e)' : 'rgba(255,255,255,0.3)',
                                                                    transition: 'all 0.3s ease',
                                                                }}
                                                            />
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })()
                            ) : (
                                /* ── Shop by Room fallback ── */
                                <div style={{
                                    display: 'flex', flexDirection: 'column', justifyContent: 'center',
                                    background: 'rgba(255,255,255,0.06)', backdropFilter: 'blur(20px)',
                                    border: '1px solid rgba(255,255,255,0.10)', borderRadius: '12px', overflow: 'hidden', height: '100%',
                                }}>
                                    <div style={{ padding: '18px 24px 14px', borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                            <span style={{ display: 'block', width: '20px', height: '1px', background: 'var(--base-color, #c9a96e)' }} />
                                            <span style={{ color: 'var(--base-color, #c9a96e)', fontSize: '10px', fontWeight: '700', letterSpacing: '3px', textTransform: 'uppercase' }}>
                                                Shop by Room
                                            </span>
                                        </div>
                                    </div>
                                    {[
                                        { label: 'Living Room', sub: 'Sofas, tables & accents', href: '/shop?category=living-room' },
                                        { label: 'Bedroom', sub: 'Beds, wardrobes & lighting', href: '/shop?category=bedroom' },
                                        { label: 'Dining', sub: 'Tables, chairs & sideboards', href: '/shop?category=dining' },
                                        { label: 'Lighting', sub: 'Pendants, floor & table lamps', href: '/shop?category=lighting' },
                                        { label: 'Decor & Art', sub: 'Objects, rugs & wall art', href: '/shop?category=decor' },
                                    ].map((cat, i, arr) => (
                                        <a key={cat.label} href={cat.href} style={{
                                            display: 'flex', alignItems: 'center', gap: '14px', padding: '14px 24px',
                                            borderBottom: i < arr.length - 1 ? '1px solid rgba(255,255,255,0.07)' : 'none',
                                            textDecoration: 'none', transition: 'background 0.25s ease',
                                        }}
                                        onMouseEnter={e => { (e.currentTarget as HTMLAnchorElement).style.background = 'rgba(201,169,110,0.10)'; }}
                                        onMouseLeave={e => { (e.currentTarget as HTMLAnchorElement).style.background = 'transparent'; }}>
                                            <div style={{ flex: 1, minWidth: 0 }}>
                                                <div style={{ color: '#ffffff', fontSize: '13px', fontWeight: '600', marginBottom: '2px' }}>{cat.label}</div>
                                                <div style={{ color: 'rgba(255,255,255,0.42)', fontSize: '11px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{cat.sub}</div>
                                            </div>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <path d="M9 18l6-6-6-6" />
                                            </svg>
                                        </a>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            ))}

            {/* ── Bottom Bar: Arrows + Counter + Dots ── */}
            <div style={{
                position: 'absolute',
                bottom: 0,
                left: 0,
                right: 0,
                zIndex: 10,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                padding: 'clamp(16px, 2.5vw, 28px) clamp(24px, 6vw, 80px)',
                paddingBottom: 'clamp(24px, 3vw, 40px)',
            }}>
                {/* Prev / Next Arrows */}
                {activeSlides.length > 1 && (
                    <div style={{ display: 'flex', gap: '10px' }}>
                        <button
                            onClick={goToPrev}
                            aria-label="Previous slide"
                            style={{
                                width: '46px',
                                height: '46px',
                                borderRadius: '50%',
                                background: 'rgba(255,255,255,0.10)',
                                backdropFilter: 'blur(10px)',
                                border: '1px solid rgba(255,255,255,0.18)',
                                color: '#fff',
                                cursor: 'pointer',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                transition: 'all 0.3s ease',
                                padding: 0,
                            }}
                            onMouseEnter={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.22)'; }}
                            onMouseLeave={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.10)'; }}
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>
                        <button
                            onClick={goToNext}
                            aria-label="Next slide"
                            style={{
                                width: '46px',
                                height: '46px',
                                borderRadius: '50%',
                                background: 'rgba(255,255,255,0.10)',
                                backdropFilter: 'blur(10px)',
                                border: '1px solid rgba(255,255,255,0.18)',
                                color: '#fff',
                                cursor: 'pointer',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                transition: 'all 0.3s ease',
                                padding: 0,
                            }}
                            onMouseEnter={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.22)'; }}
                            onMouseLeave={e => { (e.currentTarget as HTMLButtonElement).style.background = 'rgba(255,255,255,0.10)'; }}
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                )}

                {/* Dot indicators — centre */}
                {activeSlides.length > 1 && (
                    <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                        {activeSlides.map((_, i) => (
                            <button
                                key={i}
                                onClick={() => goToSlide(i)}
                                aria-label={`Go to slide ${i + 1}`}
                                style={{
                                    width: i === currentIndex ? '28px' : '8px',
                                    height: '8px',
                                    borderRadius: '4px',
                                    background: i === currentIndex ? 'var(--base-color, #c9a96e)' : 'rgba(255,255,255,0.35)',
                                    border: 'none',
                                    cursor: 'pointer',
                                    padding: 0,
                                    transition: 'all 0.4s ease',
                                }}
                            />
                        ))}
                    </div>
                )}

                {/* Slide Counter */}
                {activeSlides.length > 1 && (
                    <div style={{
                        display: 'flex',
                        alignItems: 'baseline',
                        gap: '6px',
                        fontFamily: 'var(--font-body, sans-serif)',
                    }}>
                        <span style={{ color: '#ffffff', fontSize: 'clamp(18px, 2vw, 26px)', fontWeight: '700', letterSpacing: '-0.5px' }}>
                            {padNum(currentIndex + 1)}
                        </span>
                        <span style={{ color: 'rgba(255,255,255,0.35)', fontSize: '12px', fontWeight: '400' }}>
                            /
                        </span>
                        <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 'clamp(13px, 1.3vw, 17px)', fontWeight: '400' }}>
                            {padNum(activeSlides.length)}
                        </span>
                    </div>
                )}
            </div>

            {/* Scroll Indicator */}
            <div
                style={{
                    position: 'absolute',
                    bottom: 'clamp(28px, 4vh, 52px)',
                    left: '50%',
                    transform: 'translateX(-50%)',
                    zIndex: 10,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: '8px',
                    cursor: 'pointer',
                }}
                onClick={() => {
                    const next = document.querySelector('.hero-slider-section + *') as HTMLElement | null;
                    if (next) next.scrollIntoView({ behavior: 'smooth' });
                }}
            >
                <span style={{
                    color: 'rgba(255,255,255,0.45)',
                    fontSize: '9px',
                    fontWeight: '600',
                    letterSpacing: '3px',
                    textTransform: 'uppercase',
                }}>
                    Scroll
                </span>
                <div style={{
                    width: '22px',
                    height: '36px',
                    borderRadius: '11px',
                    border: '1.5px solid rgba(255,255,255,0.3)',
                    display: 'flex',
                    justifyContent: 'center',
                    paddingTop: '6px',
                }}>
                    <div style={{
                        width: '3px',
                        height: '8px',
                        borderRadius: '2px',
                        background: 'rgba(255,255,255,0.6)',
                        animation: 'heroScrollBounce 1.6s ease-in-out infinite',
                    }} />
                </div>

                <style>{`
                    @keyframes heroScrollBounce {
                        0%, 100% { transform: translateY(0); opacity: 1; }
                        60% { transform: translateY(8px); opacity: 0.3; }
                    }
                    @media (max-width: 768px) {
                        .hero-slider-grid {
                            grid-template-columns: 1fr !important;
                        }
                        .hero-slider-right-panel {
                            display: none !important;
                        }
                    }
                `}</style>
            </div>
        </section>
    );
}
