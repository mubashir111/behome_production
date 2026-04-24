<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

use Smartisan\Settings\Facades\Settings;

class SocialMediaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Settings::group('social_media')->set([
            'social_media_facebook'  => env('DEMO', false) ? 'https://www.facebook.com/behom' : '',
            'social_media_youtube'   => env('DEMO', false) ? 'https://www.youtube.com/@behom' : '',
            'social_media_instagram' => env('DEMO', false) ? 'https://www.instagram.com/behom' : '',
            'social_media_twitter'   => env('DEMO', false) ? 'https://twitter.com/behom' : ''
        ]);
    }
}
