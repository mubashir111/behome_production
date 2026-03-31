<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandWebController extends Controller
{
    public function index()
    {
        $brands = ProductBrand::latest()->paginate(20);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['required', 'numeric'],
            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $brand = ProductBrand::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name) . rand(100, 999),
            'description' => $request->description,
            'status'      => $request->status,
        ]);

        if ($request->hasFile('logo')) {
            $brand->addMedia($request->file('logo'))->toMediaCollection('product-brand');
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit(ProductBrand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, ProductBrand $brand)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['required', 'numeric'],
            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $brand->update([
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => $request->status,
        ]);

        if ($request->hasFile('logo')) {
            $brand->clearMediaCollection('product-brand');
            $brand->addMedia($request->file('logo'))->toMediaCollection('product-brand');
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(ProductBrand $brand)
    {
        $brand->clearMediaCollection('product-brand');
        $brand->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
