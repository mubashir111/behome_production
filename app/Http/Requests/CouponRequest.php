<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
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
        $couponId = $this->route('coupon') instanceof \App\Models\Coupon ? $this->route('coupon')->id : $this->route('coupon');

        return [
            'name'        => [
                'required',
                'string',
                'max:190',
                Rule::unique("coupons", "name")->ignore($couponId)
            ],
            'description'      => ['nullable', 'string', 'max:900'],
            'code'             => ['required', 'string', 'max:24', Rule::unique("coupons", "code")->ignore($couponId)],
            'discount'         => ['required', 'numeric'],
            'discount_type'    => ['required', 'numeric', 'max:24'],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['required', 'date', 'after_or_equal:start_date'],
            'minimum_order'    => ['required', 'numeric'],
            'maximum_discount' => ['required', 'numeric'],
            'limit_per_user'   => ['nullable', 'numeric'],
            'image'            => $couponId ? ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'] : ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->isPercentage() && request('discount') > 100) {
                $validator->errors()->add('discount', 'Percentage amount can\'t be greater than 100.');
            }

            if ($this->checkToDate()) {
                $validator->errors()->add('end_date', 'To date can\'t be older than now.');
            }
        });
    }

    private function isPercentage(): bool
    {
        return (int) request('discount_type') === DiscountType::PERCENTAGE;
    }

    public function checkToDate()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        if (strtotime(request('end_date')) < $today) {
            return true;
        }
    }

    private function isNotNull($value)
    {
        if ($value === 'null') {
            return false;
        }
        return true;
    }
}
