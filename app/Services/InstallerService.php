<?php

namespace App\Services;

use Dipokhalder\EnvEditor\EnvEditor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallerService
{
    public function siteSetup(Request $request): void
    {
        $envService = new EnvEditor();
        $envService->addData([
            'APP_NAME'          => $request->app_name,
            'APP_URL'           => rtrim($request->app_url, '/'),
            'FRONTEND_URL'      => rtrim($request->frontend_url, '/'),
            'MAIL_MAILER'       => 'smtp',
            'MAIL_HOST'         => $request->mail_host,
            'MAIL_PORT'         => $request->mail_port,
            'MAIL_ENCRYPTION'   => $request->mail_encryption ?? 'tls',
            'MAIL_USERNAME'     => $request->mail_username ?? '',
            'MAIL_PASSWORD'     => $request->mail_password ?? '',
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME'    => $request->mail_from_name,
        ]);
        Artisan::call('optimize:clear');
    }

    public function databaseSetup(Request $request): bool
    {
        $connection = $this->checkDatabaseConnection($request);
        if ($connection) {
            $envService = new EnvEditor();
            $envService->addData([
                'DB_HOST'     => $request->database_host,
                'DB_PORT'     => $request->database_port,
                'DB_DATABASE' => $request->database_name,
                'DB_USERNAME' => $request->database_username,
                'DB_PASSWORD' => $request->database_password,
            ]);

            Artisan::call('config:cache');
            Artisan::call('migrate:fresh', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('optimize:clear');
            Artisan::call('config:clear');

            return true;
        }
        return false;
    }

    public function checkDatabaseConnection(Request $request): bool
    {
        $connection = 'mysql';
        $settings   = config("database.connections.$connection");
        config([
            'database' => [
                'default'     => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver'   => $connection,
                        'host'     => $request->input('database_host'),
                        'port'     => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            DB::connection()->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function finalSetup(): void
    {
        $installedLogFile = storage_path('installed');
        $dateStamp        = date('Y-m-d h:i:s A');

        if (!file_exists($installedLogFile)) {
            $message = trans('installer.installed.success_log_message') . $dateStamp . "\n";
            file_put_contents($installedLogFile, $message);
        } else {
            $message = trans('installer.installed.update_log_message') . $dateStamp;
            file_put_contents($installedLogFile, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        Artisan::call('storage:link', ['--force' => true]);

        $envService = new EnvEditor();
        $envService->addData([
            'APP_ENV'          => 'production',
            'APP_DEBUG'        => 'false',
            'QUEUE_CONNECTION' => 'database',
        ]);

        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        Artisan::call('optimize');
    }
}
