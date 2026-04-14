import type { Metadata } from 'next';
import Image from 'next/image';
import ContactForm from '@/components/ContactForm';
import { SERVER_API_URL, API_KEY } from '@/lib/config';
import { constructMetadata } from '@/lib/metadata';


async function getContactData() {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/static-pages/contact`, {
            cache: 'no-store',
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
    const data = await getContactData();
    return constructMetadata({
        title: data?.meta_title || 'Contact Us | Behome',
        description: data?.meta_description || 'Get in touch with the Behome team.',
    });
}

export default async function Contact() {
    const data = await getContactData();
    const s = data?.sections || {};

    const address     = s.address       || 'London, United Kingdom';
    const phones: string[]  = s.phones  || ['+44 207 123 4567', '+44 800 123 4567'];
    const emails: string[]  = s.emails  || ['hello@behome.co.uk', 'support@behome.co.uk'];
    const careersEmail = s.careers_email || 'careers@behome.co.uk';

    return (
        <main>

{/* Breadcrumb */}
<section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
<div className="container-fluid">
    <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
        <ul>
            <li><a href="/" style={{textDecoration:'none'}}>Home</a></li>
            <li>Contact Us</li>
        </ul>
    </div>
</div>
</section>

{/*  start section  */}
<section className="pt-60 md-pt-40">
<div className="container">
<div className="row row-cols-1 row-cols-lg-4 row-cols-sm-2" data-anime='{ "el": "childs", "translateX": [50, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
    <div className="col md-mb-35px">
        <span className="fs-17 fw-600 d-block w-90 sm-w-100 text-base-color border-bottom border-color-base-color pb-15px mb-15px">
            <i className="feather icon-feather-map-pin d-inline-block text-base-color me-10px"></i>Office location
        </span>
        <p className="w-100 m-0 text-white opacity-7">{address}</p>
    </div>
    <div className="col md-mb-35px">
        <span className="fs-17 fw-600 d-block w-90 sm-w-100 text-base-color border-bottom border-color-base-color pb-15px mb-15px">
            <i className="feather icon-feather-mail d-inline-block text-base-color me-10px"></i>Send a message
        </span>
        {emails.map((email, i) => (
            <span key={i}>
                <a className="fs-16 text-white fw-500" href={`mailto:${email}`}>{email}</a>
                {i < emails.length - 1 && <br/>}
            </span>
        ))}
    </div>
    <div className="col xs-mb-35px">
        <span className="fs-17 fw-600 d-block w-90 sm-w-100 text-base-color border-bottom border-color-base-color pb-15px mb-15px">
            <i className="feather icon-feather-phone d-inline-block text-base-color me-10px"></i>Call us directly
        </span>
        {phones.map((phone, i) => (
            <span key={i}>
                <a className="text-white opacity-8" href={`tel:${phone.replace(/\s/g, '')}`}>{phone}</a>
                {i < phones.length - 1 && <br/>}
            </span>
        ))}
    </div>
    <div className="col">
        <span className="fs-17 fw-600 d-block w-90 sm-w-100 text-base-color border-bottom border-color-base-color pb-15px mb-15px">
            <i className="feather icon-feather-users d-inline-block text-base-color me-10px"></i>Join our team
        </span>
        <a className="fs-16 text-white fw-500" href={`mailto:${careersEmail}`}>{careersEmail}</a>
    </div>
</div>
</div>
</section>
{/*  end section  */}

{/*  start section  */}
<section className="pt-0 position-relative overflow-hidden">
<div className="container">
<div className="row mb-20px">
<div className="col-lg-10 col-md-12" data-anime='{ "effect": "slide", "color": "#1B3250", "direction":"rl", "easing": "easeOutQuad", "delay":50}'>
<Image alt="Behome showroom" src="/images/demo-decor-store-contact-01.jpg" width={1200} height={720} style={{ width: '100%', height: 'auto' }}/>
</div>
</div>
<div className="row align-items-end">
<div className="col-lg-7 col-md-12 align-self-start md-mt-15px" data-bottom-top="transform: translate3d(80px, 20px, 0px);" data-top-bottom="transform: translate3d(-80px, 20px, 0px);">
<span className="fs-120 xs-fs-75 fw-600 opacity-8 d-block d-lg-inline-block text-center ls-minus-3px text-white-space-nowrap xs-text-white-space-normal text-white">Get in touch!</span>
</div>
<div className="col-lg-5 contact-form-style-03 position-relative overlap-section-one-fourth md-mt-0" data-anime='{ "el": "childs", "translateY": [50, 0],"opacity": [0,1], "duration": 800, "delay": 550, "staggervalue": 300, "easing": "easeOutQuad" }'>
    <ContactForm />
</div>
</div>
</div>
</section>
{/*  end section  */}


        </main>
    );
}
