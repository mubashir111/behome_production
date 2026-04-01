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

interface Props {
    slides: Slide[];
    featuredPromotions?: FeaturedPromotion[];
}

export default function HeroSliderAlt({ slides, featuredPromotions = [] }: Props) {
    const activeSlides = slides.filter(s => s.status === 5);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isAnimating, setIsAnimating] = useState(false);
    const [cardHovered, setCardHovered] = useState(false);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);

    // Pick the active promotion card in sync with the slide, cycling around
    const activePromo: FeaturedPromotion | null =
        featuredPromotions.length > 0
            ? featuredPromotions[currentIndex % featuredPromotions.length]
            : null;

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

    // Derive a clean eyebrow label from the active promotion
    const eyebrowLabel = activePromo?.badge_text
        || activePromo?.subtitle
        || null;

    // Show the card only when there is at least one real promotion
    const showPromoCard = !!activePromo;

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
                            gridTemplateColumns: showPromoCard ? '1fr 1fr' : '1fr',
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

            {/* ── Persistent Overlay: Right-side Editorial Promotion Card ── */}
            {showPromoCard && (
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
                    <div /> {/* Empty left half */}

                    <div
                        className="hero-slider-right-panel"
                        style={{
                            display: 'flex',
                            justifyContent: 'flex-end',
                            pointerEvents: 'auto',
                        }}
                    >
                        {/* ── Luxury Promo Card ── */}
                        <div
                            onMouseEnter={() => setCardHovered(true)}
                            onMouseLeave={() => setCardHovered(false)}
                            style={{
                                width: '100%',
                                maxWidth: '340px',
                                background: 'rgba(10,9,7,0.62)',
                                backdropFilter: 'blur(28px)',
                                WebkitBackdropFilter: 'blur(28px)',
                                border: `1px solid ${cardHovered ? 'rgba(201,169,110,0.45)' : 'rgba(201,169,110,0.18)'}`,
                                borderRadius: '16px',
                                overflow: 'hidden',
                                boxShadow: cardHovered
                                    ? '0 0 80px rgba(201,169,110,0.14), 0 32px 80px rgba(0,0,0,0.50), inset 0 1px 0 rgba(255,255,255,0.07)'
                                    : '0 0 60px rgba(201,169,110,0.07), 0 24px 60px rgba(0,0,0,0.42), inset 0 1px 0 rgba(255,255,255,0.05)',
                                transition: 'border-color 0.4s ease, box-shadow 0.4s ease',
                            }}
                        >
                            {/* ── Image Area ── */}
                            <div style={{ position: 'relative', width: '100%', height: '190px', overflow: 'hidden' }}>
                                {activePromo?.image ? (
                                    <Image
                                        src={activePromo!.image!}
                                        alt={activePromo!.name}
                                        fill
                                        unoptimized
                                        style={{ objectFit: 'cover', objectPosition: 'center', transition: 'transform 0.6s ease' }}
                                    />
                                ) : (
                                    /* Elegant gradient placeholder — no dummy SVG */
                                    <div style={{
                                        position: 'absolute', inset: 0,
                                        background: 'linear-gradient(135deg, #1a1208 0%, #2c1f0a 40%, #1a1208 100%)',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                    }}>
                                        <span style={{
                                            fontFamily: 'var(--primary-font,serif)',
                                            fontSize: '72px',
                                            fontWeight: '800',
                                            color: 'rgba(201,169,110,0.18)',
                                            lineHeight: '1',
                                            userSelect: 'none',
                                            letterSpacing: '-3px',
                                        }}>
                                            {activePromo?.name?.charAt(0) ?? 'B'}
                                        </span>
                                        {/* Subtle pattern lines */}
                                        <div style={{
                                            position: 'absolute', inset: 0,
                                            backgroundImage: 'repeating-linear-gradient(45deg, rgba(201,169,110,0.03) 0px, rgba(201,169,110,0.03) 1px, transparent 1px, transparent 12px)',
                                        }} />
                                    </div>
                                )}

                                {/* Gradient fade bottom of image into card body */}
                                <div style={{
                                    position: 'absolute', bottom: 0, left: 0, right: 0,
                                    height: '60px',
                                    background: 'linear-gradient(to top, rgba(10,9,7,0.75) 0%, transparent 100%)',
                                }} />

                                {/* Discount badge — top-right corner of image */}
                                {activePromo?.discount_pct ? (
                                    <div style={{
                                        position: 'absolute', top: '14px', right: '14px',
                                        background: 'var(--base-color,#c9a96e)',
                                        color: '#0d0d0d',
                                        fontWeight: '800',
                                        fontSize: '10px',
                                        letterSpacing: '0.5px',
                                        padding: '5px 11px',
                                        borderRadius: '20px',
                                        zIndex: 2,
                                        boxShadow: '0 2px 12px rgba(0,0,0,0.4)',
                                    }}>
                                        {activePromo.discount_pct}% OFF
                                    </div>
                                ) : (activePromo?.subtitle && !activePromo?.discount_pct) ? (
                                    <div style={{
                                        position: 'absolute', top: '14px', right: '14px',
                                        background: 'rgba(13,11,9,0.72)',
                                        backdropFilter: 'blur(8px)',
                                        WebkitBackdropFilter: 'blur(8px)',
                                        border: '1px solid rgba(201,169,110,0.28)',
                                        color: 'var(--base-color,#c9a96e)',
                                        fontWeight: '700',
                                        fontSize: '9px',
                                        letterSpacing: '2px',
                                        textTransform: 'uppercase',
                                        padding: '5px 11px',
                                        borderRadius: '20px',
                                        zIndex: 2,
                                        maxWidth: '120px',
                                        whiteSpace: 'nowrap',
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                    }}>
                                        {activePromo.subtitle}
                                    </div>
                                ) : null}

                            </div>

                            {/* ── Card Body ── */}
                            <div style={{ padding: '20px 24px 0' }}>

                                {/* Eyebrow label — from DB only */}
                                {eyebrowLabel && (
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '10px' }}>
                                        <span style={{
                                            display: 'block', width: '18px', height: '1px',
                                            background: 'var(--base-color,#c9a96e)',
                                            flexShrink: 0,
                                        }} />
                                        <span style={{
                                            color: 'var(--base-color,#c9a96e)',
                                            fontSize: '9px',
                                            fontWeight: '700',
                                            letterSpacing: '2.5px',
                                            textTransform: 'uppercase',
                                            whiteSpace: 'nowrap',
                                            overflow: 'hidden',
                                            textOverflow: 'ellipsis',
                                        }}>
                                            {eyebrowLabel}
                                        </span>
                                    </div>
                                )}

                                {/* Promotion name */}
                                <div style={{
                                    fontFamily: 'var(--primary-font,serif)',
                                    color: '#ffffff',
                                    fontSize: 'clamp(17px,1.5vw,21px)',
                                    fontWeight: '700',
                                    letterSpacing: '-0.3px',
                                    lineHeight: '1.25',
                                    marginBottom: '8px',
                                    transition: 'opacity 0.4s ease',
                                }}>
                                    {activePromo.name}
                                </div>

                                {/* Price row — only when real price data exists */}
                                {activePromo.discounted_price ? (
                                    <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px', marginBottom: '18px' }}>
                                        <span style={{
                                            color: 'var(--base-color,#c9a96e)',
                                            fontSize: '19px',
                                            fontWeight: '800',
                                            letterSpacing: '-0.5px',
                                        }}>
                                            {activePromo.discounted_price}
                                        </span>
                                        {activePromo.currency_price && (
                                            <span style={{
                                                color: 'rgba(255,255,255,0.30)',
                                                fontSize: '13px',
                                                textDecoration: 'line-through',
                                            }}>
                                                {activePromo.currency_price}
                                            </span>
                                        )}
                                    </div>
                                ) : (
                                    /* Description — only shown when no price */
                                    activePromo.description && (
                                        <div style={{
                                            color: 'rgba(255,255,255,0.42)',
                                            fontSize: '12px',
                                            lineHeight: '1.6',
                                            marginBottom: '18px',
                                        }}>
                                            {activePromo.description}
                                        </div>
                                    )
                                )}
                            </div>

                            {/* ── Divider ── */}
                            <div style={{ height: '1px', background: 'rgba(255,255,255,0.07)', margin: '0 24px' }} />

                            {/* ── CTA Row ── */}
                            <div style={{ padding: '16px 24px 22px' }}>
                                {/* Promotion dot indicators */}
                                {featuredPromotions.length > 1 && (
                                    <div style={{ display: 'flex', gap: '5px', alignItems: 'center', marginBottom: '12px' }}>
                                        {featuredPromotions.map((_, i) => (
                                            <div
                                                key={i}
                                                style={{
                                                    width: i === (currentIndex % featuredPromotions.length) ? '20px' : '5px',
                                                    height: '5px',
                                                    borderRadius: '3px',
                                                    background: i === (currentIndex % featuredPromotions.length)
                                                        ? 'var(--base-color,#c9a96e)'
                                                        : 'rgba(255,255,255,0.20)',
                                                    transition: 'all 0.4s ease',
                                                }}
                                            />
                                        ))}
                                    </div>
                                )}

                                <Link
                                    href={activePromo?.link || '/shop'}
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'space-between',
                                        background: 'rgba(201,169,110,0.09)',
                                        border: '1px solid rgba(201,169,110,0.22)',
                                        borderRadius: '8px',
                                        padding: '12px 16px',
                                        textDecoration: 'none',
                                        transition: 'background 0.3s ease, border-color 0.3s ease',
                                    }}
                                    onMouseEnter={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.background = 'rgba(201,169,110,0.18)';
                                        el.style.borderColor = 'rgba(201,169,110,0.40)';
                                    }}
                                    onMouseLeave={e => {
                                        const el = e.currentTarget as HTMLAnchorElement;
                                        el.style.background = 'rgba(201,169,110,0.09)';
                                        el.style.borderColor = 'rgba(201,169,110,0.22)';
                                    }}
                                >
                                    <span style={{
                                        color: '#ffffff',
                                        fontSize: '11px',
                                        fontWeight: '600',
                                        letterSpacing: '0.5px',
                                    }}>
                                        Shop the Look
                                    </span>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                        stroke="var(--base-color,#c9a96e)" strokeWidth="2"
                                        strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            )}

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

                            {/* Dots */}
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
                    className="hero-bottom-bar-scroll"
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
                <div className="hero-bottom-bar-counter" style={{ display: 'flex', justifyContent: 'flex-end' }}>
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
                @media (max-width: 767px) {
                    .hero-slider-grid {
                        grid-template-columns: 1fr !important;
                        padding: 100px 22px 100px !important;
                        align-items: flex-end !important;
                    }
                    .hero-slider-right-panel { display: none !important; }
                    .hero-slider-text-block h1 {
                        font-size: clamp(30px, 9vw, 44px) !important;
                        margin-bottom: 12px !important;
                    }
                    .hero-slider-text-block p {
                        font-size: 14px !important;
                        margin-bottom: 24px !important;
                        -webkit-line-clamp: 2;
                        display: -webkit-box;
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                    }
                    .hero-bottom-bar-scroll  { display: none !important; }
                    .hero-bottom-bar-counter { display: none !important; }
                }
            `}</style>
        </section>
    );
}
