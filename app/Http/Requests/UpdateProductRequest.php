<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'image_url' => 'required|url',
            'active'    => 'required|boolean',
        ];
    }
}
