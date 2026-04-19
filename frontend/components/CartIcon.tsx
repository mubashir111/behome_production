'use client';

import { useCart } from './CartProvider';

export default function CartIcon() {
    const { count } = useCart();

    if (count === 0) return null;

    return (
        <span
            className="cart-count alt-font bg-base-color text-white d-flex align-items-center justify-content-center rounded-circle position-absolute"
            style={{ width: '16px', height: '16px', fontSize: '10px', top: '-6px', insetInlineEnd: '-6px', fontWeight: '700' }}
        >
            {count > 99 ? '99+' : count}
        </span>
    );
}
