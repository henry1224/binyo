<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'business_id',
    'outlet_id',
    'product_variant_id',
    'movement_type',
    'quantity_delta',
    'unit_cost_snapshot',
    'value_delta',
    'source_type',
    'source_id',
    'occurred_at',
    'created_by',
])]
class StockMovement extends Model
{
    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<Outlet, $this> */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /** @return BelongsTo<ProductVariant, $this> */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quantity_delta' => 'decimal:6',
            'unit_cost_snapshot' => 'decimal:6',
            'value_delta' => 'decimal:6',
            'occurred_at' => 'datetime',
        ];
    }
}
