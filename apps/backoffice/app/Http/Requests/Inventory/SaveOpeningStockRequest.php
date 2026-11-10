<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOpeningStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->outletAssignments()
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('stock.manage'))
            ->exists();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'outlet_id' => [
                'required',
                'integer',
                Rule::exists('outlets', 'id')->where(
                    fn (Builder $query) => $query->where('business_id', $businessId),
                ),
            ],
            'product_variant_id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id')->where(
                    fn (Builder $query) => $query
                        ->where('business_id', $businessId)
                        ->where('track_stock', true),
                ),
            ],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999999999.999999'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999999.999999'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'outlet_id' => 'outlet',
            'product_variant_id' => 'produk',
            'quantity' => 'jumlah stok awal',
            'unit_cost' => 'harga modal per satuan',
        ];
    }
}
