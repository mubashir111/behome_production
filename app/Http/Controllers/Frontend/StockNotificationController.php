<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'email'      => 'required|email|max:255',
        ]);

        $product = Product::find($request->product_id);

        // If product is actually in stock, no need to subscribe
        if ($product->stock > 0) {
            return response()->json(['status' => false, 'message' => 'This product is currently in stock.'], 422);
        }

        StockNotification::firstOrCreate(
            ['product_id' => $request->product_id, 'email' => strtolower(trim($request->email))],
            ['notified' => false]
        );

        return response()->json(['status' => true, 'message' => "We'll notify you at {$request->email} when this product is back in stock."]);
    }
}
