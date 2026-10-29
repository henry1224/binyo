<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'business_id',
    'code',
    'name',
    'profit_center_code',
    'address',
    'timezone',
    'business_day_start',
    'receipt_prefix',
    'prevent_negative_stock',
    'replica_enabled',
    'status',
])]
class Outlet extends Model
{
    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return HasMany<UserOutletRole, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(UserOutletRole::class);
    }

    /** @return HasMany<Device, $this> */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /** @return HasMany<CashDrawer, $this> */
    public function cashDrawers(): HasMany
    {
        return $this->hasMany(CashDrawer::class);
    }

    /** @return Attribute<bool, never> */
    protected function preventNegativeStock(): Attribute
    {
        return Attribute::make(get: fn (mixed $value): bool => (bool) $value);
    }

    /** @return Attribute<bool, never> */
    protected function replicaEnabled(): Attribute
    {
        return Attribute::make(get: fn (mixed $value): bool => (bool) $value);
    }
}
