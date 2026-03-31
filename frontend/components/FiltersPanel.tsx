'use client';

import Image from 'next/image';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface FiltersPanelProps {
    categories: Category[];
    selectedCategory?: string;
    onCategoryChange: (categorySlug: string | null) => void;
}

export default function FiltersPanel({ categories, selectedCategory, onCategoryChange }: FiltersPanelProps) {
    return (
        <div className="col-xxl-2 col-lg-3 shop-sidebar">
            <div className="mb-30px">
                <span className="fw-600 fs-17 text-white d-block mb-10px">Filter by categories</span>
                <ul className="fs-15 shop-filter category-filter">
                    <li>
                        <a href="#" onClick={(e) => { e.preventDefault(); onCategoryChange(null); }}>
                            <span className="product-cb product-category-cb"></span>
                            All Categories
                        </a>
                    </li>
                    {categories.map((category) => (
                        <li key={category.id}>
                            <a href="#" onClick={(e) => { e.preventDefault(); onCategoryChange(category.slug); }}>
                                <span className="product-cb product-category-cb"></span>
                                {category.name}
                            </a>
                        </li>
                    ))}
                </ul>
            </div>
            {/* Static filters for now - can be made dynamic later */}
            <div className="mb-30px">
                <span className="fw-600 fs-17 text-white d-block mb-10px">Filter by color</span>
                <ul className="fs-15 shop-filter color-filter">
                    <li><a href="/shop#"><span className="product-cb product-color-cb" style={{ backgroundColor: '#232323' }}></span>Black</a><span className="item-qty">05</span></li>
                    <li><a href="/shop#"><span className="product-cb product-color-cb" style={{ backgroundColor: '#8E412E' }}></span>Chestnut</a><span className="item-qty">24</span></li>
                    <li><a href="/shop#"><span className="product-cb product-color-cb" style={{ backgroundColor: '#E0A699' }}></span>Brown</a><span className="item-qty">32</span></li>
                    <li><a href="/shop#"><span className="product-cb product-color-cb" style={{ backgroundColor: '#E0A699' }}></span>Pastel pink</a><span className="item-qty">22</span></li>
                    <li><a href="/shop#"><span className="product-cb product-color-cb" style={{ backgroundColor: '#9DA693' }}></span>Litchen green</a><span className="item-qty">09</span></li>
                    <li><a href="/shop#"><span className="product-cb product-color-cb" style={{ backgroundColor: '#E7C06D' }}></span>Yellow</a><span className="item-qty">06</span></li>
                </ul>
            </div>
            <div className="mb-30px">
                <span className="fw-600 fs-17 text-white d-block mb-10px">Filter by fabric</span>
                <ul className="fs-15 shop-filter fabric-filter">
                    <li><a href="/shop#"><span className="product-cb product-fabric-cb"><Image alt="" src="/images/demo-decor-store-product-listing-fabric-01.jpg" width={20} height={20} /></span>Polyolefin</a><span className="item-qty">08</span></li>
                    <li><a href="/shop#"><span className="product-cb product-fabric-cb"><Image alt="" src="/images/demo-decor-store-product-listing-fabric-02.jpg" width={20} height={20} /></span>Jute fabric</a><span className="item-qty">03</span></li>
                    <li><a href="/shop#"><span className="product-cb product-fabric-cb"><Image alt="" src="/images/demo-decor-store-product-listing-fabric-03.jpg" width={20} height={20} /></span>Crepe fabric</a><span className="item-qty">20</span></li>
                    <li><a href="/shop#"><span className="product-cb product-fabric-cb"><Image alt="" src="/images/demo-decor-store-product-listing-fabric-04.jpg" width={20} height={20} /></span>Wollen fabric</a><span className="item-qty">08</span></li>
                </ul>
            </div>
            <div className="mb-30px">
                <span className="fw-600 fs-17 text-white d-block mb-10px">Filter by price</span>
                <ul className="fs-15 shop-filter price-filter">
                    <li><a href="/shop#"><span className="product-cb product-category-cb"></span>Under $25</a><span className="item-qty">08</span></li>
                    <li><a href="/shop#"><span className="product-cb product-category-cb"></span>$25 to $50</a><span className="item-qty">05</span></li>
                    <li><a href="/shop#"><span className="product-cb product-category-cb"></span>$50 to $100</a><span className="item-qty">25</span></li>
                    <li><a href="/shop#"><span className="product-cb product-category-cb"></span>$100 to $200</a><span className="item-qty">18</span></li>
                    <li><a href="/shop#"><span className="product-cb product-category-cb"></span>$200 &amp; Above</a><span className="item-qty">36</span></li>
                </ul>
            </div>
        </div>
    );
}
