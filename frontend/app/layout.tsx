import type { Metadata, Viewport } from 'next';
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
import { AuthModalProvider } from '@/context/AuthModalContext';
import AuthModal from '@/components/AuthModal';

import { constructMetadata } from '@/lib/metadata';

export const metadata: Metadata = constructMetadata();

export const viewport: Viewport = {
    width: 'device-width',
    initialScale: 1,
    maximumScale: 5,
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="no-js" suppressHydrationWarning>
      <head>
        <link href="/images/favicon.png" rel="shortcut icon" />
        <link href="/images/apple-touch-icon-57x57.png" rel="apple-touch-icon" />
        <link href="/images/apple-touch-icon-72x72.png" rel="apple-touch-icon" sizes="72x72" />
        <link href="/images/apple-touch-icon-114x114.png" rel="apple-touch-icon" sizes="114x114" />

        {/* Single purged bundle — all CSS including revolution (1 request) */}
        <link rel="stylesheet" href="/css/all.min.css?v=4" />
        {/* Decor-store theme overrides + FA Brands font-face */}
        <link rel="stylesheet" href="/demos/decor-store/decor-store.css" />
      </head>
      <body className={`bg-dark-gray ${marcellus.variable} ${outfit.variable}`} data-mobile-nav-style="classic">
        <SettingsProvider>
          <CartProvider>
            <ToastProvider>
              <AuthModalProvider>
                <NavbarRevealer />
                <Header />
                <div className="layout-content">{children}</div>
                <Footer />
                <AuthModal />
              </AuthModalProvider>
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

        <Script src="/js/main.js?v=2" strategy="afterInteractive" />

        {/* Google Analytics — only loads when GA_ID is set */}
        {process.env.NEXT_PUBLIC_GA_ID && (
          <>
            <Script
              src={`https://www.googletagmanager.com/gtag/js?id=${process.env.NEXT_PUBLIC_GA_ID}`}
              strategy="afterInteractive"
            />
            <Script id="ga-init" strategy="afterInteractive">{`
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '${process.env.NEXT_PUBLIC_GA_ID}', { page_path: window.location.pathname });
            `}</Script>
          </>
        )}

        {/* Meta Pixel — only loads when PIXEL_ID is set */}
        {process.env.NEXT_PUBLIC_META_PIXEL_ID && (
          <Script id="meta-pixel" strategy="afterInteractive">{`
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '${process.env.NEXT_PUBLIC_META_PIXEL_ID}');
            fbq('track', 'PageView');
          `}</Script>
        )}
      </body>
    </html>
  );
}
