import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import UserAccount from './UserAccount';
import CartIcon from './CartIcon';
import HeaderSearch from './HeaderSearch';
import LanguageSwitcher from './LanguageSwitcher';
import TopBar from './TopBar';

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
        <header className="header-with-topbar">
            {/*  start header top bar  */}
            <TopBar />
            {/*  end header top bar  */}
            {/*  start navigation  */}
            <nav className="navbar navbar-expand-lg header-light bg-transparent disable-fixed px-lg-0 py-lg-0">
                <div className="container-fluid px-4 d-flex align-items-center" style={{ height: '80px' }}>
                    <div className="row w-100 align-items-center m-0">
                        <div className="col-6 col-xl-3 col-lg-2 order-1 d-flex align-items-center gap-3">
                            <a className="glass-logo-wrapper" href="http://blackrockarchitct.ae/" target="" rel="noopener noreferrer">
                                <div className="glass-logo-box2">
                                    <Image alt="Blackrock Logo" src="/images/new/logo/blackrock.PNG" width={72} height={72} priority style={{ objectFit: 'contain' }} />
                                </div>
                            </a>
                            <a className="glass-logo-wrapper" href="/">
                                <div className="glass-logo-box">
                                    <Image alt="Behome Logo" src="/images/new/logo/Behome%20Final%20.png" width={72} height={72} priority style={{ objectFit: 'contain' }} />
                                </div>
                                <span className="logo-text">BEHOME</span>
                            </a>

                        </div>
                        <div className="col-12 col-xl-6 col-lg-8 order-3 order-lg-2 menu-order position-static">
                            <div className="collapse navbar-collapse justify-content-end" id="navbarNav">
                                {/*  Mobile Navigation Header  */}
                                <div className="mobile-nav-header d-lg-none">
                                    <div className="mobile-logo">
                                        <div className="mobile-logo d-flex align-items-center gap-3">

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
                                </div>
                                {/* ── Mobile search bar ── */}
                                <div className="mobile-drawer-search d-lg-none">
                                    <a href="/shop" className="mobile-drawer-search-bar">
                                        <i className="feather icon-feather-search"></i>
                                        <span>Search products…</span>
                                        <i className="feather icon-feather-arrow-right mobile-drawer-search-arrow"></i>
                                    </a>
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
                                                        <div className="col md-mb-30px">
                                                            <a className="text-center" href="/collections">
                                                                <Image alt=""
                                                                    src="/images/demo-decor-store-menu-category-01.jpg" width={240} height={240} />
                                                            </a>
                                                            <a className="btn btn-hover-animation text-uppercase-inherit fw-600 ls-0px justify-content-center"
                                                                href="/collections">
                                                                <span>
                                                                    <span className="btn-text text-dark-gray fs-16">Designer
                                                                        stool</span>
                                                                    <span className="btn-icon"><i
                                                                        className="fa-solid fa-arrow-right m-0"></i></span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <div className="col md-mb-30px">
                                                            <a className="text-center" href="/collections">
                                                                <Image alt=""
                                                                    src="/images/demo-decor-store-menu-category-02.jpg" width={240} height={240} />
                                                            </a>
                                                            <a className="btn btn-hover-animation text-uppercase-inherit fw-600 ls-0px justify-content-center"
                                                                href="/collections">
                                                                <span>
                                                                    <span className="btn-text text-dark-gray fs-16">Modern
                                                                        chair</span>
                                                                    <span className="btn-icon"><i
                                                                        className="fa-solid fa-arrow-right m-0"></i></span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <div className="col md-mb-30px">
                                                            <a className="text-center" href="/collections">
                                                                <Image alt=""
                                                                    src="/images/demo-decor-store-menu-category-03.jpg" width={240} height={240} />
                                                            </a>
                                                            <a className="btn btn-hover-animation text-uppercase-inherit fw-600 ls-0px justify-content-center"
                                                                href="/collections">
                                                                <span>
                                                                    <span className="btn-text text-dark-gray fs-16">Table
                                                                        lamp</span>
                                                                    <span className="btn-icon"><i
                                                                        className="fa-solid fa-arrow-right m-0"></i></span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <div className="col md-mb-30px">
                                                            <a className="text-center" href="/collections">
                                                                <Image alt=""
                                                                    src="/images/demo-decor-store-menu-category-04.jpg" width={240} height={240} />
                                                            </a>
                                                            <a className="btn btn-hover-animation text-uppercase-inherit fw-600 ls-0px justify-content-center"
                                                                href="/collections">
                                                                <span>
                                                                    <span className="btn-text text-dark-gray fs-16">Home
                                                                        decor</span>
                                                                    <span className="btn-icon"><i
                                                                        className="fa-solid fa-arrow-right m-0"></i></span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <div className="col md-mb-30px">
                                                            <a className="text-center" href="/collections">
                                                                <Image alt=""
                                                                    src="/images/demo-decor-store-menu-category-05.jpg" width={240} height={240} />
                                                            </a>
                                                            <a className="btn btn-hover-animation text-uppercase-inherit fw-600 ls-0px justify-content-center"
                                                                href="/collections">
                                                                <span>
                                                                    <span className="btn-text text-dark-gray fs-16">Ceramic
                                                                        pots</span>
                                                                    <span className="btn-icon"><i
                                                                        className="fa-solid fa-arrow-right m-0"></i></span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <div className="col md-mb-30px">
                                                            <a className="text-center" href="/collections">
                                                                <Image alt=""
                                                                    src="/images/demo-decor-store-menu-category-06.jpg" width={240} height={240} />
                                                            </a>
                                                            <a className="btn btn-hover-animation text-uppercase-inherit fw-600 ls-0px justify-content-center"
                                                                href="/collections">
                                                                <span>
                                                                    <span className="btn-text text-dark-gray fs-16">Wooden
                                                                        table</span>
                                                                    <span className="btn-icon"><i
                                                                        className="fa-solid fa-arrow-right m-0"></i></span>
                                                                </span>
                                                            </a>
                                                        </div>
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
                                        <a href="mailto:hello@behome.co.uk" className="mobile-nav-contact-item">
                                            <i className="feather icon-feather-mail"></i>
                                            hello@behome.co.uk
                                        </a>
                                        <a href="tel:+442071234567" className="mobile-nav-contact-item">
                                            <i className="feather icon-feather-phone"></i>
                                            +44 207 123 4567
                                        </a>
                                    </div>

                                    {/* Social icons */}
                                    <div className="mobile-nav-social">
                                        <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i className="fa-brands fa-instagram"></i></a>
                                        <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i className="fa-brands fa-facebook-f"></i></a>
                                        <a href="https://www.twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i className="fa-brands fa-twitter"></i></a>
                                        <a href="https://www.pinterest.com" target="_blank" rel="noopener noreferrer" aria-label="Pinterest"><i className="fa-brands fa-pinterest-p"></i></a>
                                    </div>

                                    {/* Free delivery banner */}
                                    <div className="mobile-nav-promo">
                                        <i className="feather icon-feather-truck"></i>
                                        Free delivery on orders over {settings?.site_default_currency_symbol || '£'}{settings?.site_free_delivery_threshold || '120'}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/*  Right Side Icons  */}
                        <div className="col-6 col-xl-3 col-lg-2 order-2 order-lg-3 ms-auto d-flex justify-content-end align-items-center">
                            <div className="d-flex align-items-center header-icons-row" style={{ gap: '13px' }}>
                                {/*  Search — hidden on mobile (search is inside drawer)  */}
                                <div className="d-none d-lg-flex">
                                    <HeaderSearch />
                                </div>
                                {/*  Cart — always visible  */}
                                <div className="position-relative">
                                    <a className="glass-icon-box" href="/cart" aria-label="Cart">
                                        <i className="feather icon-feather-shopping-bag text-white fs-15"></i>
                                    </a>
                                    <CartIcon />
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
