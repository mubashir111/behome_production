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
        // 401 = token expired or invalid — clear session and redirect to login
        if (response.status === 401 && typeof window !== 'undefined') {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.dispatchEvent(new Event('auth:logout'));
            // Redirect to home (auth modal will open) unless already there
            if (!['/', '/account'].includes(window.location.pathname)) {
                window.location.href = '/?session=expired';
            }
        }
        const errorData = await response.json().catch(() => ({}));
        // Surface field-level validation errors (e.g. from Laravel FormRequest)
        let errorMessage = errorData.message || `API request failed with status ${response.status}`;
        if (errorData.errors && typeof errorData.errors === 'object') {
            const fieldMessages = (Object.values(errorData.errors) as string[][]).flat();
            if (fieldMessages.length > 0) errorMessage = fieldMessages.join(' ');
        }
        throw new Error(errorMessage);
    }

    // Handle empty bodies (204 No Content = genuine success with no payload)
    const text = await response.text();
    if (!text) {
        return response.status === 204 ? { status: true } : null;
    }
    try {
        return JSON.parse(text);
    } catch {
        return { status: true };
    }
}
