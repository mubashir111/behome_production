<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $categories = ProductCategory::with('children')->withCount('products')->whereNull('parent_id')->active()->get();
            return $this->successResponse($categories, 'Categories retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show($slug)
    {
        try {
            $category = ProductCategory::active()->where('slug', $slug)->with(['children', 'products'])->firstOrFail();
            return $this->successResponse($category, 'Category details retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Category not found', 404);
        }
    }
}
