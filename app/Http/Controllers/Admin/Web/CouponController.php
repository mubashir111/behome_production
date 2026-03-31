<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\Coupon;
use App\Services\CouponService;
use Exception;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function index(PaginateRequest $request)
    {
        try {
            $request->merge(['paginate' => 1]);
            $coupons = $this->couponService->list($request);
            return view('admin.coupons.index', compact('coupons'));
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(CouponRequest $request)
    {
        try {
            $this->couponService->store($request);
            return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        try {
            $this->couponService->update($request, $coupon);
            return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function destroy(Coupon $coupon)
    {
        try {
            $this->couponService->destroy($coupon);
            return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully.');
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
