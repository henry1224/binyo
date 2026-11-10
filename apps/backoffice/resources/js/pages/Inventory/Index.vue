<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Boxes,
    ChevronLeft,
    ChevronRight,
    CircleDollarSign,
    ClipboardList,
    PackageCheck,
    Plus,
    Search,
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

type Outlet = { id: number; code: string; name: string };
type Unit = { id: number; code: string; name: string };
type Balance = {
    id: number;
    outlet_id: number;
    quantity_on_hand: string;
    average_cost: string;
};
type Variant = {
    id: number;
    sku: string;
    name: string;
    default_purchase_cost: string | null;
    status: string;
    has_movements: boolean;
    product: {
        id: number;
        name: string;
        product_type: string;
        status: string;
    };
    base_unit: Unit;
    inventory_balances: Balance[];
};
type Movement = {
    id: number;
    movement_type: string;
    quantity_delta: string;
    unit_cost_snapshot: string;
    value_delta: string;
    occurred_at: string;
    creator: { id: number; name: string };
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
    outlets: Outlet[];
    variants: Paginated<Variant>;
    selectedVariant: Omit<
        Variant,
        | 'default_purchase_cost'
        | 'status'
        | 'has_movements'
        | 'inventory_balances'
    > | null;
    movements: Movement[];
    summary: {
        trackable: number;
        stocked: number;
        empty: number;
        value: string | number;
    };
    filters: { search: string; outlet: number; perPage: number };
}>();

const search = ref(props.filters.search);
const outletFilter = ref(props.filters.outlet);
const perPage = ref(props.filters.perPage);
const openingSheetOpen = ref(false);
const openingVariant = ref<Variant | null>(null);
const selectedOutlet = computed(
    () =>
        props.outlets.find((outlet) => outlet.id === outletFilter.value) ??
        props.outlets[0],
);
const openingTotal = computed(() =>
    Number(openingForm.quantity) && Number(openingForm.unit_cost)
        ? Number(openingForm.quantity) * Number(openingForm.unit_cost)
        : 0,
);

const openingForm = useForm({
    outlet_id: props.filters.outlet,
    product_variant_id: 0,
    quantity: '',
    unit_cost: '',
});

const indexUrl = (
    values: Partial<{
        search: string;
        outlet: number;
        per_page: number;
        variant: number;
    }> = {},
) => {
    const params = new URLSearchParams();
    const query = values.search ?? search.value.trim();

    params.set('outlet', String(values.outlet ?? outletFilter.value));
    params.set('per_page', String(values.per_page ?? perPage.value));

    if (query) params.set('search', query);
    if (values.variant ?? props.selectedVariant?.id)
        params.set(
            'variant',
            String(values.variant ?? props.selectedVariant?.id),
        );

    return `/inventory?${params.toString()}`;
};

const applyFilters = () => {
    router.get(
        indexUrl({ variant: 0 }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const clearSearch = () => {
    search.value = '';
    applyFilters();
};

const openOpeningStock = (variant: Variant) => {
    openingVariant.value = variant;
    openingForm.reset();
    openingForm.clearErrors();
    openingForm.outlet_id = outletFilter.value;
    openingForm.product_variant_id = variant.id;
    openingForm.unit_cost = variant.default_purchase_cost ?? '';
    openingSheetOpen.value = true;
};

const closeOpeningStock = () => {
    openingSheetOpen.value = false;
    openingVariant.value = null;
    openingForm.reset();
    openingForm.clearErrors();
};

const saveOpeningStock = () => {
    openingForm.post('/inventory/opening-stock', {
        preserveScroll: true,
        onSuccess: closeOpeningStock,
    });
};

const balanceFor = (variant: Variant) => variant.inventory_balances[0];
const formatQuantity = (value: string | number) =>
    new Intl.NumberFormat('id-ID', { maximumFractionDigits: 6 }).format(
        Number(value),
    );
const formatCurrency = (value: string | number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: props.business.currency,
        maximumFractionDigits: 2,
    }).format(Number(value));
const dateTime = (value: string) =>
    new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search;
        outletFilter.value = filters.outlet;
        perPage.value = filters.perPage;
    },
);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Persediaan', href: '/inventory' }],
    },
});
</script>

