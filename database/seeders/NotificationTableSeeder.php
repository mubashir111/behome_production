<?php

namespace Database\Seeders;


use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Smartisan\Settings\Facades\Settings;


class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $envService = new EnvEditor();
        Settings::group('notification')->set([
            'notification_fcm_secret_key'          => $envService->getValue('DEMO') ? 'AAAAR-ItgeQ:APA91bHSRHexFge83tV33p9xiV0qyQ-naPZJj8TnCM9xg9gq4c_fyn30LP-x81SEnQDTbPFrqiMHkf7WSYnpM18Zb9uccmJX2wI6d1-DhAB13Kf6WFoUsPeDd07MdAEKTauVAGUD_d_J' : '',
            'notification_fcm_public_vapid_key'    => $envService->getValue('DEMO') ? 'BEJUlPCfKVEuTJOcT4yR53ndElyQo8LJGYaM_GzyjMXgpdvu2bN0eASgrqC18oKhGGE5I0dERO1_UqCJ-sHHETE' : '',
            'notification_fcm_api_key'             => $envService->getValue('DEMO') ? 'AIzaSyBGzuLCCMSABotWASYTSzYK3fAiQ39w5R8' : '',
            'notification_fcm_auth_domain'         => $envService->getValue('DEMO') ? 'shopperz-fe4ea.firebaseapp.com' : '',
            'notification_fcm_project_id'          => $envService->getValue('DEMO') ? 'shopperz-fe4ea' : '',
            'notification_fcm_storage_bucket'      => $envService->getValue('DEMO') ? 'shopperz-fe4ea.appspot.com' : '',
            'notification_fcm_messaging_sender_id' => $envService->getValue('DEMO') ? '308737311204' : '',
            'notification_fcm_app_id'              => $envService->getValue('DEMO') ? '1:308737311204:web:b7079c17fa6bf8d31bc7f1' : '',
            'notification_fcm_measurement_id'      => $envService->getValue('DEMO') ? 'G-T1CSXVXREN' : '',
        ]);

        $envService->addData([
            'FCM_SECRET_KEY' => $envService->getValue('DEMO') ? 'AAAAR-ItgeQ:APA91bHSRHexFge83tV33p9xiV0qyQ-naPZJj8TnCM9xg9gq4c_fyn30LP-x81SEnQDTbPFrqiMHkf7WSYnpM18Zb9uccmJX2wI6d1-DhAB13Kf6WFoUsPeDd07MdAEKTauVAGUD_d_J' : '',
        ]);
        Artisan::call('optimize:clear');
    }
}
