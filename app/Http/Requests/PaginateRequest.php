<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginateRequest extends FormRequest
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
            'per_page'     => ['numeric', 'min:1', 'max:1000'],
            // Restrict to known safe column names to prevent SQL column injection via orderBy()
            'order_column' => ['nullable', 'string', 'in:id,name,created_at,updated_at,total,status,order_serial_no,order_datetime,payment_status,order_type,email,phone,slug,title,price,selling_price,buying_price'],
            'order_by'     => ['nullable', 'in:asc,desc'],
        ];
    }
}
