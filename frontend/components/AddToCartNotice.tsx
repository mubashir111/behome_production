'use client';

import React from 'react';
import Link from 'next/link';
import Image from 'next/image';

interface AddToCartNoticeProps {
    product: {
        name: string;
        image?: string;
        price?: string | number;
    };
    onClose: () => void;
}

export default function AddToCartNotice({ product, onClose }: AddToCartNoticeProps) {
    return (
        <div 
            className="add-to-cart-notice animate__animated animate__fadeInRight"
            style={{
                width: '380px',
                background: 'rgba(23, 23, 23, 0.85)',
                backdropFilter: 'blur(20px)',
                WebkitBackdropFilter: 'blur(20px)',
                borderRadius: '16px',
                border: '1px solid rgba(255, 255, 255, 0.12)',
                boxShadow: '0 20px 40px rgba(0, 0, 0, 0.4)',
                padding: '20px',
                pointerEvents: 'auto',
                display: 'flex',
                flexDirection: 'column',
                gap: '16px',
            }}
        >
            {/* Header */}
            <div className="d-flex align-items-center justify-content-between">
                <div className="d-flex align-items-center gap-2">
                    <div className="bg-success rounded-circle d-flex align-items-center justify-content-center" style={{ width: '20px', height: '20px' }}>
                        <i className="bi bi-check-lg text-white" style={{ fontSize: '12px' }}></i>
                    </div>
                    <span className="text-white fs-13 fw-700 text-uppercase ls-1px">Added to Bag</span>
                </div>
                <button 
                    onClick={onClose}
                    className="border-0 bg-transparent text-white opacity-4 hover-opacity-100 p-0"
                >
                    <i className="bi bi-x-lg fs-14"></i>
                </button>
            </div>

            {/* Product Details */}
            <div className="d-flex gap-15px align-items-center">
                <div 
                    className="rounded-8px overflow-hidden bg-white-transparent-1 flex-shrink-0"
                    style={{ width: '70px', height: '70px', border: '1px solid rgba(255, 255, 255, 0.05)' }}
                >
                    {product.image ? (
                        <img 
                            src={product.image} 
                            alt={product.name}
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                        />
                    ) : (
                        <div className="w-100 h-100 d-flex align-items-center justify-content-center">
                            <i className="feather icon-feather-image text-white opacity-2 fs-20"></i>
                        </div>
                    )}
                </div>
                <div className="flex-grow-1 overflow-hidden">
                    <h4 className="text-white fs-15 fw-600 mb-2px text-truncate">{product.name}</h4>
                    {product.price && (
                        <span className="text-white opacity-5 fs-13 fw-500">{product.price}</span>
                    )}
                </div>
            </div>

            {/* Actions */}
            <div className="d-flex gap-10px pt-5px">
                <Link 
                    href="/cart" 
                    onClick={onClose}
                    className="btn btn-very-small btn-transparent-white-light text-white flex-grow-1 border-radius-8px fw-700 py-10px"
                    style={{ fontSize: '11px', textTransform: 'uppercase', letterSpacing: '1px' }}
                >
                    View Cart
                </Link>
                <Link 
                    href="/checkout" 
                    onClick={onClose}
                    className="btn btn-very-small btn-white text-dark-gray flex-grow-1 border-radius-8px fw-700 py-10px"
                    style={{ fontSize: '11px', textTransform: 'uppercase', letterSpacing: '1px' }}
                >
                    Checkout
                </Link>
            </div>

            <style jsx>{`
                .add-to-cart-notice {
                    position: relative;
                }
                .hover-opacity-100:hover {
                    opacity: 1 !important;
                }
            `}</style>
        </div>
    );
}
