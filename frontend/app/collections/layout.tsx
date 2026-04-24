import type { Metadata } from 'next';

export const metadata: Metadata = {
    title: 'Collections | Behom',
    description: 'Browse all Behom collections — curated categories of luxury furniture, architectural decor, lighting, and more.',
};

export default function CollectionsLayout({ children }: { children: React.ReactNode }) {
    return <>{children}</>;
}
