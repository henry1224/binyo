<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\SaveOpeningStockRequest;
use App\Models\InventoryBalance;
use App\Models\Outlet;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $outlets = Outlet::query()
            ->where('business_id', $user->business_id)
            ->whereHas('assignments', fn (Builder $query) => $query
                ->where('user_id', $user->id)
                ->whereHas('role.permissions', fn (Builder $query) => $query->whereKey('stock.manage')))
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        abort_if($outlets->isEmpty(), 403);

        $selectedOutlet = $outlets->firstWhere('id', $request->integer('outlet')) ?? $outlets->firstOrFail();
        $search = $request->query('search');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $requestedPerPage = $request->integer('per_page');
        $perPage = in_array($requestedPerPage, [10, 25, 50], true) ? $requestedPerPage : 10;
        $baseVariants = ProductVariant::query()
            ->where('business_id', $user->business_id)
            ->where('track_stock', true);

        $variants = (clone $baseVariants)
            ->with([
                'product:id,name,product_type,status',
                'baseUnit:id,code,name',
                'inventoryBalances' => fn ($query) => $query
                    ->where('outlet_id', $selectedOutlet->id)
                    ->select([
                        'id',
                        'product_variant_id',
                        'outlet_id',
                        'quantity_on_hand',
                        'average_cost',
                    ]),
            ])
            ->withExists(['stockMovements as has_movements' => fn ($query) => $query
                ->where('outlet_id', $selectedOutlet->id)])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('sku', "%{$search}%")
                ->orWhereLike('name', "%{$search}%")
                ->orWhereHas('product', fn (Builder $query) => $query->whereLike('name', "%{$search}%"))))
            ->orderBy('sku')
            ->paginate($perPage, [
                'id',
                'product_id',
                'sku',
                'name',
                'base_unit_id',
                'default_purchase_cost',
                'status',
            ])
            ->withQueryString();

        $selectedVariantId = $request->integer('variant');
        $firstVariantId = $variants->isNotEmpty() ? $variants->first()->id : 0;
        $selectedVariant = (clone $baseVariants)
            ->with(['product:id,name,product_type', 'baseUnit:id,code,name'])
            ->when($selectedVariantId > 0, fn (Builder $query) => $query->whereKey($selectedVariantId))
            ->when($selectedVariantId <= 0, fn (Builder $query) => $query->whereKey($firstVariantId))
            ->first(['id', 'product_id', 'sku', 'name', 'base_unit_id']);

        $movements = $selectedVariant === null
            ? collect()
            : StockMovement::query()
                ->where('business_id', $user->business_id)
                ->where('outlet_id', $selectedOutlet->id)
                ->where('product_variant_id', $selectedVariant->id)
                ->with('creator:id,name')
                ->latest('occurred_at')
                ->latest('id')
                ->limit(50)
                ->get([
                    'id',
                    'product_variant_id',
                    'movement_type',
                    'quantity_delta',
                    'unit_cost_snapshot',
                    'value_delta',
                    'occurred_at',
                    'created_by',
                ]);

        $stockedCount = InventoryBalance::query()
            ->where('business_id', $user->business_id)
            ->where('outlet_id', $selectedOutlet->id)
            ->where('quantity_on_hand', '>', 0)
            ->count();
        $trackableCount = (clone $baseVariants)->count();
        $inventoryValue = InventoryBalance::query()
            ->where('business_id', $user->business_id)
            ->where('outlet_id', $selectedOutlet->id)
            ->selectRaw('COALESCE(SUM(quantity_on_hand * average_cost), 0) AS total')
            ->value('total');

        return Inertia::render('Inventory/Index', [
            'business' => fn () => $user->business()->firstOrFail(['id', 'name', 'currency']),
            'outlets' => $outlets,
            'variants' => $variants,
            'selectedVariant' => $selectedVariant,
            'movements' => $movements,
            'summary' => [
                'trackable' => $trackableCount,
                'stocked' => $stockedCount,
                'empty' => max(0, $trackableCount - $stockedCount),
                'value' => $inventoryValue,
            ],
            'filters' => [
                'search' => $search,
                'outlet' => $selectedOutlet->id,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function storeOpeningStock(SaveOpeningStockRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $data = $request->validated();
        $outletId = (int) $data['outlet_id'];
        $variantId = (int) $data['product_variant_id'];
        $outlet = Outlet::query()
            ->where('business_id', $user->business_id)
            ->findOrFail($outletId);
        $canManageOutlet = $user->outletAssignments()
            ->where('outlet_id', $outlet->id)
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('stock.manage'))
            ->exists();

        abort_unless($canManageOutlet, 403);

        $variant = ProductVariant::query()
            ->where('business_id', $user->business_id)
            ->where('track_stock', true)
            ->with(['product:id,name', 'baseUnit:id,code,name'])
            ->findOrFail($variantId);
        $quantity = (string) $data['quantity'];
        $unitCost = (string) $data['unit_cost'];

        if (! is_numeric($quantity) || ! is_numeric($unitCost)) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah dan harga modal harus berupa angka.',
            ]);
        }

        DB::transaction(function () use ($outlet, $quantity, $unitCost, $user, $variant): void {
            DB::table('inventory_balances')->insertOrIgnore([
                'business_id' => $user->business_id,
                'outlet_id' => $outlet->id,
                'product_variant_id' => $variant->id,
                'quantity_on_hand' => 0,
                'average_cost' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $balance = InventoryBalance::query()
                ->where('business_id', $user->business_id)
                ->where('outlet_id', $outlet->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (StockMovement::query()
                ->where('business_id', $user->business_id)
                ->where('outlet_id', $outlet->id)
                ->where('product_variant_id', $variant->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'Stok produk ini sudah pernah dicatat pada outlet tersebut.',
                ]);
            }

            StockMovement::query()->create([
                'business_id' => $user->business_id,
                'outlet_id' => $outlet->id,
                'product_variant_id' => $variant->id,
                'movement_type' => 'opening',
                'quantity_delta' => $quantity,
                'unit_cost_snapshot' => $unitCost,
                'value_delta' => bcmul($quantity, $unitCost, 6),
                'source_type' => 'opening_stock',
                'source_id' => $variant->id,
                'occurred_at' => now(),
                'created_by' => $user->id,
            ]);

            $balance->update([
                'quantity_on_hand' => $quantity,
                'average_cost' => $unitCost,
            ]);
            $variant->update(['default_purchase_cost' => null]);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Stok awal {$variant->product->name} berhasil dicatat untuk {$outlet->name}.",
        ]);

        return to_route('inventory.index', [
            'outlet' => $outlet->id,
            'variant' => $variant->id,
        ]);
    }
}
