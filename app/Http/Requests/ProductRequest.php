<?php

namespace App\Http\Requests;

use App\Models\ProductVariation;
use App\Rules\IniAmount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    protected function currentProductId(): ?int
    {
        $routeProduct = $this->route('product');

        if ($routeProduct instanceof \App\Models\Product) {
            return $routeProduct->id;
        }

        if (is_numeric($routeProduct)) {
            return (int) $routeProduct;
        }

        $routeProductId = $this->route('product.id');

        return is_numeric($routeProductId) ? (int) $routeProductId : null;
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
        if ($this->boolean('image_only')) {
            return [
                'images'   => ['required', 'array', 'min:1'],
                'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            ];
        }

        $productId = $this->currentProductId();

        return [
            'name'                       => [
                'required',
                'string',
                'max:190',
                Rule::unique("products", "name")->whereNull('deleted_at')->ignore($productId)
            ],
            'sku'                        => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique("products", "sku")->whereNull('deleted_at')->ignore($productId)
            ],
            'product_category_id'        => ['required', 'numeric', 'not_in:0'],
            'barcode_id'                 => ['sometimes', 'nullable', 'numeric', 'not_in:0'],
            'buying_price'               => ['required', new IniAmount()],
            'selling_price'              => ['required', new IniAmount()],
            'tax_id[]'                   => ['nullable', 'numeric', 'max_digits:10'],
            'product_brand_id'           => ['nullable', 'numeric', 'max_digits:10'],
            'status'                     => ['required', 'numeric', 'max:24'],
            'can_purchasable'            => ['sometimes', 'nullable', 'numeric', 'max:24'],
            'show_stock_out'             => ['sometimes', 'nullable', 'numeric', 'max:24'],
            'refundable'                 => ['sometimes', 'nullable', 'numeric', 'max:24'],
            'maximum_purchase_quantity'  => ['sometimes', 'nullable', 'numeric', 'max_digits:10'],
            'low_stock_quantity_warning' => ['sometimes', 'nullable', 'numeric', 'max_digits:10'],
            'unit_id'                    => ['required', 'numeric', 'not_in:0'],
            'weight'                     => ['nullable', 'string', 'max:100'],
            'is_hero_slider'             => ['sometimes', 'nullable', 'numeric'],
            'description'                => ['nullable', 'string', 'max:5000'],
            'details'                    => ['nullable', 'string'],
            'additional_info'            => ['nullable', 'string'],
            'shipping_and_return'        => ['nullable', 'string', 'max:5000'],
            'tags'                       => ['nullable', 'json'],
            'discount'                   => ['nullable', new IniAmount()],
            'offer_start_date'           => ['nullable', 'date'],
            'offer_end_date'             => ['nullable', 'date', 'after_or_equal:offer_start_date'],
            'images'                     => ['nullable', 'array'],
            'images.*'                   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_category_id' => strtolower(trans('all.label.product_category_id')),
            'product_brand_id'    => strtolower(trans('all.label.product_brand_id')),
            'barcode_id'          => strtolower(trans('all.label.barcode_id')),
            'unit_id'             => strtolower(trans('all.label.unit_id')),
            'tax_id'              => strtolower(trans('all.label.tax_id')),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('image_only') || blank($this->sku)) {
                return;
            }

            $sku = ProductVariation::where('sku', $this->sku)->first();
            if ($sku) {
                $validator->getMessageBag()->add('sku', trans('all.message.sku_exist'));
            }
        });
    }
}
