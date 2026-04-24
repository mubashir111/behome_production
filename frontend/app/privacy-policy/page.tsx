import type { Metadata } from 'next';
import { SERVER_API_URL, API_KEY } from '@/lib/config';
import { constructMetadata } from '@/lib/metadata';
import DOMPurify from 'isomorphic-dompurify';


async function getPageData() {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/static-pages/privacy-policy`, {
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
    const data = await getPageData();
    return constructMetadata({
        title: data?.meta_title || 'Privacy Policy | Behom',
        description: data?.meta_description || 'Read the Behom Privacy Policy.',
    });
}

const DEFAULT_CONTENT = `
<h2>Privacy Policy</h2>
<p>At Behom, we are committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal information when you use our website and services.</p>
<h3>Information We Collect</h3>
<p>We collect information you provide directly to us, such as your name, email address, shipping address, and payment details when you place an order.</p>
<h3>How We Use Your Information</h3>
<p>We use the information we collect to process your orders, communicate with you about your purchases, and improve our services.</p>
<h3>Data Security</h3>
<p>We implement appropriate technical and organisational measures to protect your personal data against unauthorised access, alteration, disclosure, or destruction.</p>
<h3>Contact Us</h3>
<p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:hello@behom.com">hello@behom.com</a>.</p>
`;

export default async function PrivacyPolicy() {
    const data = await getPageData();
    const title   = data?.title   || 'Privacy Policy';
    const content = data?.content || DEFAULT_CONTENT;

    return (
        <main>
            <section className="top-space-padding pt-60px pb-8 xs-pt-40px">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-8 text-center">
                            <span className="fs-13 fw-600 text-uppercase ls-3px text-base-color d-block mb-15px">Legal</span>
                            <h1 className="alt-font fw-600 text-white ls-minus-1px mb-0">{title}</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section className="pt-50px pb-100px xs-pb-60px">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-8">
                            <div
                                className="privacy-content-wrap"
                                dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(content) }}
                            />
                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}
