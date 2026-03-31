'use client';

import { useCallback, useEffect, useState } from 'react';
import { apiFetch } from '@/lib/api';
import { useRouter } from 'next/navigation';
import LoadingSkeleton from '@/components/LoadingSkeleton';
import { extractCurrencySymbol, readStoredCoupon, storeCoupon } from '@/lib/checkout';
import { useCurrency } from '@/components/SettingsProvider';
import { useToast } from '@/components/ToastProvider';

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
    const [walletBalance, setWalletBalance] = useState(0);
    const [walletCurrencyBalance, setWalletCurrencyBalance] = useState('');
    const [walletGatewayId, setWalletGatewayId] = useState<number | null>(null);
    const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
    const { showToast } = useToast();
    const { symbol: currencySymbol, formatAmount } = useCurrency();
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
        country: 'Bangladesh',
        country_code: '',
        order_note: '',
        payment_method: '2', // Default to E-wallet (Stripe)
    });

    const totalAmount = Math.max(subtotal + tax + shippingCost - couponDiscount, 0);
    const isWalletPayment = formData.payment_method === 'wallet';
    const selectedGateway = isWalletPayment ? null : (paymentGateways.find(g => String(g.id) === formData.payment_method) ?? null);
    const paymentGateway = isWalletPayment ? 'credit' : (selectedGateway?.slug ?? 'stripe');

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

            const [cartResponse, addressResponse, orderAreaResponse, gatewayResponse, settingResponse, profileResponse] = await Promise.all([
                apiFetch('/cart'),
                apiFetch('/addresses'),
                apiFetch('/frontend/order-area').catch(() => ({ data: [] })),
                apiFetch('/frontend/payment-gateway').catch(() => ({ data: [] })),
                apiFetch('/frontend/setting').catch(() => ({})),
                apiFetch('/profile').catch(() => null),
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

            // Wallet balance
            const creditGateway = allGateways.find((g: any) => g.slug === 'credit');
            if (creditGateway) setWalletGatewayId(creditGateway.id);
            const profileData = profileResponse?.data ?? profileResponse;
            if (profileData?.balance !== undefined) {
                setWalletBalance(parseFloat(profileData.balance) || 0);
                setWalletCurrencyBalance(profileData.currency_balance ?? '');
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
                    showToast('Failed to save address: ' + addressResponse.message, 'error');
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
                discount: 0,
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
                payment_method: isWalletPayment ? (walletGatewayId ?? 0) : parseInt(formData.payment_method),
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

                // 4. Clear cart (backend already cleared it, but also fire client-side event)
                try { await apiFetch('/cart/clear', { method: 'DELETE' }); } catch { /* non-critical */ }
                storeCoupon(null);
                window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: 0 } }));

                // 5. Initiate Payment
                const paymentResponse = await apiFetch('/payment/initiate', {
                    method: 'POST',
                    body: JSON.stringify({
                        order_id: orderId,
                        payment_gateway: paymentGateway,
                    }),
                });

                if (paymentResponse.status) {
                    if (paymentResponse.data?.redirect_url) {
                        window.location.href = paymentResponse.data.redirect_url;
                    } else {
                        // COD or gateways that confirm inline — go straight to success
                        router.push(`/payment/success?order_id=${orderId}`);
                    }
                } else {
                    showToast('Order placed but payment initiation failed. Please contact support.', 'error');
                }
            } else {
                showToast(orderResponse.message || 'Failed to place order.', 'error');
            }
        } catch (error: any) {
            console.error('Order placement failed:', error);
            showToast(error.message || 'An unexpected error occurred. Please try again.', 'error');
        } finally {
            setPlacingOrder(false);
        }
    };

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!token) {
            router.push('/account?redirect=/checkout');
            return;
        }
        fetchCheckoutData();
    }, [fetchCheckoutData, router]);

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
        <main className="no-layout-pad" style={{ paddingTop: '100px' }}>
            <section className="page-shell page-shell-tight">
                <div className="container">
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
                                        <input name="country" value={formData.country} onChange={handleInputChange} className="border-radius-4px input-small" required type="text" />
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
                                        <label className="mb-10px">Notes & details</label>
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
                                <div className="bg-dark-gray border-radius-6px p-50px lg-p-25px your-order-box ui-panel ui-panel-lg">
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
                                                        <span className="text-white fw-500">{item.product?.name} x {item.quantity}</span>
                                                        {item.variation_names && <span className="fs-12 d-block text-gray">{item.variation_names}</span>}
                                                    </td>
                                                    <td className="text-white">{formatAmount(parseFloat(item.total))}</td>
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

                                    {/* Wallet balance notice */}
                                    {walletBalance > 0 && (
                                        <div className="mt-20px p-15px border-radius-6px d-flex align-items-center justify-content-between gap-10px" style={{ background: 'rgba(251,153,28,0.07)', border: '1px solid rgba(251,153,28,0.2)' }}>
                                            <div className="d-flex align-items-center gap-10px">
                                                <i className="feather icon-feather-dollar-sign fs-15" style={{ color: '#FB991C' }}></i>
                                                <p className="text-white fs-13 mb-0">
                                                    You have <strong style={{ color: '#FB991C' }}>{walletCurrencyBalance}</strong> in wallet balance
                                                    {walletBalance >= totalAmount ? ' — enough to cover this order.' : '.'}
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    <div className="mt-20px mb-20px">
                                        <span className="text-white fw-600 d-block mb-12px">Payment Method</span>
                                        {paymentGateways.length === 0 && walletBalance < totalAmount ? (
                                            <p className="text-white opacity-6 fs-14">No payment methods available.</p>
                                        ) : (
                                            <div className="checkout-payment-grid">
                                                {/* Wallet option — show at top if balance is sufficient */}
                                                {walletBalance >= totalAmount && (
                                                    <label
                                                        htmlFor="gw_wallet"
                                                        className={`checkout-payment-card${formData.payment_method === 'wallet' ? ' selected' : ''}`}
                                                    >
                                                        <input
                                                            type="radio"
                                                            name="payment_method"
                                                            id="gw_wallet"
                                                            value="wallet"
                                                            checked={formData.payment_method === 'wallet'}
                                                            onChange={handleInputChange}
                                                            className="visually-hidden"
                                                        />
                                                        <i className="feather icon-feather-dollar-sign checkout-payment-icon" style={{ color: '#FB991C' }}></i>
                                                        <span className="checkout-payment-name">Wallet ({walletCurrencyBalance})</span>
                                                        {formData.payment_method === 'wallet' && (
                                                            <span className="checkout-payment-check">
                                                                <i className="feather icon-feather-check"></i>
                                                            </span>
                                                        )}
                                                    </label>
                                                )}
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

                                    <button type="submit" disabled={placingOrder} className="btn btn-base-color btn-extra-large btn-switch-text btn-round-edge btn-box-shadow w-100 text-transform-none mt-10px">
                                        <span>
                                            <span className="btn-double-text" data-text={placingOrder ? "Placing order..." : "Place order"}>{placingOrder ? "Placing order..." : "Place order"}</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    );
}
