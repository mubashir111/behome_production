'use client';

import { useState } from 'react';
import { apiFetch } from '@/lib/api';

type State = 'idle' | 'submitting' | 'success' | 'error';

export default function BlogCommentForm({ slug }: { slug: string }) {
    const [state, setState] = useState<State>('idle');
    const [errorMsg, setErrorMsg] = useState('');
    const [form, setForm] = useState({ name: '', email: '', comment: '' });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setForm(prev => ({ ...prev, [e.target.name]: e.target.value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!form.name.trim() || !form.email.trim() || !form.comment.trim()) return;
        setState('submitting');
        setErrorMsg('');
        try {
            await apiFetch(`/frontend/blog-posts/${slug}/comments`, {
                method: 'POST',
                body: JSON.stringify(form),
            });
            setState('success');
            setForm({ name: '', email: '', comment: '' });
        } catch (err: any) {
            setErrorMsg(err.message || 'Failed to submit comment. Please try again.');
            setState('error');
        }
    };

    if (state === 'success') {
        return (
            <div className="p-50px md-p-30px sm-p-20px bg-dark-gray border-radius-5px text-center">
                <i className="feather icon-feather-check-circle text-white" style={{ fontSize: '36px', marginBottom: '12px', display: 'block' }}></i>
                <h6 className="alt-font text-white fw-700 mb-10px">Comment submitted!</h6>
                <p className="text-white opacity-7 fs-15 mb-20px">Thanks for joining the conversation. Your comment is awaiting moderation.</p>
                <button onClick={() => setState('idle')} className="btn btn-dark-gray btn-small btn-round-edge" type="button">
                    Write another
                </button>
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="row contact-form-style-02">
            {state === 'error' && (
                <div className="col-12 mb-20px">
                    <p className="text-danger fs-14 mb-0">{errorMsg}</p>
                </div>
            )}
            <div className="col-md-6 mb-30px">
                <input
                    className="input-name border-radius-4px form-control required"
                    name="name"
                    placeholder="Enter your name*"
                    type="text"
                    value={form.name}
                    onChange={handleChange}
                    disabled={state === 'submitting'}
                    required
                />
            </div>
            <div className="col-md-6 mb-30px">
                <input
                    className="border-radius-4px form-control required"
                    name="email"
                    placeholder="Enter your email address*"
                    type="email"
                    value={form.email}
                    onChange={handleChange}
                    disabled={state === 'submitting'}
                    required
                />
            </div>
            <div className="col-md-12 mb-30px">
                <textarea
                    className="border-radius-4px form-control"
                    cols={40}
                    name="comment"
                    placeholder="Your comment*"
                    rows={4}
                    value={form.comment}
                    onChange={handleChange}
                    disabled={state === 'submitting'}
                    required
                ></textarea>
            </div>
            <div className="col-12">
                <button
                    className="btn btn-dark-gray btn-small btn-round-edge submit"
                    type="submit"
                    disabled={state === 'submitting'}
                >
                    {state === 'submitting' ? 'Posting...' : 'Post Comment'}
                </button>
            </div>
        </form>
    );
}
