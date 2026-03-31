import { redirect } from 'next/navigation';

// This static route is replaced by /blog/[slug]/page.tsx
export default function BlogPostRedirect() {
    redirect('/blog');
}
