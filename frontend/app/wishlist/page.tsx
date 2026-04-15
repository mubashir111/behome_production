'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { apiFetch } from '@/lib/api';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useToast } from '@/components/ToastProvider';
import { useCart } from '@/components/CartProvider';

export default function Wishlist() {
    const router = useRouter();
    const { showToast } = useToast();
    const { updateCart } = useCart();
    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const [user, setUser] = useState<any>(null);
    const [wishlistItems, setWishlistItems] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [addingToCart, setAddingToCart] = useState<Set<number>>(new Set());

    useEffect(() => {
        const token = localStorage.getItem('token');
        const storedUser = localStorage.getItem('user');
        if (token) {
            setIsLoggedIn(true);
            if (storedUser) {
                try { setUser(JSON.parse(storedUser)); } catch {}
            }
            fetchWishlist();
        } else {
            setLoading(false);
        }
    }, []);

    const fetchWishlist = async () => {
        try {
            const response = await apiFetch('/frontend/product/wishlist-products');
            const items = Array.isArray(response)
                ? response
                : Array.isArray(response?.data)
                    ? response.data
                    : Array.isArray(response?.data?.data)
                        ? response.data.data
                        : [];
            setWishlistItems(items);
        } catch (err) {
            console.error('Failed to fetch wishlist:', err);
        } finally {
            setLoading(false);
        }
    };

    const addToCart = async (product: any) => {
        setAddingToCart(prev => new Set(prev).add(product.id));
        try {
            const res = await apiFetch('/cart', {
                method: 'POST',
                body: JSON.stringify({ product_id: product.id, quantity: 1, variation_id: null }),
            });
            if (res.status) {
                showToast(`${product.name} added to cart`, 'success');
                updateCart();
            } else {
                showToast(res.message || 'Failed to add to cart', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'An error occurred', 'error');
        } finally {
            setAddingToCart(prev => { const s = new Set(prev); s.delete(product.id); return s; });
        }
    };

    const removeFromWishlist = async (productId: number) => {
        setWishlistItems(prev => prev.filter(item => item.id !== productId));
        window.dispatchEvent(new CustomEvent('wishlist:updated', { detail: { productId, inWishlist: false } }));
        try {
            await apiFetch('/frontend/wishlist/toggle', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, toggle: false }),
            });
        } catch {
            fetchWishlist();
        }
    };

    const handleLogout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        router.push('/account');
    };

    return (
        <main className="no-layout-pad page-top-100">
            <section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
            <div className="container-fluid">
                <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                    <ul>
                        <li><a href="/" className="breadcrumb-link">Home</a></li>
                        <li>Wishlist</li>
                    </ul>
                </div>
            </div>
            </section>
            <section className="page-shell page-shell-tight">
                <div className="container">
                    <div className="row">

                        {/* ── Sidebar ────────────────────────────────────── */}
                        <div className="col-lg-3 col-md-4 md-mb-40px">
                            <div className="bg-dark-gray border-radius-6px box-shadow-extra-large border border-color-extra-medium-gray ui-panel ui-panel-lg">
                                <div className="text-center ui-panel-header">
                                    <div className="d-inline-flex align-items-center justify-content-center bg-base-color rounded-circle mb-10px" style={{ width: 60, height: 60, fontSize: 24 }}>
                                        <i className="feather icon-feather-user text-white"></i>
                                    </div>
                                    {user && (
                                        <>
                                            <p className="text-white fw-600 mb-0 fs-15">{user.name}</p>
                                            <p className="text-white opacity-5 fs-13 mb-0">{user.email}</p>
                                        </>
                                    )}
                                </div>
                                <ul className="list-style-01 account-sidebar">
                                    <li className="mb-10px">
                                        <Link href="/account?tab=profile"><i className="feather icon-feather-user me-10px"></i>Profile</Link>
                                    </li>
                                    <li className="mb-10px">
                                        <Link href="/account?tab=orders"><i className="feather icon-feather-package me-10px"></i>Orders</Link>
                                    </li>
                                    <li className="mb-10px">
                                        <Link href="/account?tab=addresses"><i className="feather icon-feather-map-pin me-10px"></i>Addresses</Link>
                                    </li>
                                    <li className="mb-10px">
                                        <Link href="/account?tab=security"><i className="feather icon-feather-lock me-10px"></i>Security</Link>
                                    </li>
                                    <li className="mb-10px active-link">
                                        <Link href="/wishlist"><i className="feather icon-feather-heart me-10px"></i>Wishlist</Link>
                                    </li>
                                    {isLoggedIn && (
                                        <li className="mt-10px pt-10px" style={{ borderTop: '1px solid rgba(255,255,255,0.1)' }}>
                                            <button onClick={handleLogout} className="text-white"><i className="feather icon-feather-log-out me-10px"></i>Logout</button>
                                        </li>
                                    )}
                                </ul>
                            </div>
                        </div>

                        {/* ── Wishlist Content ────────────────────────────── */}
                        <div className="col-lg-9 col-md-8 ui-content-offset">

                            {/* Header */}
                            <div className="d-flex align-items-center justify-content-between mb-30px">
                                <div>
                                    <span className="ui-page-kicker d-block mb-5px">Saved Items</span>
                                    <h4 className="alt-font fw-700 text-white ls-minus-1px mb-0">My Wishlist</h4>
                                </div>
                                {!loading && isLoggedIn && (
                                    <span className="text-white opacity-5 fs-14">
                                        {wishlistItems.length} item{wishlistItems.length !== 1 ? 's' : ''} saved
                                    </span>
                                )}
                            </div>

                            {/* Loading */}
                            {loading && (
                                <div className="ui-panel ui-empty-state">
                                    <div className="spinner-border text-white" role="status">
                                        <span className="visually-hidden">Loading...</span>
                                    </div>
                                    <p className="text-white mt-20px opacity-7">Loading your wishlist...</p>
                                </div>
                            )}

                            {/* Not logged in */}
                            {!loading && !isLoggedIn && (
                                <div className="ui-panel ui-panel-lg ui-empty-state">
                                    <i className="feather icon-feather-heart text-base-color" style={{ fontSize: 60 }}></i>
                                    <h5 className="alt-font fw-600 text-white mt-30px mb-15px">Save your favourites</h5>
                                    <p className="text-white opacity-7 mb-30px">Sign in to access your wishlist and keep track of items you love.</p>
                                    <Link href="/account" className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow">
                                        <span><span className="btn-double-text" data-text="Sign In">Sign In</span></span>
                                    </Link>
                                </div>
                            )}

                            {/* Empty */}
                            {!loading && isLoggedIn && wishlistItems.length === 0 && (
                                <div className="ui-panel ui-panel-lg ui-empty-state">
                                    <i className="feather icon-feather-heart text-base-color" style={{ fontSize: 60 }}></i>
                                    <h5 className="alt-font fw-600 text-white mt-30px mb-15px">Your wishlist is empty</h5>
                                    <p className="text-white opacity-7 mb-30px">Explore our collection and save items you love.</p>
                                    <Link href="/shop" className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow">
                                        <span><span className="btn-double-text" data-text="Explore Shop">Explore Shop</span></span>
                                    </Link>
                                </div>
                            )}

                            {/* Grid */}
                            {!loading && isLoggedIn && wishlistItems.length > 0 && (
                                <div className="row g-4">
                                    {wishlistItems.map((product: any) => (
                                        <div key={product.id} className="col-xl-4 col-md-6 col-sm-6 col-12">
                                            <div className="shop-box pb-25px position-relative">
                                                <button
                                                    onClick={() => removeFromWishlist(product.id)}
                                                    className="position-absolute bg-dark-gray text-white border-0 rounded-circle d-flex align-items-center justify-content-center box-shadow-medium-bottom"
                                                    style={{ top: 12, right: 12, width: 36, height: 36, zIndex: 10, cursor: 'pointer' }}
                                                    title="Remove from wishlist"
                                                >
                                                    <i className="feather icon-feather-x fs-14"></i>
                                                </button>
                                                <div className="shop-image">
                                                    <a href={`/product/${product.slug}`}>
                                                        <Image
                                                            alt={product.name}
                                                            src={product.cover || '/images/demo-decor-store-product-01.jpg'}
                                                            width={640}
                                                            height={720}
                                                            unoptimized
                                                            style={{ width: '100%', borderRadius: 8, height: 'auto' }}
                                                        />
                                                        <div className="product-overlay bg-gradient-extra-midium-gray-transparent"></div>
                                                    </a>
                                                    <div className="shop-hover d-flex justify-content-center">
                                                        <button
                                                            onClick={() => addToCart(product)}
                                                            disabled={addingToCart.has(product.id)}
                                                            title="Add to cart"
                                                            className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom border-0"
                                                            style={{ cursor: addingToCart.has(product.id) ? 'not-allowed' : 'pointer', opacity: addingToCart.has(product.id) ? 0.6 : 1 }}
                                                        >
                                                            <i className={`feather ${addingToCart.has(product.id) ? 'icon-feather-loader' : 'icon-feather-shopping-bag'} fs-15`}></i>
                                                        </button>
                                                        <a className="bg-dark-gray w-45px h-45px text-white d-flex flex-column align-items-center justify-content-center rounded-circle ms-5px me-5px box-shadow-medium-bottom"
                                                            href={`/product/${product.slug}`} title="View product">
                                                            <i className="feather icon-feather-eye fs-15"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div className="shop-footer text-center pt-20px">
                                                    <a className="text-white fs-17 fw-600 d-flex justify-content-center align-items-center mb-5px"
                                                        style={{ minHeight: 50 }}
                                                        href={`/product/${product.slug}`}>{product.name}</a>
                                                    <div className="fw-500 fs-15 lh-normal">
                                                        {product.is_offer ? (
                                                            <><del className="opacity-6 me-10px">{product.currency_price}</del><span className="text-base-color">{product.discounted_price}</span></>
                                                        ) : (
                                                            <span>{product.currency_price}</span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}
