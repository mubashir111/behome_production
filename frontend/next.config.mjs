/** @type {import('next').NextConfig} */

const isProd = process.env.NODE_ENV === 'production';

// Fail fast: these must be set on the production server.
// Local dev falls back to localhost — production never should.
if (isProd && !process.env.BACKEND_URL) {
  throw new Error(
    '[next.config] BACKEND_URL env var is not set. ' +
    'Add BACKEND_URL=https://api.behom.ae to your production environment.'
  );
}
if (isProd && !process.env.IMAGE_HOSTNAME) {
  throw new Error(
    '[next.config] IMAGE_HOSTNAME env var is not set. ' +
    'Add IMAGE_HOSTNAME=api.behom.ae to your production environment.'
  );
}

const BACKEND_URL    = process.env.BACKEND_URL    || 'http://localhost:8000'; // dev fallback only
const IMAGE_HOSTNAME = process.env.IMAGE_HOSTNAME || 'localhost';             // dev fallback only

const securityHeaders = [
  { key: 'X-Frame-Options',        value: 'SAMEORIGIN' },
  { key: 'X-Content-Type-Options', value: 'nosniff' },
  { key: 'X-XSS-Protection',       value: '1; mode=block' },
  { key: 'Referrer-Policy',        value: 'strict-origin-when-cross-origin' },
  { key: 'Permissions-Policy',     value: 'camera=(), microphone=(), geolocation=(), payment=()' },
  {
    key: 'Content-Security-Policy',
    value: [
      "default-src 'self'",
      // Scripts: self + inline scripts (Next.js requires unsafe-inline/eval in dev; tighten for prod)
      "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://accounts.google.com https://www.gstatic.com https://translate.googleapis.com https://translate.google.com https://translate-pa.googleapis.com https://www.google-analytics.com https://www.googletagmanager.com",
      // Styles: self + inline styles used by template/Google fonts/Translate
      "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://translate.googleapis.com https://translate.google.com https://www.gstatic.com",
      // Fonts
      "font-src 'self' data: https://fonts.gstatic.com",
      // Images: self + backend storage + data URIs + Google (fonts.gstatic.com for Translate icon)
      `img-src 'self' data: blob: http://localhost:8000 https://${IMAGE_HOSTNAME} https://behom.ae https://www.behom.ae https://www.google-analytics.com https://www.gstatic.com https://fonts.gstatic.com https://translate.google.com https://translate.googleapis.com`,
      // Fetch/XHR: self + backend API + Google APIs
      `connect-src 'self' http://localhost:8000 https://${IMAGE_HOSTNAME} https://behom.ae https://www.behom.ae https://www.google-analytics.com https://analytics.google.com https://translate.googleapis.com https://translate-pa.googleapis.com https://translate.google.com`,
      // Frames: Google Sign-In, Translate
      "frame-src 'self' https://accounts.google.com https://translate.google.com",
      "object-src 'none'",
      "base-uri 'self'",
      "form-action 'self'",
      "upgrade-insecure-requests",
    ].join('; '),
  },
];

const nextConfig = {
  poweredByHeader: false,
  eslint: {
    ignoreDuringBuilds: true,
  },
  transpilePackages: ['html-react-parser', 'html-dom-parser'],

  images: {
    remotePatterns: [
      { protocol: 'http',  hostname: IMAGE_HOSTNAME },
      { protocol: 'https', hostname: IMAGE_HOSTNAME },
      { protocol: 'http',  hostname: 'localhost' },
      { protocol: 'https', hostname: 'localhost' },
      { protocol: 'http',  hostname: '127.0.0.1' },
      { protocol: 'https', hostname: '127.0.0.1' },
      { protocol: 'https', hostname: 'behom.ae' },
      { protocol: 'https', hostname: 'www.behom.ae' },
      { protocol: 'https', hostname: '*.digitalocean.com' },
    ],
  },

  async headers() {
    return [
      {
        source: '/:path*',
        headers: securityHeaders,
      },
      {
        source: '/css/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }],
      },
      {
        source: '/js/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }],
      },
      {
        source: '/fonts/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }],
      },
      {
        source: '/revolution/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }],
      },
      {
        source: '/_next/static/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }],
      },
    ];
  },

  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${BACKEND_URL}/api/:path*`,
      },
      {
        source: '/admin/:path*',
        destination: `${BACKEND_URL}/admin/:path*`,
      },
      {
        source: '/storage/:path*',
        destination: `${BACKEND_URL}/storage/:path*`,
      },
      {
        source: '/images/:path*',
        destination: `${BACKEND_URL}/images/:path*`,
      },
    ];
  },
};

export default nextConfig;
