import type { Metadata } from 'next';
import Image from 'next/image';
import HeroSlider from '@/components/HeroSliderAlt';
import { apiFetch } from '@/lib/api';
import WishlistButton from '@/components/WishlistButton';
import HomeProductTabs from '@/components/HomeProductTabs';

export const metadata: Metadata = {
    title: 'Behome - Premium Architectural Decor & Luxury Furniture',
    description: 'Discover premium architectural decor, luxury furniture and high-end interior design pieces at Behome.',
    openGraph: {
        title: 'Behome - Premium Architectural Decor & Luxury Furniture',
        description: 'Discover premium architectural decor, luxury furniture and high-end interior design pieces at Behome.',
        type: 'website',
    },
};


export default async function Home() {
    const [
        slidersData,
        categoriesData,
        popularData,
        latestData,
        promotionsData,
        benefitsData,
        brandsData,
        offerData,
        settingsData,
    ] = await Promise.all([
        apiFetch('/frontend/slider', { cache: 'no-store' }).catch(() => ({ data: [] })),
        apiFetch('/frontend/product-category', { cache: 'no-store' }).catch(() => ({ data: [] })),
        apiFetch('/products?per_page=8&sort=popular', { cache: 'no-store' }).catch(() => ({ data: { data: [] } })),
        apiFetch('/products?per_page=8', { cache: 'no-store' }).catch(() => ({ data: { data: [] } })),
        apiFetch('/frontend/promotion', { cache: 'no-store' }).catch(() => ({ data: [] })),
        apiFetch('/frontend/benefit', { cache: 'no-store' }).catch(() => ({ data: [] })),
        apiFetch('/frontend/product-brand', { cache: 'no-store' }).catch(() => ({ data: [] })),
        apiFetch('/products?per_page=10&sort=offer', { cache: 'no-store' }).catch(() => ({ data: { data: [] } })),
        apiFetch('/frontend/setting', { cache: 'no-store' }).catch(() => ({ data: {} })),
    ]);

    const sliders = (slidersData.data || []).map((s: any) => ({ ...s, image: s.image || '' }));

    const categories = (categoriesData.data || []).slice(0, 6).map((c: any) => ({
        ...c,
        thumb: c.thumb || c.cover || '',
    }));

    const popularProducts = (popularData?.data?.data || popularData?.data || []).slice(0, 8);
    const latestProducts = (latestData?.data?.data || latestData?.data || []).slice(0, 8);
    const featuredProducts = popularProducts.slice(0, 3);

    const promotions = (promotionsData.data || []).map((p: any) => ({
        ...p,
        image: p.preview || p.cover || '',
    }));
    const featurePromo = promotions.find((p: any) => p.type === 15);

    // Map Dynamic Promotions Grid (declared first so featuredOffer can reference it)
    const bigPromotion = promotions.find((p: any) => p.type === 10);

    // Map all active-offer or flagged products for the hero card
    const offerProducts = (offerData.data?.data || []) as any[];
    const offerCards = offerProducts.map((p: any) => ({
        name: p.name,
        subtitle: p.discounted_price
            ? `Was ${p.currency_price}`
            : 'Limited Time Offer',
        badge_text: 'Sale',
        description: p.category_name || null,
        link: `/product/${p.slug}`,
        image: p.cover || null,
        discount_pct: p.discount
            ? Math.round((Number(p.discount) / Number(Number(p.price || 0) + Number(p.discount))) * 100)
            : null,
        discounted_price: p.discounted_price || null,
        currency_price: p.currency_price || null,
    }));

    // Build ordered promotion cards array: offer products first, then type=1 (Hero Slider Card) promotions
    const promotionCards = [
        ...offerCards,
        ...promotions.filter((p: any) => p.type === 1).map((p: any) => ({
            name: p.name,
            subtitle: p.subtitle || null,
            badge_text: null,
            description: null,
            link: p.link || '/shop',
            image: p.image || null,
            discount_pct: null,
            discounted_price: null,
            currency_price: null,
        })),
    ];

    const smallPromotions = promotions.filter((p: any) => p.type === 5).slice(0, 2);

    const settings = settingsData?.data || settingsData || {};
    const symbol = settings?.site_default_currency_symbol || '£';

    const formatAmount = (amount: number) => {
        const sym = settings?.site_default_currency_symbol || '£';
        const pos = settings?.site_currency_position == 10 ? 'right' : 'left';
        const dec = Number(settings?.site_digit_after_decimal_point) || 2;
        const formatted = amount.toFixed(dec);
        return pos === 'left' ? `${sym}${formatted}` : `${formatted}${sym}`;
    };

    const threshold = Number(settings?.site_free_delivery_threshold) || 120;
    const formattedThreshold = formatAmount(threshold);

    const benefits = (benefitsData.data || [] as any[]).filter((b: any) => b.status === 5) as any[];
    const tickerItems = benefits.length > 0
        ? [...benefits, ...benefits, ...benefits]
        : ['Premium Furniture', `Free Delivery Over ${formattedThreshold}`, 'Secure Checkout', 'Expert Support',
           'Premium Furniture', `Free Delivery Over ${formattedThreshold}`, 'Secure Checkout', 'Expert Support'].map((t, i) => ({ id: i, title: t }));

    const brands = (brandsData.data || [] as any[]).map((b: any) => ({
        ...b, image: b.cover || b.thumb || '',
    }));

    return (
        <main className="no-layout-pad">

        {/* ── Hero Slider ─────────────────────────────────────────── */}
        <HeroSlider slides={sliders} featuredPromotions={promotionCards} />

        {/* ── Featured Categories + Promotions ────────────────────── */}
        <section className="position-relative overflow-hidden bg-dark-texture pb-5">
            <div className="container position-relative z-index-9">

                {/* Category row */}
                {categories.length > 0 && (
                    <div className="row align-items-center mb-6 mt-7 xs-mb-30px xs-mt-35px">
                        <div className="col-xl-2 col-lg-3 md-mb-40px"
                            data-anime='{"translateX":[50,0],"opacity":[0,1],"duration":600,"delay":100,"easing":"easeOutQuad"}'>
                            <div className="feature-box feature-box-left-icon-middle mb-5px">
                                <div className="feature-box-icon me-5px">
                                    <i className="bi bi-heart-fill text-red fs-13"></i>
                                </div>
                                <div className="feature-box-content">
                                    <span className="d-inline-block fs-16 fw-500 text-white">On demand</span>
                                </div>
                            </div>
                            <h6 className="mb-0 fw-700 alt-font text-white">Featured categories</h6>
                        </div>
                        <div className="col-xl-10 col-lg-9">
                            <div className="row row-cols-2 row-cols-md-6 row-cols-sm-3 align-items-center">
                                {categories.map((cat: any) => (
                                    <div key={cat.id} className="col categories-style-01 sm-mb-30px">
                                        <div className="categories-box">
                                            <div className="icon-box position-relative mb-20px">
                                                <a href={`/shop?category=${cat.slug}`} className="d-block">
                                                    <Image
                                                        alt={cat.name}
                                                        src={cat.thumb || '/images/demo-decor-store-icon-01.png'}
                                                        width={130}
                                                        height={130}
                                                        unoptimized
                                                        style={{ borderRadius: '50%', width: 130, height: 130, objectFit: 'cover', boxShadow: '0 10px 30px rgba(0,0,0,0.2)', border: '1px solid rgba(255,255,255,0.1)' }}
                                                    />
                                                </a>
                                            </div>
                                            <a className="fw-600 fs-17 text-white text-white-hover"
                                                href={`/shop?category=${cat.slug}`}>{cat.name}</a>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* Promotions grid */}
                {(bigPromotion || smallPromotions.length > 0) && (
                    <div className="row g-4"
                        data-anime='{"el":"childs","translateY":[30,0],"opacity":[0,1],"duration":600,"delay":150,"staggervalue":150,"easing":"easeOutQuad"}'>

                        {bigPromotion && (
                            <div className="col-12 col-lg-6">
                                <div className="position-relative overflow-hidden rounded" style={{ height: 660 }}>
                                    <Image alt={bigPromotion.name} src={bigPromotion.image || '/images/demo-decor-store-main-banner-01.jpg'}
                                        fill unoptimized style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                    <div className="position-absolute top-0 start-0 w-100 h-100"
                                        style={{ background: 'rgba(10,10,10,0.45)' }} />
                                    <div className="position-absolute bottom-0 start-0 w-100 p-5">
                                        {bigPromotion.subtitle && (
                                            <span className="text-uppercase fw-500 ls-1px mb-10px d-block"
                                                style={{ color: 'var(--base-color)', fontSize: 13 }}>
                                                {bigPromotion.subtitle}
                                            </span>
                                        )}
                                        <h3 className="alt-font text-white ls-minus-1px mb-4">{bigPromotion.name}</h3>
                                        <a className="btn btn-switch-text btn-transparent-white-light btn-small border-1 btn-round-edge"
                                            href={bigPromotion.link || '/shop'}>
                                            <span>
                                                <span className="btn-double-text text-uppercase" data-text="SHOP NOW">SHOP NOW</span>
                                                <i className="feather icon-feather-arrow-right"></i>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        )}

                        {smallPromotions.length > 0 && (
                            <div className="col-12 col-lg-6 d-flex flex-column gap-4">
                                {smallPromotions.map((p: any, idx: number) => (
                                    <div key={p.id} className="position-relative overflow-hidden rounded flex-grow-1" style={{ height: 310 }}>
                                        <Image alt={p.name} src={p.image || `/images/demo-decor-store-main-banner-0${idx + 2}.jpg`}
                                            fill unoptimized style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                        <div className="position-absolute top-0 start-0 w-100 h-100"
                                            style={{ background: 'rgba(10,10,10,0.40)' }} />
                                        <div className={`position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center p-5 ${idx === 0 ? 'justify-content-start' : 'justify-content-end'}`}>
                                            <div style={{ maxWidth: '55%' }}>
                                                {p.subtitle && (
                                                    <span className="text-uppercase fw-500 ls-1px mb-10px d-block"
                                                        style={{ color: 'var(--base-color)', fontSize: 12 }}>
                                                        {p.subtitle}
                                                    </span>
                                                )}
                                                <h5 className="alt-font text-white ls-minus-1px mb-3">{p.name}</h5>
                                                <a className="btn btn-dark-gray btn-small btn-switch-text btn-round-edge btn-box-shadow"
                                                    href={p.link || '/shop'}>
                                                    <span><span className="btn-double-text" data-text="Explore">Explore</span></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </div>
        </section>

        {/* ── Product Tabs: New Arrivals / Ready to Ship / Trending Now ────────────── */}
        <HomeProductTabs 
            newArrivals={latestProducts} 
            readyToShip={popularProducts.slice().reverse()} 
            trendingNow={popularProducts} 
        />

        {/* ── Marquee Ticker ───────────────────────────────────────── */}
        <section className="pt-0 pb-0 overflow-hidden"
            data-anime='{"translateX":[0,0],"opacity":[0,1],"duration":600,"delay":100,"easing":"easeOutQuad"}'>
            <div className="container-fluid ps-8 pe-8">
                <div className="row position-relative">
                    <div className="col swiper text-center feather-shadow"
                        data-slider-options='{"slidesPerView":"auto","spaceBetween":0,"centeredSlides":true,"speed":8000,"loop":true,"allowTouchMove":false,"autoplay":{"delay":1,"disableOnInteraction":false},"effect":"slide"}'>
                        <div className="swiper-wrapper pb-20px swiper-width-auto marquee-slide">
                            {tickerItems.map((item: any, idx: number) => (
                                <div key={`ticker-${item.id}-${idx}`} className="swiper-slide">
                                    <div className="marquee-ticker-text fs-50 text-white alt-font fw-700 ls-minus-1px">
                                        <span className="w-15px h-15px border border-2 border-radius-100 border-color-medium-gray d-inline-block align-middle ms-50px me-50px"></span>
                                        {item.title}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {/* ── Feature Split Banner + Product Slider ───────────────── */}
        <section className="py-0 overflow-hidden">
            <div className="container-fluid p-0">
                <div className="row g-0">
                    <div className="col-md-8 cover-background"
                        data-anime='{"translateX":[-50,0],"opacity":[0,1],"duration":600,"delay":150,"easing":"easeOutQuad"}'
                        style={{ backgroundImage: featurePromo?.image ? `url('${featurePromo.image}')` : "url('images/demo-decor-store-banner-04.jpg')" }}>
                        <div className="pt-13 pb-13 pe-5 w-40 xl-w-45 lg-w-55 md-w-65 sm-w-75 float-end" style={{ maxWidth: '100%' }}>
                            <span className="fs-15 fw-700 text-dark-gray text-uppercase mb-20px xs-mb-15px d-inline-block text-decoration-line-bottom-medium">
                                {featurePromo?.subtitle || 'New Collection 2025'}
                            </span>
                            <h1 className="alt-font fw-400 text-dark-gray mb-40px lg-mb-35px xs-mb-25px ls-minus-1px">
                                {featurePromo?.name
                                    ? <>{featurePromo.name}</>
                                    : <>Lounge <span className="fw-700">collection</span></>}
                            </h1>
                            <a className="btn btn-dark-gray btn-extra-large btn-switch-text btn-round-edge btn-box-shadow"
                                href={featurePromo?.link || '/shop'}>
                                <span><span className="btn-double-text" data-text="Explore category">Explore category</span></span>
                            </a>
                        </div>
                    </div>
                    <div className="col-md-4 premium-slider-container"
                        data-anime='{"translateX":[50,0],"opacity":[0,1],"duration":600,"delay":150,"easing":"easeOutQuad"}'>
                        <div className="swiper position-relative h-100"
                            data-slider-options='{"slidesPerView":1,"loop":true,"allowTouchMove":true,"autoplay":{"delay":3000,"disableOnInteraction":false},"navigation":{"nextEl":".slider-one-slide-next-1","prevEl":".slider-one-slide-prev-1"},"effect":"fade"}'>
                            <div className="swiper-wrapper">
                                {featuredProducts.length > 0 ? featuredProducts.map((product: any) => (
                                    <div key={product.id} className="swiper-slide h-100 text-center d-flex flex-column align-items-center justify-content-center p-4"
                                        style={{ background: 'url(images/demo-decor-store-product-slider-bg-img.jpg) center center no-repeat', backgroundSize: 'contain' }}>
                                        <a href={`/product/${product.slug}`} className="d-block mb-3">
                                            <Image alt={product.name}
                                                src={product.cover || '/images/demo-decor-store-product-slider-01.png'}
                                                width={480}
                                                height={480}
                                                unoptimized
                                                style={{ maxHeight: '380px', objectFit: 'contain', width: 'auto', height: 'auto' }} />
                                        </a>
                                        <div className="slider-product-info">
                                            <a className="slider-product-name"
                                                href={`/product/${product.slug}`}>{product.name}</a>
                                            <div className="slider-product-price">
                                                {product.is_offer
                                                    ? (
                                                        <>
                                                            <span className="price-original">{product.currency_price}</span>
                                                            <span className="price-discount">{product.discounted_price}</span>
                                                        </>
                                                    )
                                                    : <span className="price-discount">{product.currency_price}</span>
                                                }
                                            </div>
                                        </div>
                                    </div>
                                )) : (
                                    <div className="swiper-slide h-100 text-center d-flex align-items-center justify-content-center"
                                        style={{ background: 'url(images/demo-decor-store-product-slider-bg-img.jpg) center center no-repeat', backgroundSize: 'contain' }}>
                                        <a href="/shop">
                                            <Image alt="Featured product" src="/images/demo-decor-store-product-slider-01.png" width={480} height={480} />
                                        </a>
                                        <div className="slider-product-info">
                                            <a className="slider-product-name" href="/shop">Shop Collection</a>
                                        </div>
                                    </div>
                                )}
                            </div>
                            <div className="slider-one-slide-prev-1 swiper-button-prev slider-navigation-style-line">
                                <i className="bi bi-chevron-left"></i>
                            </div>
                            <div className="slider-one-slide-next-1 swiper-button-next slider-navigation-style-line">
                                <i className="bi bi-chevron-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {/* ── Brand Logos ──────────────────────────────────────────── */}
        <section className="half-section"
            data-anime='{"translate":[0,0],"opacity":[0,1],"duration":600,"delay":100,"easing":"easeOutQuad"}'>
            <div className="container">
                <div className="row position-relative clients-style-08" style={{ minHeight: 120, alignItems: 'center' }}>
                    <div className="col swiper text-center feather-shadow"
                        data-slider-options='{"slidesPerView":1,"spaceBetween":0,"speed":3000,"loop":true,"allowTouchMove":false,"autoplay":{"delay":0,"disableOnInteraction":false},"breakpoints":{"1200":{"slidesPerView":4},"992":{"slidesPerView":3},"768":{"slidesPerView":3},"576":{"slidesPerView":2}},"effect":"slide"}'>
                        <div className="swiper-wrapper marquee-slide">
                            {(brands.length > 0 ? [...brands, ...brands] : [1,2,3,4,5,1,2,3].map((n, i) => ({
                                id: `static-${i}`, slug: '', name: '', image: `/images/demo-decor-store-client-0${n}.png`,
                            }))).map((brand: any, idx: number) => (
                                <div key={`brand-${brand.id}-${idx}`} className="swiper-slide d-flex align-items-center justify-content-center">
                                    <a href={brand.slug ? `/shop?brand=${brand.slug}` : '/shop'}
                                        style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                        <Image alt={brand.name || 'Brand'} src={brand.image}
                                            width={220} height={90} unoptimized className="brand-logo-img" />
                                    </a>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {/* ── Editorial: Latest Products ───────────────────────────── */}
        <section className="pb-3 bg-dark-gray lg-pb-40px md-pb-20px">
            <div className="container">
                <div className="row justify-content-center mb-25px">
                    <div className="col-lg-5 text-center"
                        data-anime='{"el":"childs","translateY":[50,0],"opacity":[0,1],"duration":600,"delay":0,"staggervalue":150,"easing":"easeOutQuad"}'>
                        <span className="text-uppercase fs-14 ls-2px fw-600" style={{ color: 'var(--base-color)' }}>
                            Just arrived
                        </span>
                        <h4 className="alt-font text-white fw-700 mb-0">Fresh from the collection</h4>
                    </div>
                </div>
                <div className="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4"
                    data-anime='{"el":"childs","translateY":[50,0],"opacity":[0,1],"duration":600,"delay":100,"staggervalue":150,"easing":"easeOutQuad"}'>
                    {latestProducts.slice(0, 4).map((product: any, idx: number) => (
                        <div key={product.id} className="col">
                            <div className="card bg-transparent border-0 h-100">
                                <div className="blog-image position-relative overflow-hidden border-radius-4px">
                                    <a href={`/product/${product.slug}`}>
                                        <Image
                                            alt={product.name}
                                            src={product.cover || `/images/demo-decor-store-blog-0${(idx % 4) + 3}.jpg`}
                                            width={640}
                                            height={240}
                                            unoptimized
                                            style={{ width: '100%', height: 240, objectFit: 'cover', display: 'block' }}
                                        />
                                        {product.is_offer && (
                                            <span className="position-absolute top-0 start-0 m-3 badge"
                                                style={{ background: 'var(--base-color)', color: '#111', fontSize: 11, fontWeight: 700, padding: '5px 10px', borderRadius: 4 }}>
                                                SALE
                                            </span>
                                        )}
                                    </a>
                                </div>
                                <div className="card-body px-0 pt-25px pb-25px">
                                    <span className="fs-13 text-uppercase d-block mb-5px fw-500">
                                        <a className="text-white fw-700 categories-text" href={`/shop?category=${product.category_slug || ''}`}>
                                            {product.category_name || 'Collection'}
                                        </a>
                                    </span>
                                    <a className="card-title fw-600 fs-17 lh-26 text-white d-inline-block mb-10px"
                                        href={`/product/${product.slug}`}>{product.name}</a>
                                    <div className="d-flex align-items-center justify-content-between">
                                        <div className="fw-600 fs-16" style={{ color: 'var(--base-color)' }}>
                                            {product.is_offer
                                                ? <><del className="opacity-5 me-8px fs-14 text-white">{product.currency_price}</del>{product.discounted_price}</>
                                                : product.currency_price}
                                        </div>
                                        <a href={`/product/${product.slug}`}
                                            className="btn btn-very-small btn-transparent-white border-1 border-color-transparent-white-light btn-round-edge">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
                <div className="text-center mt-50px">
                    <a href="/shop" className="btn btn-transparent-white border-1 border-color-transparent-white-light btn-large btn-round-edge btn-switch-text">
                        <span>
                            <span className="btn-double-text" data-text="View all products">View all products</span>
                            <i className="feather icon-feather-arrow-right ms-10px"></i>
                        </span>
                    </a>
                </div>
            </div>
        </section>

        {/* ── Why Choose Us (Benefits) ─────────────────────────────── */}
        <section className="border-top border-color-extra-medium-gray py-0 overflow-hidden">
            <div className="container-fluid">
                <div className="row row-cols-1 row-cols-lg-4 row-cols-sm-2 justify-content-center"
                    data-anime='{"el":"childs","translateX":[50,0],"opacity":[0,1],"duration":600,"delay":100,"staggervalue":150,"easing":"easeOutQuad"}'>
                    {benefits.length > 0 ? benefits.slice(0, 4).map((benefit: any, idx: number) => {
                        const iconMap = ['feather icon-feather-truck', 'feather icon-feather-lock', 'feather icon-feather-award', 'feather icon-feather-headphones'];
                        const borders = ['border-end md-border-bottom xs-border-end-0', 'border-end md-border-bottom md-border-end-0', 'border-end xs-border-bottom xs-border-end-0', ''];
                        return (
                            <div key={benefit.id}
                                className={`col d-flex justify-content-center icon-with-text-style-08 text-center ${borders[idx] || ''} border-color-extra-medium-gray pt-45px pb-45px`}>
                                <div className="feature-box feature-box-left-icon-middle d-inline-flex align-middle">
                                    <div className="feature-box-icon me-15px">
                                        {benefit.thumb ? (
                                            <Image alt={benefit.title} src={benefit.thumb}
                                                width={40} height={40} unoptimized
                                                style={{ width: 40, height: 40, objectFit: 'contain' }} />
                                        ) : (
                                            <i className={`${iconMap[idx] || iconMap[0]} fs-24 text-white`}></i>
                                        )}
                                    </div>
                                    <div className="feature-box-content">
                                        <span className="fw-600 text-white d-block lh-24 fs-17">{benefit.title}</span>
                                        {benefit.description && (
                                            <span className="fs-14 opacity-7">{benefit.description}</span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    }) : (
                        [
                            { icon: 'feather icon-feather-truck',      title: 'Free shipping',   sub: 'Free return & exchange' },
                            { icon: 'feather icon-feather-map-pin',    title: 'Store locator',   sub: 'Find nearest store' },
                            { icon: 'feather icon-feather-lock',       title: 'Secure payment',  sub: '100% secure method' },
                            { icon: 'feather icon-feather-headphones', title: 'Online support',  sub: '24/7 support center' },
                        ].map((item, idx) => {
                            const borders = ['border-end md-border-bottom xs-border-end-0', 'border-end md-border-bottom md-border-end-0', 'border-end xs-border-bottom xs-border-end-0', ''];
                            return (
                                <div key={idx}
                                    className={`col d-flex justify-content-center icon-with-text-style-08 text-center ${borders[idx]} border-color-extra-medium-gray pt-45px pb-45px`}>
                                    <div className="feature-box feature-box-left-icon-middle d-inline-flex align-middle">
                                        <div className="feature-box-icon me-15px">
                                            <i className={`${item.icon} fs-24 text-white`}></i>
                                        </div>
                                        <div className="feature-box-content">
                                            <span className="fw-600 text-white d-block lh-24 fs-17">{item.title}</span>
                                            <span className="fs-14 opacity-7">{item.sub}</span>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        </section>

        {/* ── Cookie + Scroll ──────────────────────────────────────── */}
        <div className="cookie-message bg-dark-gray border-radius-8px" id="cookies-model">
            <div className="cookie-description fs-14 text-white mb-20px lh-22">
                We use cookies to enhance your browsing experience, serve personalised content, and analyse traffic. By clicking "Allow cookies" you consent to our use of cookies.
            </div>
            <div className="cookie-btn">
                <a aria-label="btn" className="btn btn-transparent-white border-1 border-color-transparent-white-light btn-very-small btn-switch-text btn-rounded w-100 mb-15px" href="/shop">
                    <span><span className="btn-double-text" data-text="Cookie policy">Cookie policy</span></span>
                </a>
                <a aria-label="text" className="btn btn-white btn-very-small btn-switch-text btn-box-shadow accept_cookies_btn btn-rounded w-100" data-accept-btn="" href="/shop">
                    <span><span className="btn-double-text" data-text="Allow cookies">Allow cookies</span></span>
                </a>
            </div>
        </div>
        <div className="scroll-progress d-none d-xxl-block">
            <a aria-label="scroll" className="scroll-top" href="/">
                <span className="scroll-text">Scroll</span><span className="scroll-line"><span className="scroll-point"></span></span>
            </a>
        </div>

        </main>
    );
}
