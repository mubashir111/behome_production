import type { Metadata } from 'next';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
const API_KEY = process.env.NEXT_PUBLIC_API_KEY || '';

async function getPageData() {
    try {
        const res = await fetch(`${API_URL}/frontend/static-pages/privacy-policy`, {
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
    const data = await getPageData();
    return {
        title: data?.meta_title || 'Privacy Policy | Behome',
        description: data?.meta_description || 'Read the Behome privacy policy.',
    };
}

export default async function PrivacyPolicy() {
    const data = await getPageData();
    const content = data?.content || '<p>Privacy policy content coming soon.</p>';

    return (
        <main>
            <section className="pt-60 md-pt-40 pb-8">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-xl-8 col-lg-10">
                            <h2 className="alt-font text-white fw-700 mb-30px">{data?.title || 'Privacy Policy'}</h2>
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
