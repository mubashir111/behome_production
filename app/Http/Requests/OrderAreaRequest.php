<?php

namespace App\Http\Requests;

use App\Models\OrderArea;
use Illuminate\Foundation\Http\FormRequest;

class OrderAreaRequest extends FormRequest
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
        return [
            'country'       => ['required', 'string', 'max:255'],
            'state'         => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:255'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'status'        => ['required', 'numeric'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $country = OrderArea::where('country', $this->country)->where('state', $this->state)->where('city', $this->city)->whereNot('id', $this->route('orderArea.id'))->first();
            if ($country) {
                $validator->getMessageBag()->add('country', trans('all.message.country_exist'));
            }
        });
    }
}
