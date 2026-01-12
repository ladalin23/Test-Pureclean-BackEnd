<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|between:1,5',
            'message' => 'required|string|max:1000',
        ];
    }
}
