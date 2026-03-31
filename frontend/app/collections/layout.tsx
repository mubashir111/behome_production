import type { Metadata } from 'next';

export const metadata: Metadata = {
    title: 'Collections | Behome',
    description: 'Browse all Behome collections — curated categories of luxury furniture, architectural decor, lighting, and more.',
};

export default function CollectionsLayout({ children }: { children: React.ReactNode }) {
    return <>{children}</>;
}
