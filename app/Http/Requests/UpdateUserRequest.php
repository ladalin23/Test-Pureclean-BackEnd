<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\User;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }
    }

    public function rules(): array
    {
        // With apiResource, the param name is 'user'
        $routeParam = $this->route('user'); // could be model binding or string id/global_id

        if ($routeParam instanceof User) {
            $current = $routeParam;
        } else {
            $current = User::where('global_id', $routeParam)
                ->orWhere('id', $routeParam)
                ->first();
        }

        $emailRule = $current
            ? Rule::unique('users', 'email')->ignore($current->id)
            : Rule::unique('users', 'email');

        return [
            'username'  => 'sometimes|string|max:255',
            'gender'    => 'sometimes|nullable|in:male,female,other',
            'dob'       => 'sometimes|nullable|date',
            'email'     => ['sometimes', 'email', $emailRule],
            'phone'     => 'sometimes|nullable|string|max:20',
            'password'  => 'sometimes|nullable|string|min:6',
            'active'    => 'sometimes|in:0,1',
        ];
    }
}
