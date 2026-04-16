<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandWebController extends Controller
{
    private \App\Services\ProductBrandService $productBrandService;

    public function __construct(\App\Services\ProductBrandService $productBrandService)
    {
        $this->productBrandService = $productBrandService;
    }

    public function index(\App\Http\Requests\PaginateRequest $request)
    {
        $request->merge(['paginate' => 1]);
        $brands = $this->productBrandService->list($request);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(\App\Http\Requests\ProductBrandRequest $request)
    {
        try {
            $this->productBrandService->store($request);
            return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
        } catch (\Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function edit(ProductBrand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(\App\Http\Requests\ProductBrandRequest $request, ProductBrand $brand)
    {
        try {
            $this->productBrandService->update($request, $brand);
            return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
        } catch (\Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function destroy(ProductBrand $brand)
    {
        try {
            $this->productBrandService->destroy($brand);
            if (request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
        } catch (\Exception $exception) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
            }
            return back()->with('error', $exception->getMessage());
        }
    }
}
