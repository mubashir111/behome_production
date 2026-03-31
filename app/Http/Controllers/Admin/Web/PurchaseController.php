<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Tax;
use App\Services\PurchaseService;
use Exception;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(PaginateRequest $request)
    {
        try {
            $request->merge(['paginate' => 1]);
            $purchases = $this->purchaseService->list($request);
            return view('admin.purchases.index', compact('purchases'));
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::with('variations', 'productTaxes', 'taxes')
            ->where('status', \App\Enums\Status::ACTIVE)
            ->where('can_purchasable', \App\Enums\Ask::YES)
            ->get();
        $taxes = Tax::all();
        
        return view('admin.purchases.create', compact('suppliers', 'products', 'taxes'));
    }

    public function store(PurchaseRequest $request)
    {
        try {
            $this->purchaseService->store($request);
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase recorded successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase = $this->purchaseService->show($purchase);
        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::all();
        $products = Product::with('variations', 'productTaxes', 'taxes')
            ->where('status', \App\Enums\Status::ACTIVE)
            ->where('can_purchasable', \App\Enums\Ask::YES)
            ->get();
        $taxes = Tax::all();
        // The edit method on the service might just return the purchase with relations
        $purchase = $this->purchaseService->edit($purchase);

        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'products', 'taxes'));
    }

    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        try {
            $this->purchaseService->update($request, $purchase);
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase updated successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function destroy(Purchase $purchase)
    {
        try {
            $this->purchaseService->destroy($purchase);
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase deleted successfully.');
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
