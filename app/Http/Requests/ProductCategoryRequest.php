<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductCategoryRequest extends FormRequest
{
    protected function currentCategoryId(): ?int
    {
        $routeCategory = $this->route('productCategory') ?? $this->route('category');

        if ($routeCategory instanceof ProductCategory) {
            return $routeCategory->id;
        }

        if (is_numeric($routeCategory)) {
            return (int) $routeCategory;
        }

        $routeCategoryId = $this->route('productCategory.id') ?? $this->route('category.id');

        return is_numeric($routeCategoryId) ? (int) $routeCategoryId : null;
    }

    protected function normalizedParentId(): ?int
    {
        $parentId = $this->input('parent_id');

        if ($parentId === null || $parentId === '' || $parentId === 'NULL') {
            return null;
        }

        return is_numeric($parentId) ? (int) $parentId : null;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $categoryId = $this->currentCategoryId();

        return [
            'name'        => [
                'required',
                'string',
                'max:190',
                Rule::unique("product_categories", "name")->where('parent_id', $this->normalizedParentId())->ignore($categoryId)
            ],
            'parent_id'   => ['nullable', 'numeric'],
            'description' => ['nullable', 'string', 'max:900'],
            'status'      => ['required', 'numeric', 'max:24'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $categoryId = $this->currentCategoryId();
                $parentId   = $this->normalizedParentId();

                if (!$categoryId || !$parentId) {
                    return;
                }

                if ($parentId === $categoryId) {
                    $validator->errors()->add(
                        'parent_id',
                        'The parent field cannot be the same as the current category.'
                    );

                    return;
                }

                $parentCategory = ProductCategory::find($parentId);
                if (!$parentCategory) {
                    return;
                }

                if ($parentCategory->ancestors()->where('id', $categoryId)->exists()) {
                    $validator->errors()->add(
                        'parent_id',
                        'You cannot assign a child category as the parent.'
                    );
                }
            }
        ];
    }
}
