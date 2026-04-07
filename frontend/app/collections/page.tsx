'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { usePathname, useRouter, useSearchParams } from 'next/navigation';
import { apiFetch } from '@/lib/api';


export default function Collections() {
    const router = useRouter();
    const pathname = usePathname();
    const searchParams = useSearchParams();
    const initialPage = Number.parseInt(searchParams.get('page') || '1', 10);
    const [categories, setCategories] = useState<any[]>([]);
    const [currentPage, setCurrentPage] = useState(Number.isNaN(initialPage) ? 1 : Math.max(initialPage, 1));
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const nextPage = Number.parseInt(searchParams.get('page') || '1', 10);
        setCurrentPage(Number.isNaN(nextPage) ? 1 : Math.max(nextPage, 1));
    }, [searchParams]);

    useEffect(() => {
        const fetchCategories = async () => {
            setLoading(true);
            try {
                const response = await apiFetch(`/frontend/product-category?paginate=1&per_page=12&page=${currentPage}`);
                const list = Array.isArray(response?.data) ? response.data : [];
                const meta = response?.meta || {};

                setCategories(list);
                setCurrentPage(meta.current_page || currentPage);
                setLastPage(meta.last_page || 1);
            } catch (error) {
                console.error('Error fetching categories:', error);
                setCategories([]);
                setLastPage(1);
            } finally {
                setLoading(false);
            }
        };
        fetchCategories();
    }, [currentPage]);

    const changePage = (page: number) => {
        const nextPage = Math.max(1, Math.min(page, lastPage || 1));
        setCurrentPage(nextPage);

        const nextParams = new URLSearchParams(searchParams.toString());
        if (nextPage <= 1) {
            nextParams.delete('page');
        } else {
            nextParams.set('page', String(nextPage));
        }

        const nextUrl = nextParams.toString() ? `${pathname}?${nextParams.toString()}` : pathname;
        router.replace(nextUrl, { scroll: false });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const visiblePages = (() => {
        if (lastPage <= 1) return [];
        const start = Math.max(1, currentPage - 2);
        const end = Math.min(lastPage, currentPage + 2);
        const pages: number[] = [];
        for (let page = start; page <= end; page += 1) {
            pages.push(page);
        }
        return pages;
    })();

    if (loading) {
        return <div className="min-h-screen bg-black flex items-center justify-center text-white">Loading...</div>;
    }

    return (
        <main className="no-layout-pad" style={{ paddingTop: '50px' }}>

            {/*  start page title  */}
            <section className="page-title-center-alignment cover-background bg-dark-gray" style={{ backgroundImage: 'linear-gradient(rgba(10, 10, 10, 0.7), rgba(10, 10, 10, 0.7)), url(\'images/new/bg/bg1.webp\')', paddingBottom: '70px' }}>
                <div className="container">
                    <div className="row">
                        <div className="col-12 text-center position-relative page-title-extra-large">
                            <h1 className="alt-font d-inline-block fw-700 ls-minus-05px text-white mb-10px">Collections</h1>
                        </div>
                        <div className="col-12 breadcrumb breadcrumb-style-01 d-flex justify-content-center">
                            <ul>
                                <li><a className="text-white" href="/">Home</a></li>
                                <li className="text-white opacity-7">Collections</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
            {/*  end page title  */}
            {/*  start section  */}
            <section className="position-relative">
                <div className="container">
                    <div className="row row-cols-1 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 justify-content-center align-items-center">
                        {categories.map((category) => (
                            <div key={category.id} className="col categories-style-01 text-center mb-50px xs-mb-35px">
                                <div className="categories-box">
                                    <div className="icon-box position-relative mb-10px">
                                        <a href={`/shop?category=${category.slug}`}>
                                            <Image
                                                className="category-thumb"
                                                alt={category.name}
                                                src={category.thumb || category.cover || '/images/demo-decor-store-icon-01.png'}
                                                width={130}
                                                height={130}
                                                unoptimized
                                                style={{ objectFit: 'cover', borderRadius: '50%' }}
                                            />
                                        </a>
                                        <div className="count-circle d-flex align-items-center justify-content-center w-35px h-35px bg-base-color text-white rounded-circle fw-600 fs-12">
                                            {category.products_count || '00'}
                                        </div>
                                    </div>
                                    <a className="fw-600 fs-17 text-white text-white-hover" href={`/shop?category=${category.slug}`}>
                                        {category.name}
                                    </a>
                                </div>
                            </div>
                        ))}
                    </div>

                    {lastPage > 1 && (
                        <nav aria-label="Collections pagination" className="d-flex justify-content-center mt-4 mb-5">
                            <ul className="pagination pagination-style-01 m-0">
                                <li className={`page-item ${currentPage <= 1 ? 'disabled' : ''}`}>
                                    <button className="page-link bg-transparent border-0" onClick={() => changePage(currentPage - 1)} disabled={currentPage <= 1}>
                                        Prev
                                    </button>
                                </li>
                                {visiblePages.map((page) => (
                                    <li key={page} className={`page-item ${page === currentPage ? 'active' : ''}`}>
                                        <button className="page-link bg-transparent border-0" onClick={() => changePage(page)}>
                                            {page}
                                        </button>
                                    </li>
                                ))}
                                <li className={`page-item ${currentPage >= lastPage ? 'disabled' : ''}`}>
                                    <button className="page-link bg-transparent border-0" onClick={() => changePage(currentPage + 1)} disabled={currentPage >= lastPage}>
                                        Next
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    )}

                </div>
            </section>
            {/*  end section  */}
            {/*  start cookie message  */}
            <div className="cookie-message bg-dark-gray border-radius-8px" id="cookies-model">
                <div className="cookie-description fs-14 text-white mb-20px lh-22">We use cookies to enhance your browsing
                    experience, serve personalized ads or content, and analyze our traffic. By clicking "Allow cookies" you
                    consent to our use of cookies. </div>
                <div className="cookie-btn">
                    <a aria-label="btn" className="btn btn-transparent-white border-1 border-color-transparent-white-light btn-very-small btn-switch-text btn-rounded w-100 mb-15px" href="/collections#">
                        <span>
                            <span className="btn-double-text" data-text="Cookie policy">Cookie policy</span>
                        </span>
                    </a>
                    <a aria-label="text" className="btn btn-white btn-very-small btn-switch-text btn-box-shadow accept_cookies_btn btn-rounded w-100" data-accept-btn="" href="/collections#">
                        <span>
                            <span className="btn-double-text" data-text="Allow cookies">Allow cookies</span>
                        </span>
                    </a>
                </div>
            </div>
            {/*  end cookie message  */}
            {/*  start scroll progress  */}
            <div className="scroll-progress d-none d-xxl-block">
                <a aria-label="scroll" className="scroll-top" href="/collections#">
                    <span className="scroll-text">Scroll</span><span className="scroll-line"><span className="scroll-point"></span></span>
                </a>
            </div>
            {/*  end scroll progress  */}
            {/*  javascript libraries  */}
            {/*  Mega Menu Alignment Fix  */}
            {/*  Behome Premium Animation System  */}

        </main>
    );
}
