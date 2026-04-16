<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Services\OrderAreaService;
use App\Http\Requests\OrderAreaRequest;
use App\Http\Requests\PaginateRequest;
use Illuminate\Http\Request;
use Smartisan\Settings\Facades\Settings;

class ShippingController extends Controller
{
    public function orderAreas(PaginateRequest $request, OrderAreaService $service)
    {
        $areas          = \App\Models\OrderArea::latest()->paginate(10);
        $currencySymbol = Settings::group('site')->get('site_default_currency_symbol') ?? '$';
        return view('admin.shipping.order-areas', compact('areas', 'currencySymbol'));
    }

    public function storeOrderArea(OrderAreaRequest $request, OrderAreaService $service)
    {
        try {
            $service->store($request);
            return back()->with('success', 'Delivery area created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateOrderArea(OrderAreaRequest $request, \App\Models\OrderArea $orderArea, OrderAreaService $service)
    {
        try {
            // Status-only toggle (activate/deactivate button)
            if ($request->has('status') && !$request->has('country')) {
                $orderArea->status = (int) $request->status;
                $orderArea->save();
                \App\Models\AdminNotification::record('info', 'Shipping Status Updated', "Delivery status for '{$orderArea->country}' was updated by " . (auth()->user()->name ?? 'Admin'));
                return back()->with('success', 'Delivery area status updated.');
            }

            $orderArea->update($request->validated());
            
            \App\Models\AdminNotification::record('warning', 'Shipping Rate Modified', "Shipping rules for '{$orderArea->country}' were updated (New Cost: " . (Settings::group('site')->get('site_default_currency_symbol') ?? '$') . "{$orderArea->shipping_cost}) by " . (auth()->user()->name ?? 'Admin'));
            
            return back()->with('success', 'Delivery area updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyOrderArea(\App\Models\OrderArea $orderArea, OrderAreaService $service)
    {
        try {
            $service->destroy($orderArea);
            return back()->with('success', 'Delivery area removed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
