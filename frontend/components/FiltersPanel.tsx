'use client';

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
        </div>
    );
}
