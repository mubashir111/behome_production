<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
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
        $unitId = $this->route('unit') instanceof \App\Models\Unit ? $this->route('unit')->id : $this->route('unit');

        return [
            'name'        => [
                'required',
                'string',
                'max:190',
                Rule::unique("units", "name")->ignore($unitId)
            ],
            'code'              => [
                'required',
                'string',
                'max:20',
                Rule::unique("units", "code")->ignore($unitId)
            ],
            'status' => ['required', 'numeric'],
        ];
    }
}
