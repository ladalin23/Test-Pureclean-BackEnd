<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'file'   => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'file'   => 'required|file|mimetypes:image/*|max:10240',
            'folder' => 'nullable|string',
        ];
    }
}
