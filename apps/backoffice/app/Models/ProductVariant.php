<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'business_id',
    'product_id',
    'sku',
    'barcode',
    'name',
    'sell_price',
    'default_purchase_cost',
    'track_stock',
    'is_default',
    'status',
])]
class ProductVariant extends Model
{
    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return HasMany<OutletProduct, $this> */
    public function outletProducts(): HasMany
    {
        return $this->hasMany(OutletProduct::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sell_price' => 'decimal:2',
            'default_purchase_cost' => 'decimal:2',
            'track_stock' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
