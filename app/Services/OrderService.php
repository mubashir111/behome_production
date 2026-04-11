<?php

namespace App\Services;


use App\Models\Product;
use App\Models\ProductVariation;
use Exception;
use App\Models\User;
use App\Enums\Status;
use App\Models\Order;
use App\Models\Stock;
use App\Enums\OrderType;
use App\Models\StockTax;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\SendOrderSms;
use App\Events\SendOrderMail;
use App\Events\SendOrderPush;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PosOrderRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Requests\PaymentStatusRequest;
use App\Services\AuditLogger;

class OrderService
{
    public object $order;
    protected array $orderFilter = [
        'order_serial_no',
        'user_id',
        'total',
        'order_type',
        'order_datetime',
        'payment_method',
        'payment_status',
        'status',
        'active',
        'source'
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
            $orderType   = $request->get('order_by') ?? 'desc';

            return Order::with('transaction', 'orderProducts')->where(function ($query) use ($requests) {
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = Date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = Date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('order_datetime', '>=', $first_date)->whereDate(
                        'order_datetime',
                        '<=',
                        $last_date
                    );
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        if ($key === "status") {
                            $query->where($key, (int)$request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('order_type', '!=', $explode);
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
    public function myOrder(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            return Order::where('order_type', "!=", OrderType::POS)->where(function ($query) use ($requests) {
                $query->where('user_id', auth()->user()->id);
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('status', '!=', $explode);
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
    public function userOrder(PaginateRequest $request, User $user)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            return Order::where('order_type', "!=", OrderType::POS)->where(function ($query) use ($requests, $user) {
                $query->where('user_id', $user->id);
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('status', '!=', $explode);
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
    public function posOrderStore(PosOrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $this->order = Order::create(
                    $request->validated() + [
                        'user_id'        => $request->customer_id,
                        'status'         => OrderStatus::CONFIRMED,
                        'payment_status' => PaymentStatus::PAID,
                        'order_datetime' => date('Y-m-d H:i:s')
                    ]
                );

                $products = json_decode($request->products);
                if (!blank($products)) {
                    foreach ($products as $product) {
                        $stockId = Stock::create([
                            'product_id'      => $product->product_id,
                            'model_type'      => Order::class,
                            'model_id'        => $this->order->id,
                            'item_type'       => $product->variation_id > 0 ? ProductVariation::class : Product::class,
                            'item_id'         => $product->variation_id > 0 ? $product->variation_id : $product->product_id,
                            'variation_names' => $product->variation_names,
                            'sku'             => $product->sku,
                            'price'           => $product->price,
                            'quantity'        => -$product->quantity,
                            'discount'        => $product->discount,
                            'tax'             => number_format($product->total_tax, config('app.currency_decimal_point'), '.', ''),
                            'subtotal'        => $product->subtotal,
                            'total'           => $product->total,
                            'status'          => Status::ACTIVE,
                        ]);
                        if ($product->taxes) {
                            $j               = 0;
                            $productTaxArray = [];
                            foreach ($product->taxes as $tax) {
                                $productTaxArray[$j] = [
                                    'stock_id'   => $stockId->id,
                                    'product_id' => $product->product_id,
                                    'tax_id'     => $tax->id,
                                    'name'       => $tax->name,
                                    'code'       => $tax->code,
                                    'tax_rate'   => $tax->tax_rate,
                                    'tax_amount' => $tax->tax_amount,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                                $j++;
                            }
                            StockTax::insert($productTaxArray);
                        }
                    }
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->save();
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Order $order, $auth = false): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    return $order;
                } else {
                    return [];
                }
            } else {
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function orderDetails(User $user, Order $order): Order|array
    {
        try {
            if ($order->user_id == $user->id) {
                return $order;
            } else {
                return [];
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeStatus(Order $order, OrderStatusRequest $request, $auth = false, $sendEmail = true): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    if ($request->reason) {
                        $order->setAdminStatusReason($request->reason);
                    }
                    SendOrderMail::dispatch(['order_id' => $order->id, 'status' => $request->status, 'force' => $sendEmail]);
                    SendOrderSms::dispatch(['order_id' => $order->id, 'status' => $request->status]);
                    SendOrderPush::dispatch(['order_id' => $order->id, 'status' => $request->status]);
                    $oldStatus = $order->status;
                    $order->status = $request->status;
                    $order->save();
                    AuditLogger::orderStatusChanged($order, $oldStatus, $request->status, $request->reason ?? null);
                }
            } else {
                if ($request->status == OrderStatus::REJECTED || $request->status == OrderStatus::CANCELED) {
                    $request->validate([
                        'reason' => 'required|max:700',
                    ]);

                    if ($request->reason) {
                        $order->setAdminStatusReason($request->reason);
                    }
                    // NOTE: Refund is NOT issued automatically here.
                    // Admin must explicitly click "Issue Refund" on the order page.
                }

                // Clear cancellation request if we are canceling or rejecting
                if ($request->status == OrderStatus::CANCELED || $request->status == OrderStatus::REJECTED) {
                    $payload = $order->reasonPayload();
                    if (isset($payload['cancellation_requested'])) {
                        unset($payload['cancellation_requested']);
                        $order->reason = blank($payload) ? null : json_encode($payload, JSON_UNESCAPED_UNICODE);
                    }
                }

                SendOrderMail::dispatch(['order_id' => $order->id, 'status' => $request->status, 'force' => $sendEmail]);
                SendOrderSms::dispatch(['order_id' => $order->id, 'status' => $request->status]);
                SendOrderPush::dispatch(['order_id' => $order->id, 'status' => $request->status]);
                $oldStatus = $order->status;
                $order->status = $request->status;
                $order->save();
                AuditLogger::orderStatusChanged($order, $oldStatus, $request->status, $request->reason ?? null);
            }
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changePaymentStatus(Order $order, PaymentStatusRequest $request, $auth = false): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    $oldPayment = $order->payment_status;
                    $order->payment_status = $request->payment_status;
                    $order->save();
                    AuditLogger::paymentStatusChanged($order, $oldPayment, $request->payment_status);
                    return $order;
                } else {
                    return [];
                }
            } else {
                $oldPayment = $order->payment_status;
                $order->payment_status = $request->payment_status;
                $order->save();
                AuditLogger::paymentStatusChanged($order, $oldPayment, $request->payment_status);
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Soft-delete an order. All child records (addresses, transactions, messages, etc.)
     * are preserved for reference. Use restore() to undo. Use forceDestroy() to permanently remove.
     *
     * @throws Exception
     */
    public function destroy(Order $order): void
    {
        try {
            $order->delete(); // sets deleted_at — child records untouched
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Restore a soft-deleted order.
     *
     * @throws Exception
     */
    public function restore(Order $order): void
    {
        try {
            $order->restore();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Permanently delete a soft-deleted order and all its child records.
     *
     * @throws Exception
     */
    public function forceDestroy(Order $order): void
    {
        try {
            DB::transaction(function () use ($order) {
                if ($order->orderProducts) {
                    $stockIds = $order->orderProducts->pluck('id');
                    if ($stockIds->isNotEmpty()) {
                        StockTax::whereIn('stock_id', $stockIds)->delete();
                    }
                    $order->orderProducts()->delete();
                }

                $order->address()->delete();
                $order->outletAddress()->delete();

                \App\Models\Transaction::where('order_id', $order->id)->delete();
                \App\Models\CapturePaymentNotification::where('order_id', $order->id)->delete();
                \App\Models\OrderCoupon::where('order_id', $order->id)->delete();

                $return = \App\Models\ReturnAndRefund::where('order_id', $order->id)->first();
                if ($return) {
                    \App\Models\ReturnAndRefundProduct::where('return_and_refund_id', $return->id)->delete();
                    $return->delete();
                }

                $order->messages()->delete();
                $order->audits()->delete();

                $order->forceDelete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
