<?php

namespace App\Http\Controllers;

use App\Http\Requests\Catalog\SaveCategoryRequest;
use App\Http\Requests\Catalog\SaveProductRequest;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\OutletProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $businessId = $user->business_id;
        $search = $request->query('search');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $requestedPerPage = $request->integer('per_page');
        $perPage = in_array($requestedPerPage, [10, 25, 50], true) ? $requestedPerPage : 10;
        $categoryId = max(0, $request->integer('category'));
        $outletId = max(0, $request->integer('outlet'));
        $status = $request->string('status')->toString();
        $status = in_array($status, ['active', 'inactive'], true) ? $status : 'all';
        $canManageCatalog = $user->outletAssignments()
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('catalog.manage'))
            ->exists();

        $products = fn () => Product::query()
            ->where('business_id', $businessId)
            ->select([
                'id',
                'business_id',
                'category_id',
                'name',
                'description',
                'product_type',
                'status',
                'display_order',
                'updated_at',
            ])
            ->with([
                'category:id,name,status',
                'defaultVariant:id,product_id,sku,name,sell_price,status',
            ])
            ->withCount('variants')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('description', "%{$search}%")
                ->orWhereHas('variants', fn (Builder $query) => $query
                    ->whereLike('sku', "%{$search}%")
                    ->orWhereLike('barcode', "%{$search}%")
                    ->orWhereLike('name', "%{$search}%"))))
            ->when($categoryId > 0, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->when($status !== 'all', fn (Builder $query) => $query->where('status', $status))
            ->when($outletId > 0, fn (Builder $query) => $query->whereHas(
                'variants.outletProducts',
                fn (Builder $query) => $query
                    ->where('business_id', $businessId)
                    ->where('outlet_id', $outletId)
                    ->where('is_available', true),
            ))
            ->orderBy('display_order')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $editingProductId = max(0, $request->integer('product'));
        $editingProduct = $editingProductId > 0
            ? Product::query()
                ->where('business_id', $businessId)
                ->with([
                    'category:id,name,status',
                    'variants' => fn ($query) => $query
                        ->select([
                            'id',
                            'business_id',
                            'product_id',
                            'sku',
                            'barcode',
                            'name',
                            'base_unit_id',
                            'sell_price',
                            'track_stock',
                            'is_default',
                            'status',
                        ])
                        ->with([
                            'outletProducts:id,product_variant_id,outlet_id,is_available,price_override',
                            'productUnits:id,product_variant_id,unit_id,conversion_factor_to_base',
                        ])
                        ->orderByDesc('is_default')
                        ->orderBy('id'),
                ])
                ->find($editingProductId)
            : null;

        return Inertia::render('Catalog/Index', [
            'business' => fn () => $user->business()->firstOrFail(['id', 'name', 'currency']),
            'products' => $products,
            'categories' => fn () => Category::query()
                ->where('business_id', $businessId)
                ->withCount('products')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(['id', 'name', 'display_order', 'status']),
            'outlets' => fn () => Outlet::query()
                ->where('business_id', $businessId)
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'units' => fn () => Unit::query()
                ->where('status', 'active')
                ->orderBy('id')
                ->get(['id', 'code', 'name', 'dimension']),
            'summary' => fn () => [
                'categories' => Category::query()->where('business_id', $businessId)->count(),
                'products' => Product::query()->where('business_id', $businessId)->count(),
                'variants' => ProductVariant::query()->where('business_id', $businessId)->count(),
                'active' => Product::query()
                    ->where('business_id', $businessId)
                    ->where('status', 'active')
                    ->count(),
            ],
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
                'outlet' => $outletId,
                'status' => $status,
                'perPage' => $perPage,
            ],
            'editingProduct' => $editingProduct,
            'canManageCatalog' => $canManageCatalog,
        ]);
    }

    public function storeCategory(SaveCategoryRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $category = Category::query()->create([
            ...$request->validated(),
            'business_id' => $user->business_id,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Kategori {$category->name} berhasil ditambahkan.",
        ]);

        return to_route('catalog.index', ['category' => $category->id]);
    }

    public function updateCategory(SaveCategoryRequest $request, int $category): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $categoryModel = Category::query()
            ->where('business_id', $user->business_id)
            ->findOrFail($category);
        $categoryModel->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Kategori {$categoryModel->name} berhasil diperbarui.",
        ]);

        return to_route('catalog.index', ['category' => $categoryModel->id]);
    }

    public function storeProduct(SaveProductRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $data = $request->validated();
        $product = DB::transaction(function () use ($data, $user): Product {
            $product = Product::query()->create([
                'business_id' => $user->business_id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'product_type' => $data['product_type'],
                'status' => $data['status'],
                'display_order' => $data['display_order'],
            ]);

            foreach ($data['variants'] as $index => $variantData) {
                $variant = $product->variants()->create([
                    'business_id' => $user->business_id,
                    'sku' => $variantData['sku'],
                    'barcode' => $variantData['barcode'],
                    'name' => $variantData['name'],
                    'base_unit_id' => $variantData['base_unit_id'],
                    'sell_price' => $variantData['sell_price'],
                    'track_stock' => $variantData['track_stock'],
                    'is_default' => $index === 0,
                    'status' => $variantData['status'],
                ]);

                $this->syncOutletPrices($variant, $variantData['outlets'], $user->business_id);
                $this->syncProductUnits($variant, $variantData['purchase_units']);
            }

            return $product;
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Produk {$product->name} berhasil ditambahkan.",
        ]);

        return to_route('catalog.index');
    }

    public function updateProduct(SaveProductRequest $request, int $product): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $productModel = Product::query()
            ->where('business_id', $user->business_id)
            ->findOrFail($product);
        $data = $request->validated();

        DB::transaction(function () use ($data, $productModel, $user): void {
            $productModel->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'product_type' => $data['product_type'],
                'status' => $data['status'],
                'display_order' => $data['display_order'],
            ]);
            $productModel->variants()->update(['is_default' => false]);

            foreach ($data['variants'] as $index => $variantData) {
                if (isset($variantData['id'])) {
                    $variantId = (int) $variantData['id'];
                    $variant = ProductVariant::query()
                        ->where('business_id', $user->business_id)
                        ->where('product_id', $productModel->id)
                        ->findOrFail($variantId);
                } else {
                    $variant = new ProductVariant([
                        'business_id' => $user->business_id,
                        'product_id' => $productModel->id,
                    ]);
                }

                $variant->fill([
                    'sku' => $variantData['sku'],
                    'barcode' => $variantData['barcode'],
                    'name' => $variantData['name'],
                    'base_unit_id' => $variantData['base_unit_id'],
                    'sell_price' => $variantData['sell_price'],
                    'track_stock' => $variantData['track_stock'],
                    'is_default' => $index === 0,
                    'status' => $variantData['status'],
                ])->save();

                $this->syncOutletPrices($variant, $variantData['outlets'], $user->business_id);
                $this->syncProductUnits($variant, $variantData['purchase_units']);
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Produk {$productModel->name} berhasil diperbarui.",
        ]);

        return to_route('catalog.index');
    }

    /** @param array<int, array{outlet_id: int, is_available: bool, price_override: int|float|string|null}> $outlets */
    private function syncOutletPrices(ProductVariant $variant, array $outlets, int $businessId): void
    {
        foreach ($outlets as $outlet) {
            OutletProduct::query()->updateOrCreate(
                [
                    'business_id' => $businessId,
                    'outlet_id' => $outlet['outlet_id'],
                    'product_variant_id' => $variant->id,
                ],
                [
                    'is_available' => $outlet['is_available'],
                    'price_override' => $outlet['price_override'],
                ],
            );
        }
    }

    /** @param array<int, array{unit_id: int, conversion_factor_to_base: int|float|string}> $units */
    private function syncProductUnits(ProductVariant $variant, array $units): void
    {
        $variant->productUnits()->delete();
        $variant->productUnits()->createMany($units);
    }
}
