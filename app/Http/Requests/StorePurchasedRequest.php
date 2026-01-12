<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchasedRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id'        => 'required|exists:users,global_id',
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
