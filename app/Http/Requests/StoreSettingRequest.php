<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:settings,slug',
            'value'  => 'required|string',
            'active' => 'required|boolean',
        ];
    }
}
