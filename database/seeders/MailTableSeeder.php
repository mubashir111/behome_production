<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Smartisan\Settings\Facades\Settings;

class MailTableSeeder extends Seeder
{
    public function run()
    {
        // Seed blank mail settings — installer sets real values via siteSetup()
        Settings::group('mail')->set([
            'mail_mailer'     => 'smtp',
            'mail_host'       => '',
            'mail_port'       => '',
            'mail_username'   => '',
            'mail_password'   => '',
            'mail_encryption' => 'tls',
            'mail_from_name'  => 'Behome',
            'mail_from_email' => ''
        ]);

        Artisan::call('optimize:clear');
    }
}
