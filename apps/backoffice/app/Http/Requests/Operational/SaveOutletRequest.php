<?php

namespace App\Http\Requests\Operational;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->outletAssignments()
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('operations.manage'))
            ->exists();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $businessId = $this->user()?->business_id;
        $outletId = $this->route('outlet');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9][A-Z0-9-]*$/',
                Rule::unique('outlets')->where('business_id', $businessId)->ignore($outletId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'profit_center_code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9][A-Z0-9-]*$/',
                Rule::unique('outlets')->where('business_id', $businessId)->ignore($outletId),
            ],
            'address' => ['nullable', 'string', 'max:1000'],
            'business_day_start' => ['required', 'date_format:H:i'],
            'prevent_negative_stock' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => mb_strtoupper(trim((string) $this->input('code'))),
            'profit_center_code' => mb_strtoupper(trim((string) $this->input('profit_center_code'))),
            'name' => trim((string) $this->input('name')),
            'address' => filled($this->input('address'))
                ? trim((string) $this->input('address'))
                : null,
        ]);
    }
}
