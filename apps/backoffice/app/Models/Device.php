<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'business_id',
    'outlet_id',
    'cash_drawer_id',
    'code',
    'name',
    'platform',
    'installation_id',
    'app_version',
    'trusted_at',
    'last_seen_at',
    'last_sync_at',
    'status',
    'revoked_at',
    'revoked_by',
])]
class Device extends Model
{
    protected static function booted(): void
    {
        static::updating(function (Device $device): void {
            if ($device->isDirty('outlet_id')) {
                throw ValidationException::withMessages([
                    'outlet_id' => 'Cabut perangkat lalu registrasikan ulang untuk mengganti outlet.',
                ]);
            }
        });
    }

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

    /** @return BelongsTo<CashDrawer, $this> */
    public function cashDrawer(): BelongsTo
    {
        return $this->belongsTo(CashDrawer::class);
    }

    protected function casts(): array
    {
        return [
            'trusted_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_sync_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
