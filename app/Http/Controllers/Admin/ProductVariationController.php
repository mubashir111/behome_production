<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductVariation;
use App\Http\Requests\PaginateRequest;
use App\Services\ProductVariationService;
use App\Http\Requests\ProductVariationRequest;
use App\Http\Resources\ProductVariationResource;
use App\Http\Resources\SimpleProductVariationResource;

class ProductVariationController extends AdminController
{
    private ProductVariationService $productVariationService;
    public function __construct(ProductVariationService $productVariationService)
    {
        parent::__construct();
        $this->productVariationService = $productVariationService;
        // Permssion check removed to align with Web ProductController logic which uses is_admin
    }

    public function tree(Request $request, Product $product): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(['data' =>  $this->productVariationService->tree($request, $product)]);
    }

    public function singleTree(Product $product): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(['data' =>  $this->productVariationService->singleTree($product)]);
    }

    public function treeWithSelected(Request $request, Product $product): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(['data' =>  $this->productVariationService->treeWithSelected($request, $product)]);
    }

    public function index(PaginateRequest $request, Product $product): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ProductVariationResource::collection($this->productVariationService->list($request, $product));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ProductVariationRequest $request, Product $product): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ProductVariationResource::collection($this->productVariationService->store($request, $product));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ProductVariationRequest $request, Product $product, ProductVariation $productVariation): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ProductVariationResource::collection($this->productVariationService->update($request, $product, $productVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function updateSimple(\Illuminate\Http\Request $request, Product $product, ProductVariation $productVariation): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'price'         => ['nullable', 'numeric', 'min:0'],
                'sku'           => ['nullable', 'string'],
                'variant_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            ]);

            if ($request->filled('price')) {
                $productVariation->price = $request->price;
            }
            if ($request->filled('sku')) {
                $productVariation->sku = $request->sku;
            }
            $productVariation->save();

            if ($request->hasFile('variant_image')) {
                $productVariation->clearMediaCollection('variant');
                $productVariation->addMediaFromRequest('variant_image')->toMediaCollection('variant');
            }

            return response(new ProductVariationResource($productVariation->fresh(['media', 'productAttribute', 'productAttributeOption'])), 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Product $product, ProductVariation $productVariation): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->productVariationService->destroy($product, $productVariation);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Product $product, ProductVariation $productVariation): \Illuminate\Http\Response | ProductVariationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ProductVariationResource($this->productVariationService->show($product, $productVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function initialVariation(Product $product): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SimpleProductVariationResource::collection($this->productVariationService->initialVariation($product));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function childrenVariation(ProductVariation $productVariation): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SimpleProductVariationResource::collection($this->productVariationService->childrenVariation($productVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function ancestorsToString(ProductVariation $productVariation): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return response(['data' => $this->productVariationService->ancestorsToString($productVariation)], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function ancestorsAndSelfId(ProductVariation $productVariation): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return response(['data' => $this->productVariationService->ancestorsAndSelfId($productVariation)], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
