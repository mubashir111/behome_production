<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug'             => 'about',
                'title'            => 'About Us',
                'content'          => null,
                'sections'         => json_encode([
                    'hero' => [
                        'title'       => 'We Are Behome',
                        'subtitle'    => 'Premium Architectural Decor & Luxury Furniture',
                        'description' => 'Behome was founded with a vision to bring world-class architectural decor and luxury furniture to homes everywhere. We curate only the finest pieces, crafted by artisans who share our passion for design excellence.',
                    ],
                    'features' => [
                        ['number' => '01', 'year' => '2009', 'title' => 'Business founded',        'description' => 'Behome was founded with a vision to bring world-class architectural decor to homes everywhere.'],
                        ['number' => '02', 'year' => '2012', 'title' => 'Build new office',        'description' => 'We expanded our operations and built a new headquarters to serve our growing customer base.'],
                        ['number' => '03', 'year' => '2016', 'title' => 'Relocates headquarter',   'description' => 'As demand grew internationally, we moved our headquarters to a larger space.'],
                        ['number' => '04', 'year' => '2020', 'title' => 'Revenues of millions',    'description' => 'Behome crossed the milestone of millions in revenue, cementing our position as a market leader.'],
                    ],
                    'stats' => [
                        ['value' => '10000+', 'label' => 'people trusting us'],
                        ['value' => '4.9/5',  'label' => '8549 Total reviews'],
                    ],
                    'team' => [
                        ['name' => 'Jeremy Dupont',   'role' => 'Director', 'image' => '/images/team-08.jpg', 'description' => ''],
                        ['name' => 'Jessica Dover',   'role' => 'Founder',  'image' => '/images/team-09.jpg', 'description' => ''],
                        ['name' => 'Matthew Taylor',  'role' => 'Manager',  'image' => '/images/team-10.jpg', 'description' => ''],
                        ['name' => 'Johncy Parker',   'role' => 'Manager',  'image' => '/images/team-11.jpg', 'description' => ''],
                    ],
                ]),
                'meta_title'       => 'About Us | Behome',
                'meta_description' => 'Learn the story behind Behome — a premium architectural decor and luxury furniture brand.',
                'is_active'        => true,
                'is_system'        => true,
            ],
            [
                'slug'             => 'contact',
                'title'            => 'Contact Us',
                'content'          => null,
                'sections'         => json_encode([
                    'address'       => '27 Old Gloucester Street, London, WC1N 3AX, United Kingdom',
                    'phones'        => ['+44 20 7946 0123', '+44 20 7946 0456'],
                    'emails'        => ['hello@behome.com'],
                    'careers_email' => 'careers@behome.com',
                    'map_query'     => '27 Old Gloucester Street London',
                ]),
                'meta_title'       => 'Contact Us | Behome',
                'meta_description' => 'Get in touch with the Behome team. We\'d love to hear from you.',
                'is_active'        => true,
                'is_system'        => true,
            ],
            [
                'slug'             => 'privacy-policy',
                'title'            => 'Privacy Policy',
                'content'          => '<h2>Privacy Policy</h2><p>Last updated: ' . date('F d, Y') . '</p><p>At Behome, we are committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal information when you use our website and services.</p><h3>Information We Collect</h3><p>We collect information you provide directly to us, such as your name, email address, shipping address, and payment details when you place an order.</p><h3>How We Use Your Information</h3><p>We use the information we collect to process your orders, communicate with you about your purchases, and improve our services.</p><h3>Data Security</h3><p>We implement appropriate technical and organisational measures to protect your personal data against unauthorised access, alteration, disclosure, or destruction.</p><h3>Contact Us</h3><p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:hello@behome.com">hello@behome.com</a>.</p>',
                'sections'         => json_encode([]),
                'meta_title'       => 'Privacy Policy | Behome',
                'meta_description' => 'Read the Behome Privacy Policy to understand how we collect, use, and protect your personal information.',
                'is_active'        => true,
                'is_system'        => true,
            ],
            [
                'slug'             => 'blog',
                'title'            => 'Our Blog',
                'content'          => 'Explore interior design inspiration, home decor trends, styling guides, and expert tips from the Behome team.',
                'sections'         => json_encode([]),
                'meta_title'       => 'Blog | Behome',
                'meta_description' => 'Explore interior design inspiration, home decor trends, styling guides, and expert tips from the Behome team.',
                'is_active'        => true,
                'is_system'        => true,
            ],
            [
                'slug'             => 'shipping-policy',
                'title'            => 'Shipping Policy',
                'content'          => '<h2>Shipping Policy</h2><p>Last updated: ' . date('F d, Y') . '</p><h3>Processing Times</h3><p>All orders are processed within 1–3 business days. Orders placed on weekends or public holidays will be processed on the next business day.</p><h3>Delivery Times</h3><p>Standard delivery within the UK takes 3–7 business days. Express delivery options are available at checkout. International orders typically arrive within 7–14 business days depending on destination.</p><h3>Shipping Costs</h3><p>Free standard shipping is available on all UK orders over £100. For orders under £100, a flat shipping fee applies as shown at checkout. International shipping rates are calculated at checkout based on destination and order weight.</p><h3>Tracking Your Order</h3><p>Once your order has been dispatched, you will receive a confirmation email with a tracking number. You can use this to monitor your delivery through our carrier\'s website.</p><h3>Damaged or Lost Parcels</h3><p>If your order arrives damaged or does not arrive within the expected timeframe, please contact us at <a href="/contact">our contact page</a> and we will resolve the issue promptly.</p>',
                'sections'         => json_encode([]),
                'meta_title'       => 'Shipping Policy | Behome',
                'meta_description' => 'Read the Behome Shipping Policy — delivery times, shipping costs, tracking, and more.',
                'is_active'        => true,
                'is_system'        => true,
            ],
            [
                'slug'             => 'returns-policy',
                'title'            => 'Returns & Exchanges',
                'content'          => '<h2>Returns &amp; Exchanges</h2><p>Last updated: ' . date('F d, Y') . '</p><h3>Return Window</h3><p>We accept returns within 30 days of delivery. Items must be in their original, unused condition with all original packaging intact.</p><h3>How to Return</h3><p>To initiate a return, please contact us through our <a href="/contact">contact page</a> with your order number and reason for return. We will provide you with a returns authorisation and instructions.</p><h3>Exchanges</h3><p>If you would like to exchange an item for a different size, colour, or product, please contact us and we will do our best to accommodate your request subject to availability.</p><h3>Refunds</h3><p>Once your return is received and inspected, we will notify you of the approval or rejection of your refund. Approved refunds are processed within 5–10 business days to your original payment method.</p><h3>Non-Returnable Items</h3><p>Custom or bespoke orders, items marked as final sale, and products that have been assembled or installed cannot be returned unless they are faulty.</p><h3>Faulty or Damaged Items</h3><p>If you receive a faulty or damaged item, please contact us within 48 hours of delivery with photos and we will arrange a replacement or full refund at no cost to you.</p>',
                'sections'         => json_encode([]),
                'meta_title'       => 'Returns & Exchanges | Behome',
                'meta_description' => 'Read the Behome Returns & Exchanges policy — 30-day returns, how to start a return, and refund timelines.',
                'is_active'        => true,
                'is_system'        => true,
            ],
            [
                'slug'             => 'faq',
                'title'            => 'Frequently Asked Questions',
                'content'          => '<h2>Frequently Asked Questions</h2><h3>How do I place an order?</h3><p>Browse our shop, add items to your cart, and proceed to checkout. You can checkout as a guest or create an account to track your orders.</p><h3>What payment methods do you accept?</h3><p>We accept all major credit and debit cards, PayPal, and other payment methods as shown at checkout.</p><h3>Can I change or cancel my order?</h3><p>Orders can be changed or cancelled within 24 hours of placement. Please contact us immediately through our <a href="/contact">contact page</a> if you need to make a change.</p><h3>How do I track my order?</h3><p>Once your order is dispatched, you will receive a tracking number by email. You can also view order status in your account under Orders.</p><h3>Do you deliver internationally?</h3><p>Yes, we ship to most countries worldwide. International shipping rates and estimated delivery times are shown at checkout.</p><h3>What is your returns policy?</h3><p>We accept returns within 30 days of delivery. Please see our <a href="/returns-policy">Returns &amp; Exchanges</a> page for full details.</p><h3>How do I contact customer support?</h3><p>You can reach us through our <a href="/contact">Contact page</a> or by emailing hello@behome.com. We aim to respond within 24 hours on business days.</p>',
                'sections'         => json_encode([]),
                'meta_title'       => 'FAQs | Behome',
                'meta_description' => 'Answers to frequently asked questions about ordering, shipping, returns, and more at Behome.',
                'is_active'        => true,
                'is_system'        => true,
            ],
        ];

        foreach ($pages as $page) {
            $sections = $page['sections'];
            unset($page['sections']);

            $record = StaticPage::firstOrCreate(
                ['slug' => $page['slug']],
                array_merge($page, ['sections' => json_decode($sections, true)])
            );

            // If already exists but is_system not set, mark it
            if (!$record->wasRecentlyCreated && !$record->is_system) {
                $record->update(['is_system' => true]);
            }
        }
    }
}
