'use client';

import { Suspense } from 'react';
import { useState, useEffect, useCallback } from 'react';
import { apiFetch } from '@/lib/api';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import PageLoadingShell from '@/components/PageLoadingShell';
import { useToast } from '@/components/ToastProvider';

function AccountContent() {
    const searchParams = useSearchParams();
    const activeTab = searchParams.get('tab') || 'profile';

    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const [user, setUser] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    const { showToast } = useToast();

    // Auth States
    const [loginData, setLoginData] = useState({ email: '', password: '' });
    const [registerData, setRegisterData] = useState({ name: '', email: '', password: '', password_confirmation: '' });
    const [otpStep, setOtpStep] = useState(false);
    const [otpToken, setOtpToken] = useState('');
    const [otpEmail, setOtpEmail] = useState('');

    // Dashboard Data
    const [orders, setOrders] = useState<any[]>([]);
    const [addresses, setAddresses] = useState<any[]>([]);
    const [wishlistItems, setWishlistItems] = useState<any[]>([]);

    // Profile editing
    const [profileData, setProfileData] = useState({ name: '', email: '', phone: '', country_code: '' });
    const [profileSaving, setProfileSaving] = useState(false);

    // Password change
    const [passwordData, setPasswordData] = useState({ old_password: '', new_password: '', confirm_password: '' });
    const [passwordSaving, setPasswordSaving] = useState(false);

    // Address form
    const [showAddressForm, setShowAddressForm] = useState(false);
    const [editingAddress, setEditingAddress] = useState<any>(null);
    const [addressData, setAddressData] = useState({
        full_name: '', email: '', phone: '', country_code: '', address: '', city: '', state: '', zip_code: '', country: '',
    });
    const [addressSaving, setAddressSaving] = useState(false);

    const fetchTabData = useCallback(async () => {
        setLoading(true);
        try {
            if (activeTab === 'orders') {
                const response = await apiFetch('/orders');
                if (response.status) {
                    const orderItems = Array.isArray(response?.data)
                        ? response.data
                        : Array.isArray(response?.data?.data)
                            ? response.data.data
                            : [];
                    setOrders(orderItems);
                }
            } else if (activeTab === 'wishlist') {
                const response = await apiFetch('/frontend/wishlist');
                if (response.status) setWishlistItems(Array.isArray(response.data) ? response.data : []);
            } else if (activeTab === 'addresses') {
                const response = await apiFetch('/addresses');
                if (response.status) setAddresses(response.data);
            } else if (activeTab === 'profile') {
                const response = await apiFetch('/profile');
                const u = response?.data ?? response;
                if (u?.name !== undefined) {
                    setUser(u);
                    setProfileData({ name: u.name || '', email: u.email || '', phone: u.phone || '', country_code: u.country_code || '' });
                    localStorage.setItem('user', JSON.stringify(u));
                }
            }
        } catch (err) {
            console.error('Failed to fetch dashboard data:', err);
        } finally {
            setLoading(false);
        }
    }, [activeTab]);

    useEffect(() => {
        const token = localStorage.getItem('token');
        const storedUser = localStorage.getItem('user');
        if (token && storedUser) {
            const u = JSON.parse(storedUser);
            setIsLoggedIn(true);
            setUser(u);
            setProfileData({ name: u.name || '', email: u.email || '', phone: u.phone || '', country_code: u.country_code || '' });
            fetchTabData();
        } else {
            setLoading(false);
        }
    }, [fetchTabData]);

    // Load Google GSI script and render button when on login screen
    useEffect(() => {
        if (isLoggedIn) return;
        const clientId = process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID;
        if (!clientId) return;

        const initGoogle = () => {
            (window as any).google?.accounts.id.initialize({
                client_id: clientId,
                callback: (res: any) => {
                    if (res.credential) handleGoogleLogin(res.credential);
                },
            });
            (window as any).google?.accounts.id.renderButton(
                document.getElementById('google-login-btn'),
                { theme: 'filled_black', size: 'large', width: 320, text: 'continue_with', shape: 'pill' }
            );
        };

        if ((window as any).google?.accounts) {
            initGoogle();
        } else {
            const script = document.createElement('script');
            script.src = 'https://accounts.google.com/gsi/client';
            script.async = true;
            script.defer = true;
            script.onload = initGoogle;
            document.head.appendChild(script);
        }
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isLoggedIn]);

    const handleLogin = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        try {
            const response = await apiFetch('/v1/auth/login', {
                method: 'POST',
                body: JSON.stringify(loginData),
            });
            if (response.status) {
                localStorage.setItem('token', response.data.access_token);
                localStorage.setItem('user', JSON.stringify(response.data.user));
                window.location.reload();
            } else {
                setError(response.message || 'Login failed');
            }
        } catch (err) {
            setError('Invalid credentials. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const handleGoogleLogin = async (credential: string) => {
        setLoading(true);
        setError('');
        try {
            const response = await apiFetch('/auth/google', {
                method: 'POST',
                body: JSON.stringify({ credential }),
            });
            if (response.status) {
                localStorage.setItem('token', response.data.access_token);
                localStorage.setItem('user', JSON.stringify(response.data.user));
                window.location.reload();
            } else {
                setError(response.message || 'Google login failed');
            }
        } catch (err: any) {
            setError(err.message || 'Google login failed');
        } finally {
            setLoading(false);
        }
    };

    const handleRegister = async (e: React.FormEvent) => {
        e.preventDefault();
        if (registerData.password !== registerData.password_confirmation) {
            showToast('Passwords do not match', 'error');
            return;
        }
        setLoading(true);
        try {
            const response = await apiFetch('/auth/signup/register', {
                method: 'POST',
                body: JSON.stringify({
                    name: registerData.name,
                    email: registerData.email,
                    password: registerData.password,
                }),
            });
            if (response.status) {
                // OTP sent to email — move to OTP step
                setOtpEmail(registerData.email);
                setOtpStep(true);
                showToast('A verification code has been sent to your email.', 'success');
            } else {
                showToast(response.message || 'Registration failed', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'An error occurred during registration', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleVerifyOtp = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!otpToken.trim()) {
            showToast('Please enter the OTP code', 'error');
            return;
        }
        setLoading(true);
        try {
            const response = await apiFetch('/auth/signup/verify-email', {
                method: 'POST',
                body: JSON.stringify({ email: otpEmail, token: otpToken }),
            });
            if (response.status) {
                localStorage.setItem('token', response.token);
                localStorage.setItem('user', JSON.stringify(response.user?.data ?? response.user));
                window.location.reload();
            } else {
                showToast(response.message || 'Invalid OTP code', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'Verification failed', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleResendOtp = async () => {
        try {
            await apiFetch('/auth/signup/otp-email', {
                method: 'POST',
                body: JSON.stringify({ email: otpEmail }),
            });
            showToast('A new OTP has been sent to your email.', 'success');
        } catch {
            showToast('Failed to resend OTP', 'error');
        }
    };

    const handleLogout = async () => {
        try {
            await apiFetch('/v1/auth/logout', { method: 'POST' });
        } catch {
            // ignore
        }
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setIsLoggedIn(false);
        window.location.href = '/account';
    };

    const handleProfileSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setProfileSaving(true);
        try {
            const response = await apiFetch('/profile', {
                method: 'PUT',
                body: JSON.stringify(profileData),
            });
            if (response.status) {
                showToast('Profile updated successfully.', 'success');
                const updated = { ...user, ...profileData };
                setUser(updated);
                localStorage.setItem('user', JSON.stringify(updated));
            } else {
                showToast(response.message || 'Update failed', 'error');
            }
        } catch (err: any) {
            showToast(err.message || 'Update failed', 'error');
        } finally {
            setProfileSaving(false);
        }
    };

    const handlePasswordChange = async (e: React.FormEvent) => {
        e.preventDefault();
        setPasswordSaving(true);
        if (passwordData.new_password !== passwordData.confirm_password) {
            showToast('New passwords do not match', 'error');
            setPasswordSaving(false);
            return;
        }
        try {
            await apiFetch('/profile/change-password', {
                method: 'PUT',
                body: JSON.stringify(passwordData),
            });
            showToast('Password changed successfully.', 'success');
            setPasswordData({ old_password: '', new_password: '', confirm_password: '' });
        } catch (err: any) {
            showToast(err.message || 'Password change failed', 'error');
        } finally {
            setPasswordSaving(false);
        }
    };

    const openAddressForm = (addr?: any) => {
        if (addr) {
            setEditingAddress(addr);
            setAddressData({
                full_name: addr.full_name || '',
                email: addr.email || '',
                phone: addr.phone || '',
                address: addr.address || '',
                city: addr.city || '',
                state: addr.state || '',
                zip_code: addr.zip_code || '',
                country: addr.country || '',
                country_code: addr.country_code || '',
            });
        } else {
            setEditingAddress(null);
            setAddressData({ full_name: '', email: '', phone: '', country_code: '', address: '', city: '', state: '', zip_code: '', country: '' });
        }
        setShowAddressForm(true);
    };

    const handleAddressSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setAddressSaving(true);
        try {
            const endpoint = editingAddress ? `/addresses/${editingAddress.id}` : '/addresses';
            const method = editingAddress ? 'PUT' : 'POST';
            const response = await apiFetch(endpoint, {
                method,
                body: JSON.stringify(addressData),
            });
            if (response.status) {
                showToast(editingAddress ? 'Address updated.' : 'Address added.', 'success');
                setShowAddressForm(false);
                // Refresh addresses
                const res2 = await apiFetch('/addresses');
                if (res2.status) setAddresses(res2.data);
            }
        } catch (err: any) {
            showToast(err.message || 'Failed to save address', 'error');
        } finally {
            setAddressSaving(false);
        }
    };

    const handleAddressDelete = async (id: number) => {
        if (!confirm('Delete this address?')) return;
        try {
            await apiFetch(`/addresses/${id}`, { method: 'DELETE' });
            setAddresses(prev => prev.filter(a => a.id !== id));
        } catch (err) {
            console.error('Failed to delete address:', err);
        }
    };

    if (loading) return <div className="text-center text-white page-shell d-flex align-items-center justify-content-center">Loading...</div>;

    if (isLoggedIn) {
        return (
            <main>
                <section className="top-space-padding page-shell page-shell-tight">
                    <div className="container">

                        {/* ── Mobile: compact user card + horizontal pill nav ── */}
                        <div className="d-lg-none mb-24px" style={{ marginBottom: 24 }}>
                            {/* User card */}
                            <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 0 20px' }}>
                                <div style={{ width: 50, height: 50, borderRadius: '50%', background: 'var(--base-color)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                    <i className="feather icon-feather-user text-white" style={{ fontSize: 20 }}></i>
                                </div>
                                <div style={{ minWidth: 0 }}>
                                    <p style={{ color: '#fff', fontWeight: 700, fontSize: 15, margin: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{user?.name}</p>
                                    <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 12, margin: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{user?.email}</p>
                                </div>
                            </div>
                            {/* Horizontal scrollable pill nav */}
                            <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 4, WebkitOverflowScrolling: 'touch', scrollbarWidth: 'none' }}>
                                {[
                                    { tab: 'profile',   icon: 'icon-feather-user',        label: 'Profile'    },
                                    { tab: 'orders',    icon: 'icon-feather-package',      label: 'Orders'     },
                                    { tab: 'addresses', icon: 'icon-feather-map-pin',      label: 'Addresses'  },
                                    { tab: 'security',  icon: 'icon-feather-lock',         label: 'Security'   },
                                    { tab: 'wishlist',  icon: 'icon-feather-heart',        label: 'Wishlist'   },
                                ].map(({ tab, icon, label }) => (
                                    <Link key={tab} href={`/account?tab=${tab}`}
                                        style={{
                                            display: 'inline-flex', alignItems: 'center', gap: 6,
                                            padding: '8px 16px', borderRadius: 24, whiteSpace: 'nowrap',
                                            fontSize: 13, fontWeight: 600, textDecoration: 'none',
                                            flexShrink: 0,
                                            background: activeTab === tab ? 'var(--base-color)' : 'rgba(255,255,255,0.07)',
                                            color: activeTab === tab ? '#0a0a0a' : 'rgba(255,255,255,0.75)',
                                            border: activeTab === tab ? '1px solid var(--base-color)' : '1px solid rgba(255,255,255,0.1)',
                                        }}>
                                        <i className={`feather ${icon}`} style={{ fontSize: 13 }}></i>
                                        {label}
                                    </Link>
                                ))}
                                <button onClick={handleLogout}
                                    style={{
                                        display: 'inline-flex', alignItems: 'center', gap: 6,
                                        padding: '8px 16px', borderRadius: 24, whiteSpace: 'nowrap',
                                        fontSize: 13, fontWeight: 600, flexShrink: 0,
                                        background: 'rgba(239,68,68,0.08)', color: 'rgba(239,68,68,0.8)',
                                        border: '1px solid rgba(239,68,68,0.2)', cursor: 'pointer',
                                    }}>
                                    <i className="feather icon-feather-log-out" style={{ fontSize: 13 }}></i>
                                    Logout
                                </button>
                            </div>
                        </div>

                        <div className="row">
                            {/* Sidebar — desktop only */}
                            <div className="col-lg-3 col-md-4 d-none d-lg-block md-mb-40px">
                                <div className="bg-dark-gray border-radius-6px box-shadow-extra-large border border-color-extra-medium-gray ui-panel ui-panel-lg">
                                    <div className="text-center ui-panel-header">
                                        <div className="d-inline-flex align-items-center justify-content-center bg-base-color rounded-circle mb-10px" style={{ width: 60, height: 60, fontSize: 24 }}>
                                            <i className="feather icon-feather-user text-white"></i>
                                        </div>
                                        <p className="text-white fw-600 mb-0 fs-15">{user?.name}</p>
                                        <p className="text-white opacity-5 fs-13 mb-0">{user?.email}</p>
                                    </div>
                                    <ul className="list-style-01 account-sidebar">
                                        <li className={`mb-10px ${activeTab === 'profile' ? 'active-link' : ''}`}>
                                            <Link href="/account?tab=profile"><i className="feather icon-feather-user me-10px"></i>Profile</Link>
                                        </li>
                                        <li className={`mb-10px ${activeTab === 'orders' ? 'active-link' : ''}`}>
                                            <Link href="/account?tab=orders"><i className="feather icon-feather-package me-10px"></i>Orders</Link>
                                        </li>
                                        <li className={`mb-10px ${activeTab === 'addresses' ? 'active-link' : ''}`}>
                                            <Link href="/account?tab=addresses"><i className="feather icon-feather-map-pin me-10px"></i>Addresses</Link>
                                        </li>
                                        <li className={`mb-10px ${activeTab === 'security' ? 'active-link' : ''}`}>
                                            <Link href="/account?tab=security"><i className="feather icon-feather-lock me-10px"></i>Security</Link>
                                        </li>
                                        <li className={`mb-10px ${activeTab === 'wishlist' ? 'active-link' : ''}`}>
                                            <Link href="/account?tab=wishlist"><i className="feather icon-feather-heart me-10px"></i>Wishlist</Link>
                                        </li>
                                        <li className="mt-10px pt-10px" style={{ borderTop: '1px solid rgba(255,255,255,0.1)' }}>
                                            <button onClick={handleLogout} className="text-white"><i className="feather icon-feather-log-out me-10px"></i>Logout</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {/* Content */}
                            <div className="col-lg-9 col-md-8 col-12 ui-content-offset">

                                {/* Profile Tab */}
                                {activeTab === 'profile' && (
                                    <div className="ui-stack-md">
                                        <h4 className="ui-section-title">Edit Profile</h4>
                                        <div className="bg-dark-gray border-radius-6px box-shadow-extra-large border border-color-extra-medium-gray ui-panel ui-panel-lg">
                                            <form onSubmit={handleProfileSave}>
                                                <div className="row g-4">
                                                    <div className="col-md-6">
                                                        <label className="text-white mb-10px fw-500 fs-14">Full Name <span className="text-red">*</span></label>
                                                        <input
                                                            type="text"
                                                            className="form-control bg-dark-gray-light border-color-transparent-white-light text-white"
                                                            value={profileData.name}
                                                            onChange={e => setProfileData({ ...profileData, name: e.target.value })}
                                                            required
                                                        />
                                                    </div>
                                                    <div className="col-md-6">
                                                        <label className="text-white mb-10px fw-500 fs-14">Email <span className="text-red">*</span></label>
                                                        <input
                                                            type="email"
                                                            className="form-control bg-dark-gray-light border-color-transparent-white-light text-white"
                                                            value={profileData.email}
                                                            onChange={e => setProfileData({ ...profileData, email: e.target.value })}
                                                            required
                                                        />
                                                    </div>
                                                    <div className="col-md-6">
                                                        <label className="text-white mb-10px fw-500 fs-14">Phone</label>
                                                        <input
                                                            type="text"
                                                            className="form-control bg-dark-gray-light border-color-transparent-white-light text-white"
                                                            value={profileData.phone}
                                                            onChange={e => setProfileData({ ...profileData, phone: e.target.value })}
                                                        />
                                                    </div>
                                                    <div className="col-md-6">
                                                        <label className="text-white mb-10px fw-500 fs-14">Country Code</label>
                                                        <input
                                                            type="text"
                                                            className="form-control bg-dark-gray-light border-color-transparent-white-light text-white"
                                                            value={profileData.country_code}
                                                            onChange={e => setProfileData({ ...profileData, country_code: e.target.value })}
                                                            placeholder="+44"
                                                        />
                                                    </div>
                                                    <div className="col-12">
                                                        <button type="submit" className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow" disabled={profileSaving}>
                                                            {profileSaving ? 'Saving...' : 'Save Changes'}
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                )}

                                {/* Orders Tab */}
                                {activeTab === 'orders' && (
                                    <div className="ui-stack-md">
                                        <div className="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-0">
                                            <h4 className="ui-section-title mb-0">Your Orders</h4>
                                            <span className="text-white opacity-5 fs-13">{orders.length} order{orders.length !== 1 ? 's' : ''}</span>
                                        </div>
                                        {orders.length === 0 ? (
                                            <div className="text-center border-radius-6px border border-dashed border-color-extra-medium-gray ui-panel">
                                                <i className="feather icon-feather-package fs-40 mb-15px d-block opacity-4 text-white"></i>
                                                <p className="text-white fw-600 mb-5px">No orders yet</p>
                                                <p className="mb-0 text-white opacity-6 fs-13">When you place an order, it will appear here.</p>
                                                <Link href="/shop" className="btn btn-small btn-round-edge btn-base-color mt-20px">Start Shopping</Link>
                                            </div>
                                        ) : (
                                            <div className="orders-table-wrap" style={{ background: 'rgba(255,255,255,0.02)', borderRadius: 12, border: '1px solid rgba(255,255,255,0.07)', overflow: 'hidden' }}>
                                                <div style={{ overflowX: 'auto', WebkitOverflowScrolling: 'touch' as any }}>
                                                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
                                                        <thead>
                                                            <tr style={{ background: 'rgba(255,255,255,0.04)', borderBottom: '1px solid rgba(255,255,255,0.07)' }}>
                                                                <th style={{ padding: '13px 20px', fontWeight: 700, color: 'rgba(255,255,255,0.5)', textAlign: 'left', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.07em', whiteSpace: 'nowrap' }}>Order</th>
                                                                <th style={{ padding: '13px 16px', fontWeight: 700, color: 'rgba(255,255,255,0.5)', textAlign: 'left', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.07em', whiteSpace: 'nowrap' }}>Date</th>
                                                                <th style={{ padding: '13px 16px', fontWeight: 700, color: 'rgba(255,255,255,0.5)', textAlign: 'left', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.07em' }}>Status</th>
                                                                <th style={{ padding: '13px 16px', fontWeight: 700, color: 'rgba(255,255,255,0.5)', textAlign: 'left', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.07em' }}>Total</th>
                                                                <th style={{ padding: '13px 20px 13px 16px', fontWeight: 700, color: 'rgba(255,255,255,0.5)', textAlign: 'right', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.07em' }}>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {orders.map((order: any, idx: number) => {
                                                                const statusStyle = (() => {
                                                                    const s = (order.status_name || '').toLowerCase();
                                                                    if (s.includes('deliver') || s.includes('complet')) return { bg: 'rgba(16,185,129,0.12)', color: '#34d399' };
                                                                    if (s.includes('cancel') || s.includes('reject')) return { bg: 'rgba(239,68,68,0.12)', color: '#f87171' };
                                                                    if (s.includes('confirm') || s.includes('process')) return { bg: 'rgba(99,102,241,0.15)', color: '#a5b4fc' };
                                                                    if (s.includes('ship') || s.includes('transit') || s.includes('way')) return { bg: 'rgba(59,130,246,0.15)', color: '#93c5fd' };
                                                                    return { bg: 'rgba(245,158,11,0.12)', color: '#fbbf24' }; // pending
                                                                })();
                                                                return (
                                                                    <tr key={order.id} style={{ borderBottom: idx < orders.length - 1 ? '1px solid rgba(255,255,255,0.05)' : 'none', transition: 'background 0.15s' }}
                                                                        onMouseEnter={e => (e.currentTarget.style.background = 'rgba(255,255,255,0.025)')}
                                                                        onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}>
                                                                        <td style={{ padding: '16px 20px' }}>
                                                                            <span style={{ color: '#fff', fontWeight: 700, fontSize: 14 }}>#{order.order_serial_no}</span>
                                                                        </td>
                                                                        <td style={{ padding: '16px', color: 'rgba(255,255,255,0.5)', fontSize: 13, whiteSpace: 'nowrap' }}>{order.order_datetime}</td>
                                                                        <td style={{ padding: '16px' }}>
                                                                            <span style={{ background: statusStyle.bg, color: statusStyle.color, padding: '4px 12px', borderRadius: 20, fontSize: 12, fontWeight: 600, whiteSpace: 'nowrap' }}>
                                                                                {order.status_name}
                                                                            </span>
                                                                        </td>
                                                                        <td style={{ padding: '16px', color: 'rgba(255,255,255,0.9)', fontWeight: 700, fontSize: 14 }}>{order.total_currency_price}</td>
                                                                        <td style={{ padding: '16px 20px 16px 16px', textAlign: 'right' }}>
                                                                            <div style={{ display: 'inline-flex', gap: 8, alignItems: 'center' }}>
                                                                                {(() => {
                                                                                    const s = (order.status_name || '').toLowerCase();
                                                                                    const isDelivered = s.includes('deliver') || s.includes('complet');
                                                                                    return isDelivered ? (
                                                                                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '3px 10px', borderRadius: 20, background: 'rgba(251,153,28,0.1)', color: '#FB991C', fontSize: 11, fontWeight: 600, border: '1px solid rgba(251,153,28,0.25)', whiteSpace: 'nowrap' }}>
                                                                                            <i className="feather icon-feather-rotate-ccw" style={{ fontSize: 10 }}></i>
                                                                                            Returnable
                                                                                        </span>
                                                                                    ) : null;
                                                                                })()}
                                                                                <Link href={`/account/order/${order.id}`} style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '6px 14px', borderRadius: 8, background: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.8)', fontSize: 12, fontWeight: 600, border: '1px solid rgba(255,255,255,0.1)', textDecoration: 'none' }}>
                                                                                    <i className="feather icon-feather-eye" style={{ fontSize: 12 }}></i>
                                                                                    View
                                                                                </Link>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                );
                                                            })}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {/* Addresses Tab */}
                                {activeTab === 'addresses' && (
                                    <div className="ui-stack-md">
                                        <div className="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                            <h4 className="ui-section-title mb-0">My Addresses</h4>
                                            <button onClick={() => openAddressForm()} className="btn btn-small btn-round-edge btn-base-color">
                                                <i className="feather icon-feather-plus me-5px"></i> Add New
                                            </button>
                                        </div>

                                        {/* Address Form */}
                                        {showAddressForm && (
                                            <div className="bg-dark-gray border-radius-6px box-shadow-extra-large border border-color-extra-medium-gray ui-panel ui-panel-lg">
                                                <div className="d-flex align-items-center justify-content-between mb-25px">
                                                    <span className="text-white fw-600 fs-18">{editingAddress ? 'Edit Address' : 'Add New Address'}</span>
                                                    <button onClick={() => setShowAddressForm(false)} className="btn-close btn-close-white opacity-5"></button>
                                                </div>
                                                <form onSubmit={handleAddressSave}>
                                                    <div className="row g-3">
                                                        <div className="col-md-6">
                                                            <label className="text-white mb-8px fw-500 fs-13">Full Name <span className="text-red">*</span></label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.full_name} onChange={e => setAddressData({ ...addressData, full_name: e.target.value })} required />
                                                        </div>
                                                        <div className="col-md-6">
                                                            <label className="text-white mb-8px fw-500 fs-13">Email <span className="text-red">*</span></label>
                                                            <input type="email" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.email} onChange={e => setAddressData({ ...addressData, email: e.target.value })} required />
                                                        </div>
                                                        <div className="col-md-6">
                                                            <label className="text-white mb-8px fw-500 fs-13">Phone <span className="text-red">*</span></label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.phone} onChange={e => setAddressData({ ...addressData, phone: e.target.value })} required />
                                                        </div>
                                                        <div className="col-md-6">
                                                            <label className="text-white mb-8px fw-500 fs-13">Country Code</label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.country_code} onChange={e => setAddressData({ ...addressData, country_code: e.target.value })} placeholder="+44" />
                                                        </div>
                                                        <div className="col-12">
                                                            <label className="text-white mb-8px fw-500 fs-13">Street Address <span className="text-red">*</span></label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.address} onChange={e => setAddressData({ ...addressData, address: e.target.value })} required />
                                                        </div>
                                                        <div className="col-md-3">
                                                            <label className="text-white mb-8px fw-500 fs-13">City <span className="text-red">*</span></label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.city} onChange={e => setAddressData({ ...addressData, city: e.target.value })} required />
                                                        </div>
                                                        <div className="col-md-3">
                                                            <label className="text-white mb-8px fw-500 fs-13">State</label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.state} onChange={e => setAddressData({ ...addressData, state: e.target.value })} />
                                                        </div>
                                                        <div className="col-md-3">
                                                            <label className="text-white mb-8px fw-500 fs-13">Postcode <span className="text-red">*</span></label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.zip_code} onChange={e => setAddressData({ ...addressData, zip_code: e.target.value })} required />
                                                        </div>
                                                        <div className="col-md-3">
                                                            <label className="text-white mb-8px fw-500 fs-13">Country <span className="text-red">*</span></label>
                                                            <input type="text" className="form-control bg-dark-gray-light border-color-transparent-white-light text-white" value={addressData.country} onChange={e => setAddressData({ ...addressData, country: e.target.value })} required />
                                                        </div>
                                                        <div className="col-12 d-flex gap-3 mt-5px">
                                                            <button type="submit" className="btn btn-medium btn-round-edge btn-base-color" disabled={addressSaving}>
                                                                {addressSaving ? 'Saving...' : (editingAddress ? 'Update Address' : 'Add Address')}
                                                            </button>
                                                            <button type="button" className="btn btn-medium btn-round-edge btn-dark-gray border border-color-extra-medium-gray" onClick={() => setShowAddressForm(false)}>
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        )}

                                        <div className="row g-4 addresses-grid">
                                            {addresses.length === 0 && !showAddressForm ? (
                                                <div className="col-12">
                                                    <div className="bg-dark-gray text-center border-radius-6px border border-dashed border-color-extra-medium-gray ui-panel">
                                                        <i className="feather icon-feather-map-pin fs-40 mb-15px d-block opacity-4 text-white"></i>
                                                        <p className="mb-0 text-white">No addresses saved yet.</p>
                                                    </div>
                                                </div>
                                            ) : (
                                                addresses.map((addr: any) => (
                                                    <div className="col-md-6" key={addr.id}>
                                                        <div className="bg-dark-gray border-radius-6px box-shadow-extra-large border border-color-extra-medium-gray position-relative h-100 ui-panel ui-panel-sm">
                                                            {addr.is_default && (
                                                                <span className="badge mb-10px d-inline-block" style={{ background: 'var(--base-color)', fontSize: '11px' }}>Default</span>
                                                            )}
                                                            <span className="text-white fw-600 fs-16 d-block mb-8px">{addr.full_name}</span>
                                                            <p className="mb-0 fs-14 lh-26 opacity-7 text-white">
                                                                {addr.address}<br />
                                                                {addr.city}{addr.zip_code ? `, ${addr.zip_code}` : ''}<br />
                                                                {addr.country}
                                                                {addr.phone && <><br />{addr.phone}</>}
                                                            </p>
                                                            <div className="d-flex gap-2 mt-15px">
                                                                <button
                                                                    onClick={() => openAddressForm(addr)}
                                                                    className="btn btn-very-small btn-round-edge btn-dark-gray border border-color-extra-medium-gray"
                                                                >
                                                                    <i className="feather icon-feather-edit-2 me-5px"></i>Edit
                                                                </button>
                                                                <button
                                                                    onClick={() => handleAddressDelete(addr.id)}
                                                                    className="btn btn-very-small btn-round-edge border border-color-extra-medium-gray"
                                                                    style={{ color: '#ff6b6b', background: 'transparent' }}
                                                                >
                                                                    <i className="feather icon-feather-trash-2 me-5px"></i>Delete
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))
                                            )}
                                        </div>
                                    </div>
                                )}

                                {/* Security Tab */}
                                {activeTab === 'security' && (
                                    <div className="ui-stack-md">
                                        <h4 className="ui-section-title">Change Password</h4>
                                        <div className="bg-dark-gray border-radius-6px box-shadow-extra-large border border-color-extra-medium-gray ui-panel ui-panel-lg security-form-wrap" style={{ maxWidth: 500 }}>
                                            <form onSubmit={handlePasswordChange}>
                                                <div className="mb-20px">
                                                    <label className="text-white mb-10px fw-500 fs-14">Current Password <span className="text-red">*</span></label>
                                                    <input
                                                        type="password"
                                                        className="form-control bg-dark-gray-light border-color-transparent-white-light text-white"
                                                        value={passwordData.old_password}
                                                        onChange={e => setPasswordData({ ...passwordData, old_password: e.target.value })}
                                                        required
                                                    />
                                                </div>
                                                <div className="mb-20px">
                                                    <label className="text-white mb-10px fw-500 fs-14">New Password <span className="text-red">*</span></label>
                                                    <input
                                                        type="password"
                                                        className="form-control bg-dark-gray-light border-color-transparent-white-light text-white"
                                                        value={passwordData.new_password}
                                                        onChange={e => setPasswordData({ ...passwordData, new_password: e.target.value })}
                                                        required
                                                        minLength={6}
                                                    />
                                                </div>
                                                <div className="mb-25px">
                                                    <label className="text-white mb-10px fw-500 fs-14">Confirm New Password <span className="text-red">*</span></label>
                                                    <input
                                                        type="password"
                                                        className="form-control bg-dark-gray-light border-color-transparent-white-light text-white"
                                                        value={passwordData.confirm_password}
                                                        onChange={e => setPasswordData({ ...passwordData, confirm_password: e.target.value })}
                                                        required
                                                        minLength={6}
                                                    />
                                                </div>
                                                <button type="submit" className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow" disabled={passwordSaving}>
                                                    {passwordSaving ? 'Updating...' : 'Update Password'}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                )}

                                {/* Wishlist Tab */}
                                {activeTab === 'wishlist' && (
                                    <div className="ui-stack-md">
                                        <h4 className="ui-section-title">My Wishlist</h4>
                                        {wishlistItems.length === 0 ? (
                                            <div className="bg-dark-gray border-radius-6px border border-color-extra-medium-gray ui-panel ui-panel-lg text-center py-50px">
                                                <i className="feather icon-feather-heart text-white opacity-3" style={{ fontSize: 40, display: 'block', marginBottom: 16 }}></i>
                                                <p className="text-white fw-600 mb-5px">Your wishlist is empty</p>
                                                <p className="text-white opacity-5 fs-14 mb-20px">Save items you love to find them easily later.</p>
                                                <Link href="/shop" className="btn btn-small btn-round-edge btn-base-color">Browse Products</Link>
                                            </div>
                                        ) : (
                                            <div className="row g-3 wishlist-grid">
                                                {wishlistItems.map((item: any) => {
                                                    const product = item.product ?? item;
                                                    return (
                                                        <div key={item.id} className="col-md-6 col-lg-4">
                                                            <div className="bg-dark-gray border-radius-6px border border-color-extra-medium-gray p-20px h-100 d-flex flex-column gap-12px">
                                                                {product.thumbnail && (
                                                                    <Link href={`/product/${product.slug}`}>
                                                                        <img src={product.thumbnail} alt={product.name} style={{ width: '100%', height: 160, objectFit: 'cover', borderRadius: 6 }} />
                                                                    </Link>
                                                                )}
                                                                <div className="flex-grow-1">
                                                                    <Link href={`/product/${product.slug}`} className="text-white fw-600 fs-14" style={{ textDecoration: 'none' }}>
                                                                        {product.name}
                                                                    </Link>
                                                                    <p className="text-base-color fw-700 fs-15 mt-5px mb-0">{product.currency_price ?? product.selling_price}</p>
                                                                </div>
                                                                <div className="d-flex gap-2">
                                                                    <Link href={`/product/${product.slug}`} className="btn btn-small btn-round-edge btn-base-color flex-grow-1 text-center" style={{ fontSize: 12 }}>View Product</Link>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        )}
                                    </div>
                                )}

                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    // Not logged in — show login / register
    return (
        <main>
            <section className="top-space-padding page-shell page-shell-tight">
                <div className="container">
                    {error && <div className="alert alert-danger mb-30px">{error}</div>}
                    <div className="row g-0 justify-content-center">
                        <div className="col-xl-4 col-lg-5 col-md-10 contact-form-style-04 md-mb-50px me-lg-4">
                            <div className="bg-dark-gray box-shadow-extra-large border-radius-6px border border-color-extra-medium-gray ui-panel ui-panel-lg h-100">
                                <span className="fs-26 xs-fs-24 alt-font fw-600 text-white mb-20px d-block">Member login</span>
                                <form onSubmit={handleLogin}>
                                    <label className="text-white mb-10px fw-500 fs-14">Email address<span className="text-red">*</span></label>
                                    <input className="mb-20px bg-dark-gray-light border-color-transparent-white-light text-white form-control required" value={loginData.email} onChange={(e) => setLoginData({ ...loginData, email: e.target.value })} placeholder="Enter your email" type="email" required />
                                    <label className="text-white mb-10px fw-500 fs-14">Password<span className="text-red">*</span></label>
                                    <input className="mb-20px bg-dark-gray-light border-color-transparent-white-light text-white form-control required" value={loginData.password} onChange={(e) => setLoginData({ ...loginData, password: e.target.value })} placeholder="Enter your password" type="password" required />
                                    <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600" type="submit" disabled={loading}>
                                        {loading ? 'Logging in...' : 'Login'}
                                    </button>
                                </form>

                                {/* Divider */}
                                <div style={{ display: 'flex', alignItems: 'center', gap: 12, margin: '20px 0' }}>
                                    <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.1)' }}></div>
                                    <span style={{ color: 'rgba(255,255,255,0.35)', fontSize: 12, whiteSpace: 'nowrap' }}>or continue with</span>
                                    <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.1)' }}></div>
                                </div>

                                {/* Google Sign-In */}
                                <div id="google-login-btn" style={{ display: 'flex', justifyContent: 'center' }}></div>
                            </div>
                        </div>
                        <div className="col-lg-6 col-md-10 offset-xl-1">
                            <div className="box-shadow-extra-large border-radius-6px bg-dark-gray border border-color-extra-medium-gray ui-panel ui-panel-lg">
                                {!otpStep ? (
                                    <>
                                        <span className="fs-26 xs-fs-24 alt-font fw-600 text-white mb-20px d-block">Create an account</span>
                                        <p className="mb-25px text-white">Registering for this site allows you to access your order status and history.</p>
                                        <form onSubmit={handleRegister}>
                                            <div className="row">
                                                <div className="col-md-6 mb-20px">
                                                    <label className="text-white mb-10px fw-500 fs-14">Full Name<span className="text-red">*</span></label>
                                                    <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control required" placeholder="Enter your name" type="text" value={registerData.name} onChange={e => setRegisterData({ ...registerData, name: e.target.value })} required />
                                                </div>
                                                <div className="col-md-6 mb-20px">
                                                    <label className="text-white mb-10px fw-500 fs-14">Email address<span className="text-red">*</span></label>
                                                    <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control required" placeholder="Enter email" type="email" value={registerData.email} onChange={e => setRegisterData({ ...registerData, email: e.target.value })} required />
                                                </div>
                                                <div className="col-md-6 mb-20px">
                                                    <label className="text-white mb-10px fw-500 fs-14">Password<span className="text-red">*</span></label>
                                                    <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control required" placeholder="Enter password" type="password" value={registerData.password} onChange={e => setRegisterData({ ...registerData, password: e.target.value })} required minLength={6} />
                                                </div>
                                                <div className="col-md-6 mb-20px">
                                                    <label className="text-white mb-10px fw-500 fs-14">Confirm password<span className="text-red">*</span></label>
                                                    <input className="bg-dark-gray-light text-white border-color-transparent-white-light form-control required" placeholder="Confirm password" type="password" value={registerData.password_confirmation} onChange={e => setRegisterData({ ...registerData, password_confirmation: e.target.value })} required minLength={6} />
                                                </div>
                                            </div>
                                            <button className="btn btn-medium btn-round-edge btn-white btn-box-shadow w-100 text-transform-none fw-600 mt-10px" type="submit" disabled={loading}>
                                                {loading ? 'Creating account...' : 'Register'}
                                            </button>
                                        </form>
                                    </>
                                ) : (
                                    <>
                                        <div className="text-center mb-25px">
                                            <div style={{ width: 60, height: 60, borderRadius: '50%', background: 'rgba(197,160,89,0.12)', border: '1px solid rgba(197,160,89,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                                                <i className="feather icon-feather-mail fs-24" style={{ color: 'var(--base-color)' }}></i>
                                            </div>
                                            <span className="fs-26 xs-fs-22 alt-font fw-600 text-white mb-10px d-block">Verify your email</span>
                                            <p className="text-white opacity-6 fs-14 mb-0">We sent a 6-digit code to</p>
                                            <p className="fw-600 fs-14 mb-0" style={{ color: 'var(--base-color)' }}>{otpEmail}</p>
                                        </div>
                                        <form onSubmit={handleVerifyOtp}>
                                            <div className="mb-20px">
                                                <label className="text-white mb-10px fw-500 fs-14">Verification Code <span className="text-red">*</span></label>
                                                <input
                                                    className="bg-dark-gray-light text-white border-color-transparent-white-light form-control text-center fw-700 fs-22 letter-spacing-5px"
                                                    placeholder="000000"
                                                    type="text"
                                                    maxLength={6}
                                                    value={otpToken}
                                                    onChange={e => setOtpToken(e.target.value.replace(/\D/g, ''))}
                                                    required
                                                    autoFocus
                                                    style={{ letterSpacing: '0.35em', fontSize: 22, textAlign: 'center' }}
                                                />
                                            </div>
                                            <button className="btn btn-medium btn-round-edge btn-base-color btn-box-shadow w-100 text-transform-none fw-600" type="submit" disabled={loading}>
                                                {loading ? 'Verifying...' : 'Verify & Continue'}
                                            </button>
                                        </form>
                                        <div className="text-center mt-20px">
                                            <p className="text-white opacity-5 fs-13 mb-8px">Didn't receive the code?</p>
                                            <button onClick={handleResendOtp} className="btn-link text-white opacity-7 fs-13 fw-500" style={{ background: 'none', border: 'none', cursor: 'pointer', textDecoration: 'underline' }}>
                                                Resend OTP
                                            </button>
                                            <span className="text-white opacity-3 mx-10px">·</span>
                                            <button onClick={() => { setOtpStep(false); setOtpToken(''); }} className="btn-link text-white opacity-7 fs-13 fw-500" style={{ background: 'none', border: 'none', cursor: 'pointer', textDecoration: 'underline' }}>
                                                Change email
                                            </button>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}

export default function Account() {
    return (
        <Suspense fallback={<PageLoadingShell variant="account" />}>
            <AccountContent />
        </Suspense>
    );
}
