<?php

namespace App\Services;


use App\Http\Requests\CompanyRequest;
use App\Models\ThemeSetting;
use Dipokhalder\EnvEditor\EnvEditor;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class CompanyService
{

    public $envService;

    public function __construct()
    {
        $this->envService = new EnvEditor();
    }

    /**
     * @throws Exception
     */
    public function list()
    {
        try {
            return Settings::group('company')->all();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(CompanyRequest $request)
    {
        try {
            $data = $request->validated();
            unset($data['company_logo']); // file — not stored as a settings value
            Settings::group('company')->set($data);
            $this->envService->addData(['APP_NAME' => $request->company_name]);

            if ($request->hasFile('company_logo')) {
                $setting = ThemeSetting::firstOrCreate(
                    ['key' => 'company_logo'],
                    ['payload' => json_encode(['$value' => '', '$cast' => null]), 'group' => 'company']
                );
                $setting->clearMediaCollection('company-logo');
                $setting->addMediaFromRequest('company_logo')->toMediaCollection('company-logo');
            }

            Artisan::call('optimize:clear');
            return $this->list();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
