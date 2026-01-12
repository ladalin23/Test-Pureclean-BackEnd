<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Admin;

class UpdateAdminRequest extends FormRequest
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
        // With apiResource, the param name is 'admin'
        $routeParam = $this->route('admin'); // could be a model (if implicit binding) or a string id/global_id

        // Resolve the current Admin row regardless of how the route is set up
        if ($routeParam instanceof Admin) {
            $current = $routeParam;
        } else {
            // route param may be numeric id or global_id string — try both
            $current = Admin::where('global_id', $routeParam)
                ->orWhere('id', $routeParam)
                ->first();
        }

        // If we found the row, ignore by PK id (safest)
        $emailRule = $current
            ? Rule::unique('admins', 'email')->ignore($current->id)
            : Rule::unique('admins', 'email'); // fallback: no ignore if not found

        return [
            'username'  => 'sometimes|string|max:255',
            'email'     => ['sometimes', 'email', $emailRule],
            'branch_id' => 'sometimes|exists:branches,global_id', // incoming is branch global_id
            'role'      => 'sometimes|in:admin,super-admin,cashier',
            'active'    => 'sometimes|in:0,1,2',
            'password'  => 'sometimes|nullable|string|min:6',
        ];
    }
}
