import type { Metadata } from 'next';
import { SERVER_API_URL, API_KEY } from '@/lib/config';


async function getPageData() {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/static-pages/terms-conditions`, {
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
    return {
        title: data?.meta_title || 'Terms & Conditions | Behome',
        description: data?.meta_description || 'Read the Behome terms and conditions.',
    };
}

export default async function TermsConditions() {
    const data = await getPageData();
    const content = data?.content || '<p>Terms and conditions content coming soon.</p>';

    return (
        <main>
            <section className="pt-60 md-pt-40 pb-8">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-xl-8 col-lg-10">
                            <h2 className="alt-font text-white fw-700 mb-30px">{data?.title || 'Terms & Conditions'}</h2>
                            <div
                                className="text-white opacity-8 lh-30"
                                style={{ fontSize: '15px' }}
                                dangerouslySetInnerHTML={{ __html: content }}
                            />
                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}
