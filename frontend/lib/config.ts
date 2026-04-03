/**
 * Central API configuration.
 *
 * Server components (Node.js):  call the backend directly via BACKEND_URL.
 *                                Never exposed to the browser.
 * Client components (browser):  use relative /api path — Next.js proxies it
 *                                to BACKEND_URL, avoiding CORS completely.
 *
 * On the live server set these two env vars in frontend/.env.local (or .env.production):
 *   BACKEND_URL=http://localhost:8000          ← internal Laravel URL (server-only)
 *   NEXT_PUBLIC_API_KEY=your-api-key           ← sent with every request
 *
 * NEXT_PUBLIC_API_URL is no longer used — BACKEND_URL replaces it server-side,
 * and /api (relative) is used client-side.
 */

/** Base API URL for server-side fetches (Node.js only). */
export const SERVER_API_URL =
    (process.env.BACKEND_URL
        ? `${process.env.BACKEND_URL}/api`
        : process.env.NEXT_PUBLIC_API_URL   // legacy fallback
        ?? 'http://localhost:8000/api');

/** API key sent with every request. */
export const API_KEY = process.env.NEXT_PUBLIC_API_KEY ?? '';

/** Public site URL — used for sitemap, robots, og:url, etc.
 *  Set NEXT_PUBLIC_SITE_URL=https://yourdomain.com on the live server. */
export const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000';

/**
 * Build fetch headers for server-side requests.
 * Client-side requests use apiFetch() in lib/api.ts which handles headers.
 */
export function serverHeaders(): Record<string, string> {
    return {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'x-api-key': API_KEY,
    };
}
