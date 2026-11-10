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
            'display_order' => ['required', 'integer', 'between:0,65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'variants' => ['required', 'array', 'min:1', 'max:50'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required', 'string', 'max:100'],
            'variants.*.sku' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9._-]*$/'],
            'variants.*.barcode' => ['nullable', 'string', 'max:64'],
            'variants.*.sell_price' => ['required', 'numeric', 'between:0,9999999999999.99'],
            'variants.*.default_purchase_cost' => ['nullable', 'numeric', 'between:0,9999999999999.99'],
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
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $variants = $this->input('variants', []);

        if (is_array($variants)) {
            $variants = array_map(function (mixed $variant): mixed {
                if (! is_array($variant)) {
                    return $variant;
                }

                return [
                    ...$variant,
                    'name' => trim((string) ($variant['name'] ?? '')),
                    'sku' => mb_strtoupper(trim((string) ($variant['sku'] ?? ''))),
                    'barcode' => filled($variant['barcode'] ?? null)
                        ? mb_strtoupper(trim((string) $variant['barcode']))
                        : null,
                ];
            }, $variants);
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'description' => filled($this->input('description'))
                ? trim((string) $this->input('description'))
                : null,
            'variants' => $variants,
        ]);
    }
}
