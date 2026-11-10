<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class SaveCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->outletAssignments()
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('catalog.manage'))
            ->exists();
    }

    /** @return array<string, array<int, Unique|string>> */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->where('business_id', $this->user()?->business_id)
                    ->ignore($this->route('category')),
            ],
            'display_order' => ['required', 'integer', 'between:0,65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }
}
