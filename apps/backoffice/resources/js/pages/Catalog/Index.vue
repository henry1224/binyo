<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Boxes,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDollarSign,
    PackageOpen,
    Pencil,
    Plus,
    Search,
    Store,
    Tags,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

type Category = {
    id: number;
    name: string;
    display_order: number;
    status: 'active' | 'inactive';
    products_count: number;
};

type Outlet = { id: number; code: string; name: string };

type OutletPrice = {
    outlet_id: number;
    is_available: boolean;
    price_override: string | null;
};

type OutletPriceForm = {
    outlet_id: number;
    is_available: boolean;
    price_override: string;
};

type Variant = {
    id?: number;
    name: string;
    sku: string;
    barcode: string | null;
    sell_price: string;
    default_purchase_cost: string | null;
    track_stock: boolean;
    is_default?: boolean;
    status: 'active' | 'inactive';
    outlet_products?: OutletPrice[];
};

type Product = {
    id: number;
    category_id: number;
    name: string;
    description: string | null;
    status: 'active' | 'inactive';
    display_order: number;
    variants_count: number;
    category: Pick<Category, 'id' | 'name' | 'status'>;
    default_variant: Pick<
        Variant,
        'id' | 'name' | 'sku' | 'sell_price' | 'status'
    > | null;
};

type EditingProduct = Omit<Product, 'variants_count' | 'default_variant'> & {
    variants: Variant[];
};

type VariantForm = {
    id?: number;
    name: string;
    sku: string;
    barcode: string;
    sell_price: string;
    default_purchase_cost: string;
    track_stock: boolean;
    status: 'active' | 'inactive';
    outlets: OutletPriceForm[];
};

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};

const props = defineProps<{
    business: { id: number; name: string; currency: string };
    products: Paginated<Product>;
    categories: Category[];
    outlets: Outlet[];
    summary: {
        categories: number;
        products: number;
        variants: number;
        active: number;
    };
    filters: {
        search: string;
        category: number;
        outlet: number;
        status: 'all' | 'active' | 'inactive';
        perPage: number;
    };
    editingProduct: EditingProduct | null;
    canManageCatalog: boolean;
}>();

const search = ref(props.filters.search);
const categoryFilter = ref(props.filters.category);
const outletFilter = ref(props.filters.outlet);
const statusFilter = ref(props.filters.status);
const perPage = ref(props.filters.perPage);
const categorySheetOpen = ref(false);
const productSheetOpen = ref(false);
const editingCategory = ref<Category | null>(null);
const editingProductId = ref<number | null>(props.editingProduct?.id ?? null);

const emptyOutletPrices = (): OutletPriceForm[] =>
    props.outlets.map((outlet) => ({
        outlet_id: outlet.id,
        is_available: true,
        price_override: '',
    }));

const emptyVariant = (name = 'Reguler'): VariantForm => ({
    name,
    sku: '',
    barcode: '',
    sell_price: '',
    default_purchase_cost: '',
    track_stock: false,
    status: 'active',
    outlets: emptyOutletPrices(),
});

const categoryForm = useForm({
    name: '',
    display_order: 0,
    status: 'active' as 'active' | 'inactive',
});

const productForm = useForm({
    category_id: props.categories[0]?.id ?? 0,
    name: '',
    description: null as string | null,
    display_order: 0,
    status: 'active' as 'active' | 'inactive',
    variants: [emptyVariant()] as VariantForm[],
});

const selectedCategory = computed(() =>
    props.categories.find((category) => category.id === categoryFilter.value),
);
const hasFilters = computed(
    () =>
        props.filters.search !== '' ||
        props.filters.category > 0 ||
        props.filters.outlet > 0 ||
        props.filters.status !== 'all',
);

const indexUrl = (
    values: Partial<{
        search: string;
        category: number;
        outlet: number;
        status: 'all' | 'active' | 'inactive';
        per_page: number;
        product: number;
    }> = {},
) => {
    const params = new URLSearchParams();
    const query = values.search ?? search.value.trim();
    const selectedCategoryId = values.category ?? categoryFilter.value;
    const selectedOutletId = values.outlet ?? outletFilter.value;
    const selectedStatus = values.status ?? statusFilter.value;

    if (query) params.set('search', query);
    if (selectedCategoryId > 0)
        params.set('category', String(selectedCategoryId));
    if (selectedOutletId > 0) params.set('outlet', String(selectedOutletId));
    if (selectedStatus !== 'all') params.set('status', selectedStatus);
    params.set('per_page', String(values.per_page ?? perPage.value));
    if (values.product) params.set('product', String(values.product));

    return `/catalog?${params.toString()}`;
};

const applyFilters = () => {
    router.get(
        indexUrl(),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            only: ['products', 'filters'],
        },
    );
};

