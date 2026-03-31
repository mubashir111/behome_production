'use client';

import { useState } from 'react';
import ProductGrid from '@/components/ProductGrid';

export default function HomeProductTabs({
    newArrivals,
    readyToShip,
    trendingNow,
}: {
    newArrivals: any[];
    readyToShip: any[];
    trendingNow: any[];
}) {
    const [activeTab, setActiveTab] = useState<'new-arrivals' | 'ready-to-ship' | 'trending-now'>('new-arrivals');

    const currentProducts = activeTab === 'new-arrivals' ? newArrivals : 
                            activeTab === 'ready-to-ship' ? readyToShip : 
                            trendingNow;

    return (
        <section className="pt-0 pb-0 bg-transparent">
            <div className="container">
                <div className="row mb-4">
                    <div className="col-12">
                        <div
                            className="d-flex justify-content-center flex-wrap gap-4 mb-50px sm-mb-20px"
                            data-anime='{"el":"childs","translateY":[50,0],"opacity":[0,1],"duration":600,"delay":100,"staggervalue":150,"easing":"easeOutQuad"}'
                        >
                            <button
                                type="button"
                                onClick={() => setActiveTab('new-arrivals')}
                                className="bg-transparent border-0 alt-font fs-32 ls-minus-05px text-transform-none position-relative px-0"
                                style={{
                                    color: activeTab === 'new-arrivals' ? '#ffffff' : 'rgba(255,255,255,0.45)',
                                    fontWeight: 600,
                                }}
                            >
                                New arrivals
                                <span
                                    className="d-block mt-10px"
                                    style={{
                                        height: 2,
                                        width: '100%',
                                        background: 'var(--base-color)',
                                        opacity: activeTab === 'new-arrivals' ? 1 : 0,
                                        transition: 'opacity 0.2s ease',
                                    }}
                                />
                            </button>
                            <button
                                type="button"
                                onClick={() => setActiveTab('ready-to-ship')}
                                className="bg-transparent border-0 alt-font fs-32 ls-minus-05px text-transform-none position-relative px-0"
                                style={{
                                    color: activeTab === 'ready-to-ship' ? '#ffffff' : 'rgba(255,255,255,0.45)',
                                    fontWeight: 600,
                                }}
                            >
                                Ready to ship
                                <span
                                    className="d-block mt-10px"
                                    style={{
                                        height: 2,
                                        width: '100%',
                                        background: 'var(--base-color)',
                                        opacity: activeTab === 'ready-to-ship' ? 1 : 0,
                                        transition: 'opacity 0.2s ease',
                                    }}
                                />
                            </button>
                            <button
                                type="button"
                                onClick={() => setActiveTab('trending-now')}
                                className="bg-transparent border-0 alt-font fs-32 ls-minus-05px text-transform-none position-relative px-0"
                                style={{
                                    color: activeTab === 'trending-now' ? '#ffffff' : 'rgba(255,255,255,0.45)',
                                    fontWeight: 600,
                                }}
                            >
                                Trending now
                                <span
                                    className="d-block mt-10px"
                                    style={{
                                        height: 2,
                                        width: '100%',
                                        background: 'var(--base-color)',
                                        opacity: activeTab === 'trending-now' ? 1 : 0,
                                        transition: 'opacity 0.2s ease',
                                    }}
                                />
                            </button>
                        </div>

                        <div
                            data-anime='{"el":"childs","translateY":[50,0],"opacity":[0,1],"duration":600,"delay":150,"staggervalue":150,"easing":"easeOutQuad"}'
                        >
                            <ProductGrid products={currentProducts} />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
