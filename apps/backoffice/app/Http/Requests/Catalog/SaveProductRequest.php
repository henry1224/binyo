<?php

namespace App\Http\Requests\Catalog;

use App\Models\Outlet;
use App\Models\ProductVariant;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->outletAssignments()
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('catalog.manage'))
            ->exists();
    }

    /** @return array<string, array<mixed>|string> */
    public function rules(): array
    {
        $businessId = $this->user()?->business_id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('business_id', $businessId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'product_type' => ['required', Rule::in(['standard', 'recipe', 'ingredient', 'service'])],
            'display_order' => ['required', 'integer', 'between:0,65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'variants' => ['required', 'array', 'min:1', 'max:50'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required', 'string', 'max:100'],
            'variants.*.sku' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9._-]*$/'],
            'variants.*.barcode' => ['nullable', 'string', 'max:64'],
            'variants.*.base_unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where('status', 'active'),
            ],
            'variants.*.sell_price' => ['required', 'numeric', 'between:0,9999999999999.99'],
            'variants.*.track_stock' => ['required', 'boolean'],
            'variants.*.status' => ['required', Rule::in(['active', 'inactive'])],
            'variants.*.outlets' => ['present', 'array'],
            'variants.*.outlets.*.outlet_id' => [
                'required',
                'integer',
                Rule::exists('outlets', 'id')->where('business_id', $businessId),
            ],
            'variants.*.outlets.*.is_available' => ['required', 'boolean'],
            'variants.*.outlets.*.price_override' => ['nullable', 'numeric', 'between:0,9999999999999.99'],
            'variants.*.purchase_units' => ['present', 'array', 'max:20'],
            'variants.*.purchase_units.*.unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where('status', 'active'),
            ],
            'variants.*.purchase_units.*.conversion_factor_to_base' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999999.999999',
            ],
        ];
    }

    /** @return array<int, Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $businessId = $this->user()?->business_id;
            $productId = (int) $this->route('product');
            $variants = $this->input('variants', []);

            if (! is_int($businessId) || ! is_array($variants)) {
                return;
            }

            $variantIds = collect($variants)
                ->pluck('id')
                ->filter(fn (mixed $id): bool => is_numeric($id))
                ->map(fn (mixed $id): int => (int) $id)
                ->values();

            if ($productId === 0 && $variantIds->isNotEmpty()) {
                $validator->errors()->add('variants', 'Pilihan baru tidak boleh memakai ID lama.');
            }

            if ($variantIds->unique()->count() !== $variantIds->count()) {
                $validator->errors()->add('variants', 'Pilihan produk tidak boleh duplikat.');
            }

            if ($productId > 0) {
                $existingIds = ProductVariant::query()
                    ->where('business_id', $businessId)
                    ->where('product_id', $productId)
                    ->pluck('id')
                    ->sort()
                    ->values();

                if ($variantIds->unique()->sort()->values()->all() !== $existingIds->all()) {
                    $validator->errors()->add(
                        'variants',
                        'Semua pilihan lama harus tetap dikirim. Nonaktifkan pilihan yang tidak dipakai.',
                    );
                }
            }

            $skus = collect($variants)->pluck('sku')->filter()->values();
            $barcodes = collect($variants)->pluck('barcode')->filter()->values();

            if ($skus->unique()->count() !== $skus->count()) {
                $validator->errors()->add('variants', 'Kode produk tidak boleh duplikat.');
            }

            if ($barcodes->unique()->count() !== $barcodes->count()) {
                $validator->errors()->add('variants', 'Barcode dalam satu produk tidak boleh duplikat.');
            }

            $usedSkus = ProductVariant::query()
                ->where('business_id', $businessId)
                ->whereIn('sku', $skus)
                ->get(['id', 'sku'])
                ->keyBy('sku');
            $usedBarcodes = ProductVariant::query()
                ->where('business_id', $businessId)
                ->whereIn('barcode', $barcodes)
                ->get(['id', 'barcode'])
                ->keyBy('barcode');
            $outletIds = Outlet::query()
                ->where('business_id', $businessId)
                ->pluck('id')
                ->sort()
                ->values()
                ->all();

            foreach ($variants as $index => $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $variantId = isset($variant['id']) ? (int) $variant['id'] : null;
                $sku = $variant['sku'] ?? null;
                $barcode = $variant['barcode'] ?? null;
                $baseUnitId = isset($variant['base_unit_id']) ? (int) $variant['base_unit_id'] : 0;

                if (is_string($sku) && $usedSkus->has($sku) && $usedSkus->get($sku)?->id !== $variantId) {
                    $validator->errors()->add("variants.{$index}.sku", 'Kode produk sudah dipakai.');
                }

                if (is_string($barcode) && $barcode !== '' && $usedBarcodes->has($barcode)
                    && $usedBarcodes->get($barcode)?->id !== $variantId) {
                    $validator->errors()->add("variants.{$index}.barcode", 'Barcode sudah dipakai dalam bisnis ini.');
                }

                $submittedOutletIds = [];
                $variantOutlets = $variant['outlets'] ?? null;

                if (is_array($variantOutlets)) {
                    foreach ($variantOutlets as $variantOutlet) {
                        if (is_array($variantOutlet) && is_numeric($variantOutlet['outlet_id'] ?? null)) {
                            $submittedOutletIds[] = (int) $variantOutlet['outlet_id'];
                        }
                    }
                }

                $uniqueOutletIds = array_values(array_unique($submittedOutletIds));
                sort($uniqueOutletIds);

                if (count($submittedOutletIds) !== count($uniqueOutletIds)
                    || $uniqueOutletIds !== $outletIds) {
                    $validator->errors()->add(
                        "variants.{$index}.outlets",
                        'Pengaturan harga harus mencakup setiap outlet bisnis tepat satu kali.',
                    );
                }

                $purchaseUnits = $variant['purchase_units'] ?? [];

                if (! is_array($purchaseUnits)) {
                    $purchaseUnits = [];
                }

                $purchaseUnitIds = collect($purchaseUnits)
                    ->filter(fn (mixed $unit): bool => is_array($unit) && is_numeric($unit['unit_id'] ?? null))
                    ->map(fn (array $unit): int => (int) $unit['unit_id'])
                    ->values();

                if ($purchaseUnitIds->unique()->count() !== $purchaseUnitIds->count()) {
                    $validator->errors()->add(
                        "variants.{$index}.purchase_units",
                        'Satuan beli tidak boleh duplikat.',
                    );
                }

                if ($baseUnitId > 0 && $purchaseUnitIds->contains($baseUnitId)) {
                    $validator->errors()->add(
                        "variants.{$index}.purchase_units",
                        'Satuan beli tidak boleh sama dengan satuan dasar.',
                    );
                }
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $productType = mb_strtolower(trim((string) $this->input('product_type', 'standard')));
        $variants = $this->input('variants', []);

        if (is_array($variants)) {
            $variants = array_map(function (mixed $variant) use ($productType): mixed {
                if (! is_array($variant)) {
                    return $variant;
                }

                $trackStock = $productType === 'ingredient'
                    || ($productType === 'standard' && filter_var($variant['track_stock'] ?? false, FILTER_VALIDATE_BOOL));
                $outlets = $variant['outlets'] ?? [];

                if ($productType === 'ingredient' && is_array($outlets)) {
                    $outlets = array_map(function (mixed $outlet): mixed {
                        if (! is_array($outlet)) {
                            return $outlet;
                        }

                        return [...$outlet, 'is_available' => false, 'price_override' => null];
                    }, $outlets);
                }

                return [
                    ...$variant,
                    'name' => trim((string) ($variant['name'] ?? '')),
                    'sku' => mb_strtoupper(trim((string) ($variant['sku'] ?? ''))),
                    'barcode' => filled($variant['barcode'] ?? null)
                        ? mb_strtoupper(trim((string) $variant['barcode']))
                        : null,
                    'sell_price' => $productType === 'ingredient' ? 0 : ($variant['sell_price'] ?? null),
                    'track_stock' => $trackStock,
                    'outlets' => $outlets,
                    'purchase_units' => $trackStock
                        ? ($variant['purchase_units'] ?? [])
                        : [],
                ];
            }, $variants);
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'description' => filled($this->input('description'))
                ? trim((string) $this->input('description'))
                : null,
            'product_type' => $productType,
            'variants' => $variants,
        ]);
    }
}
