<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserOutletRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_page_is_scoped_to_assigned_outlets_and_business(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $hiddenOutlet = $this->createOutlet($business, 'KT', 'Kedai Timur');
        $otherOutlet = $this->createOutlet($otherBusiness, 'KL', 'Kedai Lain');
        $variant = $this->createTrackedVariant($business, 'Dripp Palm', 'DRIPP');
        $this->createTrackedVariant($otherBusiness, 'Produk Rahasia', 'SECRET');
        $user = $this->createUserWithStockPermission($business, $outlet);

        InventoryBalance::query()->create([
            'business_id' => $business->id,
            'outlet_id' => $outlet->id,
            'product_variant_id' => $variant->id,
            'quantity_on_hand' => 750,
            'average_cost' => '146.666667',
        ]);

        $this->actingAs($user)
            ->get(route('inventory.index', ['outlet' => $hiddenOutlet->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Index')
                ->has('outlets', 1)
                ->where('outlets.0.id', $outlet->id)
                ->where('filters.outlet', $outlet->id)
                ->has('variants.data', 1)
                ->where('variants.data.0.sku', 'DRIPP')
                ->where('summary.trackable', 1)
                ->where('summary.stocked', 1));

        $this->assertNotSame($otherOutlet->id, $outlet->id);
    }

    public function test_opening_stock_creates_ledger_and_balance_once(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $variant = $this->createTrackedVariant($business, 'Dripp Palm', 'DRIPP', '146.666667');
        $user = $this->createUserWithStockPermission($business, $outlet);
        $payload = [
            'outlet_id' => $outlet->id,
            'product_variant_id' => $variant->id,
            'quantity' => 750,
            'unit_cost' => '146.666667',
        ];

        $this->actingAs($user)
            ->post(route('inventory.opening-stock.store'), $payload)
            ->assertRedirect(route('inventory.index', [
                'outlet' => $outlet->id,
                'variant' => $variant->id,
            ]));

        $movement = StockMovement::query()->firstOrFail();
        $balance = InventoryBalance::query()->firstOrFail();

        $this->assertSame('opening', $movement->movement_type);
        $this->assertSame('750.000000', $movement->quantity_delta);
        $this->assertSame('146.666667', $movement->unit_cost_snapshot);
        $this->assertSame('110000.000250', $movement->value_delta);
        $this->assertSame('750.000000', $balance->quantity_on_hand);
        $this->assertSame('146.666667', $balance->average_cost);
        $this->assertNull($variant->fresh()?->default_purchase_cost);

        $this->actingAs($user)
            ->from(route('inventory.index'))
            ->post(route('inventory.opening-stock.store'), $payload)
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHasErrors('product_variant_id');

        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseCount('inventory_balances', 1);
    }

    public function test_opening_stock_requires_access_to_requested_outlet(): void
    {
        $business = Business::factory()->create();
        $assignedOutlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $otherOutlet = $this->createOutlet($business, 'KT', 'Kedai Timur');
        $variant = $this->createTrackedVariant($business, 'Dripp Palm', 'DRIPP');
        $user = $this->createUserWithStockPermission($business, $assignedOutlet);

        $this->actingAs($user)
            ->post(route('inventory.opening-stock.store'), [
                'outlet_id' => $otherOutlet->id,
                'product_variant_id' => $variant->id,
                'quantity' => 10,
                'unit_cost' => 1000,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('inventory_balances', 0);
    }

    public function test_opening_stock_rejects_untracked_or_foreign_product(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $untrackedVariant = $this->createTrackedVariant($business, 'Jasa', 'SERVICE');
        $untrackedVariant->update(['track_stock' => false]);
        $foreignVariant = $this->createTrackedVariant($otherBusiness, 'Rahasia', 'SECRET');
        $user = $this->createUserWithStockPermission($business, $outlet);

        foreach ([$untrackedVariant, $foreignVariant] as $variant) {
            $this->actingAs($user)
                ->from(route('inventory.index'))
                ->post(route('inventory.opening-stock.store'), [
                    'outlet_id' => $outlet->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 10,
                    'unit_cost' => 1000,
                ])
                ->assertRedirect(route('inventory.index'))
                ->assertSessionHasErrors('product_variant_id');
        }

        $this->assertDatabaseCount('stock_movements', 0);
    }

    private function createOutlet(Business $business, string $code, string $name): Outlet
    {
        return Outlet::query()->create([
            'business_id' => $business->id,
            'code' => $code,
            'name' => $name,
            'profit_center_code' => "PC-{$code}",
            'receipt_prefix' => $code,
            'status' => 'active',
        ]);
    }

    private function createTrackedVariant(
        Business $business,
        string $name,
        string $sku,
        ?string $legacyCost = null,
    ): ProductVariant {
        $category = Category::query()->create([
            'business_id' => $business->id,
            'name' => "Kategori {$sku}",
            'display_order' => 0,
            'status' => 'active',
        ]);
        $product = Product::query()->create([
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => $name,
            'product_type' => 'ingredient',
            'status' => 'active',
            'display_order' => 0,
        ]);

        return ProductVariant::query()->create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'name' => 'Reguler',
            'base_unit_id' => Unit::query()->where('code', 'ml')->valueOrFail('id'),
            'sku' => $sku,
            'sell_price' => 0,
            'default_purchase_cost' => $legacyCost,
            'track_stock' => true,
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    private function createUserWithStockPermission(Business $business, Outlet $outlet): User
    {
        $permission = Permission::query()->create([
            'code' => 'stock.manage',
            'description' => 'Mengelola stok dan opname',
        ]);
        $role = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Pengelola stok',
            'status' => 'active',
        ]);
        $role->permissions()->attach($permission);
        $user = User::factory()->for($business)->create();
        UserOutletRole::query()->create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'outlet_id' => $outlet->id,
            'role_id' => $role->id,
            'assigned_at' => now(),
        ]);

        return $user;
    }
}
