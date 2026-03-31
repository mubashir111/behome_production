<?php

namespace App\Services;


use App\Http\Requests\ShippingSetupRequest;
use Dipokhalder\EnvEditor\EnvEditor;
use Exception;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class ShippingSetupService
{
    public $envService;

    public function __construct(EnvEditor $envEditor)
    {
        $this->envService = $envEditor;
    }

    /**
     * @throws Exception
     */
    public function list()
    {
        try {
            return Settings::group('shipping_setup')->all();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ShippingSetupRequest $request)
    {
        try {
            Settings::group('shipping_setup')->set($request->validated());
            return $this->list();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}