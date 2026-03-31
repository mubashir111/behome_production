import LoadingSkeleton from '@/components/LoadingSkeleton';

export default function PageLoadingShell({ variant = 'grid' }: { variant?: 'grid' | 'account' | 'checkout' | 'message' }) {
    if (variant === 'message') {
        return (
            <main>
                <section className="page-shell">
                    <div className="container">
                        <div className="ui-panel ui-panel-lg mx-auto" style={{ maxWidth: 760 }}>
                            <div className="animate-pulse">
                                <div style={{ height: 18, width: '30%', background: 'rgba(255,255,255,0.08)', borderRadius: 999, margin: '0 auto 18px' }} />
                                <div style={{ height: 34, width: '55%', background: 'rgba(255,255,255,0.08)', borderRadius: 8, margin: '0 auto 14px' }} />
                                <div style={{ height: 14, width: '72%', background: 'rgba(255,255,255,0.06)', borderRadius: 8, margin: '0 auto 10px' }} />
                                <div style={{ height: 14, width: '58%', background: 'rgba(255,255,255,0.06)', borderRadius: 8, margin: '0 auto 28px' }} />
                                <div className="d-flex justify-content-center gap-3 flex-wrap">
                                    <div style={{ height: 44, width: 180, background: 'rgba(255,255,255,0.08)', borderRadius: 999 }} />
                                    <div style={{ height: 44, width: 180, background: 'rgba(255,255,255,0.05)', borderRadius: 999 }} />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (variant === 'account') {
        return (
            <main>
                <section className="page-shell page-shell-tight">
                    <div className="container">
                        <div className="row">
                            <div className="col-lg-3 col-md-4 md-mb-40px">
                                <div className="ui-panel ui-panel-lg">
                                    <LoadingSkeleton type="card" />
                                    <div className="mt-25px">
                                        {Array.from({ length: 5 }).map((_, i) => (
                                            <div key={i} style={{ height: 42, background: 'rgba(255,255,255,0.06)', borderRadius: 8, marginBottom: 10 }} />
                                        ))}
                                    </div>
                                </div>
                            </div>
                            <div className="col-lg-9 col-md-8 ui-content-offset">
                                <div className="ui-panel ui-panel-lg">
                                    <LoadingSkeleton type="form" rows={5} />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (variant === 'checkout') {
        return (
            <main>
                <section className="page-shell page-shell-tight">
                    <div className="container">
                        <div className="row align-items-start">
                            <div className="col-lg-7 pe-50px md-pe-15px md-mb-50px xs-mb-35px">
                                <div className="ui-panel ui-panel-lg">
                                    <LoadingSkeleton type="form" rows={7} />
                                </div>
                            </div>
                            <div className="col-lg-5">
                                <div className="ui-panel ui-panel-lg">
                                    <LoadingSkeleton type="card" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    return (
        <main>
            <section className="page-shell page-shell-tight">
                <div className="container">
                    <div className="ui-page-intro ui-page-intro-center">
                        <div className="animate-pulse">
                            <div style={{ height: 16, width: 120, background: 'rgba(255,255,255,0.08)', borderRadius: 999, margin: '0 auto 12px' }} />
                            <div style={{ height: 34, width: '35%', background: 'rgba(255,255,255,0.08)', borderRadius: 8, margin: '0 auto 10px' }} />
                            <div style={{ height: 14, width: '42%', background: 'rgba(255,255,255,0.06)', borderRadius: 8, margin: '0 auto' }} />
                        </div>
                    </div>
                    <div className="row g-4">
                        <div className="col-lg-3">
                            <div className="ui-sidebar-card">
                                <LoadingSkeleton type="card" />
                            </div>
                        </div>
                        <div className="col-lg-9">
                            <div className="row row-cols-1 row-cols-xl-3 row-cols-md-2 g-4">
                                {Array.from({ length: 6 }).map((_, i) => (
                                    <LoadingSkeleton key={i} type="product" />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}
