<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuideRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'order' => 'nullable|integer',
            'thumbnail' => 'nullable|in:washing,drying',
            'type' => 'nullable|in:washing,drying',
            'active' => 'required|boolean',
        ];
    }
}
