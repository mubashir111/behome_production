'use client';

import { createContext, useCallback, useContext, useState } from 'react';
import AddToCartNotice from './AddToCartNotice';

type ToastType = 'success' | 'error' | 'info' | 'cart';

interface CartProduct {
    name: string;
    image?: string;
    price?: string | number;
}

interface Toast {
    id: number;
    message?: string;
    type: ToastType;
    product?: CartProduct;
}

interface ToastContextValue {
    showToast: (message: string, type?: ToastType) => void;
    showCartToast: (product: CartProduct) => void;
}

const ToastContext = createContext<ToastContextValue>({ 
    showToast: () => {},
    showCartToast: () => {},
});

export function useToast() {
    return useContext(ToastContext);
}

const COLORS: Record<Exclude<ToastType, 'cart'>, { bg: string; border: string; text: string }> = {
    success: { bg: 'rgba(16,185,129,0.12)', border: '1px solid rgba(16,185,129,0.4)', text: '#6ee7b7' },
    error:   { bg: 'rgba(239,68,68,0.12)',  border: '1px solid rgba(239,68,68,0.4)',  text: '#fca5a5' },
    info:    { bg: 'rgba(99,102,241,0.12)', border: '1px solid rgba(99,102,241,0.4)', text: '#a5b4fc' },
};

const ICONS: Record<Exclude<ToastType, 'cart'>, string> = {
    success: '✓',
    error:   '✕',
    info:    'ℹ',
};

let nextId = 0;

export default function ToastProvider({ children }: { children: React.ReactNode }) {
    const [toasts, setToasts] = useState<Toast[]>([]);

    const removeToast = useCallback((id: number) => {
        setToasts(prev => prev.filter(t => t.id !== id));
    }, []);

    const showToast = useCallback((message: string, type: ToastType = 'info') => {
        const id = ++nextId;
        setToasts(prev => [...prev, { id, message, type }]);
        setTimeout(() => removeToast(id), 4000);
    }, [removeToast]);

    const showCartToast = useCallback((product: CartProduct) => {
        const id = ++nextId;
        setToasts(prev => [...prev, { id, type: 'cart', product }]);
        setTimeout(() => removeToast(id), 6000); // Cart toast stays slightly longer
    }, [removeToast]);

    const standardToasts = toasts.filter(t => t.type !== 'cart');
    const cartToasts = toasts.filter(t => t.type === 'cart');

    return (
        <ToastContext.Provider value={{ showToast, showCartToast }}>
            {children}

            {/* Cart Toast Container (Top Right) */}
            <div style={{
                position: 'fixed',
                top: 80, // Offset from header
                right: 24,
                zIndex: 9999,
                display: 'flex',
                flexDirection: 'column',
                gap: 15,
                pointerEvents: 'none',
            }}>
                {cartToasts.map(toast => (
                    <div key={toast.id} style={{ pointerEvents: 'auto' }}>
                        {toast.product && (
                            <AddToCartNotice 
                                product={toast.product} 
                                onClose={() => removeToast(toast.id)} 
                            />
                        )}
                    </div>
                ))}
            </div>

            {/* Standard Toast container (Bottom Right) */}
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
                {standardToasts.map(toast => {
                    const type = toast.type as Exclude<ToastType, 'cart'>;
                    const c = COLORS[type];
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
                            <span style={{ fontSize: 16, lineHeight: 1 }}>{ICONS[type]}</span>
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

