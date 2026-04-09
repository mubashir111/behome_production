import type { Metadata } from 'next';
import Image from 'next/image';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import AllPostsButton from '@/components/AllPostsButton';
import BlogCommentForm from '@/components/BlogCommentForm';
import { SERVER_API_URL, API_KEY } from '@/lib/config';
import { constructMetadata } from '@/lib/metadata';
import DOMPurify from 'isomorphic-dompurify';


async function getPost(slug: string) {
    try {
        const res = await fetch(`${SERVER_API_URL}/frontend/blog-posts/${slug}`, {
            headers: { 'x-api-key': API_KEY, Accept: 'application/json' },
            cache: 'no-store',
        });
        if (res.status === 404) return null;
        if (!res.ok) return null;
        const json = await res.json();
        return json;
    } catch {
        return null;
    }
}

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
    const json = await getPost(params.slug);
    if (!json?.data) return constructMetadata({ title: 'Blog Post' });
    const p = json.data;
    const title       = p.meta_title || p.title;
    const description = p.meta_description || p.excerpt || '';
    return constructMetadata({
        title,
        description,
        image: p.cover_image || '/images/og-default.png',
    });
}

function formatDate(dateStr: string) {
    try {
        return new Date(dateStr).toLocaleDateString('en-US', { day: '2-digit', month: 'long', year: 'numeric' });
    } catch { return dateStr; }
}

export default async function BlogPostPage({ params }: { params: { slug: string } }) {
    const json = await getPost(params.slug);
    if (!json?.data) notFound();

    const post: any      = json.data;
    const related: any[] = json.related ?? [];
    const comments: any[] = json.comments ?? [];

    return (
        <main>
            {/* Hero image */}
            {post.cover_image && (
                <section className="top-space-margin half-section pb-0">
                    <div className="container">
                        <div className="row">
                            <div className="col-12">
                                <Image alt={post.title} className="border-radius-6px" src={post.cover_image}
                                    width={1400} height={720} unoptimized style={{ width: '100%', height: 'auto' }} />
                            </div>
                        </div>
                    </div>
                </section>
            )}

            {/* Author / meta */}
            <section className={`pb-0 ${post.cover_image ? 'pt-40px' : 'top-space-margin'}`}>
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-10 text-center">
                            <span className="fs-16 mb-20px d-inline-block text-white opacity-7">
                                {post.author && <>By <strong className="text-white">{post.author}</strong></>}
                                {post.category && (
                                    <> in <Link className="text-white fw-600" href={`/blog?category=${post.category}`}>{post.category}</Link></>
                                )}
                                {(post.published_at || post.created_at) && (
                                    <> · <span>{formatDate(post.published_at ?? post.created_at)}</span></>
                                )}
                            </span>
                            <h2 className="alt-font fw-700 text-white mx-auto w-80 xl-w-100 mb-5">{post.title}</h2>
                        </div>
                    </div>
                </div>
            </section>

            {/* Content */}
            <section className="py-0">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-10">
                            {post.excerpt && (
                                <p className="fs-18 text-white opacity-7 mb-40px lh-32">{post.excerpt}</p>
                            )}
                            {post.content ? (
                                <div
                                    className="blog-content text-white opacity-8 fs-16 lh-32"
                                    dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(post.content) }}
                                />
                            ) : (
                                <p className="text-white opacity-5 text-center py-40px fs-15">No content yet.</p>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* Back link */}
            <section className="pt-40px pb-60px">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-10 d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <AllPostsButton />
                            <span className="text-white opacity-5 fs-13">{post.views ?? 0} views</span>
                        </div>
                    </div>
                </div>
            </section>

            {/* Comments */}
            <section className="pt-0 pb-60px">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-10">
                            {comments.length > 0 && (
                                <div className="mb-50px">
                                    <h5 className="alt-font text-white fw-700 mb-30px">
                                        {comments.length} Comment{comments.length !== 1 ? 's' : ''}
                                    </h5>
                                    <div className="d-flex flex-column gap-20px">
                                        {comments.map((c: any) => (
                                            <div key={c.id} className="p-25px bg-dark-gray border-radius-6px" style={{ border: '1px solid rgba(255,255,255,0.08)' }}>
                                                <div className="d-flex align-items-center gap-12px mb-12px">
                                                    <div style={{ width: 38, height: 38, borderRadius: '50%', background: 'rgba(255,255,255,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                        <span className="text-white fw-700 fs-14">{c.name.charAt(0).toUpperCase()}</span>
                                                    </div>
                                                    <div>
                                                        <p className="text-white fw-600 fs-14 mb-0">{c.name}</p>
                                                        <p className="text-white opacity-5 fs-12 mb-0">{formatDate(c.created_at)}</p>
                                                    </div>
                                                </div>
                                                <p className="text-white opacity-8 fs-15 mb-0" style={{ lineHeight: 1.7 }}>{c.comment}</p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            <h5 className="alt-font text-white fw-700 mb-30px">Leave a Comment</h5>
                            <BlogCommentForm slug={post.slug} />
                        </div>
                    </div>
                </div>
            </section>

            {/* Related posts */}
            {related.length > 0 && (
                <section className="page-shell page-shell-tight pt-0">
                    <div className="container">
                        <div className="row justify-content-center mb-30px">
                            <div className="col-lg-5 text-center">
                                <h5 className="alt-font text-white fw-700 mb-0">Related Articles</h5>
                            </div>
                        </div>
                        <div className="row g-4">
                            {related.map((r: any) => (
                                <div key={r.id} className="col-lg-4 col-md-6">
                                    <div className="card bg-transparent border-0 h-100">
                                        <div className="blog-image position-relative overflow-hidden border-radius-4px">
                                            <Link href={`/blog/${r.slug}`}>
                                                <Image alt={r.title} src={r.cover_image || '/images/demo-decor-store-blog-01.jpg'}
                                                    width={640} height={420} unoptimized style={{ width: '100%', height: 'auto' }} />
                                            </Link>
                                        </div>
                                        <div className="card-body px-0 pt-20px pb-0">
                                            {r.category && (
                                                <span className="fs-12 text-uppercase fw-700 text-base-color d-block mb-5px">{r.category}</span>
                                            )}
                                            <Link className="fw-600 fs-16 lh-26 text-white" href={`/blog/${r.slug}`}>{r.title}</Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </main>
    );
}
