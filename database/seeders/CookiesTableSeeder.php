<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Smartisan\Settings\Facades\Settings;


class CookiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $isDemo = env('DEMO', false);
        Settings::group('cookies')->set([
            'cookies_details_page_id' => $isDemo ? 5 : 0,
            'cookies_summary'         => $isDemo ? 'This website uses cookies to better understand how visitors use our site, for advertising, and to offer you a more personalized experience. We share information about your use of our site with analytics, social media, and advertising partners in according with our Privacy Statement. You can manage this sharing by selecting the "Cookie Settings" link.' : ''
        ]);
    }
}
