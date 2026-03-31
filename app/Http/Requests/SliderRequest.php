<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SliderRequest extends FormRequest
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
            'title'        => [
                'required',
                'string',
                'max:190',
                Rule::unique("sliders", "title")->ignore($this->route('slider.id'))
            ],
            'description'  => ['nullable'],
            'button_text'  => ['nullable', 'string', 'max:100'],
            'badge_text'   => ['nullable', 'string', 'max:100'],
            'link'         => ['nullable', 'string', 'max:500'],
            'status'       => ['required', 'numeric'],
            'image'        => $this->route('slider.id') ? ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'] : ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
