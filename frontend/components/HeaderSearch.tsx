'use client';

import { useState, useRef, useEffect, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';

interface ProductHit {
    id: number;
    name: string;
    slug: string;
    currency_price: string;
    thumbnail: string | null;
}

export default function HeaderSearch() {
    const router = useRouter();
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<ProductHit[]>([]);
    const [loading, setLoading] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);
    const wrapperRef = useRef<HTMLDivElement>(null);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    // Focus input when overlay opens
    useEffect(() => {
        if (open) {
            setTimeout(() => inputRef.current?.focus(), 50);
        } else {
            setQuery('');
            setResults([]);
        }
    }, [open]);

    // Close on outside click
    useEffect(() => {
        function handleClick(e: MouseEvent) {
            if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        if (open) document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, [open]);

    // Close on Escape
    useEffect(() => {
        function handleKey(e: KeyboardEvent) {
            if (e.key === 'Escape') setOpen(false);
        }
        document.addEventListener('keydown', handleKey);
        return () => document.removeEventListener('keydown', handleKey);
    }, []);

    const fetchSuggestions = useCallback(async (q: string) => {
        if (q.trim().length < 2) { setResults([]); return; }
        setLoading(true);
        try {
            const res = await apiFetch(`/products?search=${encodeURIComponent(q.trim())}&per_page=6`);
            setResults(res?.data?.data ?? res?.data ?? []);
        } catch {
            setResults([]);
        } finally {
            setLoading(false);
        }
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
        setOpen(false);
        router.push(`/shop?search=${encodeURIComponent(query.trim())}`);
    };

    const handleSelect = (slug: string) => {
        setOpen(false);
        router.push(`/product/${slug}`);
    };

    return (
        <div className="position-relative" ref={wrapperRef}>
            {/* Search icon button */}
            <button
                className="glass-icon-box border-0 bg-transparent d-flex align-items-center justify-content-center"
                onClick={() => setOpen(v => !v)}
                aria-label="Search"
                style={{ cursor: 'pointer' }}
            >
                <i className="feather icon-feather-search text-white fs-15"></i>
            </button>

            {/* Dropdown search panel */}
            {open && (
                <div
                    style={{
                        position: 'absolute',
                        top: 'calc(100% + 10px)',
                        right: 0,
                        width: '340px',
                        background: 'rgba(15, 15, 25, 0.92)',
                        backdropFilter: 'blur(20px)',
                        WebkitBackdropFilter: 'blur(20px)',
                        border: '1px solid rgba(255,255,255,0.12)',
                        borderRadius: '16px',
                        boxShadow: '0 20px 60px rgba(0,0,0,0.4)',
                        zIndex: 9999,
                        overflow: 'hidden',
                    }}
                >
                    {/* Input row */}
                    <form onSubmit={handleSubmit} style={{ display: 'flex', alignItems: 'center', padding: '12px 16px', borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
                        <i className="feather icon-feather-search" style={{ color: 'rgba(255,255,255,0.5)', fontSize: '15px', marginRight: '10px', flexShrink: 0 }}></i>
                        <input
                            ref={inputRef}
                            type="text"
                            value={query}
                            onChange={handleChange}
                            placeholder="Search products..."
                            style={{
                                flex: 1,
                                background: 'transparent',
                                border: 'none',
                                outline: 'none',
                                color: '#fff',
                                fontSize: '14px',
                                fontWeight: 500,
                            }}
                        />
                        {query && (
                            <button
                                type="button"
                                onClick={() => { setQuery(''); setResults([]); inputRef.current?.focus(); }}
                                style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.4)', cursor: 'pointer', padding: '0 4px', fontSize: '16px', lineHeight: 1 }}
                            >
                                ×
                            </button>
                        )}
                    </form>

                    {/* Results */}
                    {loading && (
                        <div style={{ padding: '16px', textAlign: 'center', color: 'rgba(255,255,255,0.4)', fontSize: '13px' }}>
                            Searching...
                        </div>
                    )}

                    {!loading && results.length > 0 && (
                        <ul style={{ listStyle: 'none', margin: 0, padding: '8px 0', maxHeight: '320px', overflowY: 'auto' }}>
                            {results.map((product) => (
                                <li key={product.id}>
                                    <button
                                        onClick={() => handleSelect(product.slug)}
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: '12px',
                                            width: '100%',
                                            padding: '10px 16px',
                                            background: 'transparent',
                                            border: 'none',
                                            cursor: 'pointer',
                                            textAlign: 'left',
                                            transition: 'background 0.15s',
                                        }}
                                        onMouseEnter={e => (e.currentTarget.style.background = 'rgba(255,255,255,0.07)')}
                                        onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}
                                    >
                                        {product.thumbnail ? (
                                            <Image
                                                src={product.thumbnail}
                                                alt={product.name}
                                                width={40}
                                                height={40}
                                                style={{ borderRadius: '8px', objectFit: 'cover', flexShrink: 0 }}
                                                unoptimized
                                            />
                                        ) : (
                                            <div style={{ width: 40, height: 40, borderRadius: '8px', background: 'rgba(255,255,255,0.08)', flexShrink: 0 }} />
                                        )}
                                        <div style={{ minWidth: 0, flex: 1 }}>
                                            <p style={{ margin: 0, color: '#fff', fontSize: '13px', fontWeight: 600, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                                {product.name}
                                            </p>
                                            <p style={{ margin: 0, color: 'rgba(255,255,255,0.5)', fontSize: '12px', marginTop: '2px' }}>
                                                {product.currency_price}
                                            </p>
                                        </div>
                                        <i className="feather icon-feather-arrow-right" style={{ color: 'rgba(255,255,255,0.3)', fontSize: '14px', flexShrink: 0 }}></i>
                                    </button>
                                </li>
                            ))}
                            {/* View all results link */}
                            <li style={{ borderTop: '1px solid rgba(255,255,255,0.08)', marginTop: '4px' }}>
                                <button
                                    onClick={() => { setOpen(false); router.push(`/shop?search=${encodeURIComponent(query.trim())}`); }}
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        gap: '6px',
                                        width: '100%',
                                        padding: '12px 16px',
                                        background: 'transparent',
                                        border: 'none',
                                        cursor: 'pointer',
                                        color: 'rgba(255,255,255,0.6)',
                                        fontSize: '12px',
                                        fontWeight: 600,
                                        letterSpacing: '0.05em',
                                        textTransform: 'uppercase',
                                        transition: 'color 0.15s',
                                    }}
                                    onMouseEnter={e => (e.currentTarget.style.color = '#fff')}
                                    onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.6)')}
                                >
                                    View all results
                                    <i className="feather icon-feather-arrow-right" style={{ fontSize: '12px' }}></i>
                                </button>
                            </li>
                        </ul>
                    )}

                    {!loading && query.trim().length >= 2 && results.length === 0 && (
                        <div style={{ padding: '20px 16px', textAlign: 'center' }}>
                            <p style={{ color: 'rgba(255,255,255,0.4)', fontSize: '13px', margin: '0 0 8px' }}>No results for &ldquo;{query}&rdquo;</p>
                            <button
                                onClick={() => { setOpen(false); router.push(`/shop?search=${encodeURIComponent(query.trim())}`); }}
                                style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.6)', fontSize: '12px', cursor: 'pointer', textDecoration: 'underline' }}
                            >
                                Browse all products
                            </button>
                        </div>
                    )}

                    {!loading && query.trim().length < 2 && (
                        <div style={{ padding: '16px', textAlign: 'center', color: 'rgba(255,255,255,0.3)', fontSize: '12px' }}>
                            Type to search products...
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
