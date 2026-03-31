'use client';

export default function AllPostsButton() {
    return (
        <a
            href="/blog"
            className="btn btn-transparent-white btn-medium btn-round-edge"
        >
            <span><span className="btn-double-text" data-text="← All Posts">← All Posts</span></span>
        </a>
    );
}
