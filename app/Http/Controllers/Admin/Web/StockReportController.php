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

        // Base query for counting and summary
        $query = Product::query()
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('sku', 'like', "%{$search}%");
            }));

        // Summary counts (still need products with their stock sums)
        $stockSub = function ($q) {
            $q->where('status', Status::ACTIVE);
        };

        // For summary stats (we only need the stock sums, not full models)
        $allWithStock = (clone $query)
            ->withSum(['productStocks as stock_qty' => $stockSub], 'quantity')
            ->get(['id', 'buying_price', 'low_stock_quantity_warning']);

        $totalProducts   = $allWithStock->count();
        $inStock         = $allWithStock->filter(fn($p) => ($p->stock_qty ?? 0) > ($p->low_stock_quantity_warning ?? 5))->count();
        $lowStock        = $allWithStock->filter(fn($p) => ($p->stock_qty ?? 0) > 0 && ($p->stock_qty ?? 0) <= ($p->low_stock_quantity_warning ?? 5))->count();
        $outOfStock      = $allWithStock->filter(fn($p) => ($p->stock_qty ?? 0) <= 0)->count();
        $totalStockValue = $allWithStock->sum(fn($p) => max(0, (int)($p->stock_qty ?? 0)) * (float)($p->buying_price ?? 0));

        // Filtering
        $filtered = match($filter) {
            'out_of_stock' => $allWithStock->filter(fn($p) => ($p->stock_qty ?? 0) <= 0),
            'low_stock'    => $allWithStock->filter(fn($p) => ($p->stock_qty ?? 0) > 0 && ($p->stock_qty ?? 0) <= ($p->low_stock_quantity_warning ?? 5)),
            'in_stock'     => $allWithStock->filter(fn($p) => ($p->stock_qty ?? 0) > ($p->low_stock_quantity_warning ?? 5)),
            default        => $allWithStock,
        };

        // Sorting
        $sorted = $filtered->sortBy(fn($p) => (int)($p->stock_qty ?? 0))->values();

        // Paginate only the relevant IDs to avoid loading 1000s of relationships at once
        $page      = $request->input('page', 1);
        $pagedIds  = $sorted->forPage($page, $perPage)->pluck('id');
        
        $productsList = Product::with(['category', 'media'])
            ->withSum(['productStocks as stock_qty' => $stockSub], 'quantity')
            ->whereIn('id', $pagedIds)
            ->get()
            ->sortBy(fn($p) => (int)($p->stock_qty ?? 0))
            ->values();

        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $productsList,
            $sorted->count(),
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
