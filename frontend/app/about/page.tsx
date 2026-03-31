import type { Metadata } from 'next';
import Image from 'next/image';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
const API_KEY = process.env.NEXT_PUBLIC_API_KEY || '';

async function getAboutData() {
    try {
        const res = await fetch(`${API_URL}/frontend/static-pages/about`, {
            next: { revalidate: 300 },
            headers: { 'x-api-key': API_KEY, 'Content-Type': 'application/json' },
        });
        if (!res.ok) return null;
        const json = await res.json();
        return json.data ?? null;
    } catch {
        return null;
    }
}

export async function generateMetadata(): Promise<Metadata> {
    const data = await getAboutData();
    return {
        title: data?.meta_title || 'About Us | Behome',
        description: data?.meta_description || 'Learn the story behind Behome — a premium architectural decor and luxury furniture brand.',
    };
}

const DEFAULT_FEATURES = [
    { number: '01', year: '2009', title: 'Business founded', description: 'Behome was founded with a vision to bring world-class architectural decor to homes everywhere.' },
    { number: '02', year: '2012', title: 'Build new office', description: 'We expanded our operations and built a new headquarters in London to serve our growing customer base.' },
    { number: '03', year: '2016', title: 'Relocates headquarter', description: 'As demand grew internationally, we moved our headquarters to a larger space.' },
    { number: '04', year: '2020', title: 'Revenues of millions', description: 'Behome crossed the milestone of millions in revenue, cementing our position as a market leader.' },
];
const DEFAULT_STATS = [
    { value: '10000+', label: 'people trusting us' },
    { value: '4.9/5', label: '8549 Total reviews' },
];
const DEFAULT_TEAM = [
    { name: 'Jeremy dupont', role: 'Director', image: '/images/team-08.jpg' },
    { name: 'Jessica dover', role: 'Founder', image: '/images/team-09.jpg' },
    { name: 'Matthew taylor', role: 'Manager', image: '/images/team-10.jpg' },
    { name: 'Johncy parker', role: 'Manager', image: '/images/team-11.jpg' },
];

