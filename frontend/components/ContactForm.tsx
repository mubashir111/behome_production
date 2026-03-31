'use client';

import { useState } from 'react';
import { apiFetch } from '@/lib/api';

type State = 'idle' | 'submitting' | 'success' | 'error';

export default function ContactForm() {
    const [state, setState] = useState<State>('idle');
    const [error, setError] = useState('');
    const [form, setForm] = useState({ name: '', email: '', message: '' });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setForm(prev => ({ ...prev, [e.target.name]: e.target.value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!form.name.trim() || !form.email.trim() || !form.message.trim()) {
            setError('Please fill in all required fields.');
            return;
        }
        setState('submitting');
        setError('');
        try {
            const res = await apiFetch('/frontend/contact', {
                method: 'POST',
                body: JSON.stringify(form),
            });
            if (res?.status) {
                setState('success');
                setForm({ name: '', email: '', message: '' });
            } else {
                setError(res?.message ?? 'Something went wrong. Please try again.');
                setState('error');
            }
        } catch {
            setError('Something went wrong. Please try again.');
            setState('error');
        }
    };

    if (state === 'success') {
        return (
            <div className="bg-base-color p-16 lg-p-10 position-relative overflow-hidden mt-50px d-flex flex-column align-items-center justify-content-center" style={{ minHeight: '280px' }}>
                <i className="feather icon-feather-check-circle text-white" style={{ fontSize: '48px', marginBottom: '16px' }}></i>
                <h4 className="text-white fw-600 mb-10px text-center">Message sent!</h4>
                <p className="text-white opacity-7 text-center mb-25px">Thanks for reaching out. We&apos;ll be in touch soon.</p>
                <button
                    onClick={() => setState('idle')}
                    className="btn btn-white btn-small btn-round-edge"
                    type="button"
                >
                    Send another
                </button>
            </div>
        );
    }

    return (
        <div className="bg-base-color p-16 lg-p-10 position-relative overflow-hidden mt-50px">
            <i className="bi bi-chat-text fs-140 text-white opacity-1 position-absolute top-minus-30px right-minus-20px"></i>
            <h2 className="fw-600 alt-font text-white mb-30px fancy-text-style-4 ls-minus-1px">Say hello!</h2>

            {error && (
                <div className="mb-20px p-3 bg-white bg-opacity-10 border-radius-4px" role="alert" aria-live="polite">
                    <p className="text-white fs-14 mb-0">{error}</p>
                </div>
            )}

            <form onSubmit={handleSubmit}>
                <div className="position-relative form-group mb-10px">
                    <label htmlFor="contact-name" className="sr-only">Your name (required)</label>
                    <span className="form-icon text-white" aria-hidden="true"><i className="bi bi-person icon-small"></i></span>
                    <input
                        id="contact-name"
                        className="fw-300 ps-0 border-radius-0px bg-transparent border-color-transparent-white-light placeholder-dark-gray form-control required"
                        name="name"
                        placeholder="Your name*"
                        type="text"
                        value={form.name}
                        onChange={handleChange}
                        disabled={state === 'submitting'}
                        aria-describedby="name-help"
                    />
                </div>
                <div className="position-relative form-group mb-10px">
                    <label htmlFor="contact-email" className="sr-only">Your email address (required)</label>
                    <span className="form-icon text-white" aria-hidden="true"><i className="bi bi-envelope icon-small"></i></span>
                    <input
                        id="contact-email"
                        className="fw-300 ps-0 border-radius-0px bg-transparent border-color-transparent-white-light placeholder-dark-gray form-control required"
                        name="email"
                        placeholder="Your email address*"
                        type="email"
                        value={form.email}
                        onChange={handleChange}
                        disabled={state === 'submitting'}
                        aria-describedby="email-help"
                    />
                </div>
                <div className="position-relative form-group form-textarea mt-10px">
                    <label htmlFor="contact-message" className="sr-only">Your message (required)</label>
                    <textarea
                        id="contact-message"
                        className="fw-300 ps-0 border-radius-0px bg-transparent border-color-transparent-white-light placeholder-dark-gray form-control"
                        name="message"
                        placeholder="Your message*"
                        rows={3}
                        value={form.message}
                        onChange={handleChange}
                        disabled={state === 'submitting'}
                        aria-describedby="message-help"
                    ></textarea>
                    <span className="form-icon text-white"><i className="bi bi-chat-square-dots icon-small"></i></span>
                    <button
                        className="btn btn-white btn-large fw-600 btn-switch-text btn-box-shadow btn-round-edge mt-30px"
                        type="submit"
                        disabled={state === 'submitting'}
                    >
                        <span>
                            <span className="btn-double-text" data-text="Send message">
                                {state === 'submitting' ? 'Sending...' : 'Send message'}
                            </span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    );
}
