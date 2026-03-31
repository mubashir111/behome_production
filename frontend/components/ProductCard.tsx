'use client';

import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import WishlistButton from './WishlistButton';
import { useToast } from './ToastProvider';

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
}

interface ProductCardProps {
    product: Product;
    showCategory?: boolean;
    onAddToCart?: (product: Product) => void;
}

export default function ProductCard({ product, showCategory = false, onAddToCart }: ProductCardProps) {
    const { showToast } = useToast();

    const handleAddToCart = async () => {
        if (onAddToCart) {
            onAddToCart(product);
            return;
        }

        try {
            const token = localStorage.getItem('token');
            if (!token) {
                showToast('Please login to add items to cart', 'error');
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
                showToast(`Added ${product.name} to cart!`, 'success');
            } else {
                showToast(response.message || 'Failed to add to cart', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'An error occurred', 'error');
        }
    };

    const imageSrc = product.cover || product.image || "/images/demo-decor-store-product-01.jpg";

    return (
        <div className="shop-box pb-25px">
            <div className="shop-image">
                <a href={`/product/${product.slug}`}>
                    <Image alt={product.name} src={imageSrc} width={640} height={720} unoptimized style={{ width: '100%', height: 'auto' }} />
                    {product.is_offer && <span className="lable hot">Offer</span>}
                    <div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
                </a>
                <div className="shop-hover d-flex justify-content-center">
                    <WishlistButton
                        productId={product.id}
                        initialInWishlist={Boolean(product.wishlist)}
                        className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0"
                        onRequireAuth={() => showToast('Please login to save items to wishlist', 'error')}
                        onMessage={(msg, type) => showToast(msg, type)}
                    />
                    <button className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0"
                        onClick={handleAddToCart} title="Add to cart">
                        <i className="feather icon-feather-shopping-bag fs-15"></i>
                    </button>
                    <a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom"
                        data-bs-placement="top" data-bs-toggle="tooltip"
                        href={`/product/${product.slug}`} title="Quick view">
                        <i className="feather icon-feather-eye fs-15"></i>
                    </a>
                </div>
            </div>
            <div className="shop-footer text-center pt-20px">
                {showCategory && product.category && (
                    <span className="fw-500 text-white d-block mb-5px">{product.category.name}</span>
                )}
                <a className="text-white fs-17 fw-600" href={`/product/${product.slug}`}>{product.name}</a>
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
    );
}
