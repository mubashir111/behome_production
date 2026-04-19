'use client';

import { useState, useEffect, useRef } from 'react';
import Script from 'next/script';

const LANGUAGES = [
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'ar', label: 'العربية', flag: '🇸🇦' },
];

const STORAGE_KEY = 'site_language';

export default function LanguageSwitcher() {
    const [open, setOpen] = useState(false);
    const [selected, setSelected] = useState<typeof LANGUAGES[0]>(LANGUAGES[0]);
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const saved = localStorage.getItem(STORAGE_KEY);
        
        // Check for Google Translate cookie first, fallback to local storage
        const gtCookie = document.cookie.split('; ').find(row => row.startsWith('googtrans='));
        const activeCode = gtCookie ? gtCookie.split('/')[2] : saved;

        if (activeCode) {
            const match = LANGUAGES.find(l => l.code === activeCode);
            if (match) setSelected(match);
            document.documentElement.lang = activeCode;
            document.documentElement.dir = activeCode === 'ar' ? 'rtl' : 'ltr';
        }
    }, []);

    useEffect(() => {
        const handleOutside = (e: MouseEvent) => {
            if (ref.current && !ref.current.contains(e.target as Node)) {
                setOpen(false);
            }
        };
        document.addEventListener('mousedown', handleOutside);
        return () => document.removeEventListener('mousedown', handleOutside);
    }, []);

    const choose = (lang: typeof LANGUAGES[0]) => {
        setSelected(lang);
        localStorage.setItem(STORAGE_KEY, lang.code);
        setOpen(false);
        // Set html lang attribute
        document.documentElement.lang = lang.code;
        document.documentElement.dir = lang.code === 'ar' ? 'rtl' : 'ltr';
            
            // Cookie domain must be hostname only — no port number
            const cookieDomain = window.location.hostname;

            if (lang.code === 'en') {
                // Clear the cookie to revert to English (original DOM)
                document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                document.cookie = `googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=.${cookieDomain}; path=/;`;
            } else {
                // Set Google Translate cookie (format: /source_lang/target_lang)
                document.cookie = `googtrans=/en/${lang.code}; path=/`;
                document.cookie = `googtrans=/en/${lang.code}; domain=.${cookieDomain}; path=/`;
            }

            // Reload to let Google Translate script apply the translation natively
            window.location.reload();
    };

    return (
        <>
            {/* Hidden Google Translate Element & Scripts */}
            <div id="google_translate_element" style={{ display: 'none' }}></div>
            <Script 
                src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" 
                strategy="afterInteractive" 
            />
            <Script id="google-translate-init" strategy="afterInteractive">
                {`
                    function googleTranslateElementInit() {
                        new google.translate.TranslateElement({
                            pageLanguage: 'en',
                            autoDisplay: false,
                            includedLanguages: 'en,ar'
                        }, 'google_translate_element');
                    }
                `}
            </Script>

            {/* Language Switcher UI - 'notranslate' prevents GT from translating the names themselves */}
            <div className="position-relative d-flex align-items-center notranslate" ref={ref}>
                <button
                    onClick={() => setOpen(o => !o)}
                    className="glass-icon-box d-flex align-items-center gap-1 border-0 bg-transparent"
                    style={{ cursor: 'pointer', padding: '0 7px' }}
                    aria-label="Select language"
                    type="button"
                >
                    <span className="alt-font text-white fw-700 fs-13 lh-1">{selected.code.toUpperCase()}</span>
                    <i className="feather icon-feather-chevron-down text-white" style={{ fontSize: 11, opacity: 0.7 }}></i>
                </button>

                {open && (
                    <div
                        style={{
                            position: 'absolute',
                            top: 'calc(100% + 8px)',
                            insetInlineEnd: 0,
                            minWidth: 160,
                            background: 'rgba(18,18,22,0.95)',
                            border: '1px solid rgba(255,255,255,0.12)',
                            borderRadius: 8,
                            boxShadow: '0 8px 32px rgba(0,0,0,0.5)',
                            backdropFilter: 'blur(16px)',
                            zIndex: 9999,
                            overflow: 'hidden',
                        }}
                    >
                        {LANGUAGES.map(lang => (
                            <button
                                key={lang.code}
                                onClick={() => choose(lang)}
                                type="button"
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 10,
                                    width: '100%',
                                    padding: '10px 16px',
                                    background: lang.code === selected.code ? 'rgba(255,255,255,0.08)' : 'transparent',
                                    border: 'none',
                                    color: '#fff',
                                    fontSize: 14,
                                    fontWeight: lang.code === selected.code ? 600 : 400,
                                    cursor: 'pointer',
                                    textAlign: 'start',
                                }}
                            >
                                <span style={{ fontSize: 18 }}>{lang.flag}</span>
                                <span>{lang.label}</span>
                                {lang.code === selected.code && (
                                    <i className="feather icon-feather-check ms-auto" style={{ fontSize: 13, color: 'var(--base-color)' }}></i>
                                )}
                            </button>
                        ))}
                    </div>
                )}
            </div>
            <style dangerouslySetInnerHTML={{ __html: '/* Hide the Google Translate top bar and fix body spacing */\n.skiptranslate > iframe.skiptranslate { display: none !important; }\nbody { top: 0px !important; position: static !important; }' }} />
        </>
    );
}
