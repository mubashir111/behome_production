<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\AppLibrary;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Product::active()->with(['category', 'brand', 'media']);

            if ($request->has('category_slug')) {
                $category = \App\Models\ProductCategory::where('slug', $request->category_slug)->first();
                if ($category) {
                    $categoryIds = $category->descendantsAndSelf()->pluck('id');
                    $query->whereIn('product_category_id', $categoryIds);
                }
            }

            if ($request->has('brand_slug')) {
                $query->whereHas('brand', function ($q) use ($request) {
                    $q->where('slug', $request->brand_slug);
                });
            }

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('min_price')) {
                $query->where('selling_price', '>=', (float) $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('selling_price', '<=', (float) $request->max_price);
            }

            if ($request->get('sort') === 'popular') {
                $query->orderBy('order', 'asc');
            } elseif ($request->get('sort') === 'offer') {
                $query->whereNotNull('offer_start_date')
                      ->whereNotNull('offer_end_date')
                      ->where('offer_start_date', '<=', now())
                      ->where('offer_end_date', '>=', now())
                      ->where('discount', '>', 0)
                      ->orderBy('discount', 'desc');
            } else {
                $query->latest();
            }

            $products = $query->withSum('productStocks', 'quantity')->paginate($request->get('per_page', 12));

            $products->through(function ($product) {
                return [
                    'id'                  => $product->id,
                    'name'                => $product->name,
                    'slug'                => $product->slug,
                    'sku'                 => $product->sku,
                    'price'               => number_format((float) $product->selling_price, 2, '.', ''),
                    'discounted_price'    => AppLibrary::currencyAmountFormat((float) ($product->selling_price - $product->discount)),
                    'currency_price'      => AppLibrary::currencyAmountFormat((float) $product->selling_price),
                    'cover'               => $product->getFirstMediaUrl('product') ? $product->cover : null,
                    'is_offer'            => $product->offer_start_date && $product->offer_end_date && $product->offer_start_date < now() && $product->offer_end_date > now(),
                    'product_category_id' => $product->product_category_id,
                    'category_slug'       => $product->category?->slug,
                    'brand_slug'          => $product->brand?->slug,
                    'stock'               => (int) ($product->product_stocks_sum_quantity ?? 0),
                ];
            });

            return $this->successResponse($products, 'Products retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show($slug)
    {
        try {
            $product = Product::active()->where('slug', $slug)
                ->with(['category', 'brand', 'variations.media', 'taxes', 'reviews.user'])
                ->withSum('productStocks', 'quantity')
                ->firstOrFail();

            $isOffer = $product->offer_start_date && $product->offer_end_date
                && $product->offer_start_date < now() && $product->offer_end_date > now();

            $data = [
                'id'             => $product->id,
                'name'           => $product->name,
                'slug'           => $product->slug,
                'sku'            => $product->sku,
                'description'    => $product->description,
                'details'        => $product->details,
                'additional_info'=> $product->additional_info,
                'shipping_and_return' => $product->shipping_and_return,
                'price'          => number_format((float) $product->selling_price, 2, '.', ''),
                'old_price'      => number_format((float) ($product->selling_price + $product->discount), 2, '.', ''),
                'is_offer'       => $isOffer,
                'stock'          => (int) ($product->product_stocks_sum_quantity ?? 0),
                'can_purchasable'=> $product->can_purchasable,
                'images'         => $product->images,
                'category'       => $product->category,
                'brand'          => $product->brand,
                'variations'     => $product->variations,
                'reviews'        => $product->reviews->map(fn($r) => [
                    'id'         => $r->id,
                    'name'       => $r->user?->name ?? 'Customer',
                    'star'       => $r->star,
                    'review'     => $r->review,
                    'created_at' => $r->created_at?->format('M d, Y'),
                ]),
                'rating'         => round($product->averageRating(), 1),
                'reviews_count'  => $product->reviewCount(),
            ];

            return $this->successResponse($data, 'Product details retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Product not found', 404);
        }
    }
}
