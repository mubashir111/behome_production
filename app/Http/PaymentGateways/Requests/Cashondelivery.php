<?php

namespace App\Http\PaymentGateways\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Cashondelivery extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
