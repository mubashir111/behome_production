'use client';

import { useEffect, useState } from 'react';
import { useSettings } from './SettingsProvider';

export default function TopBar() {
    const { settings, formatAmount } = useSettings();
    const [visible, setVisible] = useState(false);
    const [closing, setClosing] = useState(false);

    useEffect(() => {
        if (sessionStorage.getItem('topbar-closed') === '1') return;
        setVisible(true);
    }, []);

    const handleClose = () => {
        sessionStorage.setItem('topbar-closed', '1');
        setClosing(true);
        // Move navbar up in sync — the template CSS keeps it at top:40px
        // while the top-bar element exists in the DOM, so we override it directly.
        const nav = document.querySelector('header .navbar') as HTMLElement | null;
        if (nav) {
            nav.style.transition = 'top 0.32s ease';
            nav.style.top = '0px';
        }
        setTimeout(() => setVisible(false), 320);
    };

    if (!visible) return null;

    const phone = settings?.company_calling_code && settings?.company_phone
        ? `${settings.company_calling_code} ${settings.company_phone}`
        : null;
    const email = settings?.company_email || null;

    return (
        <div
            className="header-top-bar glass-effect top-bar-dark border-bottom border-color-transparent-white-light px-lg-0 d-none d-md-block"
            style={{
                overflow: 'hidden',
                maxHeight: closing ? '0px' : '45px',
                opacity: closing ? 0 : 1,
                transition: 'max-height 0.32s ease, opacity 0.28s ease',
            }}
        >
            <div className="container-fluid">
                <div className="row h-45px align-items-center m-0">
                    <div className="col-lg-7 col-md-8 text-center text-md-start">
                        <div className="fs-13 text-white fw-600">
                            Free Delivery on orders over {formatAmount(Number(settings?.site_free_delivery_threshold) || 120)}. Don&apos;t miss discount.{' '}
                            <a className="text-white fw-700 text-decoration-line-bottom" href="/shop">Shop now</a>
                        </div>
                    </div>
                    <div className="col-lg-5 col-md-4 text-end d-flex justify-content-end align-items-center">
                        <div className="d-none d-md-flex align-items-center">
                            {phone && (
                                <a className="widget fs-13 text-white fw-600 me-25px" href="/contact">
                                    <i className="feather icon-feather-phone-call me-5px"></i>Customer service
                                </a>
                            )}
                            {!phone && (
                                <a className="widget fs-13 text-white fw-600 me-25px" href="/contact">
                                    <i className="feather icon-feather-phone-call me-5px"></i>Customer service
                                </a>
                            )}
                            <a className="widget fs-13 text-white fw-600 me-25px d-none d-lg-inline-block" href="/contact">
                                <i className="feather icon-feather-map-pin me-5px"></i>Find our store
                            </a>
                        </div>
                        <button
                            type="button"
                            onClick={handleClose}
                            className="top-bar-close-btn"
                            aria-label="Close announcement bar"
                        >
                            <i className="feather icon-feather-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