export default async function About() {
    const data = await getAboutData();
    const sections = data?.sections || {};
    const hero     = sections.hero     || {};
    const features = (sections.features?.length ? sections.features : DEFAULT_FEATURES);
    const stats    = (sections.stats?.length    ? sections.stats    : DEFAULT_STATS);
    const team     = (sections.team?.length     ? sections.team     : DEFAULT_TEAM);

    const heroSubtitle = hero.subtitle    || 'Commitment to quality product.';
    const heroDesc     = hero.description || 'We are a premium architectural decor brand dedicated to bringing refined elegance into every space. Since 2009, we have curated the finest furniture and decor from around the world.';

    return (
        <main>

{/*  start section  */}
<section className="pt-60 md-pt-40 pb-3 position-relative overflow-hidden">
<div className="container">
<div className="row mb-4 align-items-center text-center text-sm-start">
<div className="col-lg-2 col-md-3 col-sm-4 xs-mb-20px" data-anime='{ "translateX": [0, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<div className="position-relative d-md-flex flex-column align-items-center justify-content-center w-165px h-165px border-radius-100">
<Image alt="" className="position-absolute top-50 translate-middle-y" src="/images/demo-decor-store-about-01.png" width={165} height={165}/>
<Image alt="" className="animation-rotation" src="/images/demo-decor-store-about-02.png" width={165} height={165}/>
</div>
</div>
<div className="col-lg-5 col-md-7 col-sm-8" data-anime='{ "translateX": [0, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<span className="text-uppercase fs-13 ls-2px fw-600 mb-5px d-block">Decor store story</span>
<h3 className="text-white alt-font fw-700 mb-0">{heroSubtitle}</h3>
</div>
<div className="col-lg-5 md-mt-30px last-paragraph-no-margin" data-anime='{ "translateX": [0, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<p>{heroDesc}</p>
</div>
</div>
</div>
<div className="container-fluid">
<div className="row align-items-center">
<div className="col-md-3 text-center p-4 md-p-15px" data-bottom-top="transform: translateY(-80px)" data-top-bottom="transform: translateY(80px)">
<Image alt="" className="w-100" src="/images/demo-decor-store-about-img-03.jpg" width={420} height={620}/>
</div>
<div className="col-md-6 text-center" data-bottom-top="transform: translateY(80px)" data-top-bottom="transform: translateY(-80px)">
<Image alt="" className="w-100" src="/images/demo-decor-store-about-img-01.jpg" width={960} height={620}/>
</div>
<div className="col-md-3 text-center p-4 md-p-15px" data-bottom-top="transform: translateY(-80px)" data-top-bottom="transform: translateY(80px)">
<Image alt="" className="w-100" src="/images/demo-decor-store-about-img-02.jpg" width={420} height={620}/>
</div>
</div>
</div>
<div className="marquees-text fw-700 fs-200 lg-fs-150 md-fs-130 ls-minus-5px text-base-color text-nowrap position-absolute top-50 lg-mt-5 md-mt-15 sm-mt-22 right-100px text-center z-index-minus-1 d-none d-md-inline-block">
            classic products</div>
</section>
{/*  end section  */}
{/*  start section  */}
<section className="pt-0">
<div className="container">
<div className="row row-cols-auto row-cols-xl-4 row-cols-sm-2 lg-ps-10 lg-pe-10 md-ps-7 md-pe-7 sm-ps-0 sm-pe-0 position-relative mb-6 md-mb-8 lg-mt-20px" data-anime='{ "el": "childs", "translateX": [50, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
{features.map((feat: any, idx: number) => (
<div key={idx} className={`col align-self-${idx % 2 === 1 ? 'end mt-30px lg-mt-0' : 'start'}${idx >= 2 ? ' lg-mt-30px' : ''}`}>
<div className="feature-box text-start ps-30px pe-30px sm-ps-20px sm-pe-20px">
<div className="feature-box-icon position-absolute left-0px top-0px">
<h1 className="alt-font fs-90 text-outline text-outline-width-1px text-outline-color-dark-gray fw-800 ls-minus-1px opacity-2 mb-0">
    {feat.number || String(idx + 1).padStart(2, '0')}
</h1>
</div>
<div className="feature-box-content last-paragraph-no-margin pt-30 lg-pt-22 xs-pt-40px">
<span className="text-white fs-18 d-inline-block fw-600 mb-5px">{feat.title}</span>
<p>{feat.description}</p>
<span className="w-60px h-2px bg-dark-gray mt-20px d-inline-block"></span>
</div>
</div>
</div>
))}
</div>
<div className="row justify-content-center xs-mt-12" data-anime='{ "el": "childs", "translateY": [50, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<div className="col-xl-9 col-lg-10">
<div className="row align-items-center justify-content-center border border-color-extra-medium-gray border-radius-100px sm-border-radius-6px sm-mx-0">
{stats.slice(0, 2).map((stat: any, idx: number) => (
<div key={idx} className={`col-md-6 p-20px ${idx === 0 ? 'border-end border-color-transparent-dark-very-light sm-border-end-0 sm-pb-0 sm-mb-10px' : 'sm-pt-0'} text-center ls-minus-05px align-items-center d-flex justify-content-center`}>
<i className={`bi ${idx === 0 ? 'bi-emoji-smile' : 'bi-star'} text-white icon-extra-medium me-10px`}></i>
<span className="text-white fs-18 text-start fw-500 xs-lh-28">
    {idx === 0 ? <>Join the <span className="fw-800">{stat.value}</span> {stat.label}.</> : <><span className="fw-800">{stat.value}</span> — {stat.label}.</>}
</span>
</div>
))}
</div>
</div>
</div>
</div>
</section>
{/*  end section  */}
{/*  start section  */}
<section data-parallax-background-ratio="0.5" style={{ backgroundImage: 'url(images/demo-decor-store-about-parallax-img.jpg)' }}>
<div className="opacity-light bg-base-color"></div>
<div className="container">
<div className="row align-items-center justify-content-end" data-anime='{ "el": "childs", "translateX": [50, 0],"opacity": [0,1], "duration": 800, "delay": 200, "staggervalue": 300, "easing": "easeOutQuad" }'>
<div className="col-lg-6 col-md-8">
<div className="bg-dark-gray p-70px lg-p-35px position-relative border-radius-6px">
<div className="swiper slider-one-slide text-slider-style-01 magic-cursor" data-slider-options='{ "slidesPerView": 1, "loop": true, "pagination": { "el": ".slider-one-slide-pagination", "clickable": true }, "autoplay": { "delay": 4000, "disableOnInteraction": false }, "navigation": { "nextEl": ".slider-one-slide-next-1", "prevEl": ".slider-one-slide-prev-1" }, "keyboard": { "enabled": true, "onlyInViewport": true }, "effect": "slide" }'>
<div className="swiper-wrapper mb-10px">
<div className="swiper-slide last-paragraph-no-margin">
<div className="text-uppercase fs-13 fw-600 mb-5px ls-2px">World class designers</div>
<h3 className="alt-font text-white fw-700 ls-minus-1px">Exclusive design</h3>
<p className="w-90 lg-w-100">Our world-class designers bring unparalleled creativity and expertise to every piece, ensuring your home reflects refined taste.</p>
</div>
<div className="swiper-slide">
<div className="text-uppercase fs-13 fw-600 mb-5px ls-2px">100% secure method</div>
<h3 className="alt-font text-white fw-700 ls-minus-1px">Secure payment</h3>
<p className="w-90 lg-w-100">Every transaction on Behome is protected with industry-leading encryption and secure payment gateways for your peace of mind.</p>
</div>
<div className="swiper-slide">
<div className="text-uppercase fs-13 fw-600 mb-5px ls-2px">24/7 support center</div>
<h3 className="alt-font text-white fw-700 ls-minus-1px">Online support</h3>
<p className="w-90 lg-w-100">Our dedicated support team is available around the clock to assist you with any questions or concerns about your order.</p>
</div>
</div>
<div className="d-flex">
<div className="slider-one-slide-prev-1 swiper-button-prev slider-navigation-style-04 border border-1 border-color-extra-medium-gray bg-dark-gray">
<i className="fa-solid fa-arrow-left icon-small text-white"></i>
</div>
<div className="slider-one-slide-next-1 swiper-button-next slider-navigation-style-04 border border-1 border-color-extra-medium-gray bg-dark-gray">
<i className="fa-solid fa-arrow-right icon-small text-white"></i>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
{/*  end section  */}
{/*  start section  */}
<section>
<div className="container">
<div className="row justify-content-center mb-40px sm-mb-30px">
<div className="col-lg-5 text-center" data-anime='{ "el": "childs", "translateY": [50, 0], "opacity": [0,1], "duration": 600, "delay": 0, "staggervalue": 150, "easing": "easeOutQuad" }'>
<span className="text-uppercase fs-13 ls-2px fw-600">Core people</span>
<h4 className="alt-font text-white fw-700 mb-20px">Company leaders</h4>
</div>
</div>
<div className="row row-cols-1 row-cols-lg-4 row-cols-sm-2 mb-8" data-anime='{ "el": "childs", "translateY": [-15, 0], "perspective": [1200,1200], "scale": [1.1, 1], "rotateX": [50, 0], "opacity": [0,1], "duration": 800, "delay": 200, "staggervalue": 300, "easing": "easeOutQuad" }'>
{team.map((member: any, idx: number) => {
    const fallbackImages = ['/images/team-08.jpg', '/images/team-09.jpg', '/images/team-10.jpg', '/images/team-11.jpg'];
    const imgSrc = member.image || fallbackImages[idx % 4];
    return (
    <div key={idx} className="col team-style-08 border-radius-6px md-mb-30px">
    <figure className="mb-0 position-relative overflow-hidden border-radius-6px">
    <Image alt={member.name} src={imgSrc} width={420} height={520}/>
    <figcaption className="w-100 h-100 d-flex align-items-end p-13 lg-p-10 bg-gradient-base-transparent border-radius-6px">
    <div className="w-100">
    <span className="team-member-name fw-500 text-white d-block">{member.name}</span>
    <span className="member-designation fs-15 lh-20 text-white d-block">{member.role}</span>
    </div>
    </figcaption>
    </figure>
    </div>
    );
})}
</div>
<div className="row position-relative clients-style-08" data-anime='{ "el": "childs", "translateX": [0, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
<div className="col swiper text-center feather-shadow" data-slider-options='{ "slidesPerView": 1, "spaceBetween":0, "speed": 3000, "loop": true, "allowTouchMove": false, "autoplay": { "delay":0, "disableOnInteraction": false }, "breakpoints": { "1200": { "slidesPerView": 4 }, "992": { "slidesPerView": 3 }, "576": { "slidesPerView": 2 } }, "effect": "slide" }'>
<div className="swiper-wrapper marquee-slide">
{[1,2,3,4,5,1,2,3].map((n, i) => (
<div key={i} className="swiper-slide">
<a href="/about#"><Image alt="" src={`/images/demo-decor-store-client-0${n}.png`} width={180} height={90}/></a>
</div>
))}
</div>
</div>
</div>
</div>
</section>
{/*  end section  */}
<div className="scroll-progress d-none d-xxl-block">
<a aria-label="scroll" className="scroll-top" href="/about#">
<span className="scroll-text">Scroll</span><span className="scroll-line"><span className="scroll-point"></span></span>
</a>
</div>

        </main>
    );
}
