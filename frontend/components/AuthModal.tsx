'use client';

import { useState, useEffect, useRef } from 'react';
import { apiFetch } from '@/lib/api';
import { useToast } from '@/components/ToastProvider';
import { useAuthModal } from '@/context/AuthModalContext';

type Mode = 'login' | 'register' | 'forgot-email' | 'forgot-otp' | 'forgot-reset';

export default function AuthModal() {
    const { isOpen, closeAuthModal, pendingAction } = useAuthModal();
    const { showToast } = useToast();

    const [mode, setMode] = useState<Mode>('login');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const [loginData, setLoginData] = useState({ email: '', password: '' });
    const [registerData, setRegisterData] = useState({ name: '', email: '', password: '', password_confirmation: '' });

    // Register OTP
    const [otpStep, setOtpStep] = useState(false);
    const [otpToken, setOtpToken] = useState('');
    const [otpEmail, setOtpEmail] = useState('');

    // Forgot password
    const [fpEmail, setFpEmail] = useState('');
    const [fpOtp, setFpOtp] = useState('');
    const [fpPassword, setFpPassword] = useState('');
    const [fpPasswordConfirm, setFpPasswordConfirm] = useState('');

    const googleBtnRef = useRef<HTMLDivElement>(null);
    const googleInitialized = useRef(false);

    // Reset on open
    useEffect(() => {
        if (isOpen) {
            setMode('login');
            setError('');
            setLoading(false);
            setLoginData({ email: '', password: '' });
            setRegisterData({ name: '', email: '', password: '', password_confirmation: '' });
            setOtpStep(false);
            setOtpToken('');
            setFpEmail('');
            setFpOtp('');
            setFpPassword('');
            setFpPasswordConfirm('');
            googleInitialized.current = false;
        }
    }, [isOpen]);

    // Google Sign-In button
    useEffect(() => {
        if (!isOpen || mode !== 'login' || googleInitialized.current) return;
        const clientId = process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID;
        if (!clientId) return;

        const initGoogle = () => {
            if (!(window as any).google?.accounts) return;
            googleInitialized.current = true;
            (window as any).google.accounts.id.initialize({
                client_id: clientId,
                callback: (res: any) => { if (res.credential) handleGoogleLogin(res.credential); },
            });
            const el = document.getElementById('auth-modal-google-btn');
            if (el) {
                (window as any).google.accounts.id.renderButton(el, {
                    theme: 'filled_black', size: 'large', width: 320, text: 'continue_with', shape: 'pill',
                });
            }
        };

        const timer = setTimeout(() => {
            if ((window as any).google?.accounts) {
                initGoogle();
            } else {
                const existing = document.querySelector('script[src="https://accounts.google.com/gsi/client"]');
                if (existing) { existing.addEventListener('load', initGoogle); }
                else {
                    const script = document.createElement('script');
                    script.src = 'https://accounts.google.com/gsi/client';
                    script.async = true; script.defer = true;
                    script.onload = initGoogle;
                    document.head.appendChild(script);
                }
            }
        }, 200);
        return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isOpen, mode]);

    const onSuccess = (token: string, user: any) => {
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        window.dispatchEvent(new CustomEvent('auth:login', {}));
        window.dispatchEvent(new CustomEvent('cart:updated', {}));
        closeAuthModal();
        if (pendingAction) { setTimeout(() => pendingAction(), 50); }
        else { showToast('Welcome back!', 'success'); }
    };

    // ── Login ────────────────────────────────────────────────────────────────
    const handleLogin = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true); setError('');
        try {
            const res = await apiFetch('/v1/auth/login', { method: 'POST', body: JSON.stringify(loginData) });
            if (res.status) { onSuccess(res.data.access_token, res.data.user); }
            else { setError(res.message || 'Login failed. Please check your credentials.'); }
        } catch { setError('Invalid credentials. Please try again.'); }
        finally { setLoading(false); }
    };

    const handleGoogleLogin = async (credential: string) => {
        setLoading(true); setError('');
        try {
            const res = await apiFetch('/auth/google', { method: 'POST', body: JSON.stringify({ credential }) });
            if (res.status) { onSuccess(res.data.access_token, res.data.user); }
            else { setError(res.message || 'Google login failed'); }
        } catch (err: any) { setError(err.message || 'Google login failed'); }
        finally { setLoading(false); }
    };

    // ── Register ─────────────────────────────────────────────────────────────
    const handleRegister = async (e: React.FormEvent) => {
        e.preventDefault();
        if (registerData.password !== registerData.password_confirmation) { setError('Passwords do not match'); return; }
        setLoading(true); setError('');
        try {
            const res = await apiFetch('/auth/signup/register', {
                method: 'POST',
                body: JSON.stringify({ name: registerData.name, email: registerData.email, password: registerData.password }),
            });
            if (res.status) {
                if (res.token) { onSuccess(res.token, res.user?.data ?? res.user); }
                else { setOtpEmail(registerData.email); setOtpStep(true); showToast('Verification code sent to your email.', 'success'); }
            } else { setError(res.message || 'Registration failed'); }
        } catch (err: any) { setError(err.message || 'Registration failed'); }
        finally { setLoading(false); }
    };

    const handleVerifyOtp = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!otpToken.trim()) { setError('Please enter the OTP code'); return; }
        setLoading(true); setError('');
        try {
            const res = await apiFetch('/auth/signup/verify-email', {
                method: 'POST', body: JSON.stringify({ email: otpEmail, token: otpToken }),
            });
            if (res.status) { onSuccess(res.token, res.user?.data ?? res.user); }
            else { setError(res.message || 'Invalid OTP code'); }
        } catch (err: any) { setError(err.message || 'Verification failed'); }
        finally { setLoading(false); }
    };

    const handleResendOtp = async () => {
        try {
            await apiFetch('/auth/signup/otp-email', { method: 'POST', body: JSON.stringify({ email: otpEmail }) });
            showToast('A new OTP has been sent to your email.', 'success');
        } catch { showToast('Failed to resend OTP', 'error'); }
    };

    // ── Forgot Password ───────────────────────────────────────────────────────
    const handleForgotEmail = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true); setError('');
        try {
            const res = await apiFetch('/auth/forgot-password', { method: 'POST', body: JSON.stringify({ email: fpEmail }) });
            if (res.status) { setMode('forgot-otp'); showToast('Check your email for the reset code.', 'success'); }
            else { setError(res.errors?.email?.[0] || res.message || 'Email not found.'); }
        } catch (err: any) { setError(err.message || 'Something went wrong.'); }
        finally { setLoading(false); }
    };

    const handleForgotOtp = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!fpOtp.trim()) { setError('Please enter the code'); return; }
        setLoading(true); setError('');
        try {
            const res = await apiFetch('/auth/forgot-password/verify-email', { method: 'POST', body: JSON.stringify({ email: fpEmail, token: fpOtp }) });
            if (res.status) { setMode('forgot-reset'); }
            else { setError(res.message || res.errors?.code?.[0] || 'Invalid or expired code.'); }
        } catch (err: any) { setError(err.message || 'Verification failed.'); }
        finally { setLoading(false); }
    };

    const handleForgotReset = async (e: React.FormEvent) => {
        e.preventDefault();
        if (fpPassword !== fpPasswordConfirm) { setError('Passwords do not match'); return; }
        setLoading(true); setError('');
        try {
            const res = await apiFetch('/auth/forgot-password/reset-password', {
                method: 'POST',
                body: JSON.stringify({ email: fpEmail, password: fpPassword, password_confirmation: fpPasswordConfirm }),
            });
            if (res.status) {
                showToast('Password reset! Logging you in…', 'success');
                onSuccess(res.token, res.user?.data ?? res.user);
            } else { setError(res.message || 'Reset failed. Please try again.'); }
        } catch (err: any) { setError(err.message || 'Reset failed.'); }
        finally { setLoading(false); }
    };

    const handleResendFpOtp = async () => {
        try {
            await apiFetch('/auth/forgot-password/otp-email', { method: 'POST', body: JSON.stringify({ email: fpEmail }) });
            showToast('A new code has been sent to your email.', 'success');
        } catch { showToast('Failed to resend code', 'error'); }
    };

    if (!isOpen) return null;

    const goTo = (m: Mode) => { setMode(m); setError(''); };

    return (
        <>
            {/* Backdrop */}
            <div onClick={closeAuthModal} style={{ position: 'fixed', inset: 0, zIndex: 9998, background: 'rgba(0,0,0,0.7)', backdropFilter: 'blur(4px)', WebkitBackdropFilter: 'blur(4px)' }} />

            {/* Modal */}
            <div style={{ position: 'fixed', top: '50%', left: '50%', zIndex: 9999, transform: 'translate(-50%, -50%)', width: '100%', maxWidth: 460, maxHeight: '90vh', overflowY: 'auto', padding: '0 16px' }}>
                <div style={{ background: '#111113', border: '1px solid rgba(197,160,89,0.2)', borderRadius: 12, padding: '32px 32px 28px', boxShadow: '0 24px 80px rgba(0,0,0,0.6)', width: '100%', boxSizing: 'border-box', position: 'relative' }}>

                    {/* Close */}
                    <button onClick={closeAuthModal} style={{ position: 'absolute', top: 16, right: 16, background: 'none', border: 'none', cursor: 'pointer', color: 'rgba(255,255,255,0.5)', fontSize: 20, lineHeight: 1, padding: 4 }} aria-label="Close">
                        <i className="feather icon-feather-x" />
                    </button>

                    {/* ── Register OTP ── */}
                    {otpStep ? (
                        <div>
                            <div className="text-center mb-25px">
                                <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'rgba(197,160,89,0.12)', border: '1px solid rgba(197,160,89,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                                    <i className="feather icon-feather-mail fs-22" style={{ color: 'var(--base-color)' }} />
                                </div>
                                <span className="fs-22 fw-600 text-white d-block mb-8px">Verify your email</span>
                                <p className="text-white mb-0" style={{ opacity: 0.5, fontSize: 13 }}>We sent a 6-digit code to</p>
                                <p className="fw-600 fs-14 mb-0" style={{ color: 'var(--base-color)' }}>{otpEmail}</p>
                            </div>
                            {error && <ErrorBox msg={error} />}
                            <form onSubmit={handleVerifyOtp}>
                                <label className="text-white mb-10px fw-500 fs-14 d-block">Verification Code <span className="text-red">*</span></label>
                                <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control mb-20px" placeholder="000000" type="text" maxLength={6} value={otpToken} onChange={e => setOtpToken(e.target.value.replace(/\D/g, ''))} required autoFocus style={{ letterSpacing: '0.35em', fontSize: 22, textAlign: 'center' }} />
                                <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600" type="submit" disabled={loading}>{loading ? 'Verifying…' : 'Verify & Continue'}</button>
                            </form>
                            <div className="text-center mt-20px">
                                <p className="text-white fs-13 mb-8px" style={{ opacity: 0.5 }}>Didn't receive the code?</p>
                                <LinkBtn onClick={handleResendOtp}>Resend OTP</LinkBtn>
                                <span className="text-white mx-10px" style={{ opacity: 0.3 }}>·</span>
                                <LinkBtn onClick={() => { setOtpStep(false); setOtpToken(''); }}>Change email</LinkBtn>
                            </div>
                        </div>

                    /* ── Forgot: Email ── */
                    ) : mode === 'forgot-email' ? (
                        <div>
                            <button onClick={() => goTo('login')} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'rgba(255,255,255,0.5)', fontSize: 13, padding: '0 0 16px', display: 'flex', alignItems: 'center', gap: 6 }}>
                                <i className="feather icon-feather-arrow-left" /> Back to sign in
                            </button>
                            <div className="text-center mb-24px">
                                <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'rgba(197,160,89,0.12)', border: '1px solid rgba(197,160,89,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                                    <i className="feather icon-feather-lock fs-22" style={{ color: 'var(--base-color)' }} />
                                </div>
                                <span className="fs-22 fw-600 text-white d-block mb-8px">Forgot password?</span>
                                <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 13, margin: 0 }}>Enter your email and we'll send you a reset code.</p>
                            </div>
                            {error && <ErrorBox msg={error} />}
                            <form onSubmit={handleForgotEmail}>
                                <label className="text-white mb-10px fw-500 fs-14 d-block">Email address <span className="text-red">*</span></label>
                                <input className="mb-25px bg-dark-gray-light border-color-transparent-white-light text-white form-control" value={fpEmail} onChange={e => setFpEmail(e.target.value)} placeholder="Enter your email" type="email" required autoFocus />
                                <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600" type="submit" disabled={loading}>{loading ? 'Sending…' : 'Send Reset Code'}</button>
                            </form>
                        </div>

                    /* ── Forgot: OTP ── */
                    ) : mode === 'forgot-otp' ? (
                        <div>
                            <div className="text-center mb-24px">
                                <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'rgba(197,160,89,0.12)', border: '1px solid rgba(197,160,89,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                                    <i className="feather icon-feather-mail fs-22" style={{ color: 'var(--base-color)' }} />
                                </div>
                                <span className="fs-22 fw-600 text-white d-block mb-8px">Check your email</span>
                                <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 13, margin: 0 }}>We sent a code to <strong style={{ color: 'var(--base-color)' }}>{fpEmail}</strong></p>
                            </div>
                            {error && <ErrorBox msg={error} />}
                            <form onSubmit={handleForgotOtp}>
                                <label className="text-white mb-10px fw-500 fs-14 d-block">Reset Code <span className="text-red">*</span></label>
                                <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control mb-20px" placeholder="000000" type="text" maxLength={6} value={fpOtp} onChange={e => setFpOtp(e.target.value.replace(/\D/g, ''))} required autoFocus style={{ letterSpacing: '0.35em', fontSize: 22, textAlign: 'center' }} />
                                <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600" type="submit" disabled={loading}>{loading ? 'Verifying…' : 'Verify Code'}</button>
                            </form>
                            <div className="text-center mt-20px">
                                <p className="text-white fs-13 mb-8px" style={{ opacity: 0.5 }}>Didn't receive it?</p>
                                <LinkBtn onClick={handleResendFpOtp}>Resend code</LinkBtn>
                                <span className="text-white mx-10px" style={{ opacity: 0.3 }}>·</span>
                                <LinkBtn onClick={() => goTo('forgot-email')}>Change email</LinkBtn>
                            </div>
                        </div>

                    /* ── Forgot: New Password ── */
                    ) : mode === 'forgot-reset' ? (
                        <div>
                            <div className="text-center mb-24px">
                                <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'rgba(197,160,89,0.12)', border: '1px solid rgba(197,160,89,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                                    <i className="feather icon-feather-check-circle fs-22" style={{ color: 'var(--base-color)' }} />
                                </div>
                                <span className="fs-22 fw-600 text-white d-block mb-8px">Set new password</span>
                                <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 13, margin: 0 }}>Choose a strong password for your account.</p>
                            </div>
                            {error && <ErrorBox msg={error} />}
                            <form onSubmit={handleForgotReset}>
                                <label className="text-white mb-10px fw-500 fs-14 d-block">New Password <span className="text-red">*</span></label>
                                <input className="mb-20px bg-dark-gray-light border-color-transparent-white-light text-white form-control" value={fpPassword} onChange={e => setFpPassword(e.target.value)} placeholder="At least 6 characters" type="password" required minLength={6} autoFocus />
                                <label className="text-white mb-10px fw-500 fs-14 d-block">Confirm Password <span className="text-red">*</span></label>
                                <input className="mb-25px bg-dark-gray-light border-color-transparent-white-light text-white form-control" value={fpPasswordConfirm} onChange={e => setFpPasswordConfirm(e.target.value)} placeholder="Repeat password" type="password" required minLength={6} />
                                <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600" type="submit" disabled={loading}>{loading ? 'Saving…' : 'Reset Password'}</button>
                            </form>
                        </div>

                    /* ── Login / Register tabs ── */
                    ) : (
                        <>
                            <div style={{ display: 'flex', borderBottom: '1px solid rgba(255,255,255,0.08)', marginBottom: 24 }}>
                                {(['login', 'register'] as const).map(t => (
                                    <button key={t} onClick={() => goTo(t)} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: '0 0 12px', marginRight: 24, fontSize: 15, fontWeight: 600, color: mode === t ? 'var(--base-color)' : 'rgba(255,255,255,0.4)', borderBottom: mode === t ? '2px solid var(--base-color)' : '2px solid transparent', marginBottom: -1, transition: 'color .2s', textTransform: 'capitalize' }}>
                                        {t === 'login' ? 'Sign In' : 'Create Account'}
                                    </button>
                                ))}
                            </div>

                            {error && <ErrorBox msg={error} />}

                            {mode === 'login' ? (
                                <>
                                    <form onSubmit={handleLogin}>
                                        <label className="text-white mb-10px fw-500 fs-14 d-block">Email address <span className="text-red">*</span></label>
                                        <input className="mb-20px bg-dark-gray-light border-color-transparent-white-light text-white form-control" value={loginData.email} onChange={e => setLoginData({ ...loginData, email: e.target.value })} placeholder="Enter your email" type="email" required />
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                                            <label className="text-white fw-500 fs-14">Password <span className="text-red">*</span></label>
                                            <button type="button" onClick={() => goTo('forgot-email')} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--base-color)', fontSize: 12, fontWeight: 500, padding: 0 }}>
                                                Forgot password?
                                            </button>
                                        </div>
                                        <input className="mb-25px bg-dark-gray-light border-color-transparent-white-light text-white form-control" value={loginData.password} onChange={e => setLoginData({ ...loginData, password: e.target.value })} placeholder="Enter your password" type="password" required />
                                        <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600" type="submit" disabled={loading}>{loading ? 'Signing in…' : 'Sign In'}</button>
                                    </form>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 12, margin: '20px 0' }}>
                                        <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.08)' }} />
                                        <span style={{ color: 'rgba(255,255,255,0.3)', fontSize: 12, whiteSpace: 'nowrap' }}>or continue with</span>
                                        <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.08)' }} />
                                    </div>
                                    <div id="auth-modal-google-btn" ref={googleBtnRef} style={{ display: 'flex', justifyContent: 'center' }} />
                                    <p className="text-center mt-20px mb-0" style={{ color: 'rgba(255,255,255,0.4)', fontSize: 13 }}>
                                        Don&apos;t have an account?{' '}
                                        <LinkBtn onClick={() => goTo('register')}>Create one</LinkBtn>
                                    </p>
                                </>
                            ) : (
                                <>
                                    <form onSubmit={handleRegister}>
                                        <div className="row g-3 mb-5px">
                                            <div className="col-12">
                                                <label className="text-white mb-8px fw-500 fs-14 d-block">Full Name <span className="text-red">*</span></label>
                                                <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control" placeholder="Your name" type="text" value={registerData.name} onChange={e => setRegisterData({ ...registerData, name: e.target.value })} required />
                                            </div>
                                            <div className="col-12">
                                                <label className="text-white mb-8px fw-500 fs-14 d-block">Email address <span className="text-red">*</span></label>
                                                <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control" placeholder="Enter email" type="email" value={registerData.email} onChange={e => setRegisterData({ ...registerData, email: e.target.value })} required />
                                            </div>
                                            <div className="col-6">
                                                <label className="text-white mb-8px fw-500 fs-14 d-block">Password <span className="text-red">*</span></label>
                                                <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control" placeholder="Password" type="password" value={registerData.password} onChange={e => setRegisterData({ ...registerData, password: e.target.value })} required minLength={6} />
                                            </div>
                                            <div className="col-6">
                                                <label className="text-white mb-8px fw-500 fs-14 d-block">Confirm <span className="text-red">*</span></label>
                                                <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control" placeholder="Confirm" type="password" value={registerData.password_confirmation} onChange={e => setRegisterData({ ...registerData, password_confirmation: e.target.value })} required minLength={6} />
                                            </div>
                                        </div>
                                        <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600 mt-15px" type="submit" disabled={loading}>{loading ? 'Creating account…' : 'Create Account'}</button>
                                    </form>
                                    <p className="text-center mt-20px mb-0" style={{ color: 'rgba(255,255,255,0.4)', fontSize: 13 }}>
                                        Already have an account?{' '}
                                        <LinkBtn onClick={() => goTo('login')}>Sign in</LinkBtn>
                                    </p>
                                </>
                            )}
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

function ErrorBox({ msg }: { msg: string }) {
    return (
        <div className="mb-20px p-10px border-radius-4px text-center fs-13" style={{ background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.3)', color: '#f87171' }}>
            {msg}
        </div>
    );
}

function LinkBtn({ onClick, children }: { onClick: () => void; children: React.ReactNode }) {
    return (
        <button onClick={onClick} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--base-color)', fontWeight: 600, fontSize: 13, padding: 0 }}>
            {children}
        </button>
    );
}
