'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import { apiFetch } from '@/lib/api';
import { useRouter } from 'next/navigation';
import LoadingSkeleton from '@/components/LoadingSkeleton';
import { extractCurrencySymbol, readStoredCoupon, storeCoupon } from '@/lib/checkout';
import { useCurrency } from '@/components/SettingsProvider';
import { loadStripe } from '@stripe/stripe-js';
import { Elements } from '@stripe/react-stripe-js';
import StripePaymentForm from '@/components/StripePaymentForm';

export default function Checkout() {
    const router = useRouter();
    const [cartItems, setCartItems] = useState<any[]>([]);
    const [subtotal, setSubtotal] = useState(0);
    const [tax, setTax] = useState(0);
    const [shippingCost, setShippingCost] = useState(0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [placingOrder, setPlacingOrder] = useState(false);
    const [addresses, setAddresses] = useState<any[]>([]);
    const [orderAreas, setOrderAreas] = useState<any[]>([]);
    const [shippingSettings, setShippingSettings] = useState<any>(null);
    const [paymentGateways, setPaymentGateways] = useState<any[]>([]);
    const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
    const { currency: { symbol: currencySymbol }, formatAmount } = useCurrency();
    const [couponCode, setCouponCode] = useState('');
    const [couponDiscount, setCouponDiscount] = useState(0);
    const [couponMessage, setCouponMessage] = useState('');
    const [couponId, setCouponId] = useState<number | null>(null);
    const [formData, setFormData] = useState({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        zip: '',
        country: 'United Arab Emirates',
        country_code: '',
        order_note: '',
        payment_method: '2', // Default to E-wallet (Stripe)
    });

    const [stripePromise, setStripePromise] = useState<any>(null);
    const [stripeOptions, setStripeOptions] = useState<any>(null);
    const [currentOrderId, setCurrentOrderId] = useState<number | null>(null);
    const [paymentCancelled, setPaymentCancelled] = useState(false);
    const [orderError, setOrderError] = useState<{ message: string; action?: string; orderId?: number } | null>(null);
    const [guestGate, setGuestGate] = useState(false);
    const [guestFormOpen, setGuestFormOpen] = useState(false);
    const [guestName, setGuestName] = useState('');
    const [guestEmail, setGuestEmail] = useState('');
    const [guestPhone, setGuestPhone] = useState('');
    const [guestLoading, setGuestLoading] = useState(false);
    const [guestError, setGuestError] = useState<string | null>(null);
    const [agreedToTerms, setAgreedToTerms] = useState(false);
    const paymentSectionRef = useRef<HTMLDivElement>(null);

    // Auto-scroll to payment section when it appears
    useEffect(() => {
        if (stripeOptions) {
            setTimeout(() => {
                paymentSectionRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
    }, [stripeOptions]);

    const totalAmount = Math.max(subtotal + tax + shippingCost - couponDiscount, 0);
    const selectedGateway = paymentGateways.find(g => String(g.id) === formData.payment_method) ?? null;
    const paymentGateway = selectedGateway?.slug ?? 'stripe';

    const getAreaList = (response: any) => Array.isArray(response)
        ? response
        : Array.isArray(response?.data)
            ? response.data
            : [];

    const buildAddressPayload = () => ({
        full_name: `${formData.first_name} ${formData.last_name}`.trim(),
        email: formData.email.trim(),
        phone: formData.phone.trim(),
        country_code: formData.country_code.trim(),
        country: formData.country.trim(),
        address: formData.address.trim(),
        city: formData.city.trim(),
        state: formData.state.trim(),
        zip_code: formData.zip.trim(),
    });

    const formMatchesAddress = (address: any) => {
        if (!address) return false;
        const payload = buildAddressPayload();
        return (
            (address.full_name || '').trim() === payload.full_name &&
            (address.email || '').trim() === payload.email &&
            (address.phone || '').trim() === payload.phone &&
            (address.country_code || '').trim() === payload.country_code &&
            (address.country || '').trim() === payload.country &&
            (address.address || '').trim() === payload.address &&
            (address.city || '').trim() === payload.city &&
            (address.state || '').trim() === payload.state &&
            (address.zip_code || '').trim() === payload.zip_code
        );
    };

    const getBestMatchingOrderArea = useCallback((country: string, state: string, city: string) => {
        const norm = (v: any) => (v ?? '').toString().trim().toLowerCase();
        const nc = norm(country);
        const ns = norm(state);
        const nci = norm(city);
        const activeAreas = orderAreas.filter((area: any) => Number(area.status) === 1 || area.status === undefined || area.status === null);

        // 1. Exact city match
        const exactCity = activeAreas.find((area: any) =>
            norm(area.country) === nc && norm(area.state) === ns && norm(area.city) === nci && nci !== ''
        );
        if (exactCity) return exactCity;

        // 2. State match (no city restriction on area)
        const stateMatch = activeAreas.find((area: any) =>
            norm(area.country) === nc && norm(area.state) === ns && ns !== '' && !area.city
        );
        if (stateMatch) return stateMatch;

        // 3. Country-level fallback (area has no state/city set)
        const countryMatch = activeAreas.find((area: any) =>
            norm(area.country) === nc && !area.state && !area.city
        );
        if (countryMatch) return countryMatch;

        // 4. Last resort: any active area for this country
        return activeAreas.find((area: any) => norm(area.country) === nc) || null;
    }, [orderAreas]);

    const syncCoupon = useCallback(async (nextSubtotal: number) => {
        const storedCoupon = readStoredCoupon();
        if (!storedCoupon?.code) {
            setCouponDiscount(0);
            setCouponId(null);
            return;
        }

        setCouponCode(storedCoupon.code);
        try {
            const response = await apiFetch('/frontend/coupon/coupon-checking', {
                method: 'POST',
                body: JSON.stringify({ code: storedCoupon.code, total: nextSubtotal }),
            });
            const couponData = response?.data ?? response;
            const nextDiscount = parseFloat(couponData?.discount || 0);
            const nextCurrencyDiscount = couponData?.currency_discount || formatAmount(nextDiscount);
            const nextSymbol = extractCurrencySymbol(nextCurrencyDiscount, currencySymbol);

            setCouponDiscount(nextDiscount);
            setCouponId(couponData?.id ?? storedCoupon.id ?? null);
            setCouponMessage(`Coupon applied: -${nextCurrencyDiscount}`);
            storeCoupon({
                code: storedCoupon.code,
                id: couponData?.id ?? storedCoupon.id ?? null,
                discount: nextDiscount,
                currencyDiscount: nextCurrencyDiscount,
                symbol: nextSymbol,
            });
        } catch (couponError: any) {
            setCouponDiscount(0);
            setCouponId(null);
            setCouponCode('');
            setCouponMessage(couponError.message || 'Saved coupon is no longer valid');
            storeCoupon(null);
        }
    }, [currencySymbol, formatAmount]);

    const fetchCheckoutData = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);
            const storedCoupon = readStoredCoupon();
            if (storedCoupon) {
                setCouponCode(storedCoupon.code);
                setCouponDiscount(storedCoupon.discount);
                setCouponId(storedCoupon.id);
            }

            const [cartResponse, addressResponse, orderAreaResponse, gatewayResponse, settingResponse] = await Promise.all([
                apiFetch('/cart'),
                apiFetch('/addresses'),
                apiFetch('/frontend/order-area').catch(() => ({ data: [] })),
                apiFetch('/frontend/payment-gateway').catch(() => ({ data: [] })),
                apiFetch('/frontend/setting').catch(() => ({})),
            ]);

            if (cartResponse.status) {
                const items = cartResponse.data ?? [];
                // Redirect to cart if empty — nothing to checkout
                if (items.length === 0) {
                    window.location.href = '/cart';
                    return;
                }
                setCartItems(items);
                const total = items.reduce((acc: number, item: any) => acc + parseFloat(item.subtotal), 0);
                setSubtotal(total);
                const totalTax = items.reduce((acc: number, item: any) => acc + parseFloat(item.tax || 0), 0);
                setTax(totalTax);
                await syncCoupon(total);
            } else {
                setError('Failed to load cart items');
            }

            setOrderAreas(getAreaList(orderAreaResponse));
            setShippingSettings(settingResponse?.data ?? settingResponse ?? null);

            const allGateways: any[] = getAreaList(gatewayResponse);
            const gateways = allGateways.filter((g: any) => Number(g.status) === 5 && g.slug !== 'credit');
            setPaymentGateways(gateways);
            if (gateways.length > 0) {
                setFormData(prev => ({ ...prev, payment_method: String(gateways[0].id) }));
            } else {
                // Clear stale default so a disabled gateway is never submitted
                setFormData(prev => ({ ...prev, payment_method: '' }));
            }


            if (addressResponse.status && addressResponse.data.length > 0) {
                setAddresses(addressResponse.data);
                setSelectedAddressId(addressResponse.data[0].id);
                // Pre-fill form if addresses exist
                const primary = addressResponse.data[0];
                setFormData(prev => ({
                    ...prev,
                    first_name: primary.full_name.split(' ')[0] || '',
                    last_name: primary.full_name.split(' ').slice(1).join(' ') || '',
                    email: primary.email,
                    phone: primary.phone,
                    address: primary.address,
                    city: primary.city,
                    state: primary.state || '',
                    zip: primary.zip_code,
                    country: primary.country,
                    country_code: primary.country_code || '',
                }));
            }
        } catch (error: any) {
            console.error('Failed to fetch checkout data:', error);
            setError(error.message || 'Failed to load checkout data');
        } finally {
            setLoading(false);
        }
    }, [syncCoupon]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleAddressSelect = (addressId: number) => {
        setSelectedAddressId(addressId);
        const selectedAddress = addresses.find((address: any) => address.id === addressId);
        if (!selectedAddress) return;

        setFormData(prev => ({
            ...prev,
            first_name: selectedAddress.full_name.split(' ')[0] || '',
            last_name: selectedAddress.full_name.split(' ').slice(1).join(' ') || '',
            email: selectedAddress.email || '',
            phone: selectedAddress.phone || '',
            address: selectedAddress.address || '',
            city: selectedAddress.city || '',
            state: selectedAddress.state || '',
            zip: selectedAddress.zip_code || '',
            country: selectedAddress.country || '',
            country_code: selectedAddress.country_code || '',
        }));
    };

    const applyCoupon = async () => {
        if (!couponCode.trim()) return;
        setCouponMessage('');
        try {
            const response = await apiFetch('/frontend/coupon/coupon-checking', {
                method: 'POST',
                body: JSON.stringify({ code: couponCode.trim(), total: subtotal }),
            });
            const couponData = response?.data ?? response;
            const discount = parseFloat(couponData?.discount || 0);
            const nextCurrencyDiscount = couponData?.currency_discount || formatAmount(discount);
            const nextSymbol = extractCurrencySymbol(nextCurrencyDiscount, currencySymbol);

            setCouponDiscount(discount);
            setCouponId(couponData?.id || null);
            setCouponMessage(`Coupon applied: -${nextCurrencyDiscount}`);
            storeCoupon({
                code: couponCode.trim(),
                id: couponData?.id || null,
                discount,
                currencyDiscount: nextCurrencyDiscount,
                symbol: nextSymbol,
            });
        } catch (couponError: any) {
            setCouponDiscount(0);
            setCouponId(null);
            setCouponMessage(couponError.message || 'Failed to apply coupon');
            storeCoupon(null);
        }
    };

    const placeOrder = async (e: React.FormEvent) => {
        e.preventDefault();
        setOrderError(null);

        // Client-side address validation before hitting the API
        if (!selectedAddressId) {
            const f = formData;
            if (!f.first_name.trim()) { setOrderError({ message: 'Please enter your first name.', action: 'address' }); return; }
            if (!f.last_name.trim())  { setOrderError({ message: 'Please enter your last name.', action: 'address' }); return; }
            if (!f.email.trim())      { setOrderError({ message: 'Please enter your email address.', action: 'address' }); return; }
            if (!f.phone.trim())      { setOrderError({ message: 'Please enter your phone number.', action: 'address' }); return; }
            if (!f.address.trim())    { setOrderError({ message: 'Please enter your street address.', action: 'address' }); return; }
            if (!f.city.trim())       { setOrderError({ message: 'Please enter your city.', action: 'address' }); return; }
            if (!f.country.trim())    { setOrderError({ message: 'Please select your country.', action: 'address' }); return; }
        }

        setPlacingOrder(true);

        try {
            let addressId = selectedAddressId;
            // Re-verify selected address still exists in local state (could have been deleted in another tab)
            const selectedAddress = addresses.find((address: any) => address.id === selectedAddressId) || null;
            if (addressId && !selectedAddress) {
                // Address was deleted — clear the stale selection and proceed to create a fresh one
                addressId = null;
                setSelectedAddressId(null);
            }
            const addressPayload = buildAddressPayload();

            // 1. Create a shipping address if the user has no saved one
            // or if they edited the prefilled form away from the selected saved address.
            if (!addressId || addresses.length === 0 || !formMatchesAddress(selectedAddress)) {
                const addressResponse = await apiFetch('/addresses', {
                    method: 'POST',
                    body: JSON.stringify(addressPayload),
                });
                if (addressResponse.status) {
                    addressId = addressResponse.data.id;
                    setAddresses(prev => [...prev, addressResponse.data]);
                    setSelectedAddressId(addressResponse.data.id);
                } else {
                    setOrderError({ message: 'Could not save your delivery address. Please check all address fields and try again.', action: 'address' });
                    return;
                }
            }

            // 2. Prepare Order Data
            const products = cartItems.map(item => ({
                product_id: item.product_id,
                variation_id: item.variation_id || 0,
                variation_names: item.variation_names || '',
                sku: item.sku || '',
                price: item.price,
                quantity: item.quantity,
                discount: item.discount_amount || 0,
                total_tax: item.tax || 0,
                subtotal: item.subtotal,
                total: item.total,
                taxes: [] // Assume no complex tax breakdown for now
            }));

            const orderPayload = {
                subtotal: subtotal,
                discount: couponDiscount,
                coupon_id: couponId || undefined,
                shipping_charge: shippingCost,
                tax: tax,
                total: totalAmount,
                order_type: 5, // DELIVERY
                shipping_id: addressId,
                billing_id: addressId,
                source: 5, // WEB
                payment_method: parseInt(formData.payment_method),
                reason: formData.order_note.trim() || undefined,
                products: JSON.stringify(products),
            };

            // 3. Create Order
            const orderResponse = await apiFetch('/orders', {
                method: 'POST',
                body: JSON.stringify(orderPayload),
            });

            if (orderResponse.status) {
                const orderId = orderResponse.data.id;

                // Removed client-side cart clearing. Success is handled by backend + landing page.
                storeCoupon(null);

                // 5. Initiate Payment
                const paymentResponse = await apiFetch('/payment/initiate', {
                    method: 'POST',
                    body: JSON.stringify({
                        order_id: orderId,
                        payment_gateway: paymentGateway,
                    }),
                });

                if (paymentResponse.status) {
                    if (paymentGateway === 'stripe' && paymentResponse.data?.client_secret) {
                        const { publishableKey, client_secret } = paymentResponse.data;

                        if (!publishableKey) {
                            console.error('Stripe error: publishableKey is missing from backend response');
                            setOrderError({
                                message: 'Stripe is not configured correctly on the server. Please contact support or choose a different payment method.',
                                action: 'payment',
                                orderId,
                            });
                            return;
                        }

                        setStripePromise(loadStripe(publishableKey));
                        setStripeOptions({
                            clientSecret: client_secret,
                            appearance: { theme: 'night', labels: 'floating' },
                        });
                        setCurrentOrderId(orderId);
                    } else if (paymentResponse.data?.redirect_url) {
                        window.location.href = paymentResponse.data.redirect_url;
                    } else {
                        router.push(`/payment/success?order_id=${orderId}`);
                    }
                } else {
                    // Order was created but payment failed — restore coupon so user can retry
                    const storedCoupon = readStoredCoupon();
                    if (!storedCoupon && couponId) {
                        storeCoupon({ code: couponCode, id: couponId, discount: couponDiscount, currencyDiscount: '', symbol: '' });
                    }
                    setOrderError({
                        message: `Your order was created (ref: #${orderId}) but payment could not be started. ${paymentResponse.message || ''} Please contact support or try again.`.trim(),
                        action: 'payment',
                        orderId,
                    });
                }
            } else {
                // Surface the backend validation message clearly
                const apiMsg = orderResponse.message || '';
                setOrderError({
                    message: apiMsg
                        ? `Could not place order: ${apiMsg}`
                        : 'Something went wrong while placing your order. Please review your details and try again.',
                    action: 'order',
                });
            }
        } catch (error: any) {
            console.error('Order placement failed:', error);
            const msg = error.message || '';
            setOrderError({
                message: msg.toLowerCase().includes('network') || msg.toLowerCase().includes('fetch')
                    ? 'A network error occurred. Please check your internet connection and try again.'
                    : msg || 'An unexpected error occurred. Please try again.',
                action: 'network',
            });
        } finally {
            setPlacingOrder(false);
        }
    };

    const handleGuestCheckout = async (e: React.FormEvent) => {
        e.preventDefault();
        setGuestError(null);
        if (!guestName.trim()) { setGuestError('Please enter your name.'); return; }
        if (!guestEmail.trim()) { setGuestError('Please enter your email address.'); return; }
        setGuestLoading(true);
        try {
            const password = Math.random().toString(36).slice(-8) + Math.random().toString(36).toUpperCase().slice(-4) + '!2';
            const res = await apiFetch('/v1/auth/register', {
                method: 'POST',
                body: JSON.stringify({ name: guestName.trim(), email: guestEmail.trim(), phone: guestPhone.trim() || undefined, password }),
            });
            if (res?.status && res?.data?.access_token) {
                localStorage.setItem('token', res.data.access_token);
                localStorage.setItem('user', JSON.stringify(res.data.user ?? {}));
                window.dispatchEvent(new CustomEvent('auth:login', {}));
                setGuestGate(false);
                fetchCheckoutData();
            } else {
                setGuestError(res?.message || 'Could not create guest account. Please try signing in.');
            }
        } catch (err: any) {
            setGuestError(err.message || 'Something went wrong. Please try signing in.');
        } finally {
            setGuestLoading(false);
        }
    };

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!token) {
            setGuestGate(true);
            setLoading(false);
            return;
        }
        fetchCheckoutData();
    }, [fetchCheckoutData]);

    useEffect(() => {
        const method = Number(shippingSettings?.shipping_setup_method ?? 0);
        // 5 = FREE, 10 = FLAT_WISE, 15 = AREA_WISE
        if (method === 5) {
            setShippingCost(0);
        } else if (method === 10) {
            setShippingCost(parseFloat(shippingSettings?.shipping_setup_flat_rate_wise_cost || 0));
        } else if (method === 15) {
            const matchingArea = getBestMatchingOrderArea(formData.country, formData.state, formData.city);
            const cost = matchingArea
                ? parseFloat(matchingArea.shipping_cost || 0)
                : parseFloat(shippingSettings?.shipping_setup_area_wise_default_cost || 0);
            setShippingCost(cost);
        } else {
            setShippingCost(0);
        }
    }, [formData.country, formData.state, formData.city, getBestMatchingOrderArea, shippingSettings]);

    if (loading) {
        return (
            <main>
                <section className="page-shell">
                    <div className="container">
                        <div className="row align-items-start">
                            <div className="col-lg-7 pe-50px md-pe-15px md-mb-50px xs-mb-35px">
                                <LoadingSkeleton type="form" />
                            </div>
                            <div className="col-lg-5">
                                <LoadingSkeleton type="card" />
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (guestGate) {
        return (
            <main className="no-layout-pad page-top-100">
                <section className="page-shell d-flex align-items-center" style={{ minHeight: '70vh' }}>
                    <div className="container">
                        <div className="row justify-content-center">
                            <div className="col-lg-8 col-xl-6">
                                <div className="bh-gate-card">
                                    <div className="bh-card-accent" />
                                    <div className="bh-gate-body">
                                        <p className="checkout-section-title">Checkout</p>
                                        <h2 className="checkout-section-heading">Continue to checkout</h2>
                                        <p className="checkout-section-sub">
                                            Sign in for faster checkout with saved addresses and order tracking.
                                        </p>

                                        <div className="row g-3">
                                            <div className="col-6">
                                                <a href="/account?redirect=/checkout" className="checkout-option-card checkout-option-card--primary">
                                                    <i className="feather icon-feather-log-in" style={{ fontSize: 20, color: '#000' }}></i>
                                                    <span className="checkout-option-label" style={{ color: '#000' }}>Sign In</span>
                                                    <span className="checkout-option-sub" style={{ color: 'rgba(0,0,0,0.6)' }}>Saved address &amp; orders</span>
                                                </a>
                                            </div>
                                            <div className="col-6">
                                                <a href="/account?tab=register&redirect=/checkout" className="checkout-option-card checkout-option-card--secondary">
                                                    <i className="feather icon-feather-user-plus" style={{ fontSize: 20, color: '#fff' }}></i>
                                                    <span className="checkout-option-label" style={{ color: '#fff' }}>Create Account</span>
                                                    <span className="checkout-option-sub" style={{ color: 'rgba(255,255,255,0.4)' }}>Free &amp; 30 seconds</span>
                                                </a>
                                            </div>
                                        </div>

                                        <div className="bh-divider">
                                            <span className="bh-divider-label">or</span>
                                        </div>

                                        {/* Guest checkout form */}
                                        {!guestFormOpen ? (
                                            <button
                                                type="button"
                                                onClick={() => setGuestFormOpen(true)}
                                                className="checkout-option-card checkout-option-card--ghost d-flex align-items-center justify-content-center gap-2 fs-14 fw-600 text-white-50 border-0">
                                                <i className="feather icon-feather-shopping-bag fs-16"></i>
                                                Continue as Guest
                                            </button>
                                        ) : (
                                            <form onSubmit={handleGuestCheckout} className="ui-stack-sm">
                                                <p className="fs-13 mb-0" style={{ color: 'rgba(255,255,255,0.5)', lineHeight: 1.5 }}>
                                                    Enter your details to checkout. We&apos;ll create a guest account so you can track your order.
                                                </p>
                                                <input type="text" placeholder="Full name *" required className="bh-input"
                                                    value={guestName} onChange={e => setGuestName(e.target.value)} />
                                                <input type="email" placeholder="Email address *" required className="bh-input"
                                                    value={guestEmail} onChange={e => setGuestEmail(e.target.value)} />
                                                <input type="tel" placeholder="Phone (optional)" className="bh-input"
                                                    value={guestPhone} onChange={e => setGuestPhone(e.target.value)} />
                                                {guestError && (
                                                    <p className="fs-13 mb-0 text-base-color">{guestError}</p>
                                                )}
                                                <button type="submit" disabled={guestLoading}
                                                    className="btn btn-base-color btn-round-edge w-100 fw-700"
                                                    style={{ opacity: guestLoading ? 0.6 : 1, cursor: guestLoading ? 'not-allowed' : 'pointer' }}>
                                                    {guestLoading ? 'Please wait...' : 'Continue to Checkout →'}
                                                </button>
                                                <button type="button" onClick={() => { setGuestFormOpen(false); setGuestError(null); }}
                                                    className="btn-link fs-12 text-white-50 bg-transparent border-0 p-0 cursor-pointer">
                                                    ← Back
                                                </button>
                                            </form>
                                        )}

                                        <div className="mt-20px">
                                            <a href="/cart" className="d-flex align-items-center justify-content-center gap-2 fs-12 fw-600 text-decoration-none"
                                                style={{ padding: '10px 20px', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 10, color: 'rgba(255,255,255,0.35)' }}>
                                                <i className="feather icon-feather-arrow-left fs-13"></i>
                                                Back to Cart
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (error) {
        return (
            <main>
                <section className="page-shell">
                    <div className="container">
                        <div className="text-center">
                            <h2 className="text-white mb-4">Checkout Error</h2>
                            <p className="text-white/70 mb-4">{error}</p>
                            <button className="btn btn-primary me-3" onClick={fetchCheckoutData}>Try Again</button>
                            <a href="/cart" className="btn btn-outline-primary">Back to Cart</a>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    if (cartItems.length === 0) {
        return (
            <main>
                <section className="page-shell">
                    <div className="container">
                        <div className="text-center">
                            <h2 className="text-white mb-4">Your cart is empty</h2>
                            <p className="text-white/70 mb-4">Add some products to your cart before checkout</p>
                            <a href="/shop" className="btn btn-primary">Start Shopping</a>
                        </div>
                    </div>
                </section>
            </main>
        );
    }

    return (
        <main className="no-layout-pad page-top-100">
            <section className="pt-20px pb-20px ps-45px pe-45px lg-ps-35px lg-pe-35px md-ps-15px md-pe-15px">
                <div className="container-fluid">
                    <div className="col-12 breadcrumb breadcrumb-style-01 fs-14">
                        <ul>
                            <li><a href="/" className="breadcrumb-link">Home</a></li>
                            <li><a href="/cart" className="breadcrumb-link">Cart</a></li>
                            <li>Checkout</li>
                        </ul>
                    </div>
                </div>
            </section>
            <section className="page-shell page-shell-tight">
                <div className="container">
                    {paymentCancelled && (
                        <div className="animate__animated animate__fadeInDown" style={{
                            display: 'flex', alignItems: 'flex-start', gap: 15,
                            background: 'rgba(234,179,8,0.08)', border: '1px solid rgba(234,179,8,0.25)',
                            borderRadius: 12, padding: '18px 22px', marginBottom: 35,
                            backdropFilter: 'blur(10px)'
                        }}>
                            <div style={{ width: 36, height: 36, borderRadius: '50%', background: 'rgba(234,179,8,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                <i className="feather icon-feather-info" style={{ color: '#eab308', fontSize: 18 }}></i>
                            </div>
                            <div style={{ flex: 1 }}>
                                <p className="mb-1 fw-700 fs-15" style={{ color: '#fbbf24' }}>Payment Status: Cancelled</p>
                                <p className="mb-0 fs-14 opacity-7" style={{ color: '#fff', lineHeight: 1.5 }}>
                                    Your payment was cancelled and no charges were made. Your order details are saved below — simply click <strong className="text-white">Place Order</strong> to try again.
                                </p>
                            </div>
                            <button onClick={() => setPaymentCancelled(false)} className="bg-transparent border-0 opacity-4 hover-opacity-100 text-white p-0 fs-20" style={{ lineHeight: 1 }}>&times;</button>
                        </div>
                    )}
                    <form onSubmit={placeOrder}>
                        <div className="row align-items-start">
                            <div className="col-lg-7 pe-50px md-pe-15px md-mb-50px xs-mb-35px">
                                <span className="ui-section-title mb-25px d-block">Billing details</span>
                                {addresses.length > 0 && (
                                    <div className="ui-panel ui-panel-sm mb-30px">
                                        <label className="mb-12px d-block text-white text-uppercase fs-11 fw-700 ls-1px">Saved address</label>
                                        <select
                                            className="border-radius-4px input-small"
                                            value={selectedAddressId ?? ''}
                                            onChange={(e) => handleAddressSelect(Number(e.target.value))}
                                        >
                                            {addresses.map((address: any) => (
                                                <option key={address.id} value={address.id}>
                                                    {address.full_name} - {address.address}, {address.city}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}
                                <div className="row g-4">
                                    <div className="col-md-6">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">First name <span className="text-red">*</span></label>
                                        <input name="first_name" value={formData.first_name} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" required type="text" placeholder="John" />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Last name <span className="text-red">*</span></label>
                                        <input name="last_name" value={formData.last_name} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" required type="text" placeholder="Doe" />
                                    </div>
                                    <div className="col-12">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Email address <span className="text-red">*</span></label>
                                        <input name="email" value={formData.email} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" required type="email" placeholder="john@example.com" />
                                    </div>
                                    <div className="col-12">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Phone number <span className="text-red">*</span></label>
                                        <div className="d-flex gap-2">
                                            <input name="country_code" value={formData.country_code} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white w-20" placeholder="+971" type="text" />
                                            <input name="phone" value={formData.phone} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white flex-grow-1" required type="text" placeholder="50 123 4567" />
                                        </div>
                                    </div>
                                    <div className="col-12">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Country / Region <span className="text-red">*</span></label>
                                        <select name="country" value={formData.country} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" required>
                                            {[
                                                'United Arab Emirates',
                                                'Saudi Arabia',
                                                'Kuwait',
                                                'Qatar',
                                                'Bahrain',
                                                'Oman',
                                            ].map(c => <option key={c} value={c} style={{ background: '#111' }}>{c}</option>)}
                                        </select>
                                    </div>
                                    <div className="col-12">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Street address <span className="text-red">*</span></label>
                                        <input name="address" value={formData.address} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" placeholder="House number and street name" required type="text" />
                                    </div>
                                    <div className="col-4">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Town / City <span className="text-red">*</span></label>
                                        <input name="city" value={formData.city} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" required type="text" placeholder="Dubai" />
                                    </div>
                                    <div className="col-4">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">State</label>
                                        <input name="state" value={formData.state} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" type="text" placeholder="Dubai" />
                                    </div>
                                    <div className="col-4">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Zip code</label>
                                        <input name="zip" value={formData.zip || ''} onChange={handleInputChange} className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white" type="text" placeholder="00000" />
                                    </div>
                                    <div className="col-12">
                                        <label className="mb-12px fs-11 fw-700 text-white text-uppercase ls-1px">Notes & details <span className="text-white opacity-4 fw-400 fs-10 ms-1">(optional)</span></label>
                                        <textarea
                                            name="order_note"
                                            value={formData.order_note}
                                            onChange={handleInputChange}
                                            className="border-radius-8px input-small bg-transparent border-color-transparent-white-light text-white"
                                            rows={3}
                                            maxLength={700}
                                            placeholder="Notes about your order, e.g. special instructions for delivery."
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className="col-lg-5">
                                <div className="order-summary-card">

                                    {/* Header */}
                                    <div className="order-summary-header">
                                        <span className="order-summary-title">Your order</span>
                                        <span className="order-summary-count">{cartItems.reduce((s, i) => s + i.quantity, 0)} item{cartItems.length !== 1 ? 's' : ''}</span>
                                    </div>

                                    {/* Product list */}
                                    <div className="order-item-list">
                                        {cartItems.map((item) => (
                                            <div key={item.id} className="order-item-row">
                                                {item.product?.cover && (
                                                    /* eslint-disable-next-line @next/next/no-img-element */
                                                    <img src={item.product.cover} alt={item.product?.name}
                                                        className="order-item-img"
                                                        onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }} />
                                                )}
                                                <div className="order-item-info">
                                                    <span className="order-item-name">{item.product?.name}</span>
                                                    {item.variation_names && <span className="order-item-variant">{item.variation_names}</span>}
                                                    <span className="order-item-qty">Qty: {item.quantity}</span>
                                                </div>
                                                <div className="order-item-price">
                                                    {item.old_price > item.price && (
                                                        <del className="order-item-old-price">{formatAmount(item.old_price)}</del>
                                                    )}
                                                    <span>{formatAmount(parseFloat(item.total))}</span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Totals */}
                                    <div className="order-totals">
                                        <div className="order-totals-row">
                                            <span>Subtotal</span>
                                            <span>{formatAmount(subtotal)}</span>
                                        </div>
                                        {tax > 0 && (
                                            <div className="order-totals-row">
                                                <span>Tax</span>
                                                <span>{formatAmount(tax)}</span>
                                            </div>
                                        )}
                                        {shippingCost > 0 && (
                                            <div className="order-totals-row">
                                                <span>Shipping</span>
                                                <span>{formatAmount(shippingCost)}</span>
                                            </div>
                                        )}
                                        {couponDiscount > 0 && (
                                            <div className="order-totals-row order-totals-row--discount">
                                                <span>Coupon discount</span>
                                                <span>−{formatAmount(couponDiscount)}</span>
                                            </div>
                                        )}
                                        <div className="order-totals-row order-totals-row--total">
                                            <span>Total</span>
                                            <span>{formatAmount(totalAmount)}</span>
                                        </div>
                                    </div>

                                    {/* Savings banner */}
                                    {(() => {
                                        const itemSavings = cartItems.reduce((acc, item) =>
                                            item.old_price > item.price
                                                ? acc + (parseFloat(item.old_price) - parseFloat(item.price)) * item.quantity
                                                : acc, 0);
                                        const totalSaved = itemSavings + couponDiscount;
                                        if (totalSaved <= 0) return null;
                                        return (
                                            <div className="order-savings-banner">
                                                <i className="feather icon-feather-tag order-savings-icon"></i>
                                                <span>You&apos;re saving <strong>{formatAmount(totalSaved)}</strong> on this order!</span>
                                            </div>
                                        );
                                    })()}

                                    {/* Coupon */}
                                    <div className="order-summary-section">
                                        <span className="order-summary-label">Coupon Code</span>
                                        <div className="coupon-code-panel mt-8px">
                                            <input
                                                type="text"
                                                autoComplete="off"
                                                data-form-type="other"
                                                placeholder="Have a voucher?"
                                                value={couponCode}
                                                onChange={e => setCouponCode(e.target.value)}
                                                onKeyDown={e => e.key === 'Enter' && (e.preventDefault(), applyCoupon())}
                                            />
                                            <button type="button" onClick={applyCoupon} className="apply-coupon-btn">Apply</button>
                                        </div>
                                        {couponMessage && (
                                            <p className={`fs-12 mt-8px mb-0 ${couponDiscount > 0 ? 'text-base-color' : 'text-red'}`}>{couponMessage}</p>
                                        )}
                                    </div>

                                    {/* Payment Method */}
                                    <div className="order-summary-section">
                                        <span className="order-summary-label">Payment Method</span>
                                        {paymentGateways.length === 0 ? (
                                            <p className="text-white opacity-6 fs-13 mt-8px mb-0">No payment methods available.</p>
                                        ) : (
                                            <div className="checkout-payment-grid mt-10px">
                                                {paymentGateways.map((gw: any) => {
                                                    const isSelected = formData.payment_method === String(gw.id);
                                                    return (
                                                        <label key={gw.id} htmlFor={`gw_${gw.id}`} className={`checkout-payment-card${isSelected ? ' selected' : ''}`}>
                                                            <input type="radio" name="payment_method" id={`gw_${gw.id}`} value={String(gw.id)} checked={isSelected} onChange={handleInputChange} className="visually-hidden" />
                                                            {gw.image && !gw.image.includes('default/payment-gateway/payment-gateway.png') ? (
                                                                <img src={gw.image} alt={gw.name} className="checkout-payment-logo"
                                                                    onError={(e) => {
                                                                        const img = e.currentTarget;
                                                                        if (gw.slug === 'stripe') { img.src = '/images/stripe_fallback.svg'; }
                                                                        else { img.style.display = 'none'; }
                                                                    }} />
                                                            ) : gw.slug === 'stripe' ? (
                                                                <img src="/images/stripe_fallback.svg" alt="Stripe" className="checkout-payment-logo" />
                                                            ) : (
                                                                <i className="feather icon-feather-credit-card checkout-payment-icon"></i>
                                                            )}
                                                            <span className="checkout-payment-name">{gw.name}</span>
                                                            {isSelected && <span className="checkout-payment-check"><i className="feather icon-feather-check"></i></span>}
                                                        </label>
                                                    );
                                                })}
                                            </div>
                                        )}
                                    </div>

                                    {/* Error */}
                                    {orderError && (
                                        <div className="order-error-panel animate__animated animate__shakeX">
                                            <i className="feather icon-feather-alert-triangle order-error-icon"></i>
                                            <div className="flex-1">
                                                <p className="order-error-title">{orderError.action?.charAt(0).toUpperCase()}{orderError.action?.slice(1)} Error</p>
                                                <p className="order-error-msg">{orderError.message}</p>
                                            </div>
                                            <button onClick={() => setOrderError(null)} className="order-error-close">&times;</button>
                                        </div>
                                    )}

                                    {/* T&C */}
                                    <div className="order-tc-row">
                                        <input type="checkbox" id="tc-agree" checked={agreedToTerms} onChange={e => setAgreedToTerms(e.target.checked)} className="visually-hidden" />
                                        <label htmlFor="tc-agree" className="order-tc-box" style={{ borderColor: agreedToTerms ? 'var(--base-color)' : 'rgba(255,255,255,0.25)', background: agreedToTerms ? 'var(--base-color)' : 'transparent' }}>
                                            {agreedToTerms && <i className="feather icon-feather-check" style={{ color: '#000', fontSize: 10 }}></i>}
                                        </label>
                                        <label htmlFor="tc-agree" className="order-tc-text">
                                            I have read and agree to the <a href="/privacy-policy" target="_blank" className="text-base-color">Privacy Policy</a>, <a href="/shipping-policy" target="_blank" className="text-base-color">Shipping Policy</a>, and <a href="/returns-policy" target="_blank" className="text-base-color">Returns Policy</a>.
                                        </label>
                                    </div>

                                    {/* CTA */}
                                    <div className="order-cta-wrap">
                                        <button type="submit" disabled={placingOrder || !agreedToTerms || paymentGateways.length === 0}
                                            className="btn btn-base-color btn-extra-large btn-round-edge fw-700 text-transform-none order-cta-btn"
                                            style={{ opacity: (agreedToTerms && paymentGateways.length > 0) ? 1 : 0.4 }}>
                                            {placingOrder ? 'Processing...' : 'Place Order Now'}
                                        </button>
                                    </div>

                                    {/* Trust badges */}
                                    <div className="order-trust-row">
                                        {[
                                            { icon: 'icon-feather-lock',       label: 'SSL Secured'    },
                                            { icon: 'icon-feather-rotate-ccw', label: '30-Day Returns' },
                                            { icon: 'icon-feather-shield',     label: 'Safe Checkout'  },
                                        ].map(({ icon, label }) => (
                                            <div key={label} className="order-trust-item">
                                                <i className={`feather ${icon} text-base-color`}></i>
                                                <span>{label}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            {/* Inline Stripe Payment Section */}
            <div ref={paymentSectionRef} className={`transition-all duration-700 ease-in-out overflow-hidden ${stripeOptions ? 'max-h-[1000px] opacity-100 mb-60px' : 'max-h-0 opacity-0'}`}>
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-12 col-lg-8">
                            <div className="bg-[#111111] border border-white/10 p-40px md-p-20px border-radius-12px shadow-2xl relative">
                                <div className="text-center mb-30px">
                                    <img src="/images/stripe_fallback.svg" alt="Stripe" className="checkout-payment-logo mx-auto" />
                                    <h4 className="text-white alt-font fw-600 mb-5px">Secure Checkout</h4>
                                    <p className="text-white/50 fs-14">Complete your payment of {formatAmount(totalAmount)}</p>
                                </div>

                                {stripePromise && stripeOptions && (
                                    <Elements stripe={stripePromise} options={stripeOptions}>
                                        <StripePaymentForm 
                                            orderId={currentOrderId!} 
                                            onSuccess={() => router.push(`/payment/success?order_id=${currentOrderId}`)}
                                            onCancel={() => { setStripeOptions(null); setPaymentCancelled(true); }}
                                        />
                                    </Elements>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    );
}
