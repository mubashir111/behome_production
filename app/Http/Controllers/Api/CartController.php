<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $cartItems = Cart::where('user_id', Auth::id())
                ->with(['product.taxes.tax', 'variation'])
                ->get();

            // Recalculate tax from current product tax rates
            foreach ($cartItems as $item) {
                $taxRate = 0;
                if ($item->product) {
                    foreach ($item->product->taxes as $productTax) {
                        if ($productTax->tax) {
                            $taxRate += (float) $productTax->tax->tax_rate;
                        }
                    }
                }
                $taxPerUnit = round(($item->price * $taxRate) / 100, (int) config('app.currency_decimal_point'));
                $subtotal   = $item->price * $item->quantity;
                $tax        = $taxPerUnit * $item->quantity;
                $total      = $subtotal + $tax;

                if ($item->tax != $tax || $item->subtotal != $subtotal || $item->total != $total) {
                    $item->tax      = $tax;
                    $item->subtotal = $subtotal;
                    $item->total    = $total;
                    $item->save();
                }
            }

            return $this->successResponse($cartItems, 'Cart retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity'     => 'required|integer|min:1',
        ]);

        try {
            $product = Product::with(['taxes.tax'])->findOrFail($request->product_id);
            $price = $product->selling_price;
            $sku = $product->sku;
            $variation_names = '';

            if ($request->variation_id) {
                $variation = ProductVariation::with(['productAttribute', 'productAttributeOption'])->findOrFail($request->variation_id);
                $price = $variation->price;
                $sku = $variation->sku;
                if ($variation->productAttribute && $variation->productAttributeOption) {
                    $variation_names = $variation->productAttribute->name . ': ' . $variation->productAttributeOption->name;
                }
            }

            // Calculate tax from product tax rates
            $taxRate = 0;
            foreach ($product->taxes as $productTax) {
                if ($productTax->tax) {
                    $taxRate += (float) $productTax->tax->tax_rate;
                }
            }
            $subtotal = $price * $request->quantity;
            $tax = round(($subtotal * $taxRate) / 100, (int) config('app.currency_decimal_point'));
            $total = $subtotal + $tax;

            $cartItem = Cart::where([
                'user_id'      => Auth::id(),
                'product_id'   => $request->product_id,
                'variation_id' => $request->variation_id,
            ])->first();

            if ($cartItem) {
                $cartItem->quantity += $request->quantity;
                
                // Recalculate tax and totals for the new quantity
                $taxRate = 0;
                foreach ($product->taxes as $productTax) {
                    if ($productTax->tax) {
                        $taxRate += (float) $productTax->tax->tax_rate;
                    }
                }
                
                $cartItem->subtotal = $cartItem->price * $cartItem->quantity;
                $cartItem->tax      = round(($cartItem->subtotal * $taxRate) / 100, (int) config('app.currency_decimal_point'));
                $cartItem->total    = $cartItem->subtotal + $cartItem->tax;
                $cartItem->save();
            } else {
                $cartItem = Cart::create([
                    'user_id'         => Auth::id(),
                    'product_id'      => $request->product_id,
                    'variation_id'    => $request->variation_id,
                    'quantity'        => $request->quantity,
                    'price'           => $price,
                    'tax'             => $tax,
                    'subtotal'        => $subtotal,
                    'total'           => $total,
                    'sku'             => $sku,
                    'variation_names' => $variation_names,
                ]);
            }

            return $this->successResponse($cartItem, 'Item added to cart successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartItem = Cart::where('user_id', Auth::id())->with('product.taxes.tax')->findOrFail($id);

            // Validate against available stock
            if ($cartItem->product && $cartItem->product->stock !== null) {
                if ($request->quantity > $cartItem->product->stock) {
                    return $this->errorResponse('Only ' . $cartItem->product->stock . ' items available in stock');
                }
            }

            $cartItem->quantity = $request->quantity;
            
            // Recalculate tax and totals
            $taxRate = 0;
            if ($cartItem->product) {
                foreach ($cartItem->product->taxes as $productTax) {
                    if ($productTax->tax) {
                        $taxRate += (float) $productTax->tax->tax_rate;
                    }
                }
            }
            
            $cartItem->subtotal = $cartItem->price * $cartItem->quantity;
            $cartItem->tax      = round(($cartItem->subtotal * $taxRate) / 100, (int) config('app.currency_decimal_point'));
            $cartItem->total    = $cartItem->subtotal + $cartItem->tax;
            $cartItem->save();
            $cartItem->load(['product', 'variation']);

            return $this->successResponse($cartItem, 'Cart updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $cartItem = Cart::where('user_id', Auth::id())->findOrFail($id);
            $cartItem->delete();
            return $this->successResponse([], 'Item removed from cart');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function clear()
    {
        try {
            Cart::where('user_id', Auth::id())->delete();
            return $this->successResponse([], 'Cart cleared');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
