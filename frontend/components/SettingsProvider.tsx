'use client';

import { createContext, useContext, useEffect, useState } from 'react';

interface CurrencyConfig {
    symbol: string;
    position: 'left' | 'right';
    decimals: number;
}

interface SettingsContextValue {
    currency: CurrencyConfig;
    settings: any;
    loading: boolean;
    formatAmount: (amount: number) => string;
}

const STORAGE_KEY = 'site_settings_cache';

const defaults: SettingsContextValue = {
    currency: { symbol: '', position: 'left', decimals: 2 },
    settings: null,
    loading: true,
    formatAmount: (n) => `${n?.toFixed(2) || '0.00'}`,
};

const SettingsContext = createContext<SettingsContextValue>(defaults);

export function useSettings() {
    return useContext(SettingsContext);
}

export function useCurrency() {
    return useSettings();
}

function buildFormat(symbol: string, position: 'left' | 'right', decimals: number) {
    return (amount: number) => {
        if (typeof amount !== 'number') return '';
        const formatted = amount.toFixed(decimals);
        return position === 'left' ? `${symbol}${formatted}` : `${formatted}${symbol}`;
    };
}

export default function SettingsProvider({ children }: { children: React.ReactNode }) {
    const [state, setState] = useState<SettingsContextValue>(() => {
        if (typeof window !== 'undefined') {
            try {
                const cached = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
                if (cached.currency) {
                    return {
                        ...defaults,
                        currency: cached.currency,
                        settings: cached.settings,
                        loading: false,
                        formatAmount: buildFormat(cached.currency.symbol, cached.currency.position, cached.currency.decimals),
                    };
                }
            } catch {}
        }
        return defaults;
    });

    useEffect(() => {
        async function fetchSettings() {
            try {
                const API_KEY = process.env.NEXT_PUBLIC_API_KEY || '';
                const res = await fetch(`/api/frontend/setting`, {
                    headers: { 'Accept': 'application/json', 'x-api-key': API_KEY },
                });
                if (!res.ok) return;
                const json = await res.json();
                const data = json?.data ?? json;

                const currency: CurrencyConfig = {
                    symbol: data.site_default_currency_symbol || '',
                    position: data.site_currency_position == 10 ? 'right' : 'left',
                    decimals: data.site_digit_after_decimal_point ?? 2,
                };

                const next: SettingsContextValue = {
                    currency,
                    settings: data,
                    loading: false,
                    formatAmount: buildFormat(currency.symbol, currency.position, currency.decimals),
                };

                localStorage.setItem(STORAGE_KEY, JSON.stringify({ currency, settings: data }));
                setState(next);
            } catch (error) {
                console.error('[SETTINGS_FETCH_ERROR]', error);
                setState(prev => ({ ...prev, loading: false }));
            }
        }
        fetchSettings();
    }, []);

    return (
        <SettingsContext.Provider value={state}>
            {children}
        </SettingsContext.Provider>
    );
}
