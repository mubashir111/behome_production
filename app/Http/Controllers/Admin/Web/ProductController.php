<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeImageRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Requests\PaginateRequest;

class ProductController extends Controller
{
    protected $productService;
    protected $categoryService;
    protected $brandService;
    protected $unitService;
    protected $taxService;
    protected $barcodeService;

    public function __construct(
        ProductService $productService,
        \App\Services\ProductCategoryService $categoryService,
        \App\Services\ProductBrandService $brandService,
        \App\Services\UnitService $unitService,
        \App\Services\TaxService $taxService,
        \App\Services\BarcodeService $barcodeService
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->brandService = $brandService;
        $this->unitService = $unitService;
        $this->taxService = $taxService;
        $this->barcodeService = $barcodeService;
    }

    public function index(PaginateRequest $request)
    {
        $request->merge(['paginate' => 1]);
        $products = $this->productService->list($request);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = $this->categoryService->list(new PaginateRequest(['paginate' => 0]));
        $brands = $this->brandService->list(new PaginateRequest(['paginate' => 0]));
        $units = $this->unitService->list(new PaginateRequest(['paginate' => 0]));
        $taxes = $this->taxService->list(new PaginateRequest(['paginate' => 0]));
        $barcodes = $this->barcodeService->list(new PaginateRequest(['paginate' => 0]));
        
        return view('admin.products.create', compact('categories', 'brands', 'units', 'taxes', 'barcodes'));
    }

    public function store(\App\Http\Requests\ProductRequest $request)
    {
        try {
            $this->productService->store($request);
            return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product = $this->productService->show($product);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product = $this->productService->show($product);
        $categories = $this->categoryService->list(new PaginateRequest(['paginate' => 0]));
        $brands = $this->brandService->list(new PaginateRequest(['paginate' => 0]));
        $units = $this->unitService->list(new PaginateRequest(['paginate' => 0]));
        $taxes = $this->taxService->list(new PaginateRequest(['paginate' => 0]));
        $barcodes = $this->barcodeService->list(new PaginateRequest(['paginate' => 0]));
        
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'units', 'taxes', 'barcodes'));
    }

    public function update(\App\Http\Requests\ProductRequest $request, Product $product)
    {
        try {
            if ($request->boolean('image_only')) {
                $this->productService->addImages($product, $request->file('images', []));
                return redirect()->route('admin.products.edit', $product)->with('success', 'Product images added successfully.');
            }

            $this->productService->update($request, $product);
            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            \Illuminate\Support\Facades\Log::warning("Product ID {$product->id} ({$product->name}) was DELETED by User ID " . auth()->id());
            $this->productService->destroy($product);
            return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function replaceImage(ChangeImageRequest $request, Product $product, int $index)
    {
        try {
            $this->productService->replaceImage($product, $index, $request->file('image'));
            return redirect()->route('admin.products.edit', $product)->with('success', 'Product image updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteImage(Product $product, int $index)
    {
        try {
            $this->productService->deleteImage($product, $index);
            return redirect()->route('admin.products.edit', $product)->with('success', 'Product image deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function uploadBlockImage(Request $request, Product $product)
    {
        try {
            if (!$request->hasFile('image')) {
                return response(['status' => false, 'message' => 'No image uploaded'], 400);
            }

            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            $directory = public_path('images/products/blocks');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            $image->move($directory, $filename);
            $path = '/images/products/blocks/' . $filename;

            return response(['status' => true, 'data' => ['url' => $path]], 200);
        } catch (\Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function uploadGalleryImageAjax(Request $request, Product $product)
    {
        try {
            if (!$request->hasFile('image')) {
                return response(['status' => false, 'message' => 'No image uploaded'], 400);
            }

            $this->productService->addImages($product, [$request->file('image')]);
            return response(['status' => true, 'message' => 'Image added successfully'], 200);
        } catch (\Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function replaceGalleryImageAjax(Request $request, Product $product, int $index)
    {
        try {
            if (!$request->hasFile('image')) {
                return response(['status' => false, 'message' => 'No image uploaded'], 400);
            }

            $this->productService->replaceImage($product, $index, $request->file('image'));
            return response(['status' => true, 'message' => 'Image replaced successfully'], 200);
        } catch (\Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
