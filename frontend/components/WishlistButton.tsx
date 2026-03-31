'use client';

import { useEffect, useState } from 'react';
import { apiFetch } from '@/lib/api';

type WishlistUpdatedDetail = {
    productId: number;
    inWishlist: boolean;
};

interface WishlistButtonProps {
    productId: number;
    initialInWishlist?: boolean;
    className?: string;
    iconClassName?: string;
    activeIconClassName?: string;
    titleAdd?: string;
    titleRemove?: string;
    onRequireAuth?: () => void;
    onMessage?: (message: string, type: 'success' | 'error') => void;
}

export default function WishlistButton({
    productId,
    initialInWishlist = false,
    className = '',
    iconClassName = 'feather icon-feather-heart fs-15',
    activeIconClassName = 'bi bi-heart-fill fs-15',
    titleAdd = 'Add to wishlist',
    titleRemove = 'Remove from wishlist',
    onRequireAuth,
    onMessage,
}: WishlistButtonProps) {
    const [inWishlist, setInWishlist] = useState(initialInWishlist);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setInWishlist(initialInWishlist);
    }, [initialInWishlist]);

    useEffect(() => {
        const handleWishlistUpdated = (event: Event) => {
            const customEvent = event as CustomEvent<WishlistUpdatedDetail>;
            if (customEvent.detail?.productId === productId) {
                setInWishlist(customEvent.detail.inWishlist);
            }
        };

        window.addEventListener('wishlist:updated', handleWishlistUpdated);
        return () => window.removeEventListener('wishlist:updated', handleWishlistUpdated);
    }, [productId]);

    const dispatchWishlistUpdate = (nextState: boolean) => {
        window.dispatchEvent(new CustomEvent<WishlistUpdatedDetail>('wishlist:updated', {
            detail: { productId, inWishlist: nextState },
        }));
    };

    const handleClick = async () => {
        const token = localStorage.getItem('token');
        if (!token) {
            if (onRequireAuth) {
                onRequireAuth();
            } else {
                window.location.href = '/account';
            }
            return;
        }

        const previousState = inWishlist;
        const nextState = !previousState;

        setLoading(true);
        setInWishlist(nextState);
        dispatchWishlistUpdate(nextState);

        try {
            await apiFetch('/frontend/wishlist/toggle', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, toggle: nextState }),
            });
            onMessage?.(nextState ? 'Added to wishlist' : 'Removed from wishlist', 'success');
        } catch (err: any) {
            setInWishlist(previousState);
            dispatchWishlistUpdate(previousState);
            onMessage?.(err.message || 'Failed to update wishlist', 'error');
        } finally {
            setLoading(false);
        }
    };

    return (
        <button
            type="button"
            className={className}
            onClick={handleClick}
            title={inWishlist ? titleRemove : titleAdd}
            aria-pressed={inWishlist}
            disabled={loading}
        >
            <i className={inWishlist ? activeIconClassName : iconClassName} />
        </button>
    );
}
