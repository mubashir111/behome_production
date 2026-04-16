<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductBrandRequest extends FormRequest
{
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
        $brandId = $this->route('brand') instanceof \App\Models\ProductBrand ? $this->route('brand')->id : $this->route('brand');

        return [
            'name'        => [
                'required',
                'string',
                'max:190',
                Rule::unique("product_brands", "name")->ignore($brandId)
            ],
            'description' => ['nullable', 'string', 'max:900'],
            'status'      => ['required', 'numeric', 'max:24'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048']
        ];
    }
}
