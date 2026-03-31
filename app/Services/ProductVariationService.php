<?php

namespace App\Services;


use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ProductVariationRequest;
use App\Libraries\AppLibrary;
use App\Models\Product;
use App\Models\ProductVariation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IlluminateAgnostic\Collection\Support\Str;


class ProductVariationService
{
    public object $productVariation;
    protected array $productVariationFilter = [
        'product_attribute_id',
        'product_attribute_option_id',
        'price',
        'sku',
        'parent_id',
        'order'
    ];

    private function variationChild($arrays, $filters): void
    {
        if (count($arrays)) {
            foreach ($arrays as $array) {
                $array->selected = in_array($array->id, $filters);
                $this->variationChild($array->children, $filters);
            }
        }
    }

    private function filterChild(&$arrays, $variations): void
    {
        if (count($variations)) {
            foreach ($variations as $variation) {
                $arrays[] = $variation->id;
                if (count($variation->children) > 0) {
                    $this->filterChild($arrays, $variation->children);
                }
            }
        }
    }

    public function treeWithSelected(Request $request, Product $product)
    {
        $filters = [];
        $this->filterChild($filters, ProductVariation::find($request->id)->bloodline()->get()->toTree());

        $variations = ProductVariation::with(['productAttribute', 'productAttributeOption'])->where([
            'product_id' => $product->id
        ])->tree()->get()->toTree();

        if ($variations) {
            $this->variationChild($variations, $filters);
        }

        return $variations;
    }

    public function tree(Request $request, Product $product)
    {
        if ($request->id) {
            return ProductVariation::find($request->id)->bloodline()->get()->toTree();
        } else {
            return ProductVariation::with(['productAttribute', 'productAttributeOption'])->where(
                ['product_id' => $product->id]
            )->tree()->get()->toTree();
        }
    }

    public function singleTree(Product $product)
    {
        $productVariations = ProductVariation::where('product_id', $product->id)->where('sku', '!=', null)->orderBy('parent_id', 'asc')->get();
        if (count($productVariations)) {
            foreach ($productVariations as $productVariation) {
                $productVariation->options = $this->nested(ProductVariation::find($productVariation->id)->ancestorsAndSelf->load('productAttribute', 'productAttributeOption')->reverse());
            }
        }

        return $productVariations;
    }

    private function nested($variations): array
    {
        $array = [];
        if (count($variations)) {
            foreach ($variations as $variation) {
                $array[$variation->productAttribute?->name] = $variation->productAttributeOption?->name;
            }
        }
        return $array;
    }