const setCategoryFilter = (categoryId: number) => {
    categoryFilter.value = categoryId;
    applyFilters();
};

const clearFilters = () => {
    search.value = '';
    categoryFilter.value = 0;
    outletFilter.value = 0;
    statusFilter.value = 'all';
    applyFilters();
};

const openCreateCategory = () => {
    editingCategory.value = null;
    categoryForm.reset();
    categoryForm.clearErrors();
    categorySheetOpen.value = true;
};

const openEditCategory = (category: Category) => {
    editingCategory.value = category;
    categoryForm.name = category.name;
    categoryForm.display_order = category.display_order;
    categoryForm.status = category.status;
    categoryForm.clearErrors();
    categorySheetOpen.value = true;
};

const saveCategory = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            categorySheetOpen.value = false;
            editingCategory.value = null;
        },
    };

    if (editingCategory.value) {
        categoryForm.put(
            `/catalog/categories/${editingCategory.value.id}`,
            options,
        );
        return;
    }

    categoryForm.post('/catalog/categories', options);
};

const fillProductForm = (product: EditingProduct | null) => {
    productForm.clearErrors();
    productForm.category_id =
        product?.category_id ?? props.categories[0]?.id ?? 0;
    productForm.name = product?.name ?? '';
    productForm.description = product?.description ?? null;
    productForm.display_order = product?.display_order ?? 0;
    productForm.status = product?.status ?? 'active';
    productForm.variants = product?.variants.length
        ? product.variants.map((variant) => {
              const outletPrices = new Map(
                  variant.outlet_products?.map((price) => [
                      price.outlet_id,
                      price,
                  ]),
              );

              return {
                  id: variant.id,
                  name: variant.name,
                  sku: variant.sku,
                  barcode: variant.barcode ?? '',
                  sell_price: variant.sell_price,
                  default_purchase_cost: variant.default_purchase_cost ?? '',
                  track_stock: variant.track_stock,
                  status: variant.status,
                  outlets: props.outlets.map((outlet) => ({
                      outlet_id: outlet.id,
                      is_available:
                          outletPrices.get(outlet.id)?.is_available ?? true,
                      price_override:
                          outletPrices.get(outlet.id)?.price_override ?? '',
                  })),
              };
          })
        : [emptyVariant()];
};

const openCreateProduct = () => {
    if (props.categories.length === 0) {
        openCreateCategory();
        return;
    }

    fillProductForm(null);
    editingProductId.value = null;
    productSheetOpen.value = true;
};

const openEditProduct = (product: Product) => {
    router.get(
        indexUrl({ product: product.id }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            only: ['editingProduct'],
        },
    );
};

const closeProductSheet = () => {
    productSheetOpen.value = false;
    editingProductId.value = null;
    productForm.clearErrors();
};

const addVariant = () => {
    productForm.variants.push(
        emptyVariant(`Pilihan ${productForm.variants.length + 1}`),
    );
};

const removeVariant = (index: number) => {
    if (productForm.variants.length > 1 && !productForm.variants[index]?.id) {
        productForm.variants.splice(index, 1);
    }
};

const saveProduct = () => {
    const options = {
        preserveScroll: true,
        onSuccess: closeProductSheet,
    };

    if (editingProductId.value) {
        productForm.put(`/catalog/products/${editingProductId.value}`, options);
        return;
    }

    productForm.post('/catalog/products', options);
};

const errorFor = (key: string) =>
    (productForm.errors as Record<string, string>)[key];

const outletName = (outletId: number) =>
    props.outlets.find((outlet) => outlet.id === outletId)?.name ?? 'Outlet';

const formatCurrency = (value: string | number | null) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: props.business.currency,
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search;
        categoryFilter.value = filters.category;
        outletFilter.value = filters.outlet;
        statusFilter.value = filters.status;
        perPage.value = filters.perPage;
    },
);

watch(
    () => props.editingProduct,
    (product) => {
        if (product) {
            editingProductId.value = product.id;
            fillProductForm(product);
            productSheetOpen.value = true;
        }
    },
    { immediate: true },
);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Katalog', href: '/catalog' }],
    },
});
</script>

