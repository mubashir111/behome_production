
import Image from 'next/image';


export default function Footer() {
    return (
        <footer className="footer-dark bg-dark-gray pb-0 pt-0 cover-background"
            style={{ backgroundImage: 'linear-gradient(rgba(10, 10, 10, 0.85), rgba(10, 10, 10, 0.85)), url(\'/images/new/bg/bg1.png\')' }}>
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
                                <li><a className="facebook" href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-facebook-f"></i></a></li>
                                <li><a className="dribbble" href="http://www.dribbble.com" target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-dribbble"></i></a></li>
                                <li><a className="twitter" href="https://www.twitter.com" target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-twitter"></i></a></li>
                                <li><a className="instagram" href="https://www.instagram.com" target="_blank" rel="noopener noreferrer"><i className="fa-brands fa-instagram"></i></a></li>
                            </ul>
                        </div>
                    </div>

                    {/* Links columns — 3 equal cols on mobile, side by side */}
                    <div className="col-4 col-lg-2 offset-lg-1">
                        <span className="fs-13 fw-600 text-white d-block mb-15px text-uppercase ls-1px opacity-9">Categories</span>
                        <ul className="footer-link-list">
                            <li><a href="/shop">Bed room</a></li>
                            <li><a href="/shop">Living room</a></li>
                            <li><a href="/shop">Lighting</a></li>
                            <li><a href="/shop">Fabric sofa</a></li>
                        </ul>
                    </div>

                    <div className="col-4 col-lg-2">
                        <span className="fs-13 fw-600 text-white d-block mb-15px text-uppercase ls-1px opacity-9">Information</span>
                        <ul className="footer-link-list">
                            <li><a href="/about">About us</a></li>
                            <li><a href="/blog">Blog</a></li>
                            <li><a href="/contact">Contact</a></li>
                            <li><a href="/faq">FAQs</a></li>
                            <li><a href="/privacy-policy">Privacy Policy</a></li>
                            <li><a href="/terms-conditions">Terms</a></li>
                        </ul>
                    </div>

                    <div className="col-4 col-lg-2">
                        <span className="fs-13 fw-600 text-white d-block mb-15px text-uppercase ls-1px opacity-9">Account</span>
                        <ul className="footer-link-list">
                            <li><a href="/account">My account</a></li>
                            <li><a href="/cart">Orders</a></li>
                            <li><a href="/checkout">Checkout</a></li>
                            <li><a href="/wishlist">Wishlist</a></li>
                        </ul>
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
                            <a className="fs-14 text-white fw-500" href="tel:+442071234567">+44 207 123 4567</a>
                        </div>
                        <div className="col-6 col-md-3 text-center text-md-end">
                            <span className="d-block fs-12 opacity-5 mb-3px">Customer care</span>
                            <a className="fs-14 text-white fw-500" href="mailto:hello@behome.co.uk">hello@behome.co.uk</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
