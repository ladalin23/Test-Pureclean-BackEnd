<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'     => 'required|email|max:255|unique:admins',
            'username'  => 'required|string|max:255',
            'password'  => 'required|string|min:8',
            'branch_id' => 'required|exists:branches,global_id',
            'role'      => 'required|in:admin,super-admin,cashier',
            'active'    => 'nullable|boolean'
        ];
    }
}
