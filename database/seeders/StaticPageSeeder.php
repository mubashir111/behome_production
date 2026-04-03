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
