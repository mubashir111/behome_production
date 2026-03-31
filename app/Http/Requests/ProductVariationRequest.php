<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Libraries\AppLibrary;
use App\Models\ProductVariation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariationRequest extends FormRequest
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
        // Check if this is the new simple variant format or the old complex format
        if ($this->has('product_attribute_name')) {
            // New simple variant format from admin UI
            return [
                'product_attribute_name'       => ['required', 'string'],
                'product_attribute_option_name' => ['required', 'string'],
                'price'                         => ['nullable', 'numeric', 'min:0'],
                'stock'                         => ['nullable', 'integer', 'min:0'],
                'sku'                           => ['nullable', 'string'],
                'variant_image'                 => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048']
            ];
        } else {
            // Old complex variant format
            return [
                'product_variation_id' => ['nullable', 'numeric'],
                'attribute'            => ['required', 'json']
            ];
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Skip this validation block for simple variant format
            if ($this->has('product_attribute_name')) {
                return;
            }

            $status     = false;
            $message    = "";
            $variations = json_decode($this->attribute);

            if (is_array($variations) && count($variations)) {
                foreach ($variations as $variation) {
                    if ($status) {
                        break;
                    }

                    $price           = AppLibrary::amountCheck($variation->price);
                    $checkProductSku = Product::where(['sku' => $variation->sku])->first();
                    if ($this->route('productVariation.id')) {
                        $checkVariationSku = ProductVariation::where('sku', $variation->sku)->where('id', '!=', $this->route('productVariation.id'))->first();
                    } else {
                        $checkVariationSku = ProductVariation::where('sku', $variation->sku)->first();
                    }

                    if (!$price->status) {
                        $status  = true;
                        $message = trans('all.message.price_invalid');
                    } elseif (!is_int((int)$variation->product_attribute_id)) {
                        $status  = true;
                        $message = trans('all.message.product_attribute_invalid');
                    } elseif (!is_int((int)$variation->product_attribute_option_id)) {
                        $status  = true;
                        $message = trans('all.message.product_attribute_option_invalid');
                    } elseif (blank($variation->sku)) {
                        $status  = true;
                        $message = trans('all.message.variation_sku_required');
                    } elseif ($checkVariationSku || $checkProductSku) {
                        $status  = true;
                        $message = trans('all.message.sku_exist');
                    }
                }
            } else {
                $status  = true;
                $message = trans('all.message.attribute_invalid');
            }

            if ($status) {
                $validator->errors()->add('global', $message);
            }
        });
    }
}
