export const CHECKOUT_COUPON_STORAGE_KEY = 'checkout_coupon';

export type AppliedCoupon = {
    code: string;
    id: number | null;
    discount: number;
    currencyDiscount: string;
    symbol: string;
};

export function extractCurrencySymbol(value?: string | null, fallback = '£') {
    if (!value) return fallback;
    const symbol = value.replace(/[0-9\s,.\-]/g, '').trim();
    return symbol || fallback;
}

export function formatCurrency(amount: number, symbol = '£') {
    return `${symbol}${amount.toFixed(2)}`;
}

export function readStoredCoupon(): AppliedCoupon | null {
    if (typeof window === 'undefined') return null;
    const raw = window.localStorage.getItem(CHECKOUT_COUPON_STORAGE_KEY);
    if (!raw) return null;

    try {
        return JSON.parse(raw) as AppliedCoupon;
    } catch {
        window.localStorage.removeItem(CHECKOUT_COUPON_STORAGE_KEY);
        return null;
    }
}

export function storeCoupon(coupon: AppliedCoupon | null) {
    if (typeof window === 'undefined') return;
    if (!coupon) {
        window.localStorage.removeItem(CHECKOUT_COUPON_STORAGE_KEY);
        return;
    }

    window.localStorage.setItem(CHECKOUT_COUPON_STORAGE_KEY, JSON.stringify(coupon));
}
