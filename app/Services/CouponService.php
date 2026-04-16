<?php

namespace App\Services;



use Carbon\Carbon;
use Exception;
use App\Models\Coupon;
use App\Libraries\AppLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CouponRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\CouponCheckRequest;

class CouponService
{
    public object $coupon;
    protected array $couponFilter = [
        'name',
        'code',
        'discount',
        'discount_type',
        'start_date',
        'end_date',
        'minimum_order',
        'maximum_discount',
        'limit_per_user',
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Coupon::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->couponFilter)) {
                        if ($key == "start_date") {
                            $start_date  = Date('Y-m-d', strtotime($request));
                            $query->whereDate($key, '>=', $start_date);
                        } else if ($key == "end_date") {
                            $end_date  = Date('Y-m-d', strtotime($request));
                            $query->whereDate($key, '<=', $end_date);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(CouponRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $this->coupon = Coupon::create([
                    'name'             => $request->name,
                    'description'      => $request->description,
                    'code'             => $request->code,
                    'discount'         => $request->discount,
                    'discount_type'    => $request->discount_type,
                    'start_date'       => !blank($request->start_date) ? date(
                        'Y-m-d H:i:s',
                        strtotime($request->start_date)
                    ) : "",
                    'end_date'         => !blank($request->end_date) ? date(
                        'Y-m-d H:i:s',
                        strtotime($request->end_date)
                    ) : "",
                    'minimum_order'    => $request->minimum_order,
                    'maximum_discount' => $request->maximum_discount,
                    'limit_per_user'   => $request->limit_per_user,
                ]);
                if ($request->image) {
                    $this->coupon->addMedia($request->image)->toMediaCollection('coupon');
                }

                \App\Models\AdminNotification::record('info', 'Coupon Created', "Coupon '{$this->coupon->code}' was created by " . (auth()->user()->name ?? 'Admin'));
                
                return $this->coupon;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        try {
            return DB::transaction(function () use ($request, $coupon) {
                $this->coupon             = $coupon;
                $coupon->name             = $request->name;
                $coupon->description      = $request->description;
                $coupon->code             = $request->code;
                $coupon->discount         = $request->discount;
                $coupon->discount_type    = $request->discount_type;
                $coupon->start_date       = !blank($request->start_date) ? date(
                    'Y-m-d H:i:s',
                    strtotime($request->start_date)
                ) : null;
                $coupon->end_date         = !blank($request->end_date) ? date(
                    'Y-m-d H:i:s',
                    strtotime($request->end_date)
                ) : null;
                $coupon->minimum_order    = $request->minimum_order;
                $coupon->maximum_discount = $request->maximum_discount;
                $coupon->limit_per_user   = $request->limit_per_user;
                $coupon->save();
                if ($request->image) {
                    $coupon->clearMediaCollection('coupon');
                    $coupon->addMedia($request->image)->toMediaCollection('coupon');
                }

                \App\Models\AdminNotification::record('info', 'Coupon Updated', "Coupon '{$coupon->code}' (ID #{$coupon->id}) was updated by " . (auth()->user()->name ?? 'Admin'));

                return $this->coupon;
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function destroy(Coupon $coupon): void
    {
        try {
            $code = $coupon->code;
            $id   = $coupon->id;
            DB::transaction(function () use ($coupon, $code, $id) {
                $coupon->delete();
                \App\Models\AdminNotification::record('warning', 'Coupon Deleted', "Coupon '{$code}' (ID #{$id}) was deleted by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function couponDateWise(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $now = Carbon::now();
            return Coupon::where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function couponChecking(CouponCheckRequest $request)
    {
        try {
            $coupon = Coupon::where(['code' => $request->code])->first();
            if ($coupon) {
                $now = Carbon::now();
                
                // Check if coupon period has started
                if (strtotime($coupon->start_date) > strtotime($now)) {
                    throw new Exception(trans('all.message.coupon_not_started_yet') ?? 'This coupon is not active yet.', 422);
                }

                // Check if coupon period has ended
                if (strtotime($coupon->end_date) < strtotime($now)) {
                    throw new Exception(trans('all.message.coupon_date_expired'), 422);
                }

                if ($coupon->minimum_order > $request->total) {
                    throw new Exception(trans('all.message.minimum_order_amount') . AppLibrary::convertAmountFormat($coupon->minimum_order), 422);
                }
                
                // Check if user has exceeded their usage limit for this coupon
                if (Auth::check() && $coupon->limit_per_user > 0) {
                    $usageCount = \App\Models\OrderCoupon::where('coupon_id', $coupon->id)
                        ->where('user_id', Auth::id())
                        ->whereHas('order', function($q) {
                            $q->where('status', '!=', \App\Enums\OrderStatus::CANCELED);
                        })->count();
                    
                    if ($usageCount >= $coupon->limit_per_user) {
                        throw new Exception(trans('all.message.coupon_limit_exceeded') ?? 'You have already used this coupon maximum number of times.', 422);
                    }
                }
                
                return $coupon;
            } else {
                throw new Exception(trans('all.message.coupon_not_exist'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
