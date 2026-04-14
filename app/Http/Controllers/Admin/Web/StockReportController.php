<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Enums\Status;
use Illuminate\Http\Request;

class StockReportController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $filter  = $request->input('filter', 'all');
        $perPage = 20;

        // Load all products with their current stock qty (SUM in PHP to avoid MySQL ONLY_FULL_GROUP_BY)
        $allProducts = Product::query()
            ->with(['category', 'media'])
            ->withSum(['productStocks as stock_qty' => function ($q) {
                $q->where('status', Status::ACTIVE);
            }], 'quantity')
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->get();

        // Summary counts (always from full unfiltered set when no search)
        $summaryBase    = $search ? $allProducts : $allProducts;
        $totalProducts  = $summaryBase->count();
        $inStock        = $summaryBase->filter(fn($p) => ($p->stock_qty ?? 0) > ($p->low_stock_quantity_warning ?? 5))->count();
        $lowStock       = $summaryBase->filter(fn($p) => ($p->stock_qty ?? 0) > 0 && ($p->stock_qty ?? 0) <= ($p->low_stock_quantity_warning ?? 5))->count();
        $outOfStock     = $summaryBase->filter(fn($p) => ($p->stock_qty ?? 0) <= 0)->count();
        $totalStockValue = $summaryBase->sum(fn($p) => max(0, (int)($p->stock_qty ?? 0)) * (float)($p->buying_price ?? 0));

        // Apply filter in PHP
        $filtered = match($filter) {
            'out_of_stock' => $allProducts->filter(fn($p) => ($p->stock_qty ?? 0) <= 0),
            'low_stock'    => $allProducts->filter(fn($p) => ($p->stock_qty ?? 0) > 0 && ($p->stock_qty ?? 0) <= ($p->low_stock_quantity_warning ?? 5)),
            'in_stock'     => $allProducts->filter(fn($p) => ($p->stock_qty ?? 0) > ($p->low_stock_quantity_warning ?? 5)),
            default        => $allProducts,
        };

        // Sort by stock qty ascending (out-of-stock first)
        $filtered = $filtered->sortBy(fn($p) => (int)($p->stock_qty ?? 0))->values();

        // Manual pagination
        $page     = $request->input('page', 1);
        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $filtered->forPage($page, $perPage),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Recent stock movements (last 15)
        $recentMovements = Stock::with('product')
            ->where('status', Status::ACTIVE)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return view('admin.stock.report', compact(
            'products', 'search', 'filter',
            'totalProducts', 'inStock', 'lowStock', 'outOfStock', 'totalStockValue',
            'recentMovements'
        ));
    }

    public function productHistory(Request $request, Product $product)
    {
        $movements = Stock::where('product_id', $product->id)
            ->where('status', Status::ACTIVE)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $currentStock = Stock::where('product_id', $product->id)
            ->where('status', Status::ACTIVE)
            ->sum('quantity');

        return view('admin.stock.product_history', compact('product', 'movements', 'currentStock'));
    }
}
