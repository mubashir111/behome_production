import { NextRequest, NextResponse } from 'next/server';

/**
 * Injects the backend API key into every /api/* request that Next.js proxies
 * to the Laravel backend. This keeps the key server-side only — it is never
 * bundled into client JavaScript (no NEXT_PUBLIC_ prefix needed).
 */
export function middleware(request: NextRequest) {
  const headers = new Headers(request.headers);
  headers.set('x-api-key', process.env.API_KEY ?? '');

  return NextResponse.next({ request: { headers } });
}

export const config = {
  // Only run on proxied backend API calls — not on Next.js internal routes
  matcher: '/api/:path*',
};
