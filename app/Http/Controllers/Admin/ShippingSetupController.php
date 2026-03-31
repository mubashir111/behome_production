<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ShippingSetupRequest;
use App\Http\Resources\ShippingSetupResource;
use App\Services\ShippingSetupService;
use Exception;

class ShippingSetupController extends AdminController
{
    public ShippingSetupService $shippingSetupService;

    public function __construct(ShippingSetupService $shippingSetupService)
    {
        parent::__construct();
        $this->shippingSetupService = $shippingSetupService;
        $this->middleware(['permission:settings'])->only('update');
    }

    public function index(): \Illuminate\Http\Response | ShippingSetupResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ShippingSetupResource($this->shippingSetupService->list());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        ShippingSetupRequest $request
    ): \Illuminate\Http\Response | ShippingSetupResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ShippingSetupResource($this->shippingSetupService->update($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}