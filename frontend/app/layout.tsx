import type { Metadata } from 'next';
import Script from 'next/script';
import { Marcellus, Outfit } from 'next/font/google';
import NavbarRevealer from '@/components/NavbarRevealer';

const marcellus = Marcellus({ weight: '400', subsets: ['latin'], display: 'swap', variable: '--font-marcellus' });
const outfit = Outfit({ weight: ['300','400','500','600','700'], subsets: ['latin'], display: 'swap', variable: '--font-outfit' });
import './globals.css';

import Header from '@/components/Header';
import Footer from '@/components/Footer';
import ToastProvider from '@/components/ToastProvider';
import SettingsProvider from '@/components/SettingsProvider';
import CartProvider from '@/components/CartProvider';
import { SITE_URL } from '@/lib/config';
import { constructMetadata } from '@/lib/metadata';

export const metadata: Metadata = constructMetadata();

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

        {/* Single combined CSS — 1 request instead of 9, Cloudflare caches at edge */}
        <link rel="stylesheet" href="/css/all.min.css?v=1" />
      </head>
      <body className={`bg-dark-gray ${marcellus.variable} ${outfit.variable}`} data-mobile-nav-style="classic">
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
        <Script src="/js/vendors.min.js" strategy="afterInteractive" />

        <Script src="/revolution/js/jquery.themepunch.tools.min.js" strategy="afterInteractive" />
        <Script src="/revolution/js/jquery.themepunch.revolution.min.js" strategy="afterInteractive" />
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
