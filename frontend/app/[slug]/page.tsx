import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import HTMLReactParser from 'html-react-parser';
import { SERVER_API_URL, API_KEY } from '@/lib/config';
import { constructMetadata } from '@/lib/metadata';


async function getPageData(slug: string) {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/static-pages/${slug}`, {
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

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
    const data = await getPageData(params.slug);
    if (!data) return constructMetadata({ title: 'Page Not Found' });
    return constructMetadata({
        title: data.meta_title || `${data.title} | Behome`,
        description: data.meta_description || '',
    });
}

export default async function DynamicPage({ params }: { params: { slug: string } }) {
    const data = await getPageData(params.slug);

    if (!data) {
        notFound();
    }

    return (
        <main className="no-layout-pad" style={{ paddingTop: '50px' }}>
            {/* Start Page Title */}
            <section className="page-title-center-alignment cover-background bg-dark-gray" 
                style={{ 
                    backgroundImage: 'linear-gradient(rgba(10, 10, 10, 0.7), rgba(10, 10, 10, 0.7)), url(\'/images/new/bg/bg1.webp\')', 
                    paddingBottom: '70px' 
                }}>
                <div className="container">
                    <div className="row">
                        <div className="col-12 text-center position-relative page-title-extra-large">
                            <h1 className="alt-font d-inline-block fw-700 ls-minus-05px text-white mb-10px">{data.title}</h1>
                        </div>
                        <div className="col-12 breadcrumb breadcrumb-style-01 d-flex justify-content-center">
                            <ul>
                                <li><a className="text-white" href="/">Home</a></li>
                                <li className="text-white opacity-7">{data.title}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
            {/* End Page Title */}

            {/* Start Page Content */}
            <section className="position-relative pt-6 md-pt-4 pb-6 md-pb-4">
                <div className="container">
                    <div className="row">
                        <div className="col-12 static-page-content text-white">
                            {HTMLReactParser(data.content || '')}
                        </div>
                    </div>
                </div>
            </section>
            {/* End Page Content */}

            {/*  start scroll progress  */}
            <div className="scroll-progress d-none d-xxl-block">
                <a aria-label="scroll" className="scroll-top" href={`/${params.slug}#`}>
                    <span className="scroll-text">Scroll</span><span className="scroll-line"><span className="scroll-point"></span></span>
                </a>
            </div>
            {/*  end scroll progress  */}
        </main>
    );
}
