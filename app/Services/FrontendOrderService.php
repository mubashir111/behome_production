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
                ->with(['user', 'orderProducts'])
                // Hide orders that are mid-payment (unpaid online + created within last 30 min).
                // COD and Credit orders are always unpaid at creation — always show them.
                ->where(function ($q) {
                    $codGatewayIds = \App\Models\PaymentGateway::whereIn('slug', ['cashondelivery', 'credit'])->pluck('id');
                    $q->where('payment_status', \App\Enums\PaymentStatus::PAID)           // paid — always show
                      ->orWhereIn('payment_method', $codGatewayIds)                        // COD/Credit — always show
                      ->orWhere('created_at', '<', now()->subMinutes(30));                 // abandoned — show so customer can retry
                })
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
                // Only clean up abandoned online-payment orders.
                // COD and Credit orders are active/confirmed at placement — never delete them here.
                $offlineGatewayIds = \App\Models\PaymentGateway::whereIn('slug', ['cashondelivery', 'credit'])->pluck('id');
                $oldOrder     = Order::where(['user_id' => Auth::user()->id, 'active' => Status::INACTIVE])
                    ->whereNotIn('payment_method', $offlineGatewayIds);
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
                        $isOffer = $product->offer_start_date && $product->offer_end_date && $product->offer_start_date < now() && $product->offer_end_date > now();
                        $variation = null;
                        if ($p->variation_id > 0) {
                            $variation = ProductVariation::findOrFail($p->variation_id);
                            $price = $variation->price;
                            $sku = $variation->sku;
                        } else {
                            $price = $product->selling_price;
                            $sku = $product->sku;
                        }

                        if ($isOffer) {
                            $price = max(0, $price - $product->discount);
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

                        $unitDiscount = $isOffer ? $product->discount : 0;
                        $itemTotalDiscount = $unitDiscount * $p->quantity;

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
                            'discount'        => $itemTotalDiscount,
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
                
                $finalTotalTax = 0;
                $finalSubtotal = 0;
                $finalTotalAmount = 0;

                foreach ($products as $p) {
                    $product = Product::findOrFail($p->product_id);
                    $isOffer = $product->offer_start_date && $product->offer_end_date && $product->offer_start_date < now() && $product->offer_end_date > now();
                    
                    if ($p->variation_id > 0) {
                        $variation = ProductVariation::findOrFail($p->variation_id);
                        $price = $variation->price;
                    } else {
                        $price = $product->selling_price;
                    }

                    if ($isOffer) {
                        $price = max(0, $price - $product->discount);
                    }

                    $itemSubtotal = $price * $p->quantity;
                    $itemTotalTax = 0;
                    $productTaxes = $product->taxes()->with('tax')->get();
                    foreach ($productTaxes as $pt) {
                        $itemTotalTax += ($itemSubtotal * $pt->tax->tax_rate) / 100;
                    }

                    $finalTotalTax += $itemTotalTax;
                    $finalSubtotal += $itemSubtotal;
                    $finalTotalAmount += ($itemSubtotal + $itemTotalTax);
                }

                $this->order->tax = $finalTotalTax;
                $this->order->subtotal = $finalSubtotal;
                $this->order->total = $finalTotalAmount + $this->order->shipping_charge - $this->order->discount;
                
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

                // COD orders are confirmed at placement — activate immediately.
                // Online payment orders stay inactive until PaymentService::payment() is called.
                $paymentGateway = \App\Models\PaymentGateway::find($request->payment_method);
                if ($paymentGateway && in_array($paymentGateway->slug, ['cashondelivery', 'credit'])) {
                    $this->order->active = \App\Enums\Ask::YES;
                    $this->order->save();
                    Stock::where(['model_id' => $this->order->id, 'model_type' => Order::class, 'status' => Status::INACTIVE])
                        ->update(['status' => Status::ACTIVE]);

                    // Clear cart for COD
                    $orderProducts = json_decode($request->products);
                    if (!blank($orderProducts)) {
                        foreach ($orderProducts as $p) {
                            $varId = $p->variation_id ?? null;
                            $q = Cart::where('user_id', Auth::user()->id)
                                     ->where('product_id', $p->product_id);
                            $varId > 0
                                ? $q->where('variation_id', $varId)
                                : $q->where(fn($w) => $w->whereNull('variation_id')->orWhere('variation_id', 0));
                            $q->delete();
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
