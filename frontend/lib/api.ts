import { SERVER_API_URL, API_KEY } from './config';

// Server → absolute internal URL (BACKEND_URL/api). Client → /api (Next.js proxy, no CORS).
const API_URL = typeof window === 'undefined' ? SERVER_API_URL : '/api';

export async function apiFetch(endpoint: string, options: RequestInit = {}) {
    // Get token from localStorage if available
    const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;

    const isServer = typeof window === 'undefined';
    const headers: Record<string, string> = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        // Server-side: attach key directly. Client-side: middleware injects it.
        ...(isServer ? { 'x-api-key': API_KEY } : {}),
        ...(options.headers as Record<string, string>),
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    // Default to v1 if not specified and not a standard Laravel route
    let finalEndpoint = endpoint;
    if (!endpoint.startsWith('/v1') && !endpoint.startsWith('v1')) {
        // Only prefix with v1 if it's one of our new API modules
        const v1Modules = ['/products', '/categories', '/cart', '/orders', '/payment', '/addresses'];
        if (v1Modules.some(module => endpoint.startsWith(module))) {
            finalEndpoint = `/v1${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;
        }
    }

    const url = `${API_URL}${finalEndpoint.startsWith('/') ? finalEndpoint : `/${finalEndpoint}`}`;

    const response = await fetch(url, {
        ...options,
        headers,
    });

    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `API request failed with status ${response.status}`);
    }

    // Handle empty bodies (e.g. 202 Accepted with no JSON)
    const text = await response.text();
    return text ? JSON.parse(text) : { status: true };
}