<template>
    <Head title="Katalog" />

    <div class="flex flex-1 flex-col">
        <section
            class="relative overflow-hidden border-b border-white/10 bg-[#163f35] px-5 py-6 text-white md:px-8"
            aria-label="Ringkasan katalog"
        >
            <div
                aria-hidden="true"
                class="absolute inset-0 opacity-[0.07]"
                style="
                    background-image: linear-gradient(
                        to right,
                        white 1px,
                        transparent 1px
                    );
                    background-size: 5rem 100%;
                "
            />
            <div
                aria-hidden="true"
                class="absolute -top-24 right-[8%] size-72 rounded-full border border-white/10"
            />
            <div
                class="catalog-reveal relative mx-auto flex w-full max-w-7xl flex-col gap-6"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-semibold tracking-[0.18em] text-[#e4ba76] uppercase"
                        >
                            {{ business.name }} / data produk
                        </p>
                        <h1
                            class="mt-2 text-3xl font-semibold tracking-[-0.04em]"
                        >
                            Katalog kerja
                        </h1>
                        <p
                            class="mt-2 max-w-xl text-sm leading-6 text-white/70"
                        >
                            Atur produk, pilihan, dan harga jual untuk seluruh
                            outlet.
                        </p>
                    </div>
                    <div v-if="canManageCatalog" class="flex gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white"
                            @click="openCreateCategory"
                        >
                            <Tags class="size-4" />
                            Kategori
                        </Button>
                        <Button
                            type="button"
                            class="bg-[#d49a43] text-[#17352e] hover:bg-[#e1ad5e]"
                            @click="openCreateProduct"
                        >
                            <Plus class="size-4" />
                            Tambah produk
                        </Button>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 overflow-hidden rounded-2xl border border-white/15 bg-black/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] sm:grid-cols-4"
                >
                    <div
                        class="flex items-center justify-between border-r border-b border-white/10 px-4 py-3.5 sm:border-b-0"
                    >
                        <div>
                            <p class="text-xs text-white/55">Produk</p>
                            <p class="mt-1 text-xl font-semibold tabular-nums">
                                {{ summary.products }}
                            </p>
                        </div>
                        <PackageOpen class="size-4 text-[#e4ba76]" />
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-white/10 px-4 py-3.5 sm:border-r sm:border-b-0"
                    >
                        <div>
                            <p class="text-xs text-white/55">Pilihan produk</p>
                            <p class="mt-1 text-xl font-semibold tabular-nums">
                                {{ summary.variants }}
                            </p>
                        </div>
                        <Boxes class="size-4 text-[#e4ba76]" />
                    </div>
                    <div
                        class="flex items-center justify-between border-r border-white/10 px-4 py-3.5"
                    >
                        <div>
                            <p class="text-xs text-white/55">Kategori</p>
                            <p class="mt-1 text-xl font-semibold tabular-nums">
                                {{ summary.categories }}
                            </p>
                        </div>
                        <Tags class="size-4 text-[#e4ba76]" />
                    </div>
                    <div class="flex items-center justify-between px-4 py-3.5">
                        <div>
                            <p class="text-xs text-white/55">Produk aktif</p>
                            <p class="mt-1 text-xl font-semibold tabular-nums">
                                {{ summary.active }}
                            </p>
                        </div>
                        <CheckCircle2 class="size-4 text-[#e4ba76]" />
                    </div>
                </div>
            </div>
        </section>

        <main class="mx-auto w-full max-w-7xl space-y-5 p-5 md:p-8">
            <form
                class="catalog-reveal catalog-reveal-delay-1 grid gap-3 rounded-2xl border border-[#163f35]/10 bg-background/95 p-3 shadow-[0_8px_30px_rgba(22,63,53,0.06)] backdrop-blur md:grid-cols-[minmax(14rem,1fr)_11rem_11rem_9rem_auto] lg:sticky lg:top-4 lg:z-10 dark:border-white/10"
                @submit.prevent="applyFilters"
            >
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        class="h-10 pr-9 pl-9"
                        placeholder="Cari nama, kode produk, atau barcode"
                        aria-label="Cari katalog"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute top-1/2 right-3 -translate-y-1/2 rounded text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        aria-label="Hapus pencarian"
                        @click="
                            search = '';
                            applyFilters();
                        "
                    >
                        <X class="size-4" />
                    </button>
                </div>
                <select
                    v-model.number="outletFilter"
                    class="h-10 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                    aria-label="Filter outlet"
                    @change="applyFilters"
                >
                    <option :value="0">Semua outlet</option>
                    <option
                        v-for="outlet in outlets"
                        :key="outlet.id"
                        :value="outlet.id"
                    >
                        {{ outlet.name }}
                    </option>
                </select>
                <select
                    v-model="statusFilter"
                    class="h-10 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                    aria-label="Filter status"
                    @change="applyFilters"
                >
                    <option value="all">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
                <select
                    v-model.number="perPage"
                    class="h-10 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                    aria-label="Data per halaman"
                    @change="applyFilters"
                >
                    <option :value="10">10 / halaman</option>
                    <option :value="25">25 / halaman</option>
                    <option :value="50">50 / halaman</option>
                </select>
                <Button
                    type="submit"
                    class="h-10 bg-[#163f35] text-white hover:bg-[#205548]"
                >
                    Terapkan
                </Button>
            </form>

            <div
                class="catalog-reveal catalog-reveal-delay-2 grid gap-5 lg:grid-cols-[15rem_minmax(0,1fr)]"
            >
                <aside class="min-w-0" aria-label="Kategori produk">
                    <div
                        class="rounded-2xl border border-[#163f35]/10 bg-card p-2 shadow-[0_10px_30px_rgba(22,63,53,0.05)] lg:sticky lg:top-24 dark:border-white/10"
                    >
                        <div
                            class="flex items-center justify-between px-3 py-2"
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                Kategori
                            </p>
                            <Button
                                v-if="canManageCatalog"
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="size-8"
                                aria-label="Tambah kategori"
                                @click="openCreateCategory"
                            >
                                <Plus class="size-4" />
                            </Button>
                        </div>
                        <div
                            class="flex gap-2 overflow-x-auto pb-1 lg:block lg:space-y-1 lg:overflow-visible"
                        >
                            <button
                                type="button"
                                class="flex min-w-40 items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm transition-all duration-200 lg:w-full"
                                :class="
                                    categoryFilter === 0
                                        ? 'bg-[#163f35] text-white'
                                        : 'hover:bg-muted'
                                "
                                @click="setCategoryFilter(0)"
                            >
                                <span>Semua produk</span>
                                <span class="text-xs tabular-nums opacity-70">
                                    {{ summary.products }}
                                </span>
                            </button>
                            <div
                                v-for="category in categories"
                                :key="category.id"
                                class="group flex min-w-40 items-center rounded-xl transition-all duration-200 lg:min-w-0"
                                :class="
                                    categoryFilter === category.id
                                        ? 'bg-[#163f35] text-white'
                                        : 'hover:bg-muted'
                                "
                            >
                                <button
                                    type="button"
                                    class="flex min-w-0 flex-1 items-center justify-between gap-2 px-3 py-2.5 text-left text-sm"
                                    @click="setCategoryFilter(category.id)"
                                >
                                    <span class="truncate">{{
                                        category.name
                                    }}</span>
                                    <span
                                        class="text-xs tabular-nums opacity-65"
                                    >
                                        {{ category.products_count }}
                                    </span>
                                </button>
                                <button
                                    v-if="canManageCatalog"
                                    type="button"
                                    class="mr-1 grid size-8 shrink-0 place-items-center rounded-lg opacity-60 hover:bg-white/10 hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    :aria-label="`Ubah kategori ${category.name}`"
                                    @click="openEditCategory(category)"
                                >
                                    <Pencil class="size-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>

                <section
                    class="min-w-0 space-y-4"
                    aria-labelledby="product-list-title"
                >
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-semibold tracking-[0.14em] text-[#a66d20] uppercase dark:text-[#d3a052]"
                            >
                                {{ selectedCategory?.name ?? 'Semua kategori' }}
                            </p>
                            <h2
                                id="product-list-title"
                                class="mt-1 text-xl font-semibold tracking-tight"
                            >
                                Daftar produk
                            </h2>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ products.total }} produk ditemukan
                        </p>
                    </div>

                    <div
                        v-if="products.total === 0"
                        class="rounded-2xl border border-dashed bg-card px-6 py-16 text-center"
                    >
                        <PackageOpen
                            class="mx-auto size-9 text-muted-foreground"
                        />
                        <h3 class="mt-4 font-semibold">
                            {{
                                hasFilters
                                    ? 'Produk tidak ditemukan'
                                    : 'Katalog masih kosong'
                            }}
                        </h3>
                        <p
                            class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted-foreground"
                        >
                            {{
                                hasFilters
                                    ? 'Ubah kata kunci atau filter untuk melihat produk lain.'
                                    : 'Tambahkan kategori, lalu buat produk pertama beserta kode produk dan harga jual tiap outlet.'
                            }}
                        </p>
                        <div class="mt-5 flex justify-center gap-2">
                            <Button
                                v-if="hasFilters"
                                type="button"
                                variant="outline"
                                @click="clearFilters"
                            >
                                Bersihkan filter
                            </Button>
                            <Button
                                v-else-if="canManageCatalog"
                                type="button"
                                class="bg-[#a66d20] text-white hover:bg-[#8d5c1b]"
                                @click="openCreateProduct"
                            >
                                <Plus class="size-4" />
                                Tambah produk
                            </Button>
                        </div>
                    </div>

                    <template v-else>
                        <div
                            class="hidden overflow-hidden rounded-2xl border border-[#163f35]/10 bg-card shadow-[0_12px_35px_rgba(22,63,53,0.05)] md:block dark:border-white/10"
                        >
                            <table class="w-full text-left text-sm">
                                <thead
                                    class="border-b bg-[#f8f6ef] text-xs text-muted-foreground dark:bg-[#17201d]"
                                >
                                    <tr>
                                        <th class="px-5 py-3 font-medium">
                                            Produk
                                        </th>
                                        <th class="px-4 py-3 font-medium">
                                            Kode produk
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-medium"
                                        >
                                            Harga jual
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-medium"
                                        >
                                            Pilihan
                                        </th>
                                        <th class="px-4 py-3 font-medium">
                                            Status
                                        </th>
                                        <th class="w-14 px-4 py-3">
                                            <span class="sr-only">Aksi</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr
                                        v-for="product in products.data"
                                        :key="product.id"
                                        class="group transition-colors duration-200 hover:bg-muted/35"
                                    >
                                        <td class="px-5 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <span
                                                    class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#163f35]/8 text-[#163f35] dark:bg-[#8db7aa]/10 dark:text-[#8db7aa]"
                                                >
                                                    <PackageOpen
                                                        class="size-4"
                                                    />
                                                </span>
                                                <span class="min-w-0">
                                                    <span
                                                        class="block truncate font-semibold"
                                                        >{{
                                                            product.name
                                                        }}</span
                                                    >
                                                    <span
                                                        class="mt-0.5 block truncate text-xs text-muted-foreground"
                                                        >{{
                                                            product.category
                                                                .name
                                                        }}</span
                                                    >
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 font-mono text-xs">
                                            {{
                                                product.default_variant?.sku ??
                                                '—'
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right font-medium tabular-nums"
                                        >
                                            {{
                                                formatCurrency(
                                                    product.default_variant
                                                        ?.sell_price ?? 0,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center tabular-nums"
                                        >
                                            {{ product.variants_count }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <Badge
                                                variant="outline"
                                                :class="
                                                    product.status === 'active'
                                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                                        : 'text-muted-foreground'
                                                "
                                            >
                                                {{
                                                    product.status === 'active'
                                                        ? 'Aktif'
                                                        : 'Nonaktif'
                                                }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <Button
                                                v-if="canManageCatalog"
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                :aria-label="`Ubah ${product.name}`"
                                                @click="
                                                    openEditProduct(product)
                                                "
                                            >
                                                <Pencil class="size-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="space-y-3 md:hidden">
                            <article
                                v-for="product in products.data"
                                :key="product.id"
                                class="rounded-2xl border border-[#163f35]/10 bg-card p-4 shadow-sm transition-[transform,box-shadow,border-color] duration-200 hover:-translate-y-0.5 hover:border-[#a66d20]/30 hover:shadow-md motion-reduce:transform-none dark:border-white/10"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold">
                                            {{ product.name }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            {{ product.category.name }} ·
                                            {{
                                                product.default_variant?.sku ??
                                                'Tanpa kode'
                                            }}
                                        </p>
                                    </div>
                                    <Button
                                        v-if="canManageCatalog"
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="-mt-2 -mr-2"
                                        :aria-label="`Ubah ${product.name}`"
                                        @click="openEditProduct(product)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                </div>
                                <div
                                    class="mt-4 flex items-end justify-between border-t pt-3"
                                >
                                    <div>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Harga jual
                                        </p>
                                        <p
                                            class="mt-1 font-semibold tabular-nums"
                                        >
                                            {{
                                                formatCurrency(
                                                    product.default_variant
                                                        ?.sell_price ?? 0,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <Badge variant="outline"
                                        >{{
                                            product.variants_count
                                        }}
                                        pilihan</Badge
                                    >
                                </div>
                            </article>
                        </div>

                        <footer
                            class="flex flex-col gap-3 rounded-xl border bg-card px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between"
                        >
                            <p class="text-muted-foreground">
                                Menampilkan
                                <span class="font-medium text-foreground"
                                    >{{ products.from }}–{{ products.to }}</span
                                >
                                dari
                                <span class="font-medium text-foreground">{{
                                    products.total
                                }}</span>
                                produk
                            </p>
                            <div class="flex items-center gap-2">
                                <Button
                                    v-if="products.prev_page_url"
                                    variant="outline"
                                    size="sm"
                                    as-child
                                >
                                    <Link
                                        :href="products.prev_page_url"
                                        preserve-scroll
                                        :only="['products', 'filters']"
                                    >
                                        <ChevronLeft class="size-4" />
                                        Sebelumnya
                                    </Link>
                                </Button>
                                <Button
                                    v-else
                                    variant="outline"
                                    size="sm"
                                    disabled
                                >
                                    <ChevronLeft class="size-4" /> Sebelumnya
                                </Button>
                                <span
                                    class="min-w-20 text-center text-xs text-muted-foreground"
                                >
                                    {{ products.current_page }} /
                                    {{ products.last_page }}
                                </span>
                                <Button
                                    v-if="products.next_page_url"
                                    variant="outline"
                                    size="sm"
                                    as-child
                                >
                                    <Link
                                        :href="products.next_page_url"
                                        preserve-scroll
                                        :only="['products', 'filters']"
                                    >
                                        Berikutnya
                                        <ChevronRight class="size-4" />
                                    </Link>
                                </Button>
                                <Button
                                    v-else
                                    variant="outline"
                                    size="sm"
                                    disabled
                                >
                                    Berikutnya <ChevronRight class="size-4" />
                                </Button>
                            </div>
                        </footer>
                    </template>
                </section>
            </div>
        </main>

        <Sheet v-model:open="categorySheetOpen">
            <SheetContent side="right" class="w-full gap-0 sm:max-w-lg">
                <form
                    class="flex min-h-0 flex-1 flex-col"
                    @submit.prevent="saveCategory"
                >
                    <SheetHeader
                        class="border-b bg-[#f8f6ef]/80 px-5 py-5 text-left backdrop-blur dark:bg-[#17201d]/80"
                    >
                        <SheetTitle>{{
                            editingCategory
                                ? 'Ubah kategori'
                                : 'Tambah kategori'
                        }}</SheetTitle>
                        <SheetDescription>
                            Kategori menjaga pencarian POS dan laporan produk
                            tetap ringkas.
                        </SheetDescription>
                    </SheetHeader>
                    <div
                        class="flex-1 space-y-5 overflow-y-auto scroll-smooth px-5 py-5"
                    >
                        <div class="grid gap-2">
                            <Label for="category-name">Nama kategori</Label>
                            <Input
                                id="category-name"
                                v-model="categoryForm.name"
                                maxlength="100"
                                placeholder="Kopi"
                                required
                                autofocus
                                :aria-invalid="
                                    categoryForm.errors.name ? true : undefined
                                "
                            />
                            <InputError :message="categoryForm.errors.name" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="category-order"
                                    >Urutan tampil</Label
                                >
                                <Input
                                    id="category-order"
                                    v-model.number="categoryForm.display_order"
                                    type="number"
                                    min="0"
                                    max="65535"
                                    required
                                />
                                <InputError
                                    :message="categoryForm.errors.display_order"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="category-status">Status</Label>
                                <select
                                    id="category-status"
                                    v-model="categoryForm.status"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                                <InputError
                                    :message="categoryForm.errors.status"
                                />
                            </div>
                        </div>
                    </div>
                    <SheetFooter
                        class="border-t bg-background/90 px-5 py-4 backdrop-blur"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="categorySheetOpen = false"
                            >Batal</Button
                        >
                        <Button
                            type="submit"
                            class="bg-[#163f35] text-white hover:bg-[#205548]"
                            :disabled="categoryForm.processing"
                        >
                            {{
                                categoryForm.processing
                                    ? 'Menyimpan…'
                                    : 'Simpan kategori'
                            }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <Sheet
            :open="productSheetOpen"
            @update:open="(open) => !open && closeProductSheet()"
        >
            <SheetContent side="right" class="w-full gap-0 sm:max-w-4xl">
                <form
                    class="flex min-h-0 flex-1 flex-col"
                    @submit.prevent="saveProduct"
                >
                    <SheetHeader
                        class="border-b bg-[#f8f6ef]/80 px-5 py-5 text-left backdrop-blur md:px-7 dark:bg-[#17201d]/80"
                    >
                        <SheetTitle>{{
                            editingProductId ? 'Ubah produk' : 'Tambah produk'
                        }}</SheetTitle>
                        <SheetDescription>
                            Pilihan pertama menjadi pilihan utama. Kosongkan
                            harga jual outlet jika nilainya sama.
                        </SheetDescription>
                    </SheetHeader>

                    <div
                        class="min-h-0 flex-1 space-y-7 overflow-y-auto scroll-smooth px-5 py-6 md:px-7"
                    >
                        <section
                            class="grid gap-5 rounded-2xl border border-[#163f35]/10 bg-[#f8f6ef]/45 p-4 md:grid-cols-2 md:p-5 dark:border-white/10 dark:bg-[#17201d]/45"
                            aria-labelledby="product-information"
                        >
                            <div class="md:col-span-2">
                                <p
                                    id="product-information"
                                    class="text-xs font-semibold tracking-[0.14em] text-[#a66d20] uppercase dark:text-[#d3a052]"
                                >
                                    Informasi produk
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="product-name">Nama produk</Label>
                                <Input
                                    id="product-name"
                                    v-model="productForm.name"
                                    maxlength="255"
                                    placeholder="Cafe Latte"
                                    required
                                    autofocus
                                    :aria-invalid="
                                        productForm.errors.name
                                            ? true
                                            : undefined
                                    "
                                />
                                <InputError
                                    :message="productForm.errors.name"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="product-category">Kategori</Label>
                                <select
                                    id="product-category"
                                    v-model.number="productForm.category_id"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                                    required
                                >
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="productForm.errors.category_id"
                                />
                            </div>
                            <div class="grid gap-2 md:col-span-2">
                                <Label for="product-description"
                                    >Deskripsi
                                    <span
                                        class="font-normal text-muted-foreground"
                                        >(opsional)</span
                                    ></Label
                                >
                                <textarea
                                    id="product-description"
                                    v-model="productForm.description"
                                    rows="3"
                                    maxlength="2000"
                                    placeholder="Keterangan singkat untuk tim operasional"
                                    class="min-h-24 w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 dark:bg-input/30"
                                />
                                <InputError
                                    :message="productForm.errors.description"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="product-order">Urutan tampil</Label>
                                <Input
                                    id="product-order"
                                    v-model.number="productForm.display_order"
                                    type="number"
                                    min="0"
                                    max="65535"
                                    required
                                />
                                <InputError
                                    :message="productForm.errors.display_order"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="product-status">Status</Label>
                                <select
                                    id="product-status"
                                    v-model="productForm.status"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                                <InputError
                                    :message="productForm.errors.status"
                                />
                            </div>
                        </section>

                        <section
                            class="space-y-4"
                            aria-labelledby="variants-title"
                        >
                            <div
                                class="flex items-end justify-between gap-4 border-t pt-6"
                            >
                                <div>
                                    <p
                                        class="text-xs font-semibold tracking-[0.14em] text-[#a66d20] uppercase dark:text-[#d3a052]"
                                    >
                                        Pilihan produk
                                    </p>
                                    <h3
                                        id="variants-title"
                                        class="mt-1 font-semibold"
                                    >
                                        Pilihan dan kode produk
                                    </h3>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="addVariant"
                                >
                                    <Plus class="size-4" /> Tambah pilihan
                                </Button>
                            </div>
                            <InputError
                                :message="productForm.errors.variants"
                            />

                            <article
                                v-for="(
                                    variant, variantIndex
                                ) in productForm.variants"
                                :key="variant.id ?? `new-${variantIndex}`"
                                class="overflow-hidden rounded-2xl border bg-card"
                            >
                                <div
                                    class="flex items-center justify-between border-b bg-[#f8f6ef] px-4 py-3 dark:bg-[#17201d]"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="grid size-8 place-items-center rounded-lg bg-[#163f35] text-xs font-semibold text-white"
                                            >{{ variantIndex + 1 }}</span
                                        >
                                        <div>
                                            <p class="text-sm font-semibold">
                                                {{
                                                    variant.name ||
                                                    `Pilihan ${variantIndex + 1}`
                                                }}
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{
                                                    variantIndex === 0
                                                        ? 'Pilihan utama'
                                                        : 'Pilihan tambahan'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        v-if="
                                            productForm.variants.length > 1 &&
                                            !variant.id
                                        "
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="text-muted-foreground hover:text-destructive"
                                        :aria-label="`Hapus pilihan ${variantIndex + 1}`"
                                        @click="removeVariant(variantIndex)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>

                                <div class="grid gap-4 p-4 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`variant-name-${variantIndex}`"
                                            >Nama pilihan</Label
                                        >
                                        <Input
                                            :id="`variant-name-${variantIndex}`"
                                            v-model="variant.name"
                                            maxlength="100"
                                            placeholder="Reguler"
                                            required
                                        />
                                        <InputError
                                            :message="
                                                errorFor(
                                                    `variants.${variantIndex}.name`,
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`variant-sku-${variantIndex}`"
                                            >Kode produk</Label
                                        >
                                        <Input
                                            :id="`variant-sku-${variantIndex}`"
                                            v-model="variant.sku"
                                            maxlength="64"
                                            placeholder="LATTE-REG"
                                            class="uppercase"
                                            required
                                        />
                                        <InputError
                                            :message="
                                                errorFor(
                                                    `variants.${variantIndex}.sku`,
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`variant-price-${variantIndex}`"
                                            >Harga jual</Label
                                        >
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-xs text-muted-foreground"
                                                >Rp</span
                                            >
                                            <Input
                                                :id="`variant-price-${variantIndex}`"
                                                v-model="variant.sell_price"
                                                type="number"
                                                min="0"
                                                step="1"
                                                inputmode="decimal"
                                                class="pl-9"
                                                placeholder="28000"
                                                required
                                            />
                                        </div>
                                        <InputError
                                            :message="
                                                errorFor(
                                                    `variants.${variantIndex}.sell_price`,
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`variant-barcode-${variantIndex}`"
                                            >Barcode
                                            <span
                                                class="font-normal text-muted-foreground"
                                                >(opsional)</span
                                            ></Label
                                        >
                                        <Input
                                            :id="`variant-barcode-${variantIndex}`"
                                            v-model="variant.barcode"
                                            maxlength="64"
                                            placeholder="899…"
                                        />
                                        <InputError
                                            :message="
                                                errorFor(
                                                    `variants.${variantIndex}.barcode`,
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`variant-cost-${variantIndex}`"
                                            >Harga modal
                                            <span
                                                class="font-normal text-muted-foreground"
                                                >(opsional)</span
                                            ></Label
                                        >
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-xs text-muted-foreground"
                                                >Rp</span
                                            >
                                            <Input
                                                :id="`variant-cost-${variantIndex}`"
                                                v-model="
                                                    variant.default_purchase_cost
                                                "
                                                type="number"
                                                min="0"
                                                step="1"
                                                inputmode="decimal"
                                                class="pl-9"
                                                placeholder="12000"
                                            />
                                        </div>
                                        <InputError
                                            :message="
                                                errorFor(
                                                    `variants.${variantIndex}.default_purchase_cost`,
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`variant-status-${variantIndex}`"
                                            >Status pilihan</Label
                                        >
                                        <select
                                            :id="`variant-status-${variantIndex}`"
                                            v-model="variant.status"
                                            class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                                        >
                                            <option value="active">
                                                Aktif
                                            </option>
                                            <option value="inactive">
                                                Nonaktif
                                            </option>
                                        </select>
                                        <InputError
                                            :message="
                                                errorFor(
                                                    `variants.${variantIndex}.status`,
                                                )
                                            "
                                        />
                                    </div>
                                </div>

                                <div class="border-t px-4 py-4">
                                    <div class="mb-3 flex items-center gap-2">
                                        <CircleDollarSign
                                            class="size-4 text-[#a66d20]"
                                        />
                                        <p class="text-sm font-semibold">
                                            Harga jual per outlet
                                        </p>
                                    </div>
                                    <InputError
                                        :message="
                                            errorFor(
                                                `variants.${variantIndex}.outlets`,
                                            )
                                        "
                                    />
                                    <div class="divide-y rounded-xl border">
                                        <div
                                            v-for="(
                                                outletPrice, outletIndex
                                            ) in variant.outlets"
                                            :key="outletPrice.outlet_id"
                                            class="grid gap-3 px-3 py-3 sm:grid-cols-[minmax(8rem,1fr)_auto_12rem] sm:items-center"
                                        >
                                            <div
                                                class="flex items-center gap-2 text-sm"
                                            >
                                                <Store
                                                    class="size-4 text-muted-foreground"
                                                />
                                                <span class="font-medium">{{
                                                    outletName(
                                                        outletPrice.outlet_id,
                                                    )
                                                }}</span>
                                            </div>
                                            <label
                                                class="inline-flex items-center gap-2 text-xs text-muted-foreground"
                                            >
                                                <input
                                                    v-model="
                                                        outletPrice.is_available
                                                    "
                                                    type="checkbox"
                                                    class="size-4 rounded border-input accent-[#163f35]"
                                                />
                                                Dijual
                                            </label>
                                            <div class="relative">
                                                <span
                                                    class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-xs text-muted-foreground"
                                                    >Rp</span
                                                >
                                                <Input
                                                    v-model="
                                                        outletPrice.price_override
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    inputmode="decimal"
                                                    class="pl-9"
                                                    placeholder="Sama dengan harga jual"
                                                    :aria-label="`Harga ${outletName(outletPrice.outlet_id)}`"
                                                />
                                            </div>
                                            <InputError
                                                class="sm:col-start-3"
                                                :message="
                                                    errorFor(
                                                        `variants.${variantIndex}.outlets.${outletIndex}.price_override`,
                                                    )
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </section>
                    </div>

                    <SheetFooter
                        class="border-t bg-background/90 px-5 py-4 backdrop-blur md:px-7"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeProductSheet"
                            >Batal</Button
                        >
                        <Button
                            type="submit"
                            class="bg-[#163f35] text-white hover:bg-[#205548]"
                            :disabled="productForm.processing"
                        >
                            {{
                                productForm.processing
                                    ? 'Menyimpan…'
                                    : 'Simpan produk'
                            }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    </div>
</template>

<style scoped>
@keyframes catalog-reveal {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: no-preference) {
    .catalog-reveal {
        animation: catalog-reveal 420ms cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .catalog-reveal-delay-1 {
        animation-delay: 70ms;
    }

    .catalog-reveal-delay-2 {
        animation-delay: 140ms;
    }
}
</style>
