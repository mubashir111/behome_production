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
        fetchCart();

        // Listen for legacy events or manual triggers
        const handleUpdate = () => fetchCart();
        window.addEventListener('cart:updated', handleUpdate);
        return () => window.removeEventListener('cart:updated', handleUpdate);
    }, []);

    return (
        <CartContext.Provider value={{ count, updateCart, loading }}>
            {children}
        </CartContext.Provider>
    );
}
