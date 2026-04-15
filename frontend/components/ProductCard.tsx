'use client';

import { useState } from 'react';
import { apiFetch } from '@/lib/api';
import WishlistButton from './WishlistButton';
import { useToast } from './ToastProvider';
import { useAuthModal } from '@/context/AuthModalContext';
import QuickViewModal from './QuickViewModal';

interface Product {
    id: number;
    name: string;
    slug: string;
    cover?: string;
    image?: string;
    currency_price: string;
    discounted_price?: string;
    is_offer: boolean;
    category?: { name: string };
    wishlist?: boolean;
    rating_star?: number;
    rating_star_count?: number;
}

interface ProductCardProps {
    product: Product;
    showCategory?: boolean;
    onAddToCart?: (product: Product) => void;
}

export default function ProductCard({ product, showCategory = false, onAddToCart }: ProductCardProps) {
    const { showToast, showCartToast } = useToast();
    const { openAuthModal } = useAuthModal();
    const [quickViewSlug, setQuickViewSlug] = useState<string | null>(null);

    const handleAddToCart = async () => {
        if (onAddToCart) {
            onAddToCart(product);
            return;
        }

        try {
            const token = localStorage.getItem('token');
            if (!token) {
                openAuthModal(() => handleAddToCart());
                return;
            }

            const response = await apiFetch('/cart', {
                method: 'POST',
                body: JSON.stringify({
                    product_id: product.id,
                    quantity: 1,
                    variation_id: null
                }),
            });

            if (response.status) {
                const img = product.image || product.cover || '';
                showCartToast({
                    name: product.name,
                    image: img.includes('/images/default/') ? undefined : img,
                    price: product.discounted_price || product.currency_price
                });
            } else {
                showToast(response.message || 'Failed to add to cart', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'An error occurred', 'error');
        }
    };

    const imageSrc = (product.cover || product.image || '').trim() || "/images/demo-decor-store-product-01.jpg";

    return (
        <>
        <div className="shop-box pb-25px">
            <div className="shop-image">
                <a href={`/product/${product.slug}`}>
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img alt={product.name} src={imageSrc} style={{ width: '100%', height: 'auto' }}
                        onError={e => { (e.currentTarget as HTMLImageElement).src = '/images/demo-decor-store-product-01.jpg'; }} />
                    {product.is_offer && <span className="lable hot">Offer</span>}
                    <div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
                </a>
                <div className="shop-hover d-flex justify-content-center">
                    <WishlistButton
                        productId={product.id}
                        initialInWishlist={Boolean(product.wishlist)}
                        className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0"
                        onRequireAuth={() => openAuthModal()}
                        onMessage={(msg, type) => showToast(msg, type)}
                    />
                    <button className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0"
                        onClick={handleAddToCart} title="Add to cart">
                        <i className="feather icon-feather-shopping-bag fs-15"></i>
                    </button>
                    <button className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0"
                        onClick={() => setQuickViewSlug(product.slug)} title="Quick view">
                        <i className="feather icon-feather-eye fs-15"></i>
                    </button>
                </div>
            </div>
            <div className="shop-footer pt-20px text-center">
                {showCategory && product.category && (
                    <span className="fw-500 text-white d-block mb-5px">{product.category.name}</span>
                )}
                <a className="text-white fs-17 fw-600" href={`/product/${product.slug}`}>{product.name}</a>
                {product.rating_star_count ? (
                    <div className="d-flex align-items-center justify-content-center gap-1 mt-5px mb-3px">
                        {[1,2,3,4,5].map(i => (
                            <i key={i} className={`bi ${i <= Math.round(product.rating_star ?? 0) ? 'bi-star-fill' : 'bi-star'}`}
                                style={{ fontSize: 11, color: i <= Math.round(product.rating_star ?? 0) ? '#f59e0b' : 'rgba(255,255,255,0.2)' }} />
                        ))}
                        <span className="fs-11 text-white ms-3px" style={{ opacity: 0.5 }}>({product.rating_star_count})</span>
                    </div>
                ) : null}
                <div className="fw-500 fs-15 lh-normal">
                    {product.is_offer ? (
                        <>
                            <del className="me-5px">{product.currency_price}</del>
                            <span>{product.discounted_price}</span>
                        </>
                    ) : (
                        <span>{product.currency_price}</span>
                    )}
                </div>
            </div>
        </div>

        {quickViewSlug && (
            <QuickViewModal slug={quickViewSlug} onClose={() => setQuickViewSlug(null)} />
        )}
    </>
    );
}
