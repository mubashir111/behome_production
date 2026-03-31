'use client';

const shimmerStyle: React.CSSProperties = {
    background: 'linear-gradient(90deg, rgba(255,255,255,0.06) 25%, rgba(255,255,255,0.12) 50%, rgba(255,255,255,0.06) 75%)',
    backgroundSize: '200% 100%',
    animation: 'skeleton-shimmer 1.5s infinite',
};

export default function LoadingSkeleton({ type = 'product', rows = 3 }: { type?: 'product' | 'text' | 'card' | 'table' | 'form'; rows?: number }) {
    return (
        <>
            <style>{`
                @keyframes skeleton-shimmer {
                    0%   { background-position: 200% 0; }
                    100% { background-position: -200% 0; }
                }
            `}</style>

            {type === 'form' && (
                <div>
                    {Array.from({ length: rows }).map((_, i) => (
                        <div key={i} style={{ marginBottom: 20 }}>
                            <div style={{ ...shimmerStyle, height: 12, width: '30%', borderRadius: 4, marginBottom: 8 }} />
                            <div style={{ ...shimmerStyle, height: 40, borderRadius: 4 }} />
                        </div>
                    ))}
                </div>
            )}

            {type === 'table' && (
                <div>
                    {Array.from({ length: rows }).map((_, i) => (
                        <div key={i} className="d-flex gap-3 mb-15px align-items-center">
                            <div style={{ ...shimmerStyle, width: 70, height: 70, borderRadius: 4, flexShrink: 0 }} />
                            <div className="flex-fill">
                                <div style={{ ...shimmerStyle, height: 14, width: '60%', borderRadius: 4, marginBottom: 8 }} />
                                <div style={{ ...shimmerStyle, height: 12, width: '40%', borderRadius: 4 }} />
                            </div>
                            <div style={{ ...shimmerStyle, width: 80, height: 14, borderRadius: 4 }} />
                        </div>
                    ))}
                </div>
            )}

            {type === 'product' && (
                <div className="col mb-45px">
                    <div className="shop-box pb-25px">
                        <div className="shop-image">
                            <div style={{ ...shimmerStyle, height: 260, width: '100%', borderRadius: 4 }} />
                        </div>
                        <div className="shop-footer text-center pt-20px">
                            <div style={{ ...shimmerStyle, height: 14, width: '75%', margin: '0 auto 8px', borderRadius: 4 }} />
                            <div style={{ ...shimmerStyle, height: 14, width: '50%', margin: '0 auto', borderRadius: 4 }} />
                        </div>
                    </div>
                </div>
            )}

            {type === 'card' && (
                <div style={{ borderRadius: 8, padding: 24, background: 'rgba(255,255,255,0.04)' }}>
                    <div style={{ ...shimmerStyle, height: 14, width: '75%', marginBottom: 16, borderRadius: 4 }} />
                    <div style={{ ...shimmerStyle, height: 14, width: '50%', marginBottom: 8, borderRadius: 4 }} />
                    <div style={{ ...shimmerStyle, height: 14, width: '65%', borderRadius: 4 }} />
                </div>
            )}

            {type === 'text' && (
                <div style={{ ...shimmerStyle, height: 14, width: '100%', borderRadius: 4 }} />
            )}
        </>
    );
}
