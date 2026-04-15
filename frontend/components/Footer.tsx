'use client';
import { useState, useEffect } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import { useSettings } from '@/components/SettingsProvider';

export default function Footer() {
    const { settings } = useSettings();
    const [pages, setPages] = useState<any[]>([]);
    const [categories, setCategories] = useState<any[]>([]);
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
        const fetchPages = async () => {
            try {
                const response = await apiFetch('/frontend/static-pages');
                setPages(response?.data || []);
            } catch (error) {
                console.error('Error fetching footer pages:', error);
            }
        };
        const fetchCategories = async () => {
            try {
                const response = await apiFetch('/categories');
                const cats = Array.isArray(response)
                    ? response
                    : Array.isArray(response?.data)
                        ? response.data
                        : Array.isArray(response?.data?.data)
                            ? response.data.data
                            : [];
                setCategories(cats.slice(0, 5));
            } catch (error) {
                console.error('Error fetching footer categories:', error);
            }
        };
        fetchPages();
        fetchCategories();
    }, []);


    return (
        <footer className="footer-dark bg-dark-gray pb-0 pt-0 cover-background"
            style={{ backgroundImage: 'linear-gradient(rgba(10, 10, 10, 0.85), rgba(10, 10, 10, 0.85)), url(\'/images/new/bg/bg1.webp\')' }}>
            <div className="container pt-60px pb-60px md-pt-45px md-pb-45px sm-pt-40px sm-pb-40px">
                <div className="row g-4 g-lg-0">

                    {/* Brand column */}
                    <div className="col-12 col-lg-4 last-paragraph-no-margin pe-lg-5">
                        <a className="footer-logo mb-20px d-inline-block" href="/">
                            <Image alt="Behome Logo" src="/images/new/logo/Behome%20Final%20.png" width={100} height={100} priority style={{ maxHeight: '70px', width: 'auto', height: 'auto' }} />
                        </a>
                        <p className="opacity-7 fs-14 lh-26 mb-20px" style={{ maxWidth: 280 }}>
                            Exquisite architectural decor and premium furniture for the modern luxury interior.
                        </p>
                        <div className="elements-social social-icon-style-02">
                            <ul className="small-icon light">
                                {mounted && settings?.social_media_facebook && (
                                    <li><a className="facebook" href={settings.social_media_facebook} target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-facebook-f"></i></a></li>
                                )}
                                {mounted && settings?.social_media_instagram && (
                                    <li><a className="instagram" href={settings.social_media_instagram} target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-instagram"></i></a></li>
                                )}
                                {mounted && settings?.social_media_twitter && (
                                    <li><a className="twitter" href={settings.social_media_twitter} target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-x-twitter"></i></a></li>
                                )}
                                {mounted && settings?.social_media_youtube && (
                                    <li><a className="youtube" href={settings.social_media_youtube} target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-youtube"></i></a></li>
                                )}
                                {mounted && settings?.social_media_linkedin && (
                                    <li><a className="linkedin" href={settings.social_media_linkedin} target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-linkedin-in"></i></a></li>
                                )}
                            </ul>
                        </div>
                    </div>

                    {/* Links columns — 3 equal cols on mobile, side by side */}
                    <div className="col-4 col-lg-2 offset-lg-1">
                        <span className="fs-13 fw-600 text-white d-block mb-15px text-uppercase ls-1px opacity-9">Categories</span>
                        <ul className="footer-link-list">
                            {categories.length > 0 ? (
                                categories.map((cat) => (
                                    <li key={cat.id}><a href={`/shop?category=${cat.slug}`}>{cat.name}</a></li>
                                ))
                            ) : (
                                <>
                                    <li><a href="/shop?category=bed-room">Bed room</a></li>
                                    <li><a href="/shop?category=living-room">Living room</a></li>
                                    <li><a href="/shop?category=lighting">Lighting</a></li>
                                    <li><a href="/shop?category=fabric-sofa">Fabric sofa</a></li>
                                </>
                            )}
                        </ul>
                    </div>

                    <div className="col-4 col-lg-2">
                        <span className="fs-13 fw-600 text-white d-block mb-15px text-uppercase ls-1px opacity-9">Information</span>
                        <ul className="footer-link-list">
                            {pages.length > 0 ? (
                                pages.map((page) => (
                                    <li key={page.id}><a href={`/${page.slug}`}>{page.title}</a></li>
                                ))
                            ) : (
                                <>
                                    <li><a href="/blog">Blog</a></li>
                                    <li><a href="/contact">Contact</a></li>
                                    <li><a href="/faq">FAQs</a></li>
                                </>
                            )}
                        </ul>
                    </div>

                    <div className="col-4 col-lg-2">
                        <span className="fs-13 fw-600 text-white d-block mb-15px text-uppercase ls-1px opacity-9">Policies</span>
                        <ul className="footer-link-list">
                            <li><a href="/shipping-policy">Shipping Policy</a></li>
                            <li><a href="/returns-policy">Returns &amp; Exchanges</a></li>
                            <li><a href="/privacy-policy">Privacy Policy</a></li>
                            <li><a href="/faq">FAQs</a></li>
                        </ul>
                    </div>

                </div>
            </div>

            {/* Policy strip */}
            <div style={{ borderTop: '1px solid rgba(255,255,255,0.06)', padding: '14px 0' }}>
                <div className="container">
                    <div className="d-flex flex-wrap justify-content-center gap-3" style={{ fontSize: 12, color: 'rgba(255,255,255,0.3)' }}>
                        <a href="/shipping-policy" style={{ color: 'rgba(255,255,255,0.35)', textDecoration: 'none' }} className="footer-policy-link">Shipping Policy</a>
                        <span style={{ opacity: 0.3 }}>·</span>
                        <a href="/returns-policy" style={{ color: 'rgba(255,255,255,0.35)', textDecoration: 'none' }} className="footer-policy-link">Returns &amp; Exchanges</a>
                        <span style={{ opacity: 0.3 }}>·</span>
                        <a href="/privacy-policy" style={{ color: 'rgba(255,255,255,0.35)', textDecoration: 'none' }} className="footer-policy-link">Privacy Policy</a>
                        <span style={{ opacity: 0.3 }}>·</span>
                        <a href="/faq" style={{ color: 'rgba(255,255,255,0.35)', textDecoration: 'none' }} className="footer-policy-link">FAQs</a>
                    </div>
                </div>
            </div>

            {/* Bottom bar */}
            <div className="border-top border-color-transparent-white-light pt-25px pb-25px">
                <div className="container">
                    <div className="row align-items-center g-3">
                        <div className="col-12 col-md-5 text-center text-md-start">
                            <p className="fs-13 mb-0 opacity-6">© 2026 Behome. All rights reserved.</p>
                            <p className="fs-12 mb-0 mt-3px" style={{ color: 'rgba(255,255,255,0.3)' }}>
                                Designed &amp; developed by{' '}
                                <a href="https://spider-web.in/" target="_blank" rel="noopener noreferrer" className="footer-dev-credit" style={{ color: 'rgba(255,255,255,0.45)', textDecoration: 'none' }}>
                                    Spider Web Studio
                                </a>
                            </p>
                        </div>
                        <div className="col-6 col-md-4 text-center">
                            <span className="d-block fs-12 opacity-5 mb-3px">Need support?</span>
                            <a className="fs-14 text-white fw-500" href={`tel:${(mounted && settings?.company_phone) ? settings.company_phone.replace(/\s+/g, '') : '+442071234567'}`}>
                                {(mounted && settings?.company_phone) ? settings.company_phone : '+44 207 123 4567'}
                            </a>
                        </div>
                        <div className="col-6 col-md-3 text-center text-md-end">
                            <span className="d-block fs-12 opacity-5 mb-3px">Customer care</span>
                            <a className="fs-14 text-white fw-500" href={`mailto:${(mounted && settings?.company_email) ? settings.company_email : 'hello@behome.co.uk'}`}>
                                {(mounted && settings?.company_email) ? settings.company_email : 'hello@behome.co.uk'}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
