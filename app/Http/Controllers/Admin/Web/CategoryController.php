<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Services\ProductCategoryService;
use App\Http\Requests\PaginateRequest;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(ProductCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(PaginateRequest $request)
    {
        $request->merge(['paginate' => 1]);
        $categories = $this->categoryService->list($request);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = $this->categoryService->list(new PaginateRequest(['paginate' => 0]));
        return view('admin.categories.create', compact('categories'));
    }

    public function store(\App\Http\Requests\ProductCategoryRequest $request)
    {
        try {
            $this->categoryService->store($request);
            return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(\App\Models\ProductCategory $category)
    {
        $categories = $this->categoryService->list(new PaginateRequest(['paginate' => 0]));
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(\App\Http\Requests\ProductCategoryRequest $request, \App\Models\ProductCategory $category)
    {
        try {
            $this->categoryService->update($request, $category);
            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(\App\Models\ProductCategory $category)
    {
        try {
            $this->categoryService->destroy($category);
            return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
