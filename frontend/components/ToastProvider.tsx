'use client';

import { createContext, useCallback, useContext, useState } from 'react';

type ToastType = 'success' | 'error' | 'info';

interface Toast {
    id: number;
    message: string;
    type: ToastType;
}

interface ToastContextValue {
    showToast: (message: string, type?: ToastType) => void;
}

const ToastContext = createContext<ToastContextValue>({ showToast: () => {} });

export function useToast() {
    return useContext(ToastContext);
}

const COLORS: Record<ToastType, { bg: string; border: string; text: string }> = {
    success: { bg: 'rgba(16,185,129,0.12)', border: '1px solid rgba(16,185,129,0.4)', text: '#6ee7b7' },
    error:   { bg: 'rgba(239,68,68,0.12)',  border: '1px solid rgba(239,68,68,0.4)',  text: '#fca5a5' },
    info:    { bg: 'rgba(99,102,241,0.12)', border: '1px solid rgba(99,102,241,0.4)', text: '#a5b4fc' },
};

const ICONS: Record<ToastType, string> = {
    success: '✓',
    error:   '✕',
    info:    'ℹ',
};

let nextId = 0;

export default function ToastProvider({ children }: { children: React.ReactNode }) {
    const [toasts, setToasts] = useState<Toast[]>([]);

    const showToast = useCallback((message: string, type: ToastType = 'info') => {
        const id = ++nextId;
        setToasts(prev => [...prev, { id, message, type }]);
        setTimeout(() => {
            setToasts(prev => prev.filter(t => t.id !== id));
        }, 4000);
    }, []);

    return (
        <ToastContext.Provider value={{ showToast }}>
            {children}

            {/* Toast container */}
            <div style={{
                position: 'fixed',
                bottom: 24,
                right: 24,
                zIndex: 9999,
                display: 'flex',
                flexDirection: 'column',
                gap: 10,
                pointerEvents: 'none',
            }}>
                {toasts.map(toast => {
                    const c = COLORS[toast.type];
                    return (
                        <div
                            key={toast.id}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 10,
                                padding: '12px 18px',
                                borderRadius: 12,
                                background: c.bg,
                                border: c.border,
                                backdropFilter: 'blur(12px)',
                                color: c.text,
                                fontSize: 14,
                                fontWeight: 500,
                                maxWidth: 340,
                                boxShadow: '0 8px 32px rgba(0,0,0,0.3)',
                                animation: 'toast-in 0.25s ease',
                                pointerEvents: 'auto',
                            }}
                        >
                            <span style={{ fontSize: 16, lineHeight: 1 }}>{ICONS[toast.type]}</span>
                            {toast.message}
                        </div>
                    );
                })}
            </div>

            <style>{`
                @keyframes toast-in {
                    from { opacity: 0; transform: translateY(8px); }
                    to   { opacity: 1; transform: translateY(0); }
                }
            `}</style>
        </ToastContext.Provider>
    );
}
