'use client';

import ProductCard from './ProductCard';

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
    stock?: number;
}

interface ProductGridProps {
    products: Product[];
    showCategory?: boolean;
    onAddToCart?: (product: Product) => void;
}

export default function ProductGrid({ products, showCategory = false, onAddToCart }: ProductGridProps) {
    return (
        <div className="row row-cols-1 row-cols-xl-4 row-cols-lg-3 row-cols-md-2">
            {products.map((product) => (
                <div key={product.id} className="col mb-45px">
                    <ProductCard
                        product={product}
                        showCategory={showCategory}
                        onAddToCart={onAddToCart}
                    />
                </div>
            ))}
        </div>
    );
}