
import Image from 'next/image';


export default function Footer() {
    return (
        <footer className="footer-dark bg-dark-gray pb-0 pt-0 cover-background"
            style={{ backgroundImage: 'linear-gradient(rgba(10, 10, 10, 0.85), rgba(10, 10, 10, 0.85)), url(\'/images/new/bg/bg1.png\')' }}>
            <div className="container pt-4 pb-4 md-pt-45px md-pb-45px">
                <div className="row justify-content-center">
                    {/*  start footer column  */}
                    <div className="col-6 col-lg-3 last-paragraph-no-margin order-sm-1 md-mb-50px xs-mb-30px">
                        <a className="footer-logo mb-30px d-inline-block" href="/">
                            <Image alt="Behome Logo" src="/images/new/logo/Behome%20Final%20.png" width={200} height={200} priority style={{ maxHeight: '200px', width: 'auto', height: 'auto' }} />
                        </a>
                        <p className="w-80 sm-w-100">Exquisite architectural decor and premium furniture for the modern luxury
                            interior.</p>
                        <div className="elements-social social-icon-style-02 mt-15px">
                            <ul className="small-icon light">
                                <li><a className="facebook" href="https://www.facebook.com/" target="_blank"><i
                                            className="fa-brands fa-facebook-f"></i></a></li>
                                <li><a className="dribbble" href="http://www.dribbble.com" target="_blank"><i
                                            className="fa-brands fa-dribbble"></i></a></li>
                                <li><a className="twitter" href="https://www.twitter.com" target="_blank"><i
                                            className="fa-brands fa-twitter"></i></a></li>
                                <li><a className="instagram" href="https://www.instagram.com" target="_blank"><i
                                            className="fa-brands fa-instagram"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    {/*  end footer column  */}
                    {/*  start footer column  */}
                    <div className="col-6 col-lg-2 col-sm-4 xs-mb-30px order-sm-3 order-lg-2">
                        <span className="fs-16 fw-500 d-block text-white mb-5px">Categories</span>
                        <ul>
                            <li><a href="/shop">Bed room</a></li>
                            <li><a href="/shop">Living room</a></li>
                            <li><a href="/shop">Lightning</a></li>
                            <li><a href="/shop">Fabrics sofa</a></li>
                        </ul>
                    </div>
                    {/*  end footer column  */}
                    {/*  start footer column  */}
                    <div className="col-6 col-lg-2 col-sm-4 xs-mb-30px order-sm-3 order-lg-2">
                        <span className="fs-16 fw-500 d-block text-white mb-5px">Information</span>
                        <ul>
                            <li><a href="/about">About us</a></li>
                            <li><a href="/blog">Blog</a></li>
                            <li><a href="/contact">Contact us</a></li>
                            <li><a href="/faq">FAQs</a></li>
                            <li><a href="/privacy-policy">Privacy Policy</a></li>
                            <li><a href="/terms-conditions">Terms &amp; Conditions</a></li>
                        </ul>
                    </div>
                    {/*  end footer column  */}
                    {/*  start footer column  */}
                    <div className="col-6 col-lg-2 col-sm-4 xs-mb-30px order-sm-3 order-lg-2">
                        <span className="fs-16 fw-500 d-block text-white mb-5px">Account</span>
                        <ul>
                            <li><a href="/account">My account</a></li>
                            <li><a href="/cart">Orders</a></li>
                            <li><a href="/checkout">Checkout</a></li>
                            <li><a href="/wishlist">My wishlists</a></li>
                        </ul>
                    </div>
                    {/*  end footer column  */}

                </div>
            </div>
            <div className="border-top border-color-transparent-white-light pt-30px pb-30px">
                <div className="container">
                    <div className="row align-items-center justify-content-center">
                        <div
                            className="col-xl-8 last-paragraph-no-margin text-center text-xl-start lg-mt-20px order-3 order-xl-1">
                            <p className="fs-14 w-90 xl-w-100 mb-0">© 2026 Behome. All rights reserved.</p>
                            <p className="fs-12 w-90 xl-w-100 mt-5px mb-0" style={{ color: 'rgba(255,255,255,0.35)' }}>
                                Designed &amp; Developed by{' '}
                                <a href="https://spider-web.in/" target="_blank" rel="noopener noreferrer" style={{ color: 'rgba(255,255,255,0.5)', textDecoration: 'none', transition: 'color 0.2s' }}
                                    onMouseEnter={undefined}
                                    className="footer-dev-credit">
                                    Spider Web Studio
                                </a>
                            </p>
                        </div>
                        <div className="col-6 col-xl-2 col-md-3 col-sm-5 text-center text-xl-start order-1 order-xl-2">
                            <span className="lh-26 alt-font d-block">Need support?</span>
                            <a className="fs-16 text-white fw-500" href="tel:+442071234567">+44 207 123 4567</a>
                        </div>
                        <div className="col-6 col-xl-2 col-md-3 col-sm-5 text-center text-xl-start order-2 order-xl-3">
                            <span className="lh-26 alt-font d-block">Customer care</span>
                            <a className="fs-16 text-white fw-500" href="mailto:hello@behome.co.uk">hello@behome.co.uk</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