    /**
     * @throws Exception
     */
    public function initialVariation(Product $product)
    {
        try {
            $variations = $product->variations;
            if (count($variations)) {
                $variations = $product->variations()
                    ->with(['productAttribute:id,name', 'productAttributeOption:id,name', 'product:id,offer_start_date,offer_end_date,discount,show_stock_out,can_purchasable'])
                    ->get();

                $variations->each(function ($variation) {
                    $variation->stock_items_sum_quantity = $variation->stockItems()->selectRaw('SUM(quantity) as stock_items_sum_quantity')->value('stock_items_sum_quantity');
                });

                return $variations->groupBy('product_attribute_id')->first();
            }
            return $variations;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function childrenVariation(ProductVariation $productVariation): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return $productVariation->children()->with(['productAttribute:id,name', 'productAttributeOption:id,name', 'product:id,offer_start_date,offer_end_date,discount,show_stock_out,can_purchasable'])->withSum('stockItems', 'quantity')->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function allVariation(Product $product)
    {
        return ProductVariation::tree()->depthFirst()->where('product_id', $product->id)->withSum('stockItems', 'quantity')->get()->toTree();
    }

    /**
     * @throws Exception
     */
    public function ancestorsAndSelf(ProductVariation $productVariation)
    {
        try {
            return $productVariation->ancestorsAndSelf->load('productAttribute', 'productAttributeOption')->reverse();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function ancestorsAndSelfId(ProductVariation $productVariation)
    {
        try {
            $productVariations = $productVariation->ancestorsAndSelf->reverse();
            if (count($productVariations)) {
                return $productVariation->ancestorsAndSelf->reverse()->pluck('id')->toArray();
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
    public function ancestorsToString(ProductVariation $productVariation): string
    {
        try {
            $string = '';
            $arrays = $this->ancestorsAndSelf($productVariation);
            if (count($arrays)) {
                $i = 1;
                foreach ($arrays as $array) {
                    if (isset($array['productAttributeOption']['name'])) {
                        $string .= Str::ucfirst($array['productAttributeOption']['name']) . ($i != count($arrays) ? ' | ' : '');
                    }
                    $i++;
                }
            }
            return $string;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }


    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request, Product $product)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return ProductVariation::with(['product', 'productAttribute', 'productAttributeOption'])->where([
                'product_id' => $product->id
            ])->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->productVariationFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
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
    public function store(ProductVariationRequest $request, Product $product): object
    {
        try {
            DB::transaction(function () use ($request, $product) {
                // Handle new simple variant format (from admin UI with image upload)
                if ($request->has('product_attribute_name')) {
                    // Get or create product attribute
                    $attributeName = $request->product_attribute_name;
                    $optionName = $request->product_attribute_option_name;
                    
                    $attribute = \App\Models\ProductAttribute::firstOrCreate(['name' => $attributeName]);
                    $attributeOption = \App\Models\ProductAttributeOption::firstOrCreate([
                        'product_attribute_id' => $attribute->id,
                        'name' => $optionName,
                    ]);
                    
                    $productVariation = ProductVariation::create([
                        'product_id'                  => $product->id,
                        'product_attribute_id'        => $attribute->id,
                        'product_attribute_option_id' => $attributeOption->id,
                        'price'                       => $request->price ?? 0,
                        'sku'                         => $request->sku ?: \App\Libraries\AppLibrary::sku(rand(1, 99999999999)),
                    ]);
                    
                    // Handle image upload for color variants
                    if ($request->has('variant_image') && $request->file('variant_image')) {
                        $productVariation->addMediaFromRequest('variant_image')
                            ->toMediaCollection('variant');
                    }
                    
                    // The ProductVariationResource will natively stringify the attributes upon API return, 
                    // attempting to save them as columns crashes MySQL since they are relational.
                    
                    $productVariation->save();
                    $this->productVariation = collect([$productVariation]);
                    return;
                }
                
                // Handle old complex variant format
                $order           = 1;
                $parentId        = null;
                $collection      = [];
                $variations      = json_decode($request->attribute);
                $variationsCount = (count($variations) - 1);

                if (is_array($variations)) {
                    foreach ($variations as $key => $variation) {
                        $productVariationExistCheck = ProductVariation::where([
                            'product_id'                  => $product->id,
                            'product_attribute_id'        => $variation->product_attribute_id,
                            'product_attribute_option_id' => $variation->product_attribute_option_id,
                            'parent_id'                   => $parentId
                        ])->orderBy('id', 'desc')->first();

                        $productVariationOrderCheck = ProductVariation::where([
                            'product_id'           => $product->id,
                            'product_attribute_id' => $variation->product_attribute_id,
                        ])->orderBy('id', 'desc')->first();

                        if ($productVariationOrderCheck) {
                            $order = $productVariationOrderCheck->order + 1;
                        }

                        if ($productVariationExistCheck) {
                            $productVariationExistCheck->update(['price' => $variation->price]);
                            if ($key != $variationsCount) {
                                $productVariationExistCheck->update(['sku' => null]);
                            }

                            $parentId     = $productVariationExistCheck->id;
                            $collection[] = $productVariationExistCheck;
                            continue;
                        }

                        $productVariationArray = [
                            'product_id'                  => $product->id,
                            'product_attribute_id'        => $variation->product_attribute_id,
                            'product_attribute_option_id' => $variation->product_attribute_option_id,
                            'price'                       => $variation->price,
                            'sku'                         => ($key == $variationsCount ? $variation->sku : null),
                            'parent_id'                   => $parentId,
                            'order'                       => $order
                        ];
                        $productVariation      = ProductVariation::create($productVariationArray);
                        $parentId              = $productVariation->id;
                        $collection[]          = $productVariation;
                    }

                    $this->productVariation = collect($collection);

                    $productData   = Product::find($product->id);
                    $checkMinPrice = $product->variations->min('price');
                    if ($checkMinPrice) {
                        $productData->variation_price = $checkMinPrice;
                        $productData->save();
                    }
                }
            });
            return $this->productVariation;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }


    private function recursiveDelete($variation): void
    {
        if ($variation->sku == null && count($variation->children) == 0) {
            $productId = $variation->product_id;
            $variation->delete();

            $treeVariations = ProductVariation::where(['product_id' => $productId])->tree()->get()->toTree();
            foreach ($treeVariations as $treeVariation) {
                $this->recursiveDelete($treeVariation);
            }
        }
    }


    /**
     * @throws Exception
     */
    public function update(ProductVariationRequest $request, Product $product, ProductVariation $productVariation): object
    {
        try {
            DB::transaction(function () use ($request, $product, $productVariation) {
                $order           = 1;
                $parentId        = null;
                $collection      = [];
                $variations      = json_decode($request->attribute);
                $variationsCount = (count($variations) - 1);

                if (is_array($variations)) {
                    $oldProductVariationsCount = ProductVariation::find($productVariation->id)->bloodline()->get()->count();

                    foreach ($variations as $key => $variation) {
                        $productVariationExistCheck = ProductVariation::where([
                            'product_id'                  => $product->id,
                            'product_attribute_id'        => $variation->product_attribute_id,
                            'product_attribute_option_id' => $variation->product_attribute_option_id,
                            'parent_id'                   => $parentId
                        ])->orderBy('id', 'desc')->first();

                        $productVariationOrderCheck = ProductVariation::where([
                            'product_id'           => $product->id,
                            'product_attribute_id' => $variation->product_attribute_id,
                        ])->orderBy('id', 'desc')->first();

                        if ($productVariationOrderCheck) {
                            $order = $productVariationOrderCheck->order + 1;
                        }

                        if ($productVariationExistCheck) {
                            $productVariationExistCheck->update(['price' => $variation->price]);

                            if ($key != $variationsCount) {
                                $productVariationExistCheck->update(['sku' => null]);
                            } else {
                                $productVariationExistCheck->update(['sku' => $variation->sku]);
                            }

                            $parentId     = $productVariationExistCheck->id;
                            $collection[] = $productVariationExistCheck;
                            continue;
                        } else {
                            if ($key == $variationsCount && ($variationsCount + 1) == $oldProductVariationsCount) {
                                $productVariation->update([
                                    'product_attribute_id'        => $variation->product_attribute_id,
                                    'product_attribute_option_id' => $variation->product_attribute_option_id,
                                    'price'                       => $variation->price,
                                    'parent_id'                   => $parentId,
                                    'order'                       => $order,
                                    'sku'                         => $variation->sku
                                ]);
                                $collection[] = $productVariation;
                                continue;
                            }
                        }

                        $createProductVariation = ProductVariation::create([
                            'product_id'                  => $product->id,
                            'product_attribute_id'        => $variation->product_attribute_id,
                            'product_attribute_option_id' => $variation->product_attribute_option_id,
                            'price'                       => $variation->price,
                            'sku'                         => ($key == $variationsCount ? AppLibrary::sku(rand(1, 99999999999)) : null),
                            'parent_id'                   => $parentId,
                            'order'                       => $order
                        ]);
                        $parentId               = $createProductVariation->id;
                        $collection[]           = $createProductVariation;
                    }

                    $treeVariations = ProductVariation::where(['product_id' => $product->id])->tree()->get()->toTree();
                    foreach ($treeVariations as $treeVariation) {
                        $this->recursiveDelete($treeVariation);
                    }

                    $this->productVariation = collect($collection);
                    $productData            = Product::find($product->id);
                    $checkMinPrice          = $product->variations->min('price');
                    if ($checkMinPrice) {
                        $productData->variation_price = $checkMinPrice;
                        $productData->save();
                    }
                }
            });
            return $this->productVariation;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Product $product, ProductVariation $productVariation): void
    {
        try {
            DB::transaction(function () use ($product, $productVariation) {
                if ($productVariation->product_id == $product->id && $productVariation->sku != null) {

                    $productVariation->delete();
                    $this->parentDelete($productVariation->parent_id);

                    $productData   = Product::find($product->id);
                    $checkMinPrice = $product->variations->min('price');
                    if ($checkMinPrice) {
                        $productData->variation_price = $checkMinPrice;
                        $productData->save();
                    }
                }
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function parentDelete($id): void
    {
        $parentVariation = ProductVariation::where(['id' => $id])->orderBy('id', 'desc')->first();
        if ($parentVariation && blank($parentVariation->children()->get())) {
            $parentVariation->delete();
            $this->parentDelete($parentVariation->parent_id);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Product $product, ProductVariation $productVariation)
    {
        try {
            return ProductVariation::where(['product_id' => $product->id, 'id' => $productVariation->id])->first();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}