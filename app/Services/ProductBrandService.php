<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Str;
use App\Models\ProductBrand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\ProductBrandRequest;

class ProductBrandService
{
    protected $productCateFilter = [
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $exceptFilter = [
        'excepts'
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

            return ProductBrand::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->productCateFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
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
    public function store(ProductBrandRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $slug = Str::slug($request->name);
                $base = $slug;
                $i    = 1;
                while (ProductBrand::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }

                $productBrand = ProductBrand::create($request->validated() + ['slug' => $slug]);
                
                if ($request->hasFile('image')) {
                    $productBrand->addMediaFromRequest('image')->toMediaCollection('product-brand');
                }

                \App\Models\AdminNotification::record('info', 'Brand Created', "Brand '{$productBrand->name}' was created by " . (auth()->user()->name ?? 'Admin'));

                return $productBrand;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function update(ProductBrandRequest $request, ProductBrand $productBrand): ProductBrand
    {
        return DB::transaction(function () use ($request, $productBrand) {
            try {
                $slug = $productBrand->slug;
                if ($productBrand->name !== $request->name) {
                    $slug = Str::slug($request->name);
                    $base = $slug;
                    $i    = 1;
                    while (ProductBrand::where('slug', $slug)->where('id', '!=', $productBrand->id)->exists()) {
                        $slug = "{$base}-{$i}";
                        $i++;
                    }
                }

                $productBrand->update($request->validated() + ['slug' => $slug]);
                
                if ($request->hasFile('image')) {
                    $productBrand->clearMediaCollection('product-brand');
                    $productBrand->addMediaFromRequest('image')->toMediaCollection('product-brand');
                }

                \App\Models\AdminNotification::record('info', 'Brand Updated', "Brand '{$productBrand->name}' (ID #{$productBrand->id}) was updated by " . (auth()->user()->name ?? 'Admin'));

                return $productBrand;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function destroy(ProductBrand $productBrand)
    {
        try {
            // Check ALL products (active + inactive) — the products() relationship scopes to active only,
            // so using it would miss inactive products and cause a FK constraint violation on delete.
            $productCount = \App\Models\Product::where('product_brand_id', $productBrand->id)->count();
            if ($productCount > 0) {
                throw new Exception("Cannot delete brand: It is linked to {$productCount} product(s). Please reassign or remove them first.", 422);
            }

            $name = $productBrand->name;
            $id   = $productBrand->id;

            DB::transaction(function () use ($productBrand) {
                $productBrand->clearMediaCollection('product-brand');
                $productBrand->delete();
            });

            \App\Models\AdminNotification::record('warning', 'Brand Deleted', "Brand '{$name}' (ID #{$id}) was deleted by " . (auth()->user()->name ?? 'Admin'));

        } catch (Exception $exception) {
            Log::info("Brand deletion error: " . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(ProductBrand $productBrand)
    {
        try {
            return $productBrand;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
