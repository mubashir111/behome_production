import Image from 'next/image';

export default function Product() {
    return (
        <main>
            
{/*  start breadcrumb  */}
<section className="top-space-margin border-top border-color-extra-medium-gray pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px sm-ps-0 sm-pe-0">
<div className="container-fluid">
<div className="row align-items-center">
<div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
<ul>
<li><a href="/">Home</a></li>
<li><a href="/shop">Shop</a></li>
<li>Minimalist wooden chair</li>
</ul>
</div>
</div>
</div>
</section>
{/*  end breadcrumb  */}
{/*  start section  */}
<section className="pt-40px pb-0">
<div className="container">
<div className="row">
<div className="col-lg-6 md-mb-40px" data-anime='{ "translate": [0, 0], "opacity": [0,1], "duration": 600, "delay": 100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<div className="row overflow-hidden position-relative">
<div className="col-12 position-relative product-image">
<div className="swiper product-image-slider" data-slider-options='{ "spaceBetween": 0, "watchOverflow": true, "navigation": { "nextEl": ".slider-product-next", "prevEl": ".slider-product-prev" }, "thumbs": { "swiper": { "el": ".product-image-thumb", "slidesPerView": "5", "spaceBetween": 15 } } }' data-swiper-thumb-click="1">
<div className="swiper-wrapper">
{/*  start slider item  */}
<div className="swiper-slide gallery-box">
<a data-group="lightbox-gallery" href="images/demo-decor-store-product-detail-01.jpg" title="Minimalist wooden chair">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01.jpg" width={900} height={900}/>
</a>
</div>
{/*  end slider item  */}
<div className="swiper-slide gallery-box">
<a data-group="lightbox-gallery" href="images/demo-decor-store-product-detail-01-a.jpg" title="Minimalist wooden chair">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-a.jpg" width={900} height={900}/>
</a>
</div>
{/*  end slider item  */}
<div className="swiper-slide gallery-box">
<a data-group="lightbox-gallery" href="images/demo-decor-store-product-detail-01-b.jpg" title="Minimalist wooden chair">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-b.jpg" width={900} height={900}/>
</a>
</div>
{/*  end slider item  */}
<div className="swiper-slide gallery-box">
<a data-group="lightbox-gallery" href="images/demo-decor-store-product-detail-01-c.jpg" title="Minimalist wooden chair">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-c.jpg" width={900} height={900}/>
</a>
</div>
{/*  end slider item  */}
<div className="swiper-slide gallery-box">
<a data-group="lightbox-gallery" href="images/demo-decor-store-product-detail-01-d.jpg" title="Minimalist wooden chair">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-d.jpg" width={900} height={900}/>
</a>
</div>
{/*  end slider item  */}
<div className="swiper-slide gallery-box">
<a data-group="lightbox-gallery" href="images/demo-decor-store-product-detail-01-d.jpg" title="Minimalist wooden chair">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-d.jpg" width={900} height={900}/>
</a>
</div>
{/*  end slider item  */}
<div className="swiper-slide gallery-box">
<a data-group="lightbox-gallery" href="images/demo-decor-store-product-detail-01-d.jpg" title="Minimalist wooden chair">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-d.jpg" width={900} height={900}/>
</a>
</div>
{/*  end slider item  */}
</div>
</div>
<div className="slider-product-next swiper-button-next border-radius-100 border border-1 border-color-extra-medium-gray">
<i className="fa fa-chevron-right text-white icon-very-small"></i>
</div>
<div className="slider-product-prev swiper-button-prev border-radius-100 border border-1 border-color-extra-medium-gray">
<i className="fa fa-chevron-left text-white icon-very-small"></i>
</div>
</div>
<div className="col-12 position-relative">
<div className="swiper-container product-image-thumb">
<div className="swiper-wrapper">
<div className="swiper-slide"><Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01.jpg" width={180} height={180}/></div>
<div className="swiper-slide"><Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-a.jpg" width={180} height={180}/></div>
<div className="swiper-slide"><Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-b.jpg" width={180} height={180}/></div>
<div className="swiper-slide"><Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-c.jpg" width={180} height={180}/></div>
<div className="swiper-slide"><Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-d.jpg" width={180} height={180}/></div>
<div className="swiper-slide"><Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-d.jpg" width={180} height={180}/></div>
<div className="swiper-slide"><Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-01-d.jpg" width={180} height={180}/></div>
</div>
</div>
</div>
</div>
</div>
<div className="col-lg-5 offset-lg-1 product-info" data-anime='{ "translate": [0, 0], "opacity": [0,1], "duration": 600, "delay": 100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<span className="fw-500 text-white d-block">Interio</span>
<h5 className="alt-font text-white fw-700 mb-10px">Minimalist wooden chair</h5>
<div className="d-block d-sm-flex align-items-center mb-20px">
<div className="me-10px xs-me-0">
<a className="section-link ls-minus-1px icon-small" href="/product#tab">
<i className="bi bi-star-fill text-golden-yellow"></i>
<i className="bi bi-star-fill text-golden-yellow"></i>
<i className="bi bi-star-fill text-golden-yellow"></i>
<i className="bi bi-star-fill text-golden-yellow"></i>
<i className="bi bi-star-fill text-golden-yellow"></i>
</a>
</div>
<a className="me-25px text-white fw-500 section-link xs-me-0" href="/product#tab">165 Reviews</a>
<div><span className="text-white fw-500">SKU: </span>M492300</div>
</div>
<div className="product-price mb-10px">
<span className="text-white fs-28 xs-fs-24 fw-700 ls-minus-1px"><del className="text-white opacity-6 me-10px fw-500">$85.00</del>$65.00</span>
</div>
<p>Lorem ipsum is simply dummy text of the printing and typesetting industry lorem ipsum standard.
                    </p>
<div className="d-flex align-items-center mb-35px">
<label className="text-white alt-font me-15px fw-500">Color</label>
<ul className="shop-color mb-0">
<li>
<input defaultChecked className="d-none" id="color-1" name="color" type="radio"/>
<label htmlFor="color-1"><span style={{ backgroundColor: '#232323' }}></span></label>
</li>
<li>
<input defaultChecked className="d-none" id="color-2" name="color" type="radio"/>
<label htmlFor="color-2"><span style={{ backgroundColor: '#8E412E' }}></span></label>
</li>
<li>
<input defaultChecked className="d-none" id="color-3" name="color" type="radio"/>
<label htmlFor="color-3"><span style={{ backgroundColor: '#BAB9B8' }}></span></label>
</li>
<li>
<input defaultChecked className="d-none" id="color-4" name="color" type="radio"/>
<label htmlFor="color-4"><span style={{ backgroundColor: '#9DA693' }}></span></label>
</li>
</ul>
</div>
<div className="d-flex align-items-center flex-column flex-sm-row mb-20px position-relative">
<div className="quantity me-15px xs-mb-15px order-1">
<button className="qty-minus" type="button">-</button>
<input aria-label="qty-text" className="qty-text" id="1" type="text" value="1"/>
<button className="qty-plus" type="button">+</button>
</div>
<a className="btn btn-cart btn-extra-large btn-switch-text btn-box-shadow btn-none-transform btn-dark-gray left-icon border-radius-5px me-15px xs-me-0 order-3 order-sm-2" href="/cart">
<span>
<span><i className="feather icon-feather-shopping-bag"></i></span>
<span className="btn-double-text" data-text="Add to cart">Add to cart</span>
</span>
</a>
<a className="wishlist d-flex align-items-center justify-content-center border border-radius-5px border-color-extra-medium-gray order-2 order-sm-3" href="/product#">
<i className="feather icon-feather-heart icon-small text-base-color"></i>
</a>
</div>
<div className="row mb-20px">
<div className="col-auto icon-with-text-style-08">
<div className="feature-box feature-box-left-icon-middle d-inline-flex align-middle">
<div className="feature-box-icon me-10px">
<i className="feather icon-feather-repeat align-middle text-white"></i>
</div>
<div className="feature-box-content">
<a className="alt-font fw-500 text-white d-block" href="/product#">Compare</a>
</div>
</div>
</div>
<div className="col-auto icon-with-text-style-08">
<div className="feature-box feature-box-left-icon-middle d-inline-flex align-middle">
<div className="feature-box-icon me-10px">
<i className="feather icon-feather-mail align-middle text-white"></i>
</div>
<div className="feature-box-content">
<a className="alt-font fw-500 text-white d-block" href="/product#">Ask a question</a>
</div>
</div>
</div>
<div className="col-auto icon-with-text-style-08">
<div className="feature-box feature-box-left-icon-middle d-inline-flex align-middle">
<div className="feature-box-icon me-10px">
<i className="feather icon-feather-share-2 align-middle text-white"></i>
</div>
<div className="feature-box-content">
<a className="alt-font fw-500 text-white d-block" href="/product#">Share</a>
</div>
</div>
</div>
</div>
<div className="mb-20px h-1px w-100 bg-extra-medium-gray d-block"></div>
<div className="row mb-15px">
<div className="col-12 icon-with-text-style-08">
<div className="feature-box feature-box-left-icon d-inline-flex align-middle">
<div className="feature-box-icon me-10px">
<i className="feather icon-feather-truck top-8px position-relative align-middle text-white"></i>
</div>
<div className="feature-box-content">
<span><span className="alt-font text-white fw-500">Estimated delivery:</span> March 03 -
                                        March 07</span>
</div>
</div>
</div>
<div className="col-12 icon-with-text-style-08 mb-10px">
<div className="feature-box feature-box-left-icon d-inline-flex align-middle">
<div className="feature-box-icon me-10px">
<i className="feather icon-feather-archive top-8px position-relative align-middle text-white"></i>
</div>
<div className="feature-box-content">
<span><span className="alt-font text-white fw-500">Free shipping &amp; returns:</span> On
                                        all orders over $50</span>
</div>
</div>
</div>
</div>
<div className="bg-dark-gray ps-30px pe-30px pt-25px pb-25px mb-20px xs-p-25px border-radius-4px">
<span className="fs-15 fw-500 text-white mb-15px d-block lh-initial">Guarantee safe and
                            secure checkout</span>
<div>
<a href="/product#"><Image alt="" className="h-30px me-5px mb-5px" src="/images/visa.svg" width={48} height={30}/></a>
<a href="/product#"><Image alt="" className="h-30px me-5px mb-5px" src="/images/mastercard.svg" width={48} height={30}/></a>
<a href="/product#"><Image alt="" className="h-30px me-5px mb-5px" src="/images/american-express.svg" width={48} height={30}/></a>
<a href="/product#"><Image alt="" className="h-30px me-5px mb-5px" src="/images/discover.svg" width={48} height={30}/></a>
<a href="/product#"><Image alt="" className="h-30px me-5px mb-5px" src="/images/diners-club.svg" width={48} height={30}/></a>
<a href="/product#"><Image alt="" className="h-30px" src="/images/union-pay.svg" width={48} height={30}/></a>
</div>
</div>
<div>
<div className="w-100 d-block"><span className="text-white alt-font fw-500">Category:</span> <a href="/product#">Decor,</a> <a href="/product#">Minimalist</a></div>
<div><span className="text-white alt-font fw-500">Tags: </span><a href="/product#">Chair,</a> <a href="/product#">Modern,</a> <a href="/product#">Wooden</a></div>
</div>
</div>
</div>
</div>
</section>
{/*  end section  */}
{/*  start section  */}
<section id="tab">
<div className="container">
<div className="row">
<div className="col-12 tab-style-04" data-anime='{ "translate": [0, 0], "opacity": [0,1], "duration": 600, "delay": 100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<ul className="nav nav-tabs border-0 justify-content-center fs-17 fw-500">
<li className="nav-item"><a className="nav-link active" data-bs-toggle="tab" href="/product#tab_five1">Description<span className="tab-border bg-dark-gray"></span></a>
</li>
<li className="nav-item"><a className="nav-link" data-bs-toggle="tab" href="/product#tab_five2">Additional information<span className="tab-border bg-dark-gray"></span></a></li>
<li className="nav-item"><a className="nav-link" data-bs-toggle="tab" href="/product#tab_five3">Shipping and return<span className="tab-border bg-dark-gray"></span></a></li>
<li className="nav-item"><a className="nav-link" data-bs-toggle="tab" data-tab="review-tab" href="/product#tab_five4">Reviews
                                (3)<span className="tab-border bg-dark-gray"></span></a></li>
</ul>
<div className="mb-6 h-1px w-100 bg-extra-medium-gray sm-mt-10px"></div>
<div className="tab-content">
{/*  start tab content  */}
<div className="tab-pane fade in active show" id="tab_five1">
<div className="row align-items-center justify-content-center mb-5 sm-mb-10">
<div className="col-xl-4 col-lg-5 md-mb-40px">
<div className="d-flex align-items-center mb-10px">
<div className="col-auto pe-5px"><i className="bi bi-heart-fill text-red fs-14"></i>
</div>
<div className="col fs-15 fw-500 text-white">Designer thoughts</div>
</div>
<h4 className="alt-font text-white fw-700 mb-20px">Minimalist design and modern chair.
                                    </h4>
<p>Lorem ipsum is simply dummy text of the printing and typesetting industry lorem
                                        ipsum has been the standard dummy text typesetting.</p>
<div>
<div className="feature-box feature-box-left-icon-middle mb-10px">
<div className="feature-box-icon feature-box-icon-rounded w-30px h-30px rounded-circle bg-dark-gray me-10px">
<i className="fa-solid fa-check fs-12 text-white"></i>
</div>
<div className="feature-box-content">
<span className="d-block text-white fw-500">FSC certified natural wood teak
                                                    product.</span>
</div>
</div>
<div className="feature-box feature-box-left-icon-middle mb-10px">
<div className="feature-box-icon feature-box-icon-rounded w-30px h-30px rounded-circle bg-dark-gray me-10px">
<i className="fa-solid fa-check fs-12 text-white"></i>
</div>
<div className="feature-box-content">
<span className="d-block text-white fw-500">Removable cushion with
                                                    polypropylene.</span>
</div>
</div>
<div className="feature-box feature-box-left-icon-middle mb-10px">
<div className="feature-box-icon feature-box-icon-rounded w-30px h-30px rounded-circle bg-dark-gray me-10px">
<i className="fa-solid fa-check fs-12 text-white"></i>
</div>
<div className="feature-box-content">
<span className="d-block text-white fw-500">Durability wood &amp; lightweight
                                                    modern.</span>
</div>
</div>
<div className="feature-box feature-box-left-icon-middle">
<div className="feature-box-icon feature-box-icon-rounded w-30px h-30px rounded-circle bg-dark-gray me-10px">
<i className="fa-solid fa-check fs-12 text-white"></i>
</div>
<div className="feature-box-content">
<span className="d-block text-white fw-500">Topstitch detailing along back
                                                    of seat.</span>
</div>
</div>
</div>
</div>
<div className="col-lg-7 offset-xl-1">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-tab-01.jpg" width={900} height={620}/>
</div>
</div>
<div className="row mb-6 sm-mb-10">
<div className="col-12">
<Image alt="" className="w-100" src="/images/demo-decor-store-product-detail-tab-03.jpg" width={900} height={620}/>
</div>
</div>
<div className="row mb-6 sm-mb-10">
<div className="co-12 col-md-4 sm-mb-35px">
<h5 className="text-white alt-font mb-0 fw-700">The dining chair design for those
                                        looking for a new level of comfort.</h5>
</div>
<div className="co-12 col-md-4 sm-mb-35px outside-box-top-18 sm-outside-box-top-0"><Image alt="" src="/images/demo-decor-store-product-detail-tab-04.png" width={360} height={360}/></div>
<div className="co-12 col-md-4 last-paragraph-no-margin">
<p>Lorem ipsum is simply dummy text printing typesetting industry lorem ipsum has
                                        been standard dummy text lorem ipsum.</p>
</div>
</div>
<div className="row row-cols-1 row-cols-lg-4 row-cols-sm-2 mb-5 sm-mb-10 border border-1 border-color-extra-medium-gray g-0">
<div className="col border-end xs-border-end-0 md-border-bottom border-color-extra-medium-gray last-paragraph-no-margin text-center ps-3 pe-3 pt-4 pb-4 xl-ps-2 xl-pe-2 md-p-30px">
<Image alt="" className="border-radius-100 box-shadow-quadruple-large mb-30px" src="/images/demo-decor-store-product-detail-tab-05.jpg" width={200} height={200}/>
<span className="d-block fs-15 fw-700 text-white text-uppercase ls-05px mb-5px">Wooden</span>
<p>Lorem ipsum simply dummy text printing typesetting.</p>
</div>
<div className="col border-end md-border-end-0 md-border-bottom border-color-extra-medium-gray last-paragraph-no-margin text-center ps-3 pe-3 pt-4 pb-4 xl-ps-2 xl-pe-2 md-p-30px">
<Image alt="" className="border-radius-100 box-shadow-quadruple-large mb-30px" src="/images/demo-decor-store-product-detail-tab-06.jpg" width={200} height={200}/>
<span className="d-block fs-15 fw-700 text-white text-uppercase ls-05px mb-5px">Fabric</span>
<p>Lorem ipsum simply dummy text printing typesetting.</p>
</div>
<div className="col border-end xs-border-end-0 xs-border-bottom border-color-extra-medium-gray last-paragraph-no-margin text-center ps-3 pe-3 pt-4 pb-4 xl-ps-2 xl-pe-2 md-p-30px">
<Image alt="" className="border-radius-100 box-shadow-quadruple-large mb-30px" src="/images/demo-decor-store-product-detail-tab-07.jpg" width={200} height={200}/>
<span className="d-block fs-15 fw-700 text-white text-uppercase ls-05px mb-5px">Strength</span>
<p>Lorem ipsum simply dummy text printing typesetting.</p>
</div>
<div className="col text-center last-paragraph-no-margin ps-3 pe-3 pt-4 pb-4 xl-ps-2 xl-pe-2 md-p-30px">
<Image alt="" className="border-radius-100 box-shadow-quadruple-large mb-30px" src="/images/demo-decor-store-product-detail-tab-08.jpg" width={200} height={200}/>
<span className="d-block fs-15 fw-700 text-white text-uppercase ls-05px mb-5px">Comfort</span>
<p>Lorem ipsum simply dummy text printing typesetting.</p>
</div>
</div>
<div className="row justify-content-center">
<div className="col-auto text-center last-paragraph-no-margin">
<div className="d-inline-block align-middle me-10px"><i className="bi bi-patch-check-fill icon-extra-medium text-white"></i></div>
<div className="d-inline-block text-white text-uppercase fs-15 fw-600 ls-05px align-middle">
                                        Premium quality solid wood finish product materials.</div>
</div>
</div>
</div>
{/*  end tab content  */}
{/*  start tab content  */}
<div className="tab-pane fade in" id="tab_five2">
<div className="row m-0">
<div className="col-12">
<div className="row">
<div className="col-lg-2 col-md-3 col-sm-4 pt-10px pb-10px xs-pb-0 text-white alt-font fw-600">
                                            Overall:</div>
<div className="col-lg-10 col-md-9 col-sm-8 pt-10px pb-10px xs-pt-0">29.10'' H x
                                            19.50'' W x 23.20'' D</div>
</div>
<div className="row bg-dark-gray">
<div className="col-lg-2 col-md-3 col-sm-4 pt-10px pb-10px xs-pb-0 text-white alt-font fw-600">
                                            Product weight:</div>
<div className="col-lg-10 col-md-9 col-sm-8 pt-10px pb-10px xs-pt-0">16.11 lb.</div>
</div>
<div className="row">
<div className="col-lg-2 col-md-3 col-sm-4 pt-10px pb-10px xs-pb-0 text-white alt-font fw-600">
                                            Color:</div>
<div className="col-lg-10 col-md-9 col-sm-8 pt-10px pb-10px xs-pt-0">Golden Oak,
                                            Dark Brown, Light Oak</div>
</div>
<div className="row bg-dark-gray">
<div className="col-lg-2 col-md-3 col-sm-4 pt-10px pb-10px xs-pb-0 text-white alt-font fw-600">
                                            Fabric:</div>
<div className="col-lg-10 col-md-9 col-sm-8 pt-10px pb-10px xs-pt-0">Polyolefin,
                                            Crepe fabric, Wollen fabric, Modacrylics, Polyester</div>
</div>
<div className="row">
<div className="col-lg-2 col-md-3 col-sm-4 pt-10px pb-10px xs-pb-0 text-white alt-font fw-600">
                                            Material:</div>
<div className="col-lg-10 col-md-9 col-sm-8 pt-10px pb-10px xs-pt-0">Solid wood,
                                            Fabric, Soft cotton</div>
</div>
</div>
</div>
<div className="row mt-6">
<div className="col-12 text-center">
<Image alt="" src="/images/demo-decor-store-product-detail-tab-02.jpg" width={1200} height={720}/>
</div>
</div>
</div>
{/*  end tab content  */}
{/*  start tab content  */}
<div className="tab-pane fade in" id="tab_five3">
<div className="row">
<div className="col-md-6 last-paragraph-no-margin sm-mb-30px">
<div className="fs-20 alt-font text-white mb-20px fw-600">Shipping information</div>
<p className="mb-0"><span className="fw-600 text-white">Standard:</span> Arrives in 5-8
                                        business days</p>
<p><span className="fw-600 text-white">Express:</span> Arrives in 2-3 business days</p>
<p className="w-80 md-w-100">These shipping rates are not applicable for orders shipped
                                        outside of the US. Some oversized items may require an additional shipping
                                        charge. Free Shipping applies only to merchandise taxes and gift cards do not
                                        count toward the free shipping total.</p>
</div>
<div className="col-md-6 last-paragraph-no-margin">
<div className="fs-20 alt-font text-white mb-20px fw-600">Return information</div>
<p className="w-80 md-w-100">Orders placed between 10/1/2023 and 12/23/2023 can be
                                        returned by 2/27/2023.</p>
<p className="w-80 md-w-100">Return or exchange any unused or defective merchandise by
                                        mail or at one of our US or Canada store locations. Returns made within 30 days
                                        of the order delivery date will be issued a full refund to the original form of
                                        payment.</p>
</div>
</div>
</div>
{/*  end tab content  */}
{/*  start tab content  */}
<div className="tab-pane fade in" id="tab_five4">
<div className="row align-items-center mb-6 sm-mb-10">
<div className="col-lg-4 col-md-12 col-sm-7 md-mb-30px text-center text-lg-start">
<h5 className="alt-font text-white fw-600 mb-0 w-85 lg-w-100"><span className="fw-800">25,000+</span> people are like our product and say good
                                        story.</h5>
</div>
<div className="col-lg-2 col-md-4 col-sm-5 text-center sm-mb-20px p-0 md-ps-15px md-pe-15px">
<div className="border-radius-4px bg-dark-gray p-30px xl-p-25px">
<h2 className="mb-5px alt-font text-white fw-800">4.9</h2>
<span className="text-golden-yellow icon-small d-block ls-minus-1px mb-5px">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
</span>
<span className="ps-15px pe-15px pt-10px pb-10px lh-normal bg-dark-gray text-white fs-12 fw-600 text-uppercase border-radius-4px d-inline-block text-center">2,488
                                            Reviews</span>
</div>
</div>
<div className="col-9 col-lg-4 col-md-5 col-sm-8 progress-bar-style-02">
<div className="ps-20px md-ps-0">
<div className="text-white mb-15px fw-600">Average customer ratings</div>
{/*  start progress bar item  */}
<div className="progress mb-20px border-radius-6px">
<div aria-label="rating-one" aria-valuemax={100} aria-valuemin={0} aria-valuenow={95} className="progress-bar bg-green m-0" role="progressbar"></div>
</div>
{/*  end progress bar item  */}
{/*  start progress bar item  */}
<div className="progress mb-20px border-radius-6px">
<div aria-label="rating-two" aria-valuemax={100} aria-valuemin={0} aria-valuenow={66} className="progress-bar bg-green m-0" role="progressbar"></div>
</div>
{/*  end progress bar item  */}
{/*  start progress bar item  */}
<div className="progress mb-20px border-radius-6px">
<div aria-label="rating-three" aria-valuemax={100} aria-valuemin={0} aria-valuenow={40} className="progress-bar bg-green m-0" role="progressbar"></div>
</div>
{/*  end progress bar item  */}
{/*  start progress bar item  */}
<div className="progress mb-20px border-radius-6px">
<div aria-label="rating-four" aria-valuemax={100} aria-valuemin={0} aria-valuenow={25} className="progress-bar bg-green m-0" role="progressbar"></div>
</div>
{/*  end progress bar item  */}
{/*  start progress bar item  */}
<div className="progress sm-mb-0 border-radius-6px">
<div aria-label="rating-five" aria-valuemax={100} aria-valuemin={0} aria-valuenow={5} className="progress-bar bg-green m-0" role="progressbar"></div>
</div>
{/*  end progress bar item  */}
</div>
</div>
<div className="col-3 col-lg-2 col-md-3 col-sm-4 mt-45px">
<div className="mb-15px lh-0 xs-lh-normal xs-mb-10px">
<span className="text-golden-yellow fs-15 ls-minus-1px d-none d-sm-inline-block">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
</span>
<span className="fs-13 text-white fw-600 ms-10px xs-ms-0">80%</span>
</div>
<div className="mb-15px lh-0 xs-lh-normal xs-mb-10px">
<span className="text-golden-yellow fs-15 ls-minus-1px d-none d-sm-inline-block">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="feather icon-feather-star"></i>
</span>
<span className="fs-13 text-white fw-600 ms-10px xs-ms-0">10%</span>
</div>
<div className="mb-15px lh-0 xs-lh-normal xs-mb-10px">
<span className="text-golden-yellow fs-15 ls-minus-1px d-none d-sm-inline-block">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="feather icon-feather-star"></i>
<i className="feather icon-feather-star"></i>
</span>
<span className="fs-13 text-white fw-600 ms-10px xs-ms-0">05%</span>
</div>
<div className="mb-15px lh-0 xs-lh-normal xs-mb-10px">
<span className="text-golden-yellow fs-15 ls-minus-1px d-none d-sm-inline-block">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="feather icon-feather-star"></i>
<i className="feather icon-feather-star"></i>
<i className="feather icon-feather-star"></i>
</span>
<span className="fs-13 text-white fw-600 ms-10px xs-ms-0">03%</span>
</div>
<div className="lh-0 xs-lh-normal">
<span className="text-golden-yellow fs-15 ls-minus-1px d-none d-sm-inline-block">
<i className="bi bi-star-fill"></i>
<i className="feather icon-feather-star"></i>
<i className="feather icon-feather-star"></i>
<i className="feather icon-feather-star"></i>
<i className="feather icon-feather-star"></i>
</span>
<span className="fs-13 text-white fw-600 ms-10px xs-ms-0">02%</span>
</div>
</div>
</div>
<div className="row g-0 mb-4 md-mb-35px">
<div className="col-12 border-bottom border-color-extra-medium-gray pb-40px mb-40px xs-pb-30px xs-mb-30px">
<div className="d-block d-md-flex w-100 align-items-center">
<div className="w-300px md-w-250px sm-w-100 sm-mb-10px text-center">
<Image alt="" className="rounded-circle w-90px mb-10px" src="/images/avtar-27.jpg" width={90} height={90}/>
<span className="text-white fw-600 d-block">Herman miller</span>
<div className="fs-14 lh-18">06 April 2023</div>
</div>
<div className="w-100 last-paragraph-no-margin sm-ps-0 position-relative text-center text-md-start">
<span className="text-golden-yellow ls-minus-1px mb-5px sm-me-10px sm-mb-0 d-inline-block d-md-block">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
</span>
<a className="w-65px bg-light-red border-radius-15px fs-13 text-white fw-600 text-center position-absolute sm-position-relative d-inline-block d-md-block right-0px top-0px" href="/product#"><i className="fa-solid fa-heart text-red me-5px"></i><span>08</span></a>
<p className="w-85 sm-w-100 sm-mt-15px">Lorem ipsum dolor sit sed do eiusmod
                                                tempor incididunt labore enim ad minim veniam, quis nostrud exercitation
                                                ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure
                                                dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat
                                                nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>
</div>
</div>
</div>
<div className="col-12 border-bottom border-color-extra-medium-gray pb-40px mb-40px xs-pb-30px xs-mb-30px">
<div className="d-block d-md-flex w-100 align-items-center">
<div className="w-300px md-w-250px sm-w-100 sm-mb-10px text-center">
<Image alt="" className="rounded-circle w-90px mb-10px" src="/images/avtar-28.jpg" width={90} height={90}/>
<span className="text-white fw-600 d-block">Wilbur haddock</span>
<div className="fs-14 lh-18">26 April 2023</div>
</div>
<div className="w-100 last-paragraph-no-margin sm-ps-0 position-relative text-center text-md-start">
<span className="text-golden-yellow ls-minus-1px mb-5px sm-me-10px sm-mb-0 d-inline-block d-md-block">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
</span>
<a className="w-65px bg-light-red border-radius-15px fs-13 text-white fw-600 text-center position-absolute sm-position-relative d-inline-block d-md-block right-0px top-0px" href="/product#"><i className="fa-solid fa-heart text-red me-5px"></i><span>06</span></a>
<p className="w-85 sm-w-100 sm-mt-15px">Lorem ipsum dolor sit sed do eiusmod
                                                tempor incididunt labore enim ad minim veniamnisi ut aliquip ex ea
                                                commodo consequat. Duis aute irure dolor in reprehenderit in voluptate
                                                velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint
                                                occaecat cupidatat non proident.</p>
</div>
</div>
</div>
<div className="col-12 border-bottom border-color-extra-medium-gray pb-40px mb-40px xs-pb-30px md-mb-25px">
<div className="d-block d-md-flex w-100 align-items-center">
<div className="w-300px md-w-250px sm-w-100 sm-mb-10px text-center">
<Image alt="" className="rounded-circle w-90px mb-10px" src="/images/avtar-29.jpg" width={90} height={90}/>
<span className="text-white fw-600 d-block">Colene landin</span>
<div className="fs-14 lh-18">28 April 2023</div>
</div>
<div className="w-100 last-paragraph-no-margin sm-ps-0 position-relative text-center text-md-start">
<span className="text-golden-yellow ls-minus-1px mb-5px sm-me-10px sm-mb-0 d-inline-block d-md-block">
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
<i className="bi bi-star-fill"></i>
</span>
<a className="w-65px bg-light-red border-radius-15px fs-13 text-white fw-600 text-center position-absolute sm-position-relative d-inline-block d-md-block right-0px top-0px" href="/product#"><i className="fa-regular fa-heart text-red me-5px"></i><span>00</span></a>
<p className="w-85 sm-w-100 sm-mt-15px">Lorem ipsum dolor sit sed do eiusmod
                                                tempor incididunt labore enim adquis nostrud exercitation ullamco
                                                laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
                                                in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                                                pariatur. Excepteur sint occaecat cupidatat non proident.</p>
</div>
</div>
</div>
<div className="col-12 last-paragraph-no-margin text-center">
<a className="btn btn-link btn-hover-animation-switch btn-extra-large fw-600 text-white" href="/product#">
<span>
<span className="btn-text">Show more reviews</span>
<span className="btn-icon"><i className="fa-solid fa-chevron-down"></i></span>
<span className="btn-icon"><i className="fa-solid fa-chevron-down"></i></span>
</span>
</a>
</div>
</div>
<div className="row justify-content-center">
<div className="col-12">
<div className="p-7 lg-p-5 sm-p-7 bg-dark-gray">
<div className="row justify-content-center mb-30px sm-mb-10px">
<div className="col-md-9 text-center">
<h4 className="alt-font text-white fw-700 mb-20px">Add a review</h4>
</div>
</div>
<form action="https://craftohtml.themezaa.com/email-templates/contact-form.php" className="row contact-form-style-02" method="post">
<div className="col-lg-5 col-md-6 mb-20px">
<label className="form-label mb-15px">Your name*</label>
<input className="input-name border-radius-4px form-control required" name="name" placeholder="Enter your name" type="text"/>
</div>
<div className="col-lg-5 col-md-6 mb-20px">
<label className="form-label mb-15px">Your email address*</label>
<input className="border-radius-4px form-control required" name="email" placeholder="Enter your email address" type="email"/>
</div>
<div className="col-lg-2 mb-20px">
<label className="form-label">Your rating*</label>
<div>
<span className="ls-minus-1px icon-small d-block mt-20px md-mt-0">
<i className="feather icon-feather-star text-golden-yellow"></i>
<i className="feather icon-feather-star text-golden-yellow"></i>
<i className="feather icon-feather-star text-golden-yellow"></i>
<i className="feather icon-feather-star text-golden-yellow"></i>
<i className="feather icon-feather-star text-golden-yellow"></i>
</span>
</div>
</div>
<div className="col-md-12 mb-20px">
<label className="form-label mb-15px">Your review</label>
<textarea className="border-radius-4px form-control" cols={40} name="comment" placeholder="Your message" rows={4}></textarea>
</div>
<div className="col-lg-9 md-mb-25px">
<div className="position-relative terms-condition-box text-start mt-10px">
<label className="d-inline-block">
<input className="terms-condition check-box align-middle required" id="terms_condition" name="terms_condition" type="checkbox" value="1"/>
<span className="box fs-15">I accept the crafto terms and conditions
                                                            and I have read the privacy policy.</span>
</label>
</div>
</div>
<div className="col-lg-3 text-start text-lg-end">
<input name="redirect" type="hidden" value=""/>
<button className="btn btn-base-color btn-small btn-box-shadow btn-round-edge submit" type="submit">Submit review</button>
</div>
<div className="col-12">
<div className="form-results mt-20px d-none"></div>
</div>
</form>
</div>
</div>
</div>
</div>
{/*  end tab content  */}
</div>
</div>
</div>
</div>
</section>
{/*  end section  */}
{/*  start section  */}
<section className="pt-0">
<div className="container">
<div className="row justify-content-center">
<div className="col-lg-5 text-center mb-25px sm-mb-10px" data-anime='{ "el": "childs", "translateY": [50, 0], "opacity": [0,1], "duration": 600, "delay": 0, "staggervalue": 150, "easing": "easeOutQuad" }'>
<span className="text-uppercase fs-14 ls-2px fw-600">You may also like</span>
<h4 className="alt-font text-white fw-700 mb-20px">Related products</h4>
</div>
</div>
<div className="row">
<div className="col-12 px-0 sm-ps-15px sm-pe-15px">
<ul className="shop-boxed shop-wrapper grid grid-4col xxl-grid-4col xl-grid-4col lg-grid-3col md-grid-2col sm-grid-2col xs-grid-1col gutter-large text-center" data-anime='{ "el": "childs", "translateY": [50, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<li className="grid-sizer"></li>
{/*  start shop item  */}
<li className="grid-item">
<div className="shop-box pb-25px">
<div className="shop-image">
<a href="/product">
<Image alt="" src="/images/demo-decor-store-product-01.jpg" width={640} height={720}/>
<div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
</a>
<div className="shop-hover d-flex justify-content-center">
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to wishlist"><i className="feather icon-feather-heart fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to cart"><i className="feather icon-feather-shopping-bag fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Quick shop"><i className="feather icon-feather-eye fs-15"></i></a>
</div>
</div>
<div className="shop-footer text-center pt-20px">
<a className="text-white fs-17 fw-600" href="/product">Table
                                        clock</a>
<div className="fw-500 fs-15 lh-normal"><del>$30.00</del>$23.00</div>
</div>
</div>
</li>
{/*  end shop item  */}
{/*  start shop item  */}
<li className="grid-item">
<div className="shop-box pb-25px">
<div className="shop-image">
<a href="/product">
<Image alt="" src="/images/demo-decor-store-product-14.jpg" width={640} height={720}/>
<div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
</a>
<div className="shop-hover d-flex justify-content-center">
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to wishlist"><i className="feather icon-feather-heart fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to cart"><i className="feather icon-feather-shopping-bag fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Quick shop"><i className="feather icon-feather-eye fs-15"></i></a>
</div>
</div>
<div className="shop-footer text-center pt-20px">
<a className="text-white fs-17 fw-600" href="/product">Wood
                                        stool</a>
<div className="fw-500 fs-15 lh-normal">$54.00</div>
</div>
</div>
</li>
{/*  end shop item  */}
{/*  start shop item  */}
<li className="grid-item">
<div className="shop-box pb-25px">
<div className="shop-image">
<a href="/product">
<Image alt="" src="/images/demo-decor-store-product-12.jpg" width={640} height={720}/>
<div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
</a>
<div className="shop-hover d-flex justify-content-center">
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to wishlist"><i className="feather icon-feather-heart fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to cart"><i className="feather icon-feather-shopping-bag fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Quick shop"><i className="feather icon-feather-eye fs-15"></i></a>
</div>
</div>
<div className="shop-footer text-center pt-20px">
<a className="text-white fs-17 fw-600" href="/product">Ceramic mug</a>
<div className="fw-500 fs-15 lh-normal"><del>$20.00</del>$15.00</div>
</div>
</div>
</li>
{/*  end shop item  */}
{/*  start shop item  */}
<li className="grid-item">
<div className="shop-box pb-25px">
<div className="shop-image">
<a href="/product">
<Image alt="" src="/images/demo-decor-store-product-05.jpg" width={640} height={720}/>
<div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
</a>
<div className="shop-hover d-flex justify-content-center">
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to wishlist"><i className="feather icon-feather-heart fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Add to cart"><i className="feather icon-feather-shopping-bag fs-15"></i></a>
<a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom" data-bs-placement="top" data-bs-toggle="tooltip" href="/product#" title="Quick shop"><i className="feather icon-feather-eye fs-15"></i></a>
</div>
</div>
<div className="shop-footer text-center pt-20px">
<a className="text-white fs-17 fw-600" href="/product">Decorative plants</a>
<div className="fw-500 fs-15 lh-normal"><del>$30.00</del>$35.00</div>
</div>
</div>
</li>
{/*  end shop item  */}
</ul>
</div>
</div>
</div>
</section>
{/*  end section  */}
{/*  start cookie message  */}
<div className="cookie-message bg-dark-gray border-radius-8px" id="cookies-model">
<div className="cookie-description fs-14 text-white mb-20px lh-22">We use cookies to enhance your browsing
            experience, serve personalized ads or content, and analyze our traffic. By clicking "Allow cookies" you
            consent to our use of cookies. </div>
<div className="cookie-btn">
<a aria-label="btn" className="btn btn-transparent-white border-1 border-color-transparent-white-light btn-very-small btn-switch-text btn-rounded w-100 mb-15px" href="/product#">
<span>
<span className="btn-double-text" data-text="Cookie policy">Cookie policy</span>
</span>
</a>
<a aria-label="text" className="btn btn-white btn-very-small btn-switch-text btn-box-shadow accept_cookies_btn btn-rounded w-100" data-accept-btn="" href="/product#">
<span>
<span className="btn-double-text" data-text="Allow cookies">Allow cookies</span>
</span>
</a>
</div>
</div>
{/*  end cookie message  */}
{/*  start scroll progress  */}
<div className="scroll-progress d-none d-xxl-block">
<a aria-label="scroll" className="scroll-top" href="/product#">
<span className="scroll-text">Scroll</span><span className="scroll-line"><span className="scroll-point"></span></span>
</a>
</div>
{/*  end scroll progress  */}
{/*  javascript libraries  */}
{/*  Mega Menu Alignment Fix  */}
    {/*  Behome Premium Animation System  */}
    
        </main>
    );
}
