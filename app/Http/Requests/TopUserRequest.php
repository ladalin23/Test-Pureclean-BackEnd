<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUserRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'start_date' => 'required',
            'end_date' => 'required',
            'top' => 'required|integer',
        ];
    }
}
