import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import UserAccount from './UserAccount';
import CartIcon from './CartIcon';
import NotificationBell from './NotificationBell';
import HeaderSearch from './HeaderSearch';
import MobileSearchBar from './MobileSearchBar';
import LanguageSwitcher from './LanguageSwitcher';
export default async function Header() {
    let categories = [];
    let settings: any = null;

    try {
        const [categoriesResponse, settingsResponse] = await Promise.all([
            apiFetch('/frontend/product-category'),
            apiFetch('/frontend/setting')
        ]);
        categories = categoriesResponse?.data || [];
        settings = settingsResponse?.data || settingsResponse;
    } catch (error) {
        console.error('[HEADER_ERROR] Failed to fetch data:', error);
    }

    // Group categories if needed, or just list them. 
    // For now, let's just list them in the first column or spread them.
    return (
        <header className="header-with-topbar no-sticky">
            {/*  start navigation  */}
            <nav className="navbar navbar-expand-lg header-light bg-transparent disable-fixed px-lg-0 py-0 py-lg-0">
                <div className="container-fluid px-0 px-md-4 d-flex align-items-center header-navbar-main">
                    <div className="row w-100 align-items-center m-0">
                        <div className="col-6 col-xl-3 col-lg-2 order-1 d-flex align-items-center gap-3">
                            <a className="glass-logo-wrapper d-none d-lg-flex" href="https://www.blkrockarchitect.ae/en" target="" rel="noopener noreferrer">
                                <div className="glass-logo-box2">
                                    <Image alt="Blackrock Logo" src="/images/new/logo/blackrock.png" width={72} height={72} priority style={{ objectFit: 'contain' }} />
                                </div>
                            </a>
                            <a className="glass-logo-wrapper" href="/">
                                <div className="glass-logo-box">
                                    <Image alt="Behome Logo" src="/images/new/logo/Behome%20Final%20.png" width={72} height={72} priority style={{ objectFit: 'contain' }} />
                                </div>
                                <span className="logo-text">BEHOME</span>
                            </a>
                        </div>
                        <div className="col-12 col-xl-5 col-lg-6 order-3 order-lg-2 menu-order position-static">
                            <div className="collapse navbar-collapse justify-content-end" id="navbarNav">
                                {/*  Mobile Navigation Header  */}
                                <div className="mobile-nav-header d-lg-none">
                                    <div className="mobile-logo">
                                        <a className="glass-logo-wrapper" href="/">
                                            <div className="glass-logo-box">
                                                <Image alt="Behome Logo" src="/images/new/logo/Behome%20Final%20.png" width={72} height={72} priority style={{ objectFit: 'contain' }} />
                                            </div>
                                            <span className="logo-text">BEHOME</span>
                                        </a>
                                    </div>
                                    <div className="mobile-icons">
                                        <a className="icon-box position-relative" href="/cart">
                                            <i className="feather icon-feather-shopping-bag"></i>
                                            <CartIcon />
                                        </a>
                                        <a className="icon-box" href="/account">
                                            <i className="feather icon-feather-user"></i>
                                        </a>
                                        <div className="close-button" data-bs-target="#navbarNav" data-bs-toggle="collapse">
                                            <i className="feather icon-feather-x"></i>
                                        </div>
                                    </div>
                                </div>
                                {/* ── Mobile search bar ── */}
                                <div className="mobile-drawer-search d-lg-none">
                                    <MobileSearchBar />
                                </div>

                                {/* ── Mobile account quick-link ── */}
                                <div className="mobile-drawer-account d-lg-none">
                                    <a href="/account" className="mobile-drawer-account-link">
                                        <div className="mobile-drawer-account-avatar">
                                            <i className="feather icon-feather-user"></i>
                                        </div>
                                        <div className="mobile-drawer-account-text">
                                            <span className="mobile-drawer-account-label">My Account</span>
                                            <span className="mobile-drawer-account-sub">View profile &amp; orders</span>
                                        </div>
                                        <i className="feather icon-feather-chevron-right mobile-drawer-account-arrow"></i>
                                    </a>
                                </div>

                                {/* ── Mobile Notifications quick-link ── */}
                                <div className="mobile-drawer-account d-lg-none">
                                    <a href="/account?tab=orders" className="mobile-drawer-account-link">
                                        <div className="mobile-drawer-account-avatar">
                                            <i className="feather icon-feather-bell"></i>
                                        </div>
                                        <div className="mobile-drawer-account-text">
                                            <span className="mobile-drawer-account-label">Notifications</span>
                                            <span className="mobile-drawer-account-sub">Orders &amp; updates</span>
                                        </div>
                                        <i className="feather icon-feather-chevron-right mobile-drawer-account-arrow"></i>
                                    </a>
                                </div>

                                {/* ── Nav divider label ── */}
                                <div className="mobile-drawer-nav-label d-lg-none">Navigation</div>

                                <div className="glass-pill-nav">
                                    <ul className="navbar-nav">
                                        <li className="nav-item"><a className="nav-link text-white-hover" href="/">Home</a>
                                        </li>
                                        <li className="nav-item dropdown submenu">
                                            <a className="nav-link text-white-hover" href="/shop">Shop
                                                <span
                                                    className="badge bg-base-color text-dark-gray fw-700 py-1 px-2 rounded-1 ms-1"
                                                    style={{ fontSize: '10px', verticalAlign: 'middle' }}>HOT</span></a>
                                            <i aria-expanded="false" className="fa-solid fa-angle-down dropdown-toggle"
                                                data-bs-toggle="dropdown" id="navbarDropdownMenuLink1" role="button"></i>
                                            <div aria-labelledby="navbarDropdownMenuLink1"
                                                className="dropdown-menu submenu-content">
                                                <div className="d-lg-flex mega-menu m-auto flex-column">
                                                    <div className="row row-cols-1 row-cols-lg-5 mb-60px md-mb-30px sm-mb-20px">
                                                        {categories.slice(0, 5).map((category: any) => (
                                                            <div className="col" key={category.id}>
                                                                <ul>
                                                                    <li className="sub-title">{category.name}</li>
                                                                    <li><a href={`/shop?category=${category.slug}`}>View All</a></li>
                                                                    {category.children?.map((child: any) => (
                                                                        <li key={child.id}><a href={`/shop?category=${child.slug}`}>{child.name}</a></li>
                                                                    ))}
                                                                </ul>
                                                            </div>
                                                        ))}
                                                    </div>
                                                    <div className="row row-cols-1 row-cols-md-2">
                                                        <div className="col">
                                                            <a href="/shop"><Image alt="" className="w-100"
                                                                src="/images/demo-decor-store-menu-banner-01.jpg" width={640} height={420} /></a>
                                                        </div>
                                                        <div className="col">
                                                            <a href="/shop"><Image alt="" className="w-100"
                                                                src="/images/demo-decor-store-menu-banner-02.jpg" width={640} height={420} /></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li className="nav-item dropdown submenu">
                                            <a className="nav-link text-white-hover"
                                                href="/collections">Collections</a>
                                            <i aria-expanded="false" className="fa-solid fa-angle-down dropdown-toggle"
                                                data-bs-toggle="dropdown" id="navbarDropdownMenuLink2" role="button"></i>
                                            <div aria-labelledby="navbarDropdownMenuLink2"
                                                className="dropdown-menu submenu-content">
                                                <div className="d-lg-flex mega-menu m-auto flex-column">
                                                    <div
                                                        className="row row-cols-2 row-cols-lg-6 row-cols-sm-3 md-pt-15px align-items-center justify-content-center mb-60px md-mb-30px sm-mb-0">
                                                        {(categories.length > 0 ? categories : []).slice(0, 6).map((cat: any, idx: number) => {
                                                            const imgSrc = cat.cover || cat.thumb || `/images/demo-decor-store-menu-category-0${(idx % 6) + 1}.jpg`;
                                                            return (
                                                                <div className="col md-mb-30px" key={cat.id}>
                                                                    <a className="text-center" href={`/shop?category=${cat.slug}`}>
                                                                        <Image alt={cat.name}
                                                                            src={imgSrc} width={240} height={240} />
                                                                    </a>
                                                                    <a className="btn btn-hover-animation text-uppercase-inherit fw-600 ls-0px justify-content-center"
                                                                        href={`/shop?category=${cat.slug}`}>
                                                                        <span>
                                                                            <span className="btn-text text-dark-gray fs-16">{cat.name}</span>
                                                                            <span className="btn-icon"><i className="fa-solid fa-arrow-right m-0"></i></span>
                                                                        </span>
                                                                    </a>
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                    <div className="row row-cols-1 row-cols-md-2">
                                                        <div className="col">
                                                            <a href="/collections"><Image alt=""
                                                                src="/images/demo-decor-store-menu-banner-03.jpg" width={640} height={420} /></a>
                                                        </div>
                                                        <div className="col">
                                                            <a href="/collections"><Image alt=""
                                                                src="/images/demo-decor-store-menu-banner-04.jpg" width={640} height={420} /></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li className="nav-item"><a className="nav-link text-white-hover"
                                            href="/contact">Contact</a>
                                        </li>
                                    </ul>
                                </div>

                                {/* ── Mobile Nav Footer (visible only on mobile) ── */}
                                <div className="mobile-nav-footer d-lg-none">
                                    {/* Quick shop categories */}
                                    <div className="mobile-nav-section">
                                        <span className="mobile-nav-section-label">Shop by Category</span>
                                        <div className="mobile-nav-quick-links">
                                            {categories.slice(0, 4).map((cat: any) => (
                                                <a key={cat.id} href={`/shop?category=${cat.slug}`} className="mobile-nav-quick-chip">{cat.name}</a>
                                            ))}
                                            <a href="/shop" className="mobile-nav-quick-chip mobile-nav-quick-chip--gold">View All →</a>
                                        </div>
                                    </div>

                                    {/* Contact + social row */}
                                    <div className="mobile-nav-contact-row">
                                        <a href={`mailto:${settings?.company_email || 'hello@behome.co.uk'}`} className="mobile-nav-contact-item">
                                            <i className="feather icon-feather-mail"></i>
                                            {settings?.company_email || 'hello@behome.co.uk'}
                                        </a>
                                        <a href={`tel:${settings?.company_phone || '+442071234567'}`} className="mobile-nav-contact-item">
                                            <i className="feather icon-feather-phone"></i>
                                            {settings?.company_calling_code} {settings?.company_phone}
                                        </a>
                                    </div>

                                    {/* Social icons */}
                                    <div className="mobile-nav-social">
                                        {settings?.social_media_instagram && (
                                            <a href={settings.social_media_instagram} target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i className="fa-brands fa-instagram"></i></a>
                                        )}
                                        {settings?.social_media_facebook && (
                                            <a href={settings.social_media_facebook} target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i className="fa-brands fa-facebook-f"></i></a>
                                        )}
                                        {settings?.social_media_twitter && (
                                            <a href={settings.social_media_twitter} target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i className="fa-brands fa-x-twitter"></i></a>
                                        )}
                                        {settings?.social_media_youtube && (
                                            <a href={settings.social_media_youtube} target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i className="fa-brands fa-youtube"></i></a>
                                        )}
                                        {settings?.social_media_linkedin && (
                                            <a href={settings.social_media_linkedin} target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i className="fa-brands fa-linkedin-in"></i></a>
                                        )}
                                    </div>

                                    {/* Free delivery banner */}
                                    <div className="mobile-nav-promo">
                                        <i className="feather icon-feather-truck"></i>
                                        Free delivery on orders over {(() => {
                                            const symbol = settings?.site_default_currency_symbol || '£';
                                            const position = settings?.site_currency_position == 10 ? 'right' : 'left';
                                            const decimals = Number(settings?.site_digit_after_decimal_point) || 2;
                                            const threshold = Number(settings?.site_free_delivery_threshold) || 120;
                                            const formatted = threshold.toFixed(decimals);
                                            return position === 'left' ? `${symbol}${formatted}` : `${formatted}${symbol}`;
                                        })()}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/*  Right Side Icons  */}
                        <div className="col-6 col-xl-4 col-lg-4 order-2 order-lg-3 ms-auto d-flex justify-content-end align-items-center">
                            <div className="d-flex align-items-center header-icons-row" style={{ gap: '13px' }}>
                                {/*  Search — desktop xl only  */}
                                <div className="d-none d-xl-flex">
                                    <HeaderSearch />
                                </div>
                                {/*  Cart — always visible  */}
                                <div className="position-relative">
                                    <a className="glass-icon-box" href="/cart" aria-label="Cart">
                                        <i className="feather icon-feather-shopping-bag text-white fs-15"></i>
                                    </a>
                                    <CartIcon />
                                </div>
                                {/*  Notification Bell — desktop only  */}
                                <div className="d-none d-lg-flex">
                                    <NotificationBell />
                                </div>
                                {/*  User Profile — hidden on mobile (account is inside drawer)  */}
                                <div className="d-none d-lg-flex">
                                    <UserAccount />
                                </div>
                                {/*  Language Switcher — desktop only (already has d-none d-lg-flex inside)  */}
                                <LanguageSwitcher />
                                {/*  Mobile Hamburger — mobile only  */}
                                <button
                                    aria-controls="navbarNav"
                                    aria-label="Open menu"
                                    className="mobile-hamburger d-lg-none"
                                    data-bs-target="#navbarNav"
                                    data-bs-toggle="collapse"
                                    type="button"
                                >
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
    );
}
