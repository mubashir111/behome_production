<?php

namespace App\Services;

use App\Enums\PurchasePaymentStatus;
use Exception;
use App\Models\Tax;
use App\Enums\Status;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockTax;
use App\Enums\PurchaseStatus;
use App\Models\PurchasePayment;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\PurchasePaymentRequest;
use App\Libraries\QueryExceptionLibrary;

class PurchaseService
{
    public object $purchase;
    protected array $purchaseFilter = [
        'supplier_id',
        'date',
        'reference_no',
        'status',
        'total',
        'note',
        'except'
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

            return Purchase::with('supplier')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->purchaseFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "supplier_id" || $key == 'status') {
                                $query->where($key, $request);
                            } else if ($key == "date" && !empty($request)) {
                                $date_start = date('Y-m-d 00:00:00', strtotime($request));
                                $date_end   = date('Y-m-d 23:59:59', strtotime($request));
                                $query->where($key, '>=', $date_start)->where($key, '<=', $date_end);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(PurchaseRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                // Calculate totals from products if not provided
                $subtotal = $request->subtotal ?? 0;
                $tax = $request->tax ?? 0;
                $discount = $request->discount ?? 0;
                
                // If subtotal is 0, calculate from products
                if ($subtotal == 0 && $request->products) {
                    $products = json_decode($request->products, true);
                    foreach ($products as $product) {
                        $subtotal += isset($product['subtotal']) ? floatval($product['subtotal']) : (floatval($product['price']) * intval($product['quantity']));
                        $tax += isset($product['total_tax']) ? floatval($product['total_tax']) : 0;
                        $discount += isset($product['total_discount']) ? floatval($product['total_discount']) : 0;
                    }
                }
                
                // Ensure subtotal has a value
                if ($subtotal <= 0) {
                    $subtotal = $request->total > 0 ? $request->total : 0;
                }
                
                $this->purchase = Purchase::create([
                    'supplier_id'    => $request->supplier_id,
                    'date'           => date('Y-m-d H:i:s', strtotime($request->date)),
                    'reference_no'   => $request->reference_no,
                    'subtotal'       => max(0, $subtotal),
                    'tax'            => max(0, $tax),
                    'discount'       => max(0, $discount),
                    'total'          => $request->total,
                    'note'           => $request->note ? $request->note : "",
                    'status'         => $request->status,
                    'payment_status' => PurchasePaymentStatus::PENDING
                ]);

                if ($request->products) {
                    $model_id = $this->purchase->id;
                    $products = json_decode($request->products, true);
                    $taxes    = Tax::all()->keyBy('id');
                    foreach ($products as $product) {
                        $stock = Stock::create([
                            'model_type'      => Purchase::class,
                            'model_id'        => $model_id,
                            'item_type'       => $product['is_variation'] ? ProductVariation::class : Product::class,
                            'item_id'         => $product['item_id'],
                            'variation_names' => $product['variation_names'],
                            'product_id'      => $product['product_id'],
                            'price'           => $product['price'],
                            'quantity'        => $product['quantity'],
                            'discount'        => $product['total_discount'],
                            'tax'             => $product['total_tax'],
                            'subtotal'        => $product['subtotal'],
                            'total'           => $product['total'],
                            'sku'             => $product['sku'],
                            'status'          => $request->status == PurchaseStatus::RECEIVED ? Status::ACTIVE : Status::INACTIVE
                        ]);

                        if (isset($product['tax_id']) && count($product['tax_id']) > 0) {
                            foreach ($product['tax_id'] as $tax_id) {
                                if (isset($taxes[$tax_id])) {
                                    $tax = $taxes[$tax_id];
                                    StockTax::create([
                                        'stock_id'   => $stock->id,
                                        'product_id' => $product['product_id'],
                                        'tax_id'     => $tax->id,
                                        'name'       => $tax->name,
                                        'code'       => $tax->code,
                                        'tax_rate'   => $tax->tax_rate,
                                        'tax_amount' => ($tax->tax_rate * ($product['price'] * $product['quantity'])) / 100,
                                    ]);
                                }
                            }
                        }
                    }
                }

                if ($request->file) {
                    $this->purchase->addMediaFromRequest('file')->toMediaCollection('purchase');
                }
            });
            return $this->purchase;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());

            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Purchase $purchase): Purchase
    {
        try {
            return $purchase->load('media');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function edit(Purchase $purchase): Purchase
    {
        try {
            return $purchase;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(PurchaseRequest $request, Purchase $purchase): object
    {
        try {
            DB::transaction(function () use ($request, $purchase) {
                // Calculate totals from products if not provided
                $subtotal = $request->subtotal ?? 0;
                $tax = $request->tax ?? 0;
                $discount = $request->discount ?? 0;
                
                // If subtotal is 0, calculate from products
                if ($subtotal == 0 && $request->products) {
                    $products = json_decode($request->products, true);
                    foreach ($products as $product) {
                        $subtotal += isset($product['subtotal']) ? floatval($product['subtotal']) : (floatval($product['price']) * intval($product['quantity']));
                        $tax += isset($product['total_tax']) ? floatval($product['total_tax']) : 0;
                        $discount += isset($product['total_discount']) ? floatval($product['total_discount']) : 0;
                    }
                }
                
                // Ensure subtotal has a value
                if ($subtotal <= 0) {
                    $subtotal = $request->total > 0 ? $request->total : 0;
                }
                
                $purchase->update([
                    'supplier_id'  => $request->supplier_id,
                    'date'         => date('Y-m-d H:i:s', strtotime($request->date)),
                    'reference_no' => $request->reference_no,
                    'subtotal'     => max(0, $subtotal),
                    'tax'          => max(0, $tax),
                    'discount'     => max(0, $discount),
                    'total'        => $request->total,
                    'note'         => $request->note ? $request->note : "",
                    'status'       => $request->status
                ]);

                if ($request->products) {
                    $model_id = $purchase->id;
                    $products = json_decode($request->products, true);
                    if ($purchase->stocks) {
                        $stockIds = $purchase->stocks->pluck('id');
                        if (!blank($stockIds)) {
                            StockTax::whereIn('stock_id', $stockIds)->delete();
                        }
                        $purchase->stocks()->delete();
                    }
                    $taxes = Tax::all()->keyBy('id');
                    foreach ($products as $product) {
                        $stock = Stock::create([
                            'model_type'      => Purchase::class,
                            'model_id'        => $model_id,
                            'item_type'       => $product['is_variation'] ? ProductVariation::class : Product::class,
                            'item_id'         => $product['item_id'],
                            'variation_names' => $product['variation_names'],
                            'product_id'      => $product['product_id'],
                            'price'           => $product['price'],
                            'quantity'        => $product['quantity'],
                            'discount'        => $product['total_discount'],
                            'tax'             => $product['total_tax'],
                            'subtotal'        => $product['subtotal'],
                            'total'           => $product['total'],
                            'sku'             => $product['sku'],
                            'status'          => $request->status == PurchaseStatus::RECEIVED ? Status::ACTIVE : Status::INACTIVE
                        ]);
                        if (isset($product['tax_id']) && count($product['tax_id']) > 0) {
                            foreach ($product['tax_id'] as $tax_id) {
                                if (isset($taxes[$tax_id])) {
                                    $tax = $taxes[$tax_id];
                                    StockTax::create([
                                        'stock_id'   => $stock->id,
                                        'product_id' => $product['product_id'],
                                        'tax_id'     => $tax->id,
                                        'name'       => $tax->name,
                                        'code'       => $tax->code,
                                        'tax_rate'   => $tax->tax_rate,
                                        'tax_amount' => ($tax->tax_rate * ($product['price'] * $product['quantity'])) / 100,
                                    ]);
                                }
                            }
                        }
                    }
                }

                if ($request->file) {
                    $file = $purchase->getFirstMedia('purchase');
                    if (isset($file)) {
                        $file->delete();
                    }
                    $purchase->addMediaFromRequest('file')->toMediaCollection('purchase');
                }
            });
            return $purchase;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());

            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Purchase $purchase): void
    {
        try {
            DB::transaction(function () use ($purchase) {
                if ($purchase->stocks) {
                    $stockIds = $purchase->stocks->pluck('id');
                    if (!blank($stockIds)) {
                        StockTax::whereIn('stock_id', $stockIds)->delete();
                    }
                    $purchase->stocks()->delete();
                }
                $file = $purchase->getFirstMedia('purchase');
                $file?->delete();
                $purchase->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());

            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function downloadAttachment(Purchase $purchase)
    {
        return $purchase->getMedia('purchase')->first();
    }

    /**
     * @throws Exception
     */
    public function payment(PurchasePaymentRequest $request, Purchase $purchase): object
    {
        try {
            DB::transaction(function () use ($request, $purchase) {
                $purchasePayment = PurchasePayment::create([
                    'purchase_id'    => $purchase->id,
                    'date'           => date('Y-m-d H:i:s', strtotime($request->date)),
                    'reference_no'   => $request->reference_no,
                    'amount'         => $request->amount,
                    'payment_method' => $request->payment_method,
                ]);

                if ($request->file) {
                    $purchasePayment->addMediaFromRequest('file')->toMediaCollection('purchase_payment');
                }

                $paidAmount = PurchasePayment::where('purchase_id', $purchase->id)->sum('amount');

                // Use a threshold for float comparison (epsilon = 0.001)
                $diff = abs($paidAmount - $purchase->total);
                
                if ($diff < 0.001) {
                    $purchase->payment_status = PurchasePaymentStatus::FULLY_PAID;
                    $purchase->save();
                } else if ($paidAmount > 0) {
                    $purchase->payment_status = PurchasePaymentStatus::PARTIAL_PAID;
                    $purchase->save();
                } else {
                    $purchase->payment_status = PurchasePaymentStatus::PENDING;
                    $purchase->save();
                }

                \App\Models\AdminNotification::record('info', 'Purchase Payment', "Payment of {$request->amount} recorded for purchase #{$purchase->reference_no}");
            });
            return $purchase;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());

            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function paymentHistory(Purchase $purchase): object
    {
        try {
            return PurchasePayment::where('purchase_id', $purchase->id)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function paymentDownloadAttachment(PurchasePayment $purchasePayment)
    {
        return $purchasePayment->getMedia('purchase_payment')->first();
    }

    public function paymentDestroy(Purchase $purchase, PurchasePayment $purchasePayment): void
    {
        try {
            DB::transaction(function() use ($purchase, $purchasePayment) {
                $amt = $purchasePayment->amount;
                $purchasePayment->delete();
                $paidAmount = PurchasePayment::where('purchase_id', $purchase->id)->sum('amount');
                
                $diff = abs($paidAmount - $purchase->total);

                if ($diff < 0.001) {
                    $purchase->payment_status = PurchasePaymentStatus::FULLY_PAID;
                    $purchase->save();
                } else if ($paidAmount > 0) {
                    $purchase->payment_status = PurchasePaymentStatus::PARTIAL_PAID;
                    $purchase->save();
                } else {
                    $purchase->payment_status = PurchasePaymentStatus::PENDING;
                    $purchase->save();
                }

                \App\Models\AdminNotification::record('warning', 'Payment Reverted', "Payment of {$amt} deleted for purchase #{$purchase->reference_no} by " . (auth()->user()->name ?? 'Admin'));
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
