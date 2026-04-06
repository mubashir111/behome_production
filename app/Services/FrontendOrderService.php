<?php

namespace App\Services;

use Exception;
use App\Enums\Status;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Outlet;
use App\Models\Address;
use App\Models\Product;
use App\Enums\OrderType;
use App\Models\StockTax;
use App\Enums\AddressType;
use App\Enums\OrderStatus;
use App\Models\OrderCoupon;
use App\Enums\PaymentStatus;
use App\Events\SendOrderSms;
use App\Models\OrderAddress;
use App\Events\SendOrderMail;
use App\Events\SendOrderPush;
use App\Models\ProductVariation;
use App\Models\OrderOutletAddress;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Services\AuditLogger;

class FrontendOrderService
{

    public object $order;
    protected array $frontendOrderFilter = [
        'order_serial_no',
        'user_id',
        'total',
        'order_type',
        'order_datetime',
        'payment_method',
        'payment_status',
        'status',
        'active'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function myOrder(PaginateRequest $request)
    {
        try {
            $requests            = $request->all();
            $method              = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue         = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $frontendOrderColumn = $request->get('order_column') ?? 'id';
            $frontendOrderType   = $request->get('order_by') ?? 'desc';

            return Order::where('order_type', "!=", OrderType::POS)
                ->with(['user', 'orderProducts']) // Eager load relations to fix N+1
                ->where(function ($query) use ($requests) {
                    $query->where('user_id', auth()->user()->id);
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->frontendOrderFilter)) {
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
                                    $query->where('status', '!=', $explode);
                                }
                            }
                        }
                    }
                })->orderBy($frontendOrderColumn, $frontendOrderType)->$method(
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
    public function myOrderStore(OrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $oldOrder     = Order::where(['user_id' => Auth::user()->id, 'active' => Status::INACTIVE]);
                $orderReplace = $oldOrder;
                if (!blank($oldOrder->get())) {
                    $ids          = $oldOrder->pluck('id');
                    $stock        = Stock::whereIn('model_id', $ids)->where(['model_type' => Order::class, 'status' => Status::INACTIVE]);
                    $stockReplace = $stock;
                    $stock        = $stock->get();
                    $stockIds     = $stock->pluck('id');
                    if (!blank($stockIds)) {
                        StockTax::whereIn('stock_id', $stockIds)?->delete();
                    }
                    $stockReplace?->delete();
                    OrderAddress::whereIn('order_id', $ids)?->delete();
                    OrderOutletAddress::whereIn('order_id', $ids)?->delete();
                    OrderCoupon::whereIn('order_id', $ids)?->delete();
                    $orderReplace->delete();
                }

                $this->order = Order::create(
                    $request->validated() + [
                        'user_id'        => Auth::user()->id,
                        'status'         => OrderStatus::PENDING,
                        'payment_status' => PaymentStatus::UNPAID,
                        'order_datetime' => date('Y-m-d H:i:s')
                    ]
                );

                $products = json_decode($request->products);
                if (!blank($products)) {
                    foreach ($products as $p) {
                        // FETCH FROM SERVER TO PREVENT PRICE MANIPULATION
                        $product = Product::findOrFail($p->product_id);
                        $variation = null;
                        if ($p->variation_id > 0) {
                            $variation = ProductVariation::findOrFail($p->variation_id);
                            $price = $variation->price;
                            $sku = $variation->sku;
                        } else {
                            $price = $product->selling_price;
                            $sku = $product->sku;
                        }

                        // Calculate server-side totals
                        $itemSubtotal = $price * $p->quantity;
                        $itemTotalTax = 0;
                        $productTaxes = $product->taxes()->with('tax')->get();
                        
                        foreach ($productTaxes as $pt) {
                            $taxRate = $pt->tax->tax_rate;
                            $itemTotalTax += ($itemSubtotal * $taxRate) / 100;
                        }

                        $itemTotal = $itemSubtotal + $itemTotalTax;

                        $stockId = Stock::create([
                            'product_id'      => $p->product_id,
                            'model_type'      => Order::class,
                            'model_id'        => $this->order->id,
                            'item_type'       => $p->variation_id > 0 ? ProductVariation::class : Product::class,
                            'item_id'         => $p->variation_id > 0 ? $p->variation_id : $p->product_id,
                            'variation_names' => $p->variation_names,
                            'sku'             => $sku,
                            'price'           => $price,
                            'quantity'        => -$p->quantity,
                            'discount'        => 0, // In this system discount seems handled differently or not yet implement for direct item
                            'tax'             => number_format($itemTotalTax, (int)config('app.currency_decimal_point'), '.', ''),
                            'subtotal'        => $itemSubtotal,
                            'total'           => $itemTotal,
                            'status'          => Status::INACTIVE,
                        ]);

                        if ($productTaxes) {
                            $productTaxArray = [];
                            foreach ($productTaxes as $pt) {
                                $taxAmount = ($itemSubtotal * $pt->tax->tax_rate) / 100;
                                $productTaxArray[] = [
                                    'stock_id'   => $stockId->id,
                                    'product_id' => $p->product_id,
                                    'tax_id'     => $pt->tax_id,
                                    'name'       => $pt->tax->name,
                                    'code'       => $pt->tax->code,
                                    'tax_rate'   => $pt->tax->tax_rate,
                                    'tax_amount' => $taxAmount,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                            StockTax::insert($productTaxArray);
                        }
                    }
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->setCustomerNote($request->reason);
                
                // Final server-side total verification for the Order record itself
                $this->order->load('orderProducts');
                $totalTax = $this->order->orderProducts->sum('tax');
                $totalSubtotal = $this->order->orderProducts->sum('subtotal');
                $totalAmount = $this->order->orderProducts->sum('total');
                
                $this->order->tax = $totalTax;
                $this->order->subtotal = $totalSubtotal;
                $this->order->total = $totalAmount;
                
                $this->order->save();

                AuditLogger::orderPlaced($this->order);

                if ($request->order_type == OrderType::DELIVERY) {
                    $shippingAddress = Address::find($request->shipping_id);
                    $billingAddress  = Address::find($request->billing_id);
                    if ($shippingAddress) {
                        OrderAddress::create([
                            'order_id'     => $this->order->id,
                            'user_id'      => $shippingAddress->user_id,
                            'address_type' => AddressType::SHIPPING,
                            'full_name'    => $shippingAddress->full_name,
                            'email'        => $shippingAddress->email,
                            'country_code' => $shippingAddress->country_code,
                            'phone'        => $shippingAddress->phone,
                            'country'      => $shippingAddress->country,
                            'address'      => $shippingAddress->address,
                            'state'        => $shippingAddress->state,
                            'city'         => $shippingAddress->city,
                            'zip_code'     => $shippingAddress->zip_code,
                            'latitude'     => $shippingAddress->latitude,
                            'longitude'    => $shippingAddress->longitude,
                        ]);
                    }
                    if ($billingAddress) {
                        OrderAddress::create([
                            'order_id'     => $this->order->id,
                            'user_id'      => $shippingAddress->user_id,
                            'address_type' => AddressType::BILLING,
                            'full_name'    => $billingAddress->full_name,
                            'email'        => $billingAddress->email,
                            'country_code' => $billingAddress->country_code,
                            'phone'        => $billingAddress->phone,
                            'country'      => $billingAddress->country,
                            'address'      => $billingAddress->address,
                            'state'        => $billingAddress->state,
                            'city'         => $billingAddress->city,
                            'zip_code'     => $billingAddress->zip_code,
                            'latitude'     => $billingAddress->latitude,
                            'longitude'    => $billingAddress->longitude,
                        ]);
                    }
                } elseif ($request->order_type === OrderType::PICK_UP) {
                    $outletAddress = Outlet::find($request->outlet_id);
                    if ($outletAddress) {
                        OrderOutletAddress::create([
                            'order_id'     => $this->order->id,
                            'user_id'      => $this->order->user_id,
                            'name'         => $outletAddress->name,
                            'email'        => $outletAddress->email,
                            'phone'        => $outletAddress->phone,
                            'country_code' => $outletAddress->country_code,
                            'latitude'     => $outletAddress->latitude,
                            'longitude'    => $outletAddress->longitude,
                            'city'         => $outletAddress->city,
                            'state'        => $outletAddress->state,
                            'zip_code'     => $outletAddress->zip_code,
                            'address'      => $outletAddress->address,
                        ]);
                    }
                }

                if ($request->coupon_id > 0) {
                    OrderCoupon::create([
                        'order_id'  => $this->order->id,
                        'coupon_id' => $request->coupon_id,
                        'user_id'   => Auth::user()->id,
                        'discount'  => $request->discount
                    ]);
                }

                // Selective cart clearing: Only for COD. Others clear on payment confirmation.
                $paymentGateway = \App\Models\PaymentGateway::find($request->payment_method);
                if ($paymentGateway && $paymentGateway->slug === 'cashondelivery') {
                    $orderProducts = json_decode($request->products);
                    if (!blank($orderProducts)) {
                        foreach ($orderProducts as $p) {
                            Cart::where([
                                'user_id'      => Auth::user()->id,
                                'product_id'   => $p->product_id,
                                'variation_id' => $p->variation_id ?? 0
                            ])->delete();
                        }
                    }
                }
            });

            try {
                $orderMailNotificationBuilderService = new OrderMailNotificationBuilder($this->order->id);
                $orderMailNotificationBuilderService->adminOrderNotification();
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

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
    public function show(Order $order): Order|array
    {
        try {
            if ($order->user_id == Auth::user()->id) {
                return $order;
            }
            return [];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeStatus(Order $order, OrderStatusRequest $request): Order
    {
        try {
            if ($order->user_id == Auth::user()->id) {
                if ($request->status == OrderStatus::CANCELED) {
                    if ($order->status >= OrderStatus::CONFIRMED) {
                        throw new Exception(trans('all.message.order_confirmed'), 422);
                    } else {
                        if ($order->transaction) {
                            app(PaymentService::class)->cashBack(
                                $order,
                                'credit',
                                rand(111111111111111, 99999999999999)
                            );
                        }
                        SendOrderMail::dispatch(['order_id' => $order->id, 'status' => $request->status]);
                        SendOrderSms::dispatch(['order_id' => $order->id, 'status' => $request->status]);
                        SendOrderPush::dispatch(['order_id' => $order->id, 'status' => $request->status]);

                        $stocks = Stock::where(['model_type' => Order::class, 'model_id' => $order->id])->get();
                        foreach ($stocks as $stock) {
                            $stock->status = Status::INACTIVE;
                            $stock->save();
                        };

                        $oldStatus = $order->status;
                        $order->status = $request->status;
                        if ($request->reason) {
                            $order->setAdminStatusReason($request->reason);
                        }
                        $order->save();
                        AuditLogger::orderStatusChanged($order, $oldStatus, $request->status);

                        try {
                            $orderMailNotificationBuilderService = new OrderMailNotificationBuilder($order->id);
                            $orderMailNotificationBuilderService->adminOrderCancellationNotification();
                        } catch (Exception $e) {
                            Log::info($e->getMessage());
                        }
                    }
                }
            }
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
