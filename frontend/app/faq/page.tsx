import type { Metadata } from 'next';

export const metadata: Metadata = {
    title: 'FAQ | Behome',
    description: 'Frequently asked questions about shopping, shipping, returns, payment, and ordering from Behome.',
};

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
const API_KEY = process.env.NEXT_PUBLIC_API_KEY || '';

const CATEGORY_ICONS: Record<string, string> = {
    'general':        'bi-file-text',
    'shopping':       'bi-bag-plus',
    'payment':        'bi-credit-card-2-back',
    'orders-returns': 'bi-box',
    'help-support':   'bi-headset',
};

async function getFaqs() {
    try {
        const res = await fetch(`${API_URL}/frontend/faqs`, {
            cache: 'no-store',
            headers: { 'x-api-key': API_KEY, 'Content-Type': 'application/json' },
        });
        if (!res.ok) return [];
        const json = await res.json();
        return json.data ?? [];
    } catch {
        return [];
    }
}

export default async function FAQ() {
    const groups: any[] = await getFaqs();

    return (
        <main className="no-layout-pad" style={{ paddingTop: '100px' }}>

            {/* Breadcrumb */}
            <section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
            <div className="container-fluid">
                <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                    <ul>
                        <li><a href="/" style={{textDecoration:'none'}}>Home</a></li>
                        <li>FAQ</li>
                    </ul>
                </div>
            </div>
            </section>

            {/* page title */}
            <section className="mb-5">
                <div className="container">
                    <div className="row">
                        <div className="col-12">
                            <h1 className="text-white alt-font fw-700 mb-2">Frequently Asked Questions</h1>
                            <p className="text-white opacity-70">Find answers to common queries about shipping, returns, payments, and orders in one place.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section className="position-relative">
                <div className="container">
                    <div className="row">

    {/* Tab navigation */}
    <div className="col-xl-3 col-lg-4 col-12 tab-style-07 md-mb-50px sm-mb-35px faq-tab-sidebar" data-anime='{ "translate": [50, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
    <div className="position-sticky top-50px">
    <ul className="nav nav-tabs justify-content-center border-0 fw-500 text-left alt-font bg-dark-gray border-radius-6px overflow-hidden">
        {groups.map((group: any, idx: number) => (
        <li key={group.category} className="nav-item">
            <a className={`nav-link${idx === 0 ? ' active' : ''}`} data-bs-toggle="tab" href={`#faq_tab_${group.category}`} style={{ color: '#fff' }}>
                <span>
                    <span className="me-5px"><i className={`bi ${CATEGORY_ICONS[group.category] || 'bi-question-circle'}`}></i></span>
                    <span>{group.label}</span>
                </span>
                <span className="bg-hover bg-base-color"></span>
            </a>
        </li>
        ))}
    </ul>
    </div>
    </div>

    {/* Tab content */}
    <div className="col-xl-9 col-lg-8 col-12" data-anime='{ "translate": [50, 0], "opacity": [0,1], "duration": 600, "delay":100, "staggervalue": 150, "easing": "easeOutQuad" }'>
    <div className="tab-content">
        {groups.map((group: any, gIdx: number) => (
        <div key={group.category} className={`tab-pane fade${gIdx === 0 ? ' show active' : ''}`} id={`faq_tab_${group.category}`}>
            <div className="accordion accordion-style-02" id={`accordion_${group.category}`}>
                {group.items.map((item: any, iIdx: number) => (
                <div key={item.id} className="accordion-item mb-10px border-0">
                    <h2 className="accordion-header" id={`heading_${group.category}_${iIdx}`}>
                        <button
                            className={`accordion-button${iIdx > 0 ? ' collapsed' : ''} bg-dark-gray text-white fw-600 border-radius-6px`}
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target={`#collapse_${group.category}_${iIdx}`}
                            aria-expanded={iIdx === 0 ? 'true' : 'false'}
                            aria-controls={`collapse_${group.category}_${iIdx}`}
                        >
                            {item.question}
                            <span className="accordion-btn-icon" style={{display:'flex',alignItems:'center',marginLeft:'auto',flexShrink:0}}>
                                <i className="feather icon-feather-plus icon-small" style={{color:'#fff'}}></i>
                                <i className="feather icon-feather-minus icon-small" style={{color:'#fff'}}></i>
                            </span>
                        </button>
                    </h2>
                    <div
                        id={`collapse_${group.category}_${iIdx}`}
                        className={`accordion-collapse collapse${iIdx === 0 ? ' show' : ''}`}
                        aria-labelledby={`heading_${group.category}_${iIdx}`}
                        data-bs-parent={`#accordion_${group.category}`}
                    >
                        <div className="accordion-body last-paragraph-no-margin pt-15px pb-25px ps-20px pe-20px">
                            <p className="text-white">{item.answer}</p>
                        </div>
                    </div>
                </div>
                ))}
            </div>
        </div>
        ))}

        {groups.length === 0 && (
        <div className="tab-pane fade show active">
            <div className="bg-dark-gray border-radius-6px p-40px text-center">
                <p className="text-white opacity-7 mb-0">No FAQ items available yet.</p>
            </div>
        </div>
        )}
    </div>
    </div>

</div>
</div>
</section>
{/*  end section  */}

        </main>
    );
}
