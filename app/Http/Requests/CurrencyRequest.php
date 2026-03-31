<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
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
        $currency = $this->route('currency');

        return [
            'name'              => [
                'required',
                'string',
                'max:190',
                Rule::unique('currencies', 'name')->ignore($currency?->id)
            ],
            'symbol'            => ['required', 'string', 'max:190'],
            'code'              => [
                'required',
                'string',
                'max:20',
                Rule::unique('currencies', 'code')->ignore($currency?->id)
            ],
            'is_cryptocurrency' => ['required', 'integer', 'in:0,1'],
            'exchange_rate'     => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
        ];
    }
}