<template>
    <Head title="Persediaan" />

    <div class="flex flex-1 flex-col">
        <section
            class="relative overflow-hidden border-b border-white/10 bg-[#163f35] px-5 py-6 text-white md:px-8"
            aria-label="Ringkasan persediaan"
        >
            <div
                aria-hidden="true"
                class="absolute -top-20 right-8 size-56 rounded-full border border-white/10"
            />
            <div class="relative mx-auto w-full max-w-7xl">
                <p
                    class="text-xs font-semibold tracking-[0.18em] text-[#e4b15e] uppercase"
                >
                    {{ business.name }} / Kontrol stok
                </p>
                <div
                    class="mt-2 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Persediaan kerja
                        </h1>
                        <p class="mt-1 max-w-xl text-sm text-white/65">
                            Saldo cepat mengikuti kartu stok. Stok awal hanya
                            dapat dicatat sekali per produk dan outlet.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <div class="rounded-xl border border-white/10 p-3">
                            <p class="text-[11px] text-white/55">
                                Item berstok
                            </p>
                            <p class="mt-1 font-semibold tabular-nums">
                                {{ summary.trackable }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-white/10 p-3">
                            <p class="text-[11px] text-white/55">
                                Sudah terisi
                            </p>
                            <p class="mt-1 font-semibold tabular-nums">
                                {{ summary.stocked }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-white/10 p-3">
                            <p class="text-[11px] text-white/55">
                                Belum terisi
                            </p>
                            <p class="mt-1 font-semibold tabular-nums">
                                {{ summary.empty }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-white/10 p-3">
                            <p class="text-[11px] text-white/55">Nilai stok</p>
                            <p class="mt-1 text-sm font-semibold tabular-nums">
                                {{ formatCurrency(summary.value) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="flex-1 px-4 py-5 md:px-8 md:py-7">
            <div class="mx-auto w-full max-w-7xl space-y-5">
                <section
                    class="grid gap-3 rounded-2xl border bg-card p-3 shadow-sm md:grid-cols-[minmax(14rem,1fr)_15rem_8rem]"
                    aria-label="Filter persediaan"
                >
                    <form class="relative" @submit.prevent="applyFilters">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            class="pr-10 pl-9"
                            placeholder="Cari produk, pilihan, atau kode"
                        />
                        <button
                            v-if="search"
                            type="button"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            aria-label="Hapus pencarian"
                            @click="clearSearch"
                        >
                            <X class="size-4" />
                        </button>
                    </form>
                    <select
                        v-model.number="outletFilter"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        aria-label="Pilih outlet"
                        @change="applyFilters"
                    >
                        <option
                            v-for="outlet in outlets"
                            :key="outlet.id"
                            :value="outlet.id"
                        >
                            {{ outlet.name }}
                        </option>
                    </select>
                    <select
                        v-model.number="perPage"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        aria-label="Data per halaman"
                        @change="applyFilters"
                    >
                        <option :value="10">10 data</option>
                        <option :value="25">25 data</option>
                        <option :value="50">50 data</option>
                    </select>
                </section>

                <section class="overflow-hidden rounded-2xl border bg-card">
                    <div
                        class="flex items-center justify-between gap-4 border-b px-4 py-4 md:px-5"
                    >
                        <div>
                            <p class="text-sm font-semibold">Posisi stok</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ selectedOutlet?.name }} ·
                                {{ variants.total }}
                                pilihan produk
                            </p>
                        </div>
                        <Boxes class="size-5 text-[#a66d20]" />
                    </div>

                    <div v-if="variants.data.length" class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead class="bg-muted/45 text-left text-xs">
                                <tr>
                                    <th class="px-4 py-3 font-medium">
                                        Produk
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Satuan
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Stok
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Harga modal
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Nilai
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Tindakan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="variant in variants.data"
                                    :key="variant.id"
                                    class="hover:bg-muted/25"
                                >
                                    <td class="px-4 py-4">
                                        <p class="font-medium">
                                            {{ variant.product.name }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            {{ variant.name }} ·
                                            {{ variant.sku }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        {{ variant.base_unit.name }}
                                        <span class="text-muted-foreground"
                                            >({{
                                                variant.base_unit.code
                                            }})</span
                                        >
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-semibold tabular-nums"
                                    >
                                        {{
                                            formatQuantity(
                                                balanceFor(variant)
                                                    ?.quantity_on_hand ?? 0,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right tabular-nums"
                                    >
                                        {{
                                            formatCurrency(
                                                balanceFor(variant)
                                                    ?.average_cost ?? 0,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right tabular-nums"
                                    >
                                        {{
                                            formatCurrency(
                                                Number(
                                                    balanceFor(variant)
                                                        ?.quantity_on_hand ?? 0,
                                                ) *
                                                    Number(
                                                        balanceFor(variant)
                                                            ?.average_cost ?? 0,
                                                    ),
                                            )
                                        }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                as-child
                                            >
                                                <Link
                                                    :href="
                                                        indexUrl({
                                                            variant: variant.id,
                                                        })
                                                    "
                                                >
                                                    <ClipboardList
                                                        class="size-4"
                                                    />
                                                    Kartu stok
                                                </Link>
                                            </Button>
                                            <Button
                                                v-if="!variant.has_movements"
                                                size="sm"
                                                class="bg-[#163f35] text-white hover:bg-[#205548]"
                                                @click="
                                                    openOpeningStock(variant)
                                                "
                                            >
                                                <Plus class="size-4" /> Stok
                                                awal
                                            </Button>
                                            <Badge v-else variant="outline"
                                                >Sudah dicatat</Badge
                                            >
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-5 py-14 text-center">
                        <PackageCheck
                            class="mx-auto size-8 text-muted-foreground"
                        />
                        <p class="mt-3 font-medium">
                            Produk berstok tidak ditemukan
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Aktifkan pelacakan stok pada produk jual langsung
                            atau buat bahan/kemasan.
                        </p>
                    </div>

                    <footer
                        v-if="variants.last_page > 1"
                        class="flex items-center justify-between gap-3 border-t px-4 py-3 text-xs text-muted-foreground"
                    >
                        <span
                            >{{ variants.from }}–{{ variants.to }} dari
                            {{ variants.total }}</span
                        >
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!variants.prev_page_url"
                                as-child
                            >
                                <Link :href="variants.prev_page_url ?? '#'">
                                    <ChevronLeft class="size-4" /> Sebelumnya
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!variants.next_page_url"
                                as-child
                            >
                                <Link :href="variants.next_page_url ?? '#'">
                                    Berikutnya <ChevronRight class="size-4" />
                                </Link>
                            </Button>
                        </div>
                    </footer>
                </section>

                <section class="overflow-hidden rounded-2xl border bg-card">
                    <div class="border-b px-4 py-4 md:px-5">
                        <p
                            class="text-xs font-semibold tracking-[0.14em] text-[#a66d20] uppercase dark:text-[#d3a052]"
                        >
                            Kartu stok
                        </p>
                        <h2 class="mt-1 font-semibold">
                            <template v-if="selectedVariant">
                                {{ selectedVariant.product.name }} ·
                                {{ selectedVariant.sku }}
                            </template>
                            <template v-else
                                >Pilih produk untuk melihat riwayat</template
                            >
                        </h2>
                    </div>
                    <div v-if="movements.length" class="divide-y">
                        <div
                            v-for="movement in movements"
                            :key="movement.id"
                            class="grid gap-2 px-4 py-4 text-sm sm:grid-cols-[1fr_auto_auto] sm:items-center md:px-5"
                        >
                            <div>
                                <p class="font-medium">Stok awal</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ dateTime(movement.occurred_at) }} ·
                                    {{ movement.creator.name }}
                                </p>
                            </div>
                            <p
                                class="font-semibold text-[#163f35] tabular-nums dark:text-[#7bc4ac]"
                            >
                                +{{ formatQuantity(movement.quantity_delta) }}
                                {{ selectedVariant?.base_unit.code }}
                            </p>
                            <p
                                class="text-right text-xs text-muted-foreground tabular-nums"
                            >
                                {{ formatCurrency(movement.value_delta) }}
                            </p>
                        </div>
                    </div>
                    <div
                        v-else
                        class="px-5 py-10 text-center text-sm text-muted-foreground"
                    >
                        Belum ada pergerakan stok pada pilihan dan outlet ini.
                    </div>
                </section>
            </div>
        </main>

        <Sheet
            :open="openingSheetOpen"
            @update:open="(open) => !open && closeOpeningStock()"
        >
            <SheetContent side="right" class="w-full gap-0 sm:max-w-lg">
                <form
                    class="flex min-h-0 flex-1 flex-col"
                    @submit.prevent="saveOpeningStock"
                >
                    <SheetHeader
                        class="border-b bg-[#f8f6ef]/80 px-5 py-5 text-left backdrop-blur dark:bg-[#17201d]/80"
                    >
                        <SheetTitle>Catat stok awal</SheetTitle>
                        <SheetDescription>
                            Jumlah dan harga modal dicatat bersama sebagai
                            pergerakan pertama.
                        </SheetDescription>
                    </SheetHeader>
                    <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                        <div class="rounded-2xl border bg-muted/30 p-4">
                            <p class="font-semibold">
                                {{ openingVariant?.product.name }}
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ openingVariant?.name }} ·
                                {{ openingVariant?.sku }}
                            </p>
                            <p class="mt-3 text-xs text-muted-foreground">
                                Outlet: {{ selectedOutlet?.name }}
                            </p>
                        </div>
                        <InputError
                            :message="openingForm.errors.product_variant_id"
                        />
                        <div class="grid gap-2">
                            <Label for="opening-quantity"
                                >Jumlah stok awal</Label
                            >
                            <div class="relative">
                                <Input
                                    id="opening-quantity"
                                    v-model="openingForm.quantity"
                                    type="number"
                                    min="0.000001"
                                    step="0.000001"
                                    inputmode="decimal"
                                    required
                                    autofocus
                                    class="pr-16"
                                    placeholder="750"
                                />
                                <span
                                    class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-xs text-muted-foreground"
                                >
                                    {{ openingVariant?.base_unit.code }}
                                </span>
                            </div>
                            <InputError
                                :message="openingForm.errors.quantity"
                            />
                            <p class="text-xs text-muted-foreground">
                                Gunakan satuan dasar produk, bukan satuan beli.
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="opening-cost"
                                >Harga modal per
                                {{ openingVariant?.base_unit.code }}</Label
                            >
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-xs text-muted-foreground"
                                    >Rp</span
                                >
                                <Input
                                    id="opening-cost"
                                    v-model="openingForm.unit_cost"
                                    type="number"
                                    min="0"
                                    step="0.000001"
                                    inputmode="decimal"
                                    required
                                    class="pl-9"
                                    placeholder="146.666667"
                                />
                            </div>
                            <InputError
                                :message="openingForm.errors.unit_cost"
                            />
                        </div>
                        <div
                            class="flex items-center justify-between rounded-xl border border-[#163f35]/15 bg-[#163f35]/5 p-4 dark:border-white/10 dark:bg-white/5"
                        >
                            <span class="text-sm text-muted-foreground"
                                >Nilai stok awal</span
                            >
                            <span class="font-semibold tabular-nums">{{
                                formatCurrency(openingTotal)
                            }}</span>
                        </div>
                    </div>
                    <SheetFooter
                        class="border-t bg-background/90 px-5 py-4 backdrop-blur"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeOpeningStock"
                            >Batal</Button
                        >
                        <Button
                            type="submit"
                            class="bg-[#163f35] text-white hover:bg-[#205548]"
                            :disabled="openingForm.processing"
                        >
                            <CircleDollarSign class="size-4" />
                            {{
                                openingForm.processing
                                    ? 'Menyimpan…'
                                    : 'Simpan stok awal'
                            }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    </div>
</template>
