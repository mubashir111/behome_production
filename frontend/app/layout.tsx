import type { Metadata } from 'next';
import Script from 'next/script';
import NavbarRevealer from '@/components/NavbarRevealer';
import './globals.css';

import Header from '@/components/Header';
import Footer from '@/components/Footer';
import ToastProvider from '@/components/ToastProvider';
import SettingsProvider from '@/components/SettingsProvider';
import CartProvider from '@/components/CartProvider';
import { SITE_URL } from '@/lib/config';

export const metadata: Metadata = {
  title: {
    default: 'Behome - Premium Architectural Decor & Luxury Furniture',
    template: '%s | Behome',
  },
  description: 'Behome - Premium E-commerce experience for architectural decor, luxury furniture, and high-end interior design.',
  metadataBase: new URL(SITE_URL),
  openGraph: {
    type: 'website',
    siteName: 'Behome',
    title: 'Behome - Premium Architectural Decor & Luxury Furniture',
    description: 'Behome - Premium E-commerce experience for architectural decor, luxury furniture, and high-end interior design.',
    images: [{ url: '/images/og-default.png', width: 1200, height: 630 }],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'Behome - Premium Architectural Decor & Luxury Furniture',
    description: 'Behome - Premium E-commerce experience for architectural decor, luxury furniture, and high-end interior design.',
    images: ['/images/og-default.png'],
  },
  robots: { index: true, follow: true },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="no-js">
      <head>
        <link href="/images/favicon.png" rel="shortcut icon" />
        <link href="/images/apple-touch-icon-57x57.png" rel="apple-touch-icon" />
        <link href="/images/apple-touch-icon-72x72.png" rel="apple-touch-icon" sizes="72x72" />
        <link href="/images/apple-touch-icon-114x114.png" rel="apple-touch-icon" sizes="114x114" />
        
        <link crossOrigin="anonymous" href="https://fonts.googleapis.com" rel="preconnect" />
        <link crossOrigin="anonymous" href="https://fonts.gstatic.com" rel="preconnect" />
      </head>
      <body className="bg-dark-gray" data-mobile-nav-style="classic">
        <SettingsProvider>
          <CartProvider>
            <ToastProvider>
              <NavbarRevealer />
              <Header />
              <div className="layout-content">{children}</div>
              <Footer />
            </ToastProvider>
          </CartProvider>
        </SettingsProvider>

        <Script src="/js/jquery.js" strategy="beforeInteractive" />
        <Script src="/js/vendors.min.js" strategy="beforeInteractive" />
        
        <Script src="/revolution/js/jquery.themepunch.tools.min.js" strategy="beforeInteractive" />
        <Script src="/revolution/js/jquery.themepunch.revolution.min.js" strategy="beforeInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.actions.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.carousel.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.kenburn.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.layeranimation.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.migration.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.navigation.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.parallax.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.slideanims.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/extensions/revolution.extension.video.min.js" strategy="afterInteractive" />

        <Script src="/js/main.js" strategy="lazyOnload" />
      </body>
    </html>
  );
}
