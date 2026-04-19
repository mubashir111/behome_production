'use client';

import { createContext, useContext, useState, useEffect } from 'react';
import { apiFetch } from '@/lib/api';

interface CartContextValue {
    count: number;
    updateCart: (count?: number) => Promise<void>;
    loading: boolean;
}

const CartContext = createContext<CartContextValue>({
    count: 0,
    updateCart: async () => {},
    loading: true,
});

export function useCart() {
    return useContext(CartContext);
}

export default function CartProvider({ children }: { children: React.ReactNode }) {
    const [count, setCount] = useState(0);
    const [loading, setLoading] = useState(true);

    const fetchCart = async () => {
        const token = localStorage.getItem('token');
        if (!token) {
            setCount(0);
            setLoading(false);
            return;
        }

        try {
            const res = await apiFetch('/cart');
            if (res.status && Array.isArray(res.data)) {
                const total = res.data.reduce((acc: number, item: any) => acc + (item.quantity || 1), 0);
                setCount(total);
            }
        } catch (error) {
            console.error('[CART_FETCH_ERROR]', error);
        } finally {
            setLoading(false);
        }
    };

    const updateCart = async (newCount?: number) => {
        if (typeof newCount === 'number') {
            setCount(newCount);
        } else {
            await fetchCart();
        }
    };

    useEffect(() => {
        // AbortController cancels the initial fetch if StrictMode unmounts before it completes
        const controller = new AbortController();

        const token = localStorage.getItem('token');
        if (!token) {
            setCount(0);
            setLoading(false);
        } else {
            apiFetch('/cart', { signal: controller.signal })
                .then(res => {
                    if (res?.status && Array.isArray(res.data)) {
                        setCount(res.data.reduce((acc: number, item: any) => acc + (item.quantity || 1), 0));
                    }
                })
                .catch((err) => { if (err?.name !== 'AbortError') console.error('[CART_FETCH_ERROR]', err); })
                .finally(() => { if (!controller.signal.aborted) setLoading(false); });
        }

        const handleUpdate = () => fetchCart();
        window.addEventListener('cart:updated', handleUpdate);
        return () => {
            controller.abort();
            window.removeEventListener('cart:updated', handleUpdate);
        };
    }, []);

    return (
        <CartContext.Provider value={{ count, updateCart, loading }}>
            {children}
        </CartContext.Provider>
    );
}
