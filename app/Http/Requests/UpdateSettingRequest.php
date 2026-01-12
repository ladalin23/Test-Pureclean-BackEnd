<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Setting;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge([
                'slug' => Str::lower(trim((string) $this->input('slug'))),
            ]);
        }
    }

    public function rules(): array
    {
        $routeParam = $this->route('setting'); // param name from apiResource

        if ($routeParam instanceof Setting) {
            $current = $routeParam;
        } else {
            $current = Setting::where('global_id', $routeParam)
                ->orWhere('id', $routeParam)
                ->first();
        }

        $slugRule = $current
            ? Rule::unique('settings', 'slug')->ignore($current->id)
            : Rule::unique('settings', 'slug');

        return [
            'name'   => 'sometimes|string|max:255',
            'slug'   => ['sometimes', 'string', 'max:255', $slugRule],
            'value'  => 'sometimes|string',
            'active' => 'sometimes|boolean',
        ];
    }
}
