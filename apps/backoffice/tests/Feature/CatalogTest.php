<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\OutletProduct;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserOutletRole;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('catalog.index'))->assertRedirect(route('login'));
    }

    public function test_products_are_tenant_scoped_searched_filtered_and_paginated(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $otherOutlet = $this->createOutlet($otherBusiness, 'KL', 'Kedai Lain');
        $category = $this->createCategory($business, 'Minuman');
        $otherCategory = $this->createCategory($otherBusiness, 'Rahasia');
        $user = $this->createUserWithCatalogPermission($business, $outlet);

        foreach (range(1, 12) as $number) {
            $this->createProduct(
                $business,
                $category,
                $outlet,
                sprintf('Produk %02d', $number),
                sprintf('SKU-%02d', $number),
                $number === 12 ? 'inactive' : 'active',
            );
        }
        $this->createProduct($otherBusiness, $otherCategory, $otherOutlet, 'Produk Rahasia', 'SECRET');

        $this->actingAs($user)
            ->get(route('catalog.index', ['per_page' => 10, 'page' => 2]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Catalog/Index')
                ->has('products.data', 2)
                ->where('products.current_page', 2)
                ->where('products.total', 12)
                ->where('summary.products', 12)
                ->where('summary.variants', 12));

        $this->actingAs($user)
            ->get(route('catalog.index', [
                'search' => 'SKU-12',
                'category' => $category->id,
                'outlet' => $outlet->id,
                'status' => 'inactive',
                'per_page' => 50,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Produk 12')
                ->where('products.data.0.default_variant.sku', 'SKU-12')
                ->where('products.total', 1)
                ->where('filters.category', $category->id)
                ->where('filters.outlet', $outlet->id)
                ->where('filters.status', 'inactive')
                ->where('filters.perPage', 50));
    }

    public function test_authorized_user_can_create_and_update_category(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $user = $this->createUserWithCatalogPermission($business, $outlet);

        $this->actingAs($user)
            ->post(route('catalog.categories.store'), [
                'name' => ' Makanan ',
                'display_order' => 20,
                'status' => 'active',
            ])
            ->assertRedirect();

        $category = Category::query()->where('business_id', $business->id)->firstOrFail();
        $this->assertSame('Makanan', $category->name);

        $this->actingAs($user)
            ->put(route('catalog.categories.update', $category), [
                'name' => 'Makanan Ringan',
                'display_order' => 30,
                'status' => 'inactive',
            ])
            ->assertRedirect(route('catalog.index', ['category' => $category->id]));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'business_id' => $business->id,
            'name' => 'Makanan Ringan',
            'display_order' => 30,
            'status' => 'inactive',
        ]);
    }

    public function test_user_without_catalog_permission_cannot_write_catalog(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $role = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Kasir',
            'status' => 'active',
        ]);
        $user = User::factory()->for($business)->create();
        UserOutletRole::query()->create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'outlet_id' => $outlet->id,
            'role_id' => $role->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('catalog.categories.store'), [
                'name' => 'Rahasia',
                'display_order' => 0,
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', [
            'business_id' => $business->id,
            'name' => 'Rahasia',
        ]);
    }

    public function test_authorized_user_can_create_product_with_variants_and_outlet_prices(): void
    {
        $business = Business::factory()->create();
        $firstOutlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $secondOutlet = $this->createOutlet($business, 'KT', 'Kedai Timur');
        $category = $this->createCategory($business, 'Kopi');
        $user = $this->createUserWithCatalogPermission($business, $firstOutlet);

        $response = $this->actingAs($user)->post(route('catalog.products.store'), [
            'category_id' => $category->id,
            'name' => ' Cafe Latte ',
            'description' => ' Susu dan espresso ',
            'product_type' => 'standard',
            'display_order' => 10,
            'status' => 'active',
            'variants' => [
                $this->variantPayload(' reguler ', ' latte-reg ', 28000, [$firstOutlet, $secondOutlet], [null, 30000]),
                $this->variantPayload('Besar', 'latte-large', 34000, [$firstOutlet, $secondOutlet]),
            ],
        ]);

        $response->assertRedirect(route('catalog.index'));
        $product = Product::query()->where('business_id', $business->id)->firstOrFail();
        $regular = ProductVariant::query()->where('sku', 'LATTE-REG')->firstOrFail();

        $this->assertSame('Cafe Latte', $product->name);
        $this->assertSame('Susu dan espresso', $product->description);
        $this->assertTrue($regular->is_default);
        $this->assertDatabaseCount('product_variants', 2);
        $this->assertDatabaseHas('outlet_products', [
            'business_id' => $business->id,
            'outlet_id' => $secondOutlet->id,
            'product_variant_id' => $regular->id,
            'is_available' => true,
            'price_override' => 30000,
        ]);
    }

    public function test_product_update_keeps_existing_variants_and_updates_outlet_price(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $category = $this->createCategory($business, 'Kopi');
        $product = $this->createProduct($business, $category, $outlet, 'Latte', 'LATTE');
        $variant = $product->variants()->firstOrFail();
        $user = $this->createUserWithCatalogPermission($business, $outlet);

        $this->actingAs($user)
            ->put(route('catalog.products.update', $product), [
                'category_id' => $category->id,
                'name' => 'Latte Baru',
                'description' => null,
                'product_type' => 'standard',
                'display_order' => 15,
                'status' => 'inactive',
                'variants' => [
                    [
                        ...$this->variantPayload('Reguler', 'LATTE', 29000, [$outlet], [31000]),
                        'id' => $variant->id,
                    ],
                    $this->variantPayload('Besar', 'LATTE-L', 35000, [$outlet]),
                ],
            ])
            ->assertRedirect(route('catalog.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Latte Baru',
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('outlet_products', [
            'outlet_id' => $outlet->id,
            'product_variant_id' => $variant->id,
            'price_override' => 31000,
        ]);
        $this->assertDatabaseCount('product_variants', 2);
        $this->assertSame(1, ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('is_default', true)
            ->count());
    }

    public function test_duplicate_sku_is_rejected_inside_business_but_allowed_across_businesses(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $otherOutlet = $this->createOutlet($otherBusiness, 'KL', 'Kedai Lain');
        $category = $this->createCategory($business, 'Kopi');
        $otherCategory = $this->createCategory($otherBusiness, 'Kopi');
        $this->createProduct($business, $category, $outlet, 'Latte', 'SKU-SAMA');
        $this->createProduct($otherBusiness, $otherCategory, $otherOutlet, 'Latte Lain', 'SKU-SAMA');
        $user = $this->createUserWithCatalogPermission($business, $outlet);

        $this->actingAs($user)
            ->from(route('catalog.index'))
            ->post(route('catalog.products.store'), [
                'category_id' => $category->id,
                'name' => 'Produk Duplikat',
                'description' => null,
                'product_type' => 'standard',
                'display_order' => 0,
                'status' => 'active',
                'variants' => [$this->variantPayload('Reguler', 'SKU-SAMA', 10000, [$outlet])],
            ])
            ->assertRedirect(route('catalog.index'))
            ->assertSessionHasErrors('variants.0.sku');
    }

    public function test_product_write_cannot_cross_business_boundary(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $otherOutlet = $this->createOutlet($otherBusiness, 'KL', 'Kedai Lain');
        $category = $this->createCategory($business, 'Kopi');
        $otherCategory = $this->createCategory($otherBusiness, 'Rahasia');
        $foreignProduct = $this->createProduct(
            $otherBusiness,
            $otherCategory,
            $otherOutlet,
            'Produk Rahasia',
            'SECRET',
        );
        $user = $this->createUserWithCatalogPermission($business, $outlet);

        $this->actingAs($user)
            ->put(route('catalog.products.update', $foreignProduct), [
                'category_id' => $category->id,
                'name' => 'Diubah Paksa',
                'description' => null,
                'product_type' => 'standard',
                'display_order' => 0,
                'status' => 'active',
                'variants' => [$this->variantPayload('Baru', 'BARU', 10000, [$outlet])],
            ])
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('catalog.products.store'), [
                'category_id' => $category->id,
                'name' => 'Harga Silang',
                'description' => null,
                'product_type' => 'standard',
                'display_order' => 0,
                'status' => 'active',
                'variants' => [$this->variantPayload('Reguler', 'SILANG', 10000, [$otherOutlet])],
            ])
            ->assertSessionHasErrors('variants.0.outlets.0.outlet_id');

        $this->assertDatabaseHas('products', [
            'id' => $foreignProduct->id,
            'name' => 'Produk Rahasia',
        ]);
    }

    public function test_ingredient_forces_stock_rules_and_saves_purchase_unit_conversion(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $category = $this->createCategory($business, 'Bahan Baku');
        $user = $this->createUserWithCatalogPermission($business, $outlet);
        $milliliter = Unit::query()->where('code', 'ml')->firstOrFail();
        $bottle = Unit::query()->where('code', 'botol')->firstOrFail();
        $variant = [
            ...$this->variantPayload('Reguler', 'DRIPP-PALM', 10000, [$outlet]),
            'base_unit_id' => $milliliter->id,
            'track_stock' => false,
            'purchase_units' => [[
                'unit_id' => $bottle->id,
                'conversion_factor_to_base' => 750,
            ]],
        ];

        $this->actingAs($user)
            ->post(route('catalog.products.store'), [
                'category_id' => $category->id,
                'name' => 'Dripp Palm Sugar',
                'description' => null,
                'product_type' => 'ingredient',
                'display_order' => 0,
                'status' => 'active',
                'variants' => [$variant],
            ])
            ->assertRedirect(route('catalog.index'));

        $product = Product::query()->where('name', 'Dripp Palm Sugar')->firstOrFail();
        $savedVariant = $product->variants()->firstOrFail();

        $this->assertSame('ingredient', $product->product_type);
        $this->assertTrue($savedVariant->track_stock);
        $this->assertSame('0.00', $savedVariant->sell_price);
        $this->assertSame($milliliter->id, $savedVariant->base_unit_id);
        $this->assertDatabaseHas('product_units', [
            'product_variant_id' => $savedVariant->id,
            'unit_id' => $bottle->id,
            'conversion_factor_to_base' => 750,
        ]);
        $this->assertDatabaseHas('outlet_products', [
            'product_variant_id' => $savedVariant->id,
            'outlet_id' => $outlet->id,
            'is_available' => false,
            'price_override' => null,
        ]);
    }

    public function test_recipe_and_service_cannot_keep_stock_settings(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $category = $this->createCategory($business, 'Menu');
        $user = $this->createUserWithCatalogPermission($business, $outlet);
        $bottle = Unit::query()->where('code', 'botol')->firstOrFail();

        foreach (['recipe', 'service'] as $type) {
            $variant = [
                ...$this->variantPayload('Reguler', mb_strtoupper($type), 20000, [$outlet]),
                'track_stock' => true,
                'purchase_units' => [[
                    'unit_id' => $bottle->id,
                    'conversion_factor_to_base' => 1,
                ]],
            ];

            $this->actingAs($user)
                ->post(route('catalog.products.store'), [
                    'category_id' => $category->id,
                    'name' => "Produk {$type}",
                    'description' => null,
                    'product_type' => $type,
                    'display_order' => 0,
                    'status' => 'active',
                    'variants' => [$variant],
                ])
                ->assertRedirect(route('catalog.index'));
        }

        $variants = ProductVariant::query()
            ->whereIn('sku', ['RECIPE', 'SERVICE'])
            ->get();

        $this->assertCount(2, $variants);
        $this->assertTrue($variants->every(fn (ProductVariant $variant): bool => ! $variant->track_stock));
        $this->assertDatabaseCount('product_units', 0);
    }

    public function test_purchase_unit_must_be_unique_and_different_from_base_unit(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama');
        $category = $this->createCategory($business, 'Bahan Baku');
        $user = $this->createUserWithCatalogPermission($business, $outlet);
        $milliliter = Unit::query()->where('code', 'ml')->firstOrFail();
        $variant = [
            ...$this->variantPayload('Reguler', 'SIRUP', 0, [$outlet]),
            'base_unit_id' => $milliliter->id,
            'track_stock' => true,
            'purchase_units' => [
                ['unit_id' => $milliliter->id, 'conversion_factor_to_base' => 1],
                ['unit_id' => $milliliter->id, 'conversion_factor_to_base' => 1000],
            ],
        ];

        $this->actingAs($user)
            ->from(route('catalog.index'))
            ->post(route('catalog.products.store'), [
                'category_id' => $category->id,
                'name' => 'Sirup',
                'description' => null,
                'product_type' => 'ingredient',
                'display_order' => 0,
                'status' => 'active',
                'variants' => [$variant],
            ])
            ->assertRedirect(route('catalog.index'))
            ->assertSessionHasErrors('variants.0.purchase_units');
    }

    public function test_database_rejects_product_category_from_another_business(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $foreignCategory = $this->createCategory($otherBusiness, 'Rahasia');

        $this->expectException(QueryException::class);

        Product::query()->create([
            'business_id' => $business->id,
            'category_id' => $foreignCategory->id,
            'name' => 'Produk Silang',
            'product_type' => 'standard',
            'status' => 'active',
            'display_order' => 0,
        ]);
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

    private function createCategory(Business $business, string $name): Category
    {
        return Category::query()->create([
            'business_id' => $business->id,
            'name' => $name,
            'display_order' => 0,
            'status' => 'active',
        ]);
    }

    private function createProduct(
        Business $business,
        Category $category,
        Outlet $outlet,
        string $name,
        string $sku,
        string $status = 'active',
    ): Product {
        $product = Product::query()->create([
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => $name,
            'product_type' => 'standard',
            'status' => $status,
            'display_order' => 0,
        ]);
        $variant = ProductVariant::query()->create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'name' => 'Reguler',
            'base_unit_id' => $this->unitId('pcs'),
            'sku' => $sku,
            'sell_price' => 20000,
            'track_stock' => false,
            'is_default' => true,
            'status' => 'active',
        ]);
        OutletProduct::query()->create([
            'business_id' => $business->id,
            'outlet_id' => $outlet->id,
            'product_variant_id' => $variant->id,
            'is_available' => true,
        ]);

        return $product;
    }

    private function createUserWithCatalogPermission(Business $business, Outlet $outlet): User
    {
        $permission = Permission::query()->create([
            'code' => 'catalog.manage',
            'description' => 'Mengelola katalog',
        ]);
        $role = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Pengelola katalog',
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

    /**
     * @param  array<int, Outlet>  $outlets
     * @param  array<int, int|null>  $overrides
     * @return array<string, mixed>
     */
    private function variantPayload(
        string $name,
        string $sku,
        int $price,
        array $outlets,
        array $overrides = [],
    ): array {
        return [
            'name' => $name,
            'sku' => $sku,
            'barcode' => null,
            'base_unit_id' => $this->unitId('pcs'),
            'sell_price' => $price,
            'track_stock' => false,
            'status' => 'active',
            'purchase_units' => [],
            'outlets' => collect($outlets)->map(fn (Outlet $outlet, int $index): array => [
                'outlet_id' => $outlet->id,
                'is_available' => true,
                'price_override' => $overrides[$index] ?? null,
            ])->all(),
        ];
    }

    private function unitId(string $code): int
    {
        return Unit::query()->where('code', $code)->valueOrFail('id');
    }
}
