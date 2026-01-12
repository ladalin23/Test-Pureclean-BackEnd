<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchasedRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'service_id'     => 'required|exists:services,global_id',
            'status'         => 'required|string',
            'det'            => 'required|numeric',
            'sft'            => 'required|numeric',
            'acn'            => 'required|numeric',
            'payment_method' => 'required|string',
            'contact'        => 'nullable|string'
        ];
    }
}
