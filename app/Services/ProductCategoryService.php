<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\ProductCategoryRequest;

class ProductCategoryService
{
    protected array $productCateFilter = [
        'name',
        'slug',
        'description',
        'status',
        'parent_id'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];


    /**
     * @throws Exception
     */
    public function ancestorsAndSelf(ProductCategory $productCategory)
    {
        try {
            return $productCategory->ancestorsAndSelf->reverse();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function depthTree()
    {
        try {
            return ProductCategory::tree()->depthFirst()->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function tree()
    {
        try {
            return ProductCategory::tree()->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

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

            return ProductCategory::tree()->depthFirst()
                ->with(['parent_category', 'media'])
                ->withCount('products') // Optimized: replaced with products relationship
                ->where(function ($query) use ($requests) {
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

    public function store(ProductCategoryRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $productCategory = ProductCategory::create(Arr::except($request->validated(), 'parent_id') + [
                    'slug' => \Illuminate\Support\Str::slug($request->name), 
                    'parent_id' => $request->parent_id == 'NULL' ? NULL : $request->parent_id
                ]);
                if ($request->hasFile('image')) {
                    $productCategory->addMediaFromRequest('image')->toMediaCollection('product-category');
                }
                
                \App\Models\AdminNotification::record('info', 'Category Created', "Category '{$productCategory->name}' was created by " . (auth()->user()->name ?? 'Admin'));
                
                return $productCategory;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory): ProductCategory
    {
        return DB::transaction(function () use ($request, $productCategory) {
            try {
                $productCategory->update(Arr::except($request->validated(), 'parent_id') + [
                    'slug' => \Illuminate\Support\Str::slug($request->name), 
                    'parent_id' => $request->parent_id == 'NULL' ? NULL : $request->parent_id
                ]);
                if ($request->hasFile('image')) {
                    $productCategory->clearMediaCollection('product-category');
                    $productCategory->addMediaFromRequest('image')->toMediaCollection('product-category');
                }

                \App\Models\AdminNotification::record('info', 'Category Updated', "Category '{$productCategory->name}' (ID #{$productCategory->id}) was updated by " . (auth()->user()->name ?? 'Admin'));

                return $productCategory;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        });
    }

    public function destroy(ProductCategory $productCategory): void
    {
        try {
            // Check for children
            if ($productCategory->children()->exists()) {
                throw new Exception('Cannot delete category: It has subcategories. Please delete or reassign them first.', 422);
            }

            // Check for active products
            if ($productCategory->products()->exists()) {
                 throw new Exception('Cannot delete category: It has active products. Please move them to a different category first.', 422);
            }

            $catName = $productCategory->name;
            $catId   = $productCategory->id;

            DB::transaction(function() use ($productCategory) {
                $productCategory->clearMediaCollection('product-category');
                $productCategory->delete();
            });
            
            \App\Models\AdminNotification::record('warning', 'Category Deleted', "Category '{$catName}' (ID #{$catId}) was deleted by " . (auth()->user()->name ?? 'Admin'));

        } catch (Exception $exception) {
            Log::info("Category deletion error: " . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(ProductCategory $productCategory): ProductCategory
    {
        try {
            return $productCategory;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
