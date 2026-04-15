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
    }, [currencySymbol]);

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
                setCartItems(cartResponse.data);
                const total = cartResponse.data.reduce((acc: number, item: any) => acc + parseFloat(item.subtotal), 0);
                setSubtotal(total);
                const totalTax = cartResponse.data.reduce((acc: number, item: any) => acc + parseFloat(item.tax || 0), 0);
                setTax(totalTax);
                await syncCoupon(total);
            } else {
                setError('Failed to load cart items');
            }

            setOrderAreas(getAreaList(orderAreaResponse));
            setShippingSettings(settingResponse?.data ?? settingResponse ?? null);

            const allGateways: any[] = getAreaList(gatewayResponse);
            const gateways = allGateways.filter((g: any) => g.status === 5 && g.slug !== 'credit');
            setPaymentGateways(gateways);
            if (gateways.length > 0) {
                setFormData(prev => ({ ...prev, payment_method: String(gateways[0].id) }));
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
        setPlacingOrder(true);
        setOrderError(null);

        try {
            let addressId = selectedAddressId;
            const selectedAddress = addresses.find((address: any) => address.id === selectedAddressId) || null;
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
                                <div style={{ background: 'rgba(15,15,25,0.97)', border: '1px solid rgba(197,160,89,0.2)', borderRadius: 16, overflow: 'hidden' }}>
                                    <div style={{ height: 4, background: 'linear-gradient(90deg, var(--base-color), rgba(197,160,89,0.2))' }} />
                                    <div style={{ padding: '40px 40px 36px' }}>
                                        <p style={{ margin: '0 0 8px', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.12em', color: 'rgba(255,255,255,0.35)' }}>Checkout</p>
                                        <h2 style={{ margin: '0 0 10px', color: '#fff', fontSize: 'clamp(20px,4vw,26px)', fontWeight: 700 }}>Continue to checkout</h2>
                                        <p style={{ margin: '0 0 32px', color: 'rgba(255,255,255,0.5)', fontSize: 14, lineHeight: 1.6 }}>
                                            Sign in for faster checkout with saved addresses, order history, and order tracking. Or create a free account in under a minute.
                                        </p>

                                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                                            <a href="/account?redirect=/checkout" style={{
                                                display: 'flex', flexDirection: 'column', alignItems: 'flex-start', gap: 6,
                                                padding: '18px 20px', background: 'var(--base-color)', borderRadius: 10,
                                                textDecoration: 'none',
                                            }}>
                                                <i className="feather icon-feather-log-in" style={{ fontSize: 20, color: '#000' }}></i>
                                                <span style={{ color: '#000', fontWeight: 700, fontSize: 14 }}>Sign In</span>
                                                <span style={{ color: 'rgba(0,0,0,0.6)', fontSize: 12 }}>Use saved address &amp; orders</span>
                                            </a>
                                            <a href="/account?tab=register&redirect=/checkout" style={{
                                                display: 'flex', flexDirection: 'column', alignItems: 'flex-start', gap: 6,
                                                padding: '18px 20px', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.12)', borderRadius: 10,
                                                textDecoration: 'none',
                                            }}>
                                                <i className="feather icon-feather-user-plus" style={{ fontSize: 20, color: '#fff' }}></i>
                                                <span style={{ color: '#fff', fontWeight: 700, fontSize: 14 }}>Create Account</span>
                                                <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 12 }}>Free &amp; takes 30 seconds</span>
                                            </a>
                                        </div>

                                        <div style={{ display: 'flex', alignItems: 'center', gap: 12, margin: '20px 0' }}>
                                            <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.08)' }} />
                                            <span style={{ color: 'rgba(255,255,255,0.3)', fontSize: 12, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.08em' }}>or</span>
                                            <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.08)' }} />
                                        </div>

                                        <a href="/cart" style={{
                                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                                            padding: '13px 20px', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 10,
                                            textDecoration: 'none', color: 'rgba(255,255,255,0.5)', fontSize: 13, fontWeight: 600,
                                        }}>
                                            <i className="feather icon-feather-arrow-left" style={{ fontSize: 14 }}></i>
                                            Back to Cart
                                        </a>
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
                        <div style={{
                            display: 'flex', alignItems: 'flex-start', gap: 12,
                            background: 'rgba(234,179,8,0.12)', border: '1px solid rgba(234,179,8,0.35)',
                            borderRadius: 10, padding: '14px 18px', marginBottom: 28,
                        }}>
                            <i className="bi bi-info-circle-fill" style={{ color: '#eab308', fontSize: 18, marginTop: 2, flexShrink: 0 }}></i>
                            <div style={{ flex: 1 }}>
                                <p style={{ color: '#fef08a', fontWeight: 600, margin: '0 0 2px' }}>Payment cancelled — you were not charged.</p>
                                <p style={{ color: 'rgba(255,255,255,0.6)', fontSize: 13, margin: 0 }}>
                                    Your order details are still saved below. Review them and click <strong style={{ color: '#fff' }}>Place Order</strong> to try again, or choose a different payment method.
                                </p>
                            </div>
                            <button onClick={() => setPaymentCancelled(false)} style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.4)', fontSize: 18, cursor: 'pointer', padding: 0, lineHeight: 1 }}>×</button>
                        </div>
                    )}
                    <form onSubmit={placeOrder}>
                        <div className="row align-items-start">
                            <div className="col-lg-7 pe-50px md-pe-15px md-mb-50px xs-mb-35px">
                                <span className="ui-section-title mb-25px d-block">Billing details</span>
                                {addresses.length > 0 && (
                                    <div className="ui-panel ui-panel-sm mb-30px">
                                        <label className="mb-10px d-block text-white">Saved address</label>
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
                                <div className="row">
                                    <div className="col-md-6 mb-20px">
                                        <label className="mb-10px">First name <span className="text-red">*</span></label>
                                        <input name="first_name" value={formData.first_name} onChange={handleInputChange} className="border-radius-4px input-small" required type="text" />
                                    </div>
                                    <div className="col-md-6 mb-20px">
                                        <label className="mb-10px">Last name <span className="text-red">*</span></label>
                                        <input name="last_name" value={formData.last_name} onChange={handleInputChange} className="border-radius-4px input-small" required type="text" />
                                    </div>
                                    <div className="col-12 mb-20px">
                                        <label className="mb-10px">Email address <span className="text-red">*</span></label>
                                        <input name="email" value={formData.email} onChange={handleInputChange} className="border-radius-4px input-small" required type="email" />
                                    </div>
                                    <div className="col-12 mb-20px">
                                        <label className="mb-10px">Phone <span className="text-red">*</span></label>
                                        <input name="phone" value={formData.phone} onChange={handleInputChange} className="border-radius-4px input-small" required type="text" />
                                    </div>
                                    <div className="col-md-6 mb-20px">
                                        <label className="mb-10px">Country code</label>
                                        <input name="country_code" value={formData.country_code} onChange={handleInputChange} className="border-radius-4px input-small" placeholder="+44" type="text" />
                                    </div>
                                    <div className="col-md-6 mb-20px">
                                        <label className="mb-10px">Country <span className="text-red">*</span></label>
                                        <select name="country" value={formData.country} onChange={handleInputChange} className="border-radius-4px input-small" required>
                                            {[
                                                'United Arab Emirates',
                                                'Saudi Arabia',
                                                'Kuwait',
                                                'Qatar',
                                                'Bahrain',
                                                'Oman',
                                            ].map(c => <option key={c} value={c}>{c}</option>)}
                                        </select>
                                    </div>
                                    <div className="col-12 mb-20px">
                                        <label className="mb-10px">Street address <span className="text-red">*</span></label>
                                        <input name="address" value={formData.address} onChange={handleInputChange} className="border-radius-4px input-small mb-10px" placeholder="House number and street name" required type="text" />
                                    </div>
                                    <div className="col-md-6 mb-20px">
                                        <label className="mb-10px">Town / City <span className="text-red">*</span></label>
                                        <input name="city" value={formData.city} onChange={handleInputChange} className="border-radius-4px input-small" required type="text" />
                                    </div>
                                    <div className="col-md-6 mb-20px">
                                        <label className="mb-10px">State</label>
                                        <input name="state" value={formData.state} onChange={handleInputChange} className="border-radius-4px input-small" type="text" />
                                    </div>
                                    <div className="col-md-6 mb-20px">
                                        <label className="mb-10px">ZIP <span className="text-red">*</span></label>
                                        <input name="zip" value={formData.zip} onChange={handleInputChange} className="border-radius-4px input-small" required type="text" />
                                    </div>
                                    <div className="col-12 mb-20px">
                                        <label className="mb-10px">Notes & details <span className="text-white opacity-5 fs-12">(optional)</span></label>
                                        <textarea
                                            name="order_note"
                                            value={formData.order_note}
                                            onChange={handleInputChange}
                                            className="border-radius-4px input-small"
                                            rows={4}
                                            placeholder="Add delivery notes or order instructions"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className="col-lg-5">
                                <div className="bg-dark-gray border-radius-6px p-50px lg-p-25px xs-p-20px your-order-box ui-panel ui-panel-lg">
                                    <span className="fs-26 alt-font fw-600 text-white mb-5px d-block">Your order</span>
                                    <table className="w-100 total-price-table your-order-table">
                                        <tbody>
                                            <tr>
                                                <th className="w-60 fw-600 text-white alt-font">Product</th>
                                                <td className="fw-600 text-white alt-font">Total</td>
                                            </tr>
                                            {cartItems.map((item) => (
                                                <tr key={item.id} className="product">
                                                    <td>
                                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                            {item.product?.cover && (
                                                                /* eslint-disable-next-line @next/next/no-img-element */
                                                                <img
                                                                    src={item.product.cover}
                                                                    alt={item.product?.name}
                                                                    style={{ width: 44, height: 44, objectFit: 'cover', borderRadius: 6, flexShrink: 0, border: '1px solid rgba(255,255,255,0.08)' }}
                                                                    onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
                                                                />
                                                            )}
                                                            <div>
                                                                <span className="text-white fw-500">{item.product?.name} <span style={{ color: 'rgba(255,255,255,0.4)' }}>×{item.quantity}</span></span>
                                                                {item.variation_names && <span className="fs-12 d-block text-gray">{item.variation_names}</span>}
                                                                {item.old_price > item.price && (
                                                                    <div className="fs-11 mt-1">
                                                                        <del className="text-white/30 me-2">{formatAmount(item.old_price)}</del>
                                                                        <span className="text-base-color fw-600">Save {formatAmount(item.discount_amount)}</span>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="text-white">
                                                        {formatAmount(parseFloat(item.total))}
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr>
                                                <th className="fw-600 text-white alt-font">Subtotal</th>
                                                <td className="text-white fw-600">{formatAmount(subtotal)}</td>
                                            </tr>
                                            {tax > 0 && (
                                                <tr>
                                                    <th className="fw-600 text-white alt-font">Tax</th>
                                                    <td className="text-white">{formatAmount(tax)}</td>
                                                </tr>
                                            )}
                                            {shippingCost > 0 && (
                                                <tr>
                                                    <th className="fw-600 text-white alt-font">Shipping</th>
                                                    <td className="text-white">{formatAmount(shippingCost)}</td>
                                                </tr>
                                            )}
                                            {couponDiscount > 0 && (
                                                <tr>
                                                    <th className="fw-600 text-white alt-font">Discount</th>
                                                    <td className="text-base-color fw-600">-{formatAmount(couponDiscount)}</td>
                                                </tr>
                                            )}
                                            <tr className="total-amount">
                                                <th className="fw-600 text-white alt-font">Total</th>
                                                <td className="text-white fw-700">{formatAmount(totalAmount)}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    {/* Coupon Code */}
                                    <div className="mt-20px mb-20px">
                                        <span className="text-white fw-600 d-block mb-10px">Coupon Code</span>
                                        <div className="d-flex gap-2">
                                            <input
                                                type="text"
                                                className="border-radius-4px input-small flex-grow-1"
                                                placeholder="Enter coupon code"
                                                value={couponCode}
                                                onChange={e => setCouponCode(e.target.value)}
                                                onKeyDown={e => e.key === 'Enter' && (e.preventDefault(), applyCoupon())}
                                            />
                                            <button type="button" onClick={applyCoupon} className="btn btn-small btn-white btn-round-edge text-nowrap">
                                                Apply
                                            </button>
                                        </div>
                                        {couponMessage && (
                                            <p className={`fs-13 mt-5px mb-0 ${couponDiscount > 0 ? 'text-base-color' : 'text-red'}`}>
                                                {couponMessage}
                                            </p>
                                        )}
                                        {shippingCost > 0 && (
                                            <p className="fs-13 mt-5px mb-0 text-white opacity-7">
                                                Shipping updated from your delivery area.
                                            </p>
                                        )}
                                    </div>

                                    <div className="mt-20px mb-20px">
                                        <span className="text-white fw-600 d-block mb-12px">Payment Method</span>
                                        {paymentGateways.length === 0 ? (
                                            <p className="text-white opacity-6 fs-14">No payment methods available.</p>
                                        ) : (
                                            <div className="checkout-payment-grid">
                                                {paymentGateways.map((gw: any) => {
                                                    const isSelected = formData.payment_method === String(gw.id);
                                                    return (
                                                        <label
                                                            key={gw.id}
                                                            htmlFor={`gw_${gw.id}`}
                                                            className={`checkout-payment-card${isSelected ? ' selected' : ''}`}
                                                        >
                                                            <input
                                                                type="radio"
                                                                name="payment_method"
                                                                id={`gw_${gw.id}`}
                                                                value={String(gw.id)}
                                                                checked={isSelected}
                                                                onChange={handleInputChange}
                                                                className="visually-hidden"
                                                            />
                                                            {gw.image && !gw.image.includes('default/payment-gateway/payment-gateway.png') ? (
                                                                <img
                                                                    src={gw.image}
                                                                    alt={gw.name}
                                                                    className="checkout-payment-logo"
                                                                    onError={(e) => {
                                                                        const img = e.currentTarget;
                                                                        if (gw.slug === 'stripe') {
                                                                            img.src = '/images/stripe_fallback.svg';
                                                                        } else {
                                                                            img.style.display = 'none';
                                                                        }
                                                                    }}
                                                                />
                                                            ) : gw.slug === 'stripe' ? (
                                                                <img src="/images/stripe_fallback.svg" alt="Stripe" className="checkout-payment-logo" />
                                                            ) : (
                                                                <i className="feather icon-feather-credit-card checkout-payment-icon"></i>
                                                            )}
                                                            <span className="checkout-payment-name">{gw.name}</span>
                                                            {isSelected && (
                                                                <span className="checkout-payment-check">
                                                                    <i className="feather icon-feather-check"></i>
                                                                </span>
                                                            )}
                                                        </label>
                                                    );
                                                })}
                                            </div>
                                        )}
                                    </div>

                                    {/* Inline error panel */}
                                    {orderError && (
                                        <div style={{
                                            display: 'flex', alignItems: 'flex-start', gap: 12,
                                            background: 'rgba(248,113,113,0.08)', border: '1px solid rgba(248,113,113,0.3)',
                                            borderRadius: 10, padding: '14px 16px', marginBottom: 16,
                                        }}>
                                            <i className="bi bi-exclamation-triangle-fill" style={{ color: '#f87171', fontSize: 16, marginTop: 2, flexShrink: 0 }}></i>
                                            <div style={{ flex: 1 }}>
                                                <p style={{ color: '#fca5a5', fontWeight: 600, margin: '0 0 2px', fontSize: 13 }}>
                                                    {orderError.action === 'address' ? 'Address error' :
                                                     orderError.action === 'payment' ? 'Payment error' :
                                                     orderError.action === 'network' ? 'Connection error' : 'Order error'}
                                                </p>
                                                <p style={{ color: 'rgba(255,255,255,0.6)', fontSize: 13, margin: 0, lineHeight: 1.5 }}>
                                                    {orderError.message}
                                                </p>
                                                {orderError.orderId && (
                                                    <p style={{ margin: '6px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.4)' }}>
                                                        Order reference: <strong style={{ color: 'rgba(255,255,255,0.6)' }}>#{orderError.orderId}</strong>
                                                    </p>
                                                )}
                                            </div>
                                            <button onClick={() => setOrderError(null)} style={{ background: 'none', border: 'none', color: 'rgba(255,255,255,0.35)', fontSize: 18, cursor: 'pointer', padding: 0, lineHeight: 1, flexShrink: 0 }}>×</button>
                                        </div>
                                    )}

                                    <button type="submit" disabled={placingOrder} className="btn btn-base-color btn-extra-large btn-switch-text btn-round-edge btn-box-shadow w-100 text-transform-none mt-10px">
                                        <span>
                                            <span className="btn-double-text" data-text={placingOrder ? "Placing order..." : "Place order"}>{placingOrder ? "Placing order..." : "Place order"}</span>
                                        </span>
                                    </button>

                                    {/* Trust badges */}
                                    <div style={{ marginTop: 18, borderTop: '1px solid rgba(255,255,255,0.07)', paddingTop: 18, display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 10 }}>
                                        {[
                                            { icon: 'icon-feather-lock',       label: 'SSL Secured'     },
                                            { icon: 'icon-feather-rotate-ccw', label: '30-Day Returns'  },
                                            { icon: 'icon-feather-shield',     label: 'Safe Checkout'   },
                                        ].map(({ icon, label }) => (
                                            <div key={label} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, padding: '10px 6px', borderRadius: 8, background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.07)' }}>
                                                <i className={`feather ${icon}`} style={{ fontSize: 16, color: 'var(--base-color)' }} />
                                                <span style={{ fontSize: 10, fontWeight: 600, color: 'rgba(255,255,255,0.45)', textAlign: 'center', letterSpacing: '0.03em', textTransform: 'uppercase' }}>{label}</span>
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
