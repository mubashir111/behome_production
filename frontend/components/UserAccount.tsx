'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { apiFetch } from '@/lib/api';

export default function UserAccount() {
    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const [user, setUser] = useState<any>(null);
    const [isOpen, setIsOpen] = useState(false);

    // Helper to format name to Proper Case
    const formatName = (name: string) => {
        if (!name) return '';
        return name.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    };

    useEffect(() => {
        const storedUser = localStorage.getItem('user');
        const token = localStorage.getItem('token');
        if (storedUser && token) {
            setIsLoggedIn(true);
            try {
                setUser(JSON.parse(storedUser));
            } catch (e) {
                console.error("Failed to parse user from localStorage", e);
            }
        }
    }, []);

    const handleLogout = async () => {
        try {
            await apiFetch('/auth/logout', { method: 'POST' });
        } catch {
            // ignore — clear client side regardless
        }
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setIsLoggedIn(false);
        setUser(null);
        window.location.href = '/account';
    };

    if (!isLoggedIn) {
        return (
            <div className="position-relative d-flex align-items-center pe-3 cursor-pointer">
                <Link className="d-flex align-items-center justify-content-center w-100 h-100 text-nowrap" href="/account">
                    <i className="feather icon-feather-user text-white fs-16 me-2"></i>
                    <span className="text-white fs-13 fw-600 transition-3s hover-text-base-color text-nowrap">Login</span>
                </Link>
            </div>
        );
    }

    return (
        <div className="position-relative d-flex align-items-center glass-icon-box px-3 user-dropdown-container"
             onMouseEnter={() => setIsOpen(true)}
             onMouseLeave={() => setIsOpen(false)}
             style={{ position: 'relative !important' as any }}>
            
            <div className="d-flex align-items-center justify-content-center w-100 h-100 cursor-pointer py-2"
                style={{ gap: '8px' }}>
                <i className="feather icon-feather-user text-white fs-16"></i>
                <span className="text-white fs-13 fw-700 ls-05px d-none d-xl-inline-block text-nowrap">
                    {formatName(user?.name?.split(' ')[0]) || 'Account'}
                </span>
                <i className={`fa-solid fa-angle-down text-white fs-10 transition-3s ${isOpen ? 'rotate-180 text-base-color' : ''}`}></i>
            </div>

            {/* Premium Dropdown Menu */}
            <div className={`user-dropdown-menu shadow-xl border-radius-8px ${isOpen ? 'show-menu' : ''}`}>
                
                {/* Carrot/Pointer */}
                <div className="dropdown-pointer"></div>

                {/* Header */}
                <div className="dropdown-header-custom">
                    <div className="fs-11 text-uppercase fw-700 text-base-color ls-1px mb-1 opacity-7">Welcome Back</div>
                    <div className="fs-15 text-white fw-700">{formatName(user?.name)}</div>
                </div>

                {/* Items */}
                <div className="dropdown-body-custom py-2">
                    <Link href="/account?tab=profile" className="dropdown-item-custom">
                        <i className="feather icon-feather-user"></i>
                        <span>My Profile</span>
                    </Link>
                    <Link href="/account?tab=orders" className="dropdown-item-custom">
                        <i className="feather icon-feather-package"></i>
                        <span>Orders</span>
                    </Link>
                    <Link href="/wishlist" className="dropdown-item-custom border-bottom-transparent">
                        <i className="feather icon-feather-heart"></i>
                        <span>Wishlist</span>
                    </Link>
                    <div className="dropdown-divider-custom"></div>
                    <Link href="/account?tab=addresses" className="dropdown-item-custom">
                        <i className="feather icon-feather-map-pin"></i>
                        <span>Addresses</span>
                    </Link>
                    <Link href="/shop" className="dropdown-item-custom">
                        <i className="feather icon-feather-shopping-cart"></i>
                        <span>Return to Shop</span>
                    </Link>
                </div>

                {/* Logout Action */}
                <div className="dropdown-footer-custom">
                    <button onClick={handleLogout} className="logout-btn-custom">
                        <i className="feather icon-feather-log-out text-red"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>

            <style jsx>{`
                .user-dropdown-container {
                    z-index: 1000;
                    position: relative !important;
                }
                .user-dropdown-menu {
                    position: absolute;
                    top: 100%;
                    right: 0;
                    width: 250px;
                    background: #1a1a1a !important;
                    border: 1px solid rgba(255, 255, 255, 0.1) !important;
                    box-shadow: 0 20px 50px rgba(0,0,0,0.9), inset 0 0 20px rgba(255,255,255,0.02) !important;
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(10px);
                    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                    pointer-events: none;
                    display: block !important; /* Ensure it's reachable */
                }
                .user-dropdown-menu.show-menu {
                    opacity: 1 !important;
                    visibility: visible !important;
                    transform: translateY(0) !important;
                    pointer-events: all !important;
                }
                .dropdown-pointer {
                    position: absolute;
                    top: -6px;
                    right: 24px;
                    width: 12px;
                    height: 12px;
                    background: #1a1a1a;
                    border-left: 1px solid rgba(255, 255, 255, 0.1);
                    border-top: 1px solid rgba(255, 255, 255, 0.1);
                    transform: rotate(45deg);
                    z-index: -1;
                }
                .dropdown-header-custom {
                    padding: 22px 45px !important;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
                    background: rgba(255, 255, 255, 0.01) !important;
                }

                /* AGGRESSIVE GLOBAL OVERRIDES */
                :global(.dropdown-body-custom) {
                    display: flex !important;
                    flex-direction: column !important;
                    padding: 10px 0 !important;
                }
                :global(.dropdown-item-custom) {
                    display: flex !important;
                    align-items: center !important;
                    padding: 12px 45px !important;
                    text-decoration: none !important;
                    transition: all 0.2s ease !important;
                    gap: 14px !important;
                    color: #ffffff !important;
                    background: transparent !important;
                }
                :global(.dropdown-item-custom span) {
                    color: #ffffff !important;
                    font-size: 14px !important;
                    font-weight: 500 !important;
                    display: inline-block !important;
                }
                :global(.dropdown-item-custom i) {
                    font-size: 16px !important;
                    color: #d1b06b !important;
                    opacity: 1 !important;
                }
                :global(.dropdown-item-custom:hover) {
                    background: rgba(255, 255, 255, 0.08) !important;
                    padding-left: 52px !important;
                }
                :global(.dropdown-divider-custom) {
                    height: 1px !important;
                    background: rgba(255, 255, 255, 0.05) !important;
                    margin: 8px 0 !important;
                }
                :global(.dropdown-footer-custom) {
                    padding: 10px 0 !important;
                    border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
                }
                :global(.logout-btn-custom) {
                    display: flex !important;
                    align-items: center !important;
                    width: 100% !important;
                    padding: 14px 45px !important;
                    background: transparent !important;
                    border: none !important;
                    gap: 14px !important;
                    cursor: pointer !important;
                    color: #ffffff !important;
                }
                :global(.logout-btn-custom span) {
                    color: #ffffff !important;
                    font-size: 14px !important;
                    font-weight: 600 !important;
                }
                :global(.logout-btn-custom i) {
                    color: #ff5e5e !important;
                    font-size: 16px !important;
                }
                :global(.logout-btn-custom:hover) {
                    background: rgba(231, 76, 60, 0.12) !important;
                }
                :global(.logout-btn-custom:hover span) {
                    color: #ff5e5e !important;
                }

                .transition-3s {
                    transition: all 0.3s ease-in-out;
                }
                .rotate-180 {
                    transform: rotate(180deg);
                }
                .cursor-pointer {
                    cursor: pointer;
                }
                .text-base-color {
                    color: #d1b06b;
                }
                .ls-1px {
                    letter-spacing: 1px;
                }
            `}</style>
        </div>
    );
}
