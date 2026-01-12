<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "name"       => "required|string|max:255",
            "price_cold" => "required|numeric|min:0",
            "price_warm" => "required|numeric|min:0",
            "price_hot"  => "required|numeric|min:0",
            "price_dry"  => "required|numeric|min:0",
            "active"     => "required|boolean"
        ];
    }
}
