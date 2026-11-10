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
    'base_unit_id',
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

    /** @return BelongsTo<Unit, $this> */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /** @return HasMany<ProductUnit, $this> */
    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    /** @return HasMany<InventoryBalance, $this> */
    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    /** @return HasMany<StockMovement, $this> */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
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
