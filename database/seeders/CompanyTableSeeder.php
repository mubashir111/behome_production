<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Smartisan\Settings\Facades\Settings;

class CompanyTableSeeder extends Seeder
{
    public function run()
    {
        Settings::group('company')->set([
            'company_name'         => 'Behom',
            'company_email'        => '',
            'company_calling_code' => '+1',
            'company_phone'        => '',
            'company_website'      => '',
            'company_city'         => '',
            'company_state'        => '',
            'company_country_code' => 'USA',
            'company_zip_code'     => '',
            'company_latitude'     => '',
            'company_longitude'    => '',
            'company_address'      => ''
        ]);

        Artisan::call('optimize:clear');
    }
}
