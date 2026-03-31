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

    public function updateOrderArea(Request $request, \App\Models\OrderArea $orderArea, OrderAreaService $service)
    {
        try {
            // Status-only toggle (activate/deactivate button)
            if ($request->has('status') && !$request->has('country')) {
                $orderArea->status = (int) $request->status;
                $orderArea->save();
                return back()->with('success', 'Delivery area status updated.');
            }

            // Full update — run proper validation via the form request manually
            $validated = $request->validate([
                'country'       => ['required', 'string', 'max:900'],
                'state'         => ['nullable', 'string', 'max:900'],
                'city'          => ['nullable', 'string', 'max:900'],
                'shipping_cost' => ['required', 'numeric'],
                'status'        => ['required', 'numeric'],
            ]);
            $orderArea->update($validated);
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
