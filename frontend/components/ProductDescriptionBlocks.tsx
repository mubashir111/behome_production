'use client';

import Image from 'next/image';

// Block images are stored on the Laravel server — prefix relative paths with its base URL
const BACKEND_BASE = (process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api').replace(/\/api$/, '');

function getImageUrl(src?: string): string {
    if (!src) return '';
    if (src.startsWith('http')) return src;
    return `${BACKEND_BASE}${src.startsWith('/') ? '' : '/'}${src}`;
}

interface Block {
    type: 'feature_split' | 'hero_banner' | 'icon_grid';
    title?: string;
    subtitle?: string;
    label?: string;
    text?: string;
    bullets?: string[];
    image?: string;
    reverse?: boolean;
    items?: Array<{ title: string; text: string; image: string }>;
}

export default function ProductDescriptionBlocks({ blocks }: { blocks: Block[] }) {
    if (!blocks || !blocks.length) return null;

    return (
        <div className="product-description-blocks">
            {blocks.map((block, idx) => {
                switch (block.type) {
                    case 'feature_split':
                        return <FeatureSplit key={idx} block={block} />;
                    case 'hero_banner':
                        return <HeroBanner key={idx} block={block} />;
                    case 'icon_grid':
                        return <IconGrid key={idx} block={block} />;
                    default:
                        return null;
                }
            })}
        </div>
    );
}

function FeatureSplit({ block }: { block: Block }) {
    return (
        <section className="py-80px lg-py-50px overflow-hidden">
            <div className="container">
                <div className={`row align-items-center ${block.reverse ? 'flex-row-reverse' : ''}`}>
                    <div className="col-xl-5 col-lg-6 md-mb-50px">
                        {block.label && (
                            <div className="d-flex align-items-center mb-20px">
                                <span className="w-30px h-1px bg-base-color me-15px"></span>
                                <span className="fs-14 fw-700 text-base-color text-uppercase ls-1px">{block.label}</span>
                            </div>
                        )}
                        <h2 className="text-white fw-600 mb-25px ls-minus-1px">{block.title}</h2>
                        <p className="fs-18 mb-35px lg-mb-30px" style={{ color: 'rgba(255,255,255,0.7)' }}>{block.text}</p>
                        {block.bullets && (
                            <ul className="p-0 m-0 list-unstyled">
                                {block.bullets.map((bullet, i) => (
                                    <li key={i} className="d-flex align-items-center text-white mb-15px">
                                        <i className="bi bi-check2 text-base-color fs-20 me-15px"></i>
                                        <span>{bullet}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                    <div className="col-lg-6 offset-xl-1">
                        <div className="position-relative">
                            {getImageUrl(block.image) ? (
                                <Image
                                    src={getImageUrl(block.image)}
                                    alt={block.title || ''}
                                    width={800}
                                    height={600}
                                    className="w-100 border-radius-10px"
                                    unoptimized
                                />
                            ) : null}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

function HeroBanner({ block }: { block: Block }) {
    return (
        <section className="py-100px lg-py-70px bg-dark-gray overflow-hidden">
            <div className="container">
                <div className="row justify-content-center mb-50px">
                    <div className="col-xl-12 text-center">
                        {getImageUrl(block.image) && (
                            <div className="position-relative mb-50px">
                                <Image
                                    src={getImageUrl(block.image)}
                                    alt={block.title || ''}
                                    width={1200}
                                    height={600}
                                    className="w-100 border-radius-10px opacity-8"
                                    unoptimized
                                    style={{ maxHeight: '600px', objectFit: 'cover' }}
                                />
                                <div className="position-absolute top-50 start-50 translate-middle w-100">
                                    <h1
                                        className="text-white fw-600 mb-0 ls-minus-2px text-uppercase"
                                        style={{ fontSize: 'clamp(2rem, 15vw, 12rem)', letterSpacing: '40px', opacity: 0.05, pointerEvents: 'none' }}
                                    >
                                        MODERN
                                    </h1>
                                </div>
                            </div>
                        )}
                        <div className="col-lg-8 mx-auto">
                            <h3 className="text-white fw-500 mb-20px">{block.title}</h3>
                            <p className="fs-17" style={{ color: 'rgba(255,255,255,0.6)' }}>{block.text}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

function IconGrid({ block }: { block: Block }) {
    return (
        <section className="py-80px lg-py-50px border-top border-color-transparent-white-light">
            <div className="container">
                <div className="row row-cols-1 row-cols-lg-4 row-cols-sm-2 justify-content-center g-4">
                    {block.items?.map((item, i) => (
                        <div key={i} className="col text-center">
                            <div className="mb-25px">
                                {getImageUrl(item.image) ? (
                                    <Image
                                        src={getImageUrl(item.image)}
                                        alt={item.title}
                                        width={140}
                                        height={140}
                                        className="rounded-circle mx-auto"
                                        unoptimized
                                        style={{ border: '1px solid rgba(255,255,255,0.1)', padding: '5px' }}
                                    />
                                ) : null}
                            </div>
                            <span className="d-block text-white fw-700 fs-13 text-uppercase ls-1px mb-5px">{item.title}</span>
                            <p className="fs-14 lh-22 w-80 mx-auto" style={{ color: 'rgba(255,255,255,0.5)' }}>{item.text}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
