'use client';

import { useState, useRef, useCallback } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';

interface ProductHit {
    id: number;
    name: string;
    slug: string;
    currency_price: string;
    thumbnail: string | null;
}

export default function MobileSearchBar() {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<ProductHit[]>([]);
    const [loading, setLoading] = useState(false);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const fetchSuggestions = useCallback(async (q: string) => {
        if (q.trim().length < 2) { setResults([]); return; }
        setLoading(true);
        try {
            const res = await apiFetch(`/products?search=${encodeURIComponent(q.trim())}&per_page=5`);
            setResults(res?.data?.data ?? res?.data ?? []);
        } catch { setResults([]); }
        finally { setLoading(false); }
    }, []);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const val = e.target.value;
        setQuery(val);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => fetchSuggestions(val), 300);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!query.trim()) return;
        window.location.href = `/shop?search=${encodeURIComponent(query.trim())}`;
    };

    const showDropdown = query.trim().length >= 2;

    return (
        <div style={{ position: 'relative' }}>
            <form onSubmit={handleSubmit} className="mobile-drawer-search-bar" style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                <i className="feather icon-feather-search" style={{ flexShrink: 0 }}></i>
                <input
                    type="text"
                    value={query}
                    onChange={handleChange}
                    placeholder="Search products…"
                    style={{ flex: 1, background: 'transparent', border: 'none', outline: 'none', color: '#fff', fontSize: 14 }}
                />
                {query ? (
                    <button type="button" onClick={() => { setQuery(''); setResults([]); }} style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.5)', cursor: 'pointer', padding: 0, fontSize: 16, lineHeight: 1 }}>×</button>
                ) : (
                    <i className="feather icon-feather-arrow-right mobile-drawer-search-arrow" style={{ flexShrink: 0 }}></i>
                )}
            </form>

            {showDropdown && (
                <div style={{ position: 'absolute', top: '100%', left: 0, right: 0, zIndex: 9999, background: 'rgba(15,15,25,0.98)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 12, marginTop: 6, overflow: 'hidden', boxShadow: '0 16px 40px rgba(0,0,0,0.5)' }}>
                    {loading && (
                        <div style={{ padding: '14px 16px', color: 'rgba(255,255,255,0.4)', fontSize: 13 }}>Searching…</div>
                    )}
                    {!loading && results.length > 0 && (
                        <ul style={{ listStyle: 'none', margin: 0, padding: '6px 0' }}>
                            {results.map(product => (
                                <li key={product.id}>
                                    <a href={`/product/${product.slug}`} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '10px 16px', textDecoration: 'none' }}
                                        onMouseEnter={e => (e.currentTarget.style.background = 'rgba(255,255,255,0.07)')}
                                        onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}>
                                        {product.thumbnail ? (
                                            <Image src={product.thumbnail} alt={product.name} width={36} height={36} style={{ borderRadius: 6, objectFit: 'cover', flexShrink: 0 }} unoptimized />
                                        ) : (
                                            <div style={{ width: 36, height: 36, borderRadius: 6, background: 'rgba(255,255,255,0.08)', flexShrink: 0 }} />
                                        )}
                                        <div style={{ minWidth: 0, flex: 1 }}>
                                            <p style={{ margin: 0, color: '#fff', fontSize: 13, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{product.name}</p>
                                            <p style={{ margin: 0, color: 'rgba(255,255,255,0.45)', fontSize: 12 }}>{product.currency_price}</p>
                                        </div>
                                    </a>
                                </li>
                            ))}
                            <li style={{ borderTop: '1px solid rgba(255,255,255,0.07)' }}>
                                <a href={`/shop?search=${encodeURIComponent(query.trim())}`} style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, padding: '11px 16px', color: 'rgba(255,255,255,0.55)', fontSize: 12, fontWeight: 600, textDecoration: 'none', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                                    View all results <i className="feather icon-feather-arrow-right" style={{ fontSize: 12 }}></i>
                                </a>
                            </li>
                        </ul>
                    )}
                    {!loading && results.length === 0 && (
                        <div style={{ padding: '16px', textAlign: 'center', color: 'rgba(255,255,255,0.4)', fontSize: 13 }}>
                            No results for &ldquo;{query}&rdquo;
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
