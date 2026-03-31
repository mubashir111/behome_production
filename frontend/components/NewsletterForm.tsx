'use client';

import { useState } from 'react';
import { apiFetch } from '@/lib/api';

export default function NewsletterForm() {
    const [email, setEmail] = useState('');
    const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
    const [message, setMessage] = useState('');

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (!email.trim()) return;

        setStatus('loading');
        setMessage('');

        try {
            await apiFetch('/frontend/subscriber', {
                method: 'POST',
                body: JSON.stringify({ email }),
            });
            setStatus('success');
            setMessage('Thank you for subscribing!');
            setEmail('');
        } catch (err: unknown) {
            setStatus('error');
            const msg = err instanceof Error ? err.message : '';
            setMessage(msg.includes('already') ? 'You are already subscribed.' : 'Subscription failed. Please try again.');
        }
    }

    return (
        <div className="d-inline-block w-100 newsletter-style-02 position-relative mb-15px">
            <form onSubmit={handleSubmit} className="position-relative w-100">
                <input
                    className="bg-transparent border-color-transparent-white-light w-100 form-control pe-50px ps-20px lg-ps-15px required"
                    value={email}
                    onChange={e => setEmail(e.target.value)}
                    placeholder="Enter your email"
                    type="email"
                    disabled={status === 'loading' || status === 'success'}
                    required
                />
                <button
                    aria-label="subscribe"
                    className="btn pe-20px submit"
                    type="submit"
                    disabled={status === 'loading' || status === 'success'}
                >
                    {status === 'loading'
                        ? <span style={{ fontSize: 12, color: 'white' }}>...</span>
                        : <i className="icon feather icon-feather-mail icon-small text-white"></i>
                    }
                </button>
            </form>
            {message && (
                <div style={{
                    marginTop: 8,
                    fontSize: 13,
                    color: status === 'success' ? '#a8edaf' : '#f8a3a3',
                }}>
                    {message}
                </div>
            )}
        </div>
    );
}
