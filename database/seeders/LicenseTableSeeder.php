<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Smartisan\Settings\Facades\Settings;

class LicenseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Settings::group('license')->set([
            'license_key' => env('MIX_API_KEY', false)
        ]);
        Artisan::call('optimize:clear');
    }
}
