<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import {
    Building2,
    ChevronLeft,
    ChevronRight,
    MonitorSmartphone,
    Pencil,
    Plus,
    Search,
    ShieldCheck,
    Users,
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
import { store, update } from '@/routes/operational/outlets';

type Assignment = {
    id: number;
    user: { id: number; name: string; email: string; status: string };
    role: { id: number; name: string };
    outlet: Pick<Outlet, 'id' | 'name' | 'code' | 'profit_center_code'>;
};

type Device = {
    id: number;
    code: string;
    name: string;
    platform: string;
    app_version: string | null;
    last_seen_at: string | null;
    last_sync_at: string | null;
    status: string;
    cash_drawer: { id: number; name: string } | null;
    outlet: Pick<Outlet, 'id' | 'name' | 'code' | 'profit_center_code'>;
};

type Outlet = {
    id: number;
    code: string;
    name: string;
    profit_center_code: string;
    address: string | null;
    timezone: string;
    business_day_start: string;
    prevent_negative_stock: boolean;
    status: string;
    assignments_count: number;
    devices_count: number;
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
    business: { id: number; name: string };
    records: Paginated<Outlet | Assignment | Device>;
    summary: { outlets: number; staff: number; devices: number };
    filters: { search: string; perPage: number };
    activeTab: 'outlets' | 'staff' | 'devices';
    selectedOutletId: number | null;
    canManageOperations: boolean;
}>();

const tabs = [
    { id: 'outlets', label: 'Outlet', icon: Building2 },
    { id: 'staff', label: 'Staf & akses', icon: Users },
    { id: 'devices', label: 'Perangkat', icon: MonitorSmartphone },
] as const;
const outlets = computed(() =>
    props.activeTab === 'outlets' ? (props.records.data as Outlet[]) : [],
);
const staff = computed(() =>
    props.activeTab === 'staff' ? (props.records.data as Assignment[]) : [],
);
const devices = computed(() =>
    props.activeTab === 'devices' ? (props.records.data as Device[]) : [],
);
const activeOutletId = ref(props.selectedOutletId);
const selectedOutlet = computed(
    () =>
        outlets.value.find((outlet) => outlet.id === activeOutletId.value) ??
        outlets.value[0],
);
const search = ref(props.filters.search);
const perPage = ref(props.filters.perPage);
const outletSheetOpen = ref(false);
const editingOutlet = ref<Outlet | null>(null);

const activeTab = computed(() =>
    tabs.find((tab) => tab.id === props.activeTab),
);
const searchPlaceholder = computed(
    () =>
        ({
            outlets: 'Cari nama, kode, profit center, atau alamat',
            staff: 'Cari nama, email, role, atau outlet',
            devices: 'Cari kode, nama, platform, atau outlet',
        })[props.activeTab],
);
const emptyState = computed(() =>
    props.filters.search
        ? {
              title: 'Data tidak ditemukan',
              description: `Tidak ada hasil untuk “${props.filters.search}”. Coba kata kunci lain.`,
          }
        : {
              title: `Belum ada ${activeTab.value?.label.toLowerCase()}`,
              description:
                  props.activeTab === 'outlets'
                      ? 'Tambahkan outlet pertama untuk mulai menyiapkan operasional.'
                      : 'Data akan tampil setelah terhubung dengan outlet yang dapat Anda akses.',
          },
);

const indexUrl = (
    values: Partial<{
        tab: 'outlets' | 'staff' | 'devices';
        search: string;
        per_page: number;
    }> = {},
) => {
    const params = new URLSearchParams();
    const tab = values.tab ?? props.activeTab;
    const query = values.search ?? search.value.trim();

    params.set('tab', tab);
    params.set('per_page', String(values.per_page ?? perPage.value));

    if (query) params.set('search', query);

    return `/operational?${params.toString()}`;
};

const applyFilters = () => {
    router.get(
        indexUrl(),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            only: ['records', 'filters', 'selectedOutletId'],
        },
    );
};

const clearSearch = () => {
    search.value = '';
    applyFilters();
};

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search;
        perPage.value = filters.perPage;
    },
);

watch(
    () => props.selectedOutletId,
    (outletId) => {
        activeOutletId.value = outletId;
    },
);

const openCreateOutlet = () => {
    editingOutlet.value = null;
    outletSheetOpen.value = true;
};

const openEditOutlet = (outlet: Outlet) => {
    editingOutlet.value = outlet;
    outletSheetOpen.value = true;
};

const setOutletSheetOpen = (open: boolean) => {
    outletSheetOpen.value = open;

    if (!open) {
        editingOutlet.value = null;
    }
};

const statusLabel = (status: string) =>
    ({
        active: 'Aktif',
        inactive: 'Nonaktif',
        standby: 'Cadangan',
        revoked: 'Dicabut',
    })[status] ?? status;

const dateTime = (value: string | null) =>
    value
        ? new Intl.DateTimeFormat('id-ID', {
              dateStyle: 'medium',
              timeStyle: 'short',
          }).format(new Date(value))
        : 'Belum pernah';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Operasional', href: '/operational' }],
    },
});
</script>

<template>
    <Head title="Operasional" />

    <div class="flex flex-1 flex-col">
        <section
            class="border-b border-white/10 bg-[#163f35] px-5 py-4 text-white md:px-8"
            aria-label="Status operasional"
        >
            <div
                class="mx-auto flex w-full max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-medium tracking-[0.18em] text-white/60 uppercase"
                    >
                        {{ business.name }}
                    </p>
                    <p class="mt-1 text-sm text-white/90">
                        Ruang kendali operasional bisnis
                    </p>
                </div>
                <div
                    class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-white/75"
                >
                    <span class="inline-flex items-center gap-2">
                        <span class="size-2 rounded-full bg-emerald-300" />
                        Data utama aktif
                    </span>
                    <span>{{ summary.outlets }} outlet</span>
                    <span>{{ summary.staff }} penugasan staf</span>
                    <span>{{ summary.devices }} perangkat</span>
                </div>
            </div>
        </section>

        <main class="mx-auto w-full max-w-7xl space-y-5 p-5 md:p-8">
            <header
                class="flex flex-col gap-5 border-b pb-6 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-semibold tracking-[0.16em] text-[#a66d20] uppercase dark:text-[#d3a052]"
                    >
                        Fondasi organisasi
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em]">
                        Operasional
                    </h1>
                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground"
                    >
                        Kelola outlet, penugasan staf, dan perangkat dari satu
                        ruang kerja yang terukur.
                    </p>
                </div>

                <Button
                    v-if="canManageOperations"
                    type="button"
                    class="min-h-11 bg-[#a66d20] text-white shadow-sm hover:bg-[#8d5c1b]"
                    @click="openCreateOutlet"
                >
                    <Plus class="size-4" />
                    Tambah outlet
                </Button>
            </header>

            <nav
                class="grid grid-cols-1 gap-2 sm:grid-cols-3"
                aria-label="Data operasional"
            >
                <Link
                    v-for="tab in tabs"
                    :key="tab.id"
                    :href="indexUrl({ tab: tab.id, search: '' })"
                    class="group flex min-h-16 items-center justify-between rounded-xl border px-4 transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="
                        activeTab?.id === tab.id
                            ? 'border-[#163f35] bg-[#163f35] text-white shadow-sm dark:border-[#315c50] dark:bg-[#17352e]'
                            : 'bg-card text-muted-foreground hover:border-[#a66d20]/40 hover:text-foreground'
                    "
                    preserve-scroll
                >
                    <span class="flex items-center gap-3">
                        <span
                            class="grid size-9 place-items-center rounded-lg"
                            :class="
                                activeTab?.id === tab.id
                                    ? 'bg-white/10'
                                    : 'bg-[#163f35]/7 text-[#163f35] dark:bg-white/5 dark:text-[#8db7aa]'
                            "
                        >
                            <component :is="tab.icon" class="size-4" />
                        </span>
                        <span class="text-sm font-semibold">{{
                            tab.label
                        }}</span>
                    </span>
                    <span
                        class="rounded-full px-2.5 py-1 text-xs font-semibold tabular-nums"
                        :class="
                            activeTab?.id === tab.id
                                ? 'bg-white/10 text-white'
                                : 'bg-muted text-foreground'
                        "
                    >
                        {{ summary[tab.id] }}
                    </span>
                </Link>
            </nav>

            <section
                v-if="summary.outlets === 0"
                class="rounded-2xl border bg-card p-10 text-center shadow-sm"
            >
                <Building2 class="mx-auto size-8 text-muted-foreground" />
                <h2 class="mt-4 font-semibold">
                    Belum ada outlet yang dapat diakses
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Hubungi pemilik bisnis untuk menambahkan penugasan outlet.
                </p>
            </section>

            <template v-else>
                <form
                    class="flex flex-col gap-3 rounded-2xl border bg-card p-3 shadow-sm sm:flex-row sm:items-center"
                    role="search"
                    @submit.prevent="applyFilters"
                >
                    <div class="relative min-w-0 flex-1">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            type="search"
                            :placeholder="searchPlaceholder"
                            class="h-11 pr-10 pl-9"
                            aria-label="Cari data operasional"
                        />
                        <button
                            v-if="search"
                            type="button"
                            class="absolute top-1/2 right-2 grid size-7 -translate-y-1/2 place-items-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            aria-label="Hapus pencarian"
                            @click="clearSearch"
                        >
                            <X class="size-3.5" />
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <label
                            for="per-page"
                            class="shrink-0 text-sm text-muted-foreground"
                        >
                            Tampilkan
                        </label>
                        <select
                            id="per-page"
                            v-model.number="perPage"
                            class="h-11 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                            @change="applyFilters"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                        <Button type="submit" class="h-11 px-5"> Cari </Button>
                    </div>
                </form>

                <section
                    v-if="records.total === 0"
                    class="rounded-2xl border border-dashed bg-card px-6 py-14 text-center"
                >
                    <component
                        :is="activeTab?.icon"
                        class="mx-auto size-8 text-muted-foreground"
                    />
                    <h2 class="mt-4 font-semibold">{{ emptyState.title }}</h2>
                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted-foreground"
                    >
                        {{ emptyState.description }}
                    </p>
                    <Button
                        v-if="filters.search"
                        type="button"
                        variant="outline"
                        class="mt-5"
                        @click="clearSearch"
                    >
                        Hapus pencarian
                    </Button>
                </section>

                <section
                    v-else-if="activeTab?.id === 'outlets'"
                    class="grid overflow-hidden rounded-2xl border bg-card shadow-sm lg:grid-cols-[18rem_1fr]"
                >
                    <div
                        class="border-b bg-[#f8f6ef] p-3 lg:border-r lg:border-b-0 dark:bg-[#17201d]"
                    >
                        <button
                            v-for="outlet in outlets"
                            :key="outlet.id"
                            type="button"
                            class="mb-1 flex min-h-16 w-full items-center justify-between rounded-xl px-4 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            :class="
                                selectedOutlet?.id === outlet.id
                                    ? 'bg-card text-foreground shadow-sm ring-1 ring-border'
                                    : 'text-muted-foreground hover:bg-card/70 hover:text-foreground'
                            "
                            @click="activeOutletId = outlet.id"
                        >
                            <span>
                                <span class="block text-sm font-semibold">
                                    {{ outlet.name }}
                                </span>
                                <span class="mt-1 block text-xs">
                                    {{ outlet.code }}
                                </span>
                            </span>
                            <span
                                class="size-2 rounded-full"
                                :class="
                                    outlet.status === 'active'
                                        ? 'bg-emerald-500'
                                        : 'bg-stone-400'
                                "
                            />
                        </button>
                    </div>

                    <div v-if="selectedOutlet" class="p-5 md:p-8">
                        <div
                            class="flex flex-col gap-3 border-b pb-6 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Outlet terpilih
                                </p>
                                <h2
                                    class="mt-1 text-xl font-semibold tracking-tight"
                                >
                                    {{ selectedOutlet.name }}
                                </h2>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    v-if="canManageOperations"
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    @click="openEditOutlet(selectedOutlet)"
                                >
                                    <Pencil class="size-3.5" />
                                    Ubah
                                </Button>
                                <Badge
                                    variant="outline"
                                    :class="
                                        selectedOutlet.status === 'active'
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                            : 'border-stone-200 bg-stone-50 text-stone-700 dark:border-stone-700 dark:bg-stone-900 dark:text-stone-200'
                                    "
                                >
                                    {{ statusLabel(selectedOutlet.status) }}
                                </Badge>
                            </div>
                        </div>

                        <dl
                            class="grid gap-x-8 gap-y-6 py-7 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Kode outlet
                                </dt>
                                <dd class="mt-2 font-semibold">
                                    {{ selectedOutlet.code }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Profit center
                                </dt>
                                <dd
                                    class="mt-2 font-semibold text-[#163f35] dark:text-[#8db7aa]"
                                >
                                    {{ selectedOutlet.profit_center_code }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Awal hari usaha
                                </dt>
                                <dd class="mt-2 font-semibold">
                                    {{
                                        selectedOutlet.business_day_start.slice(
                                            0,
                                            5,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Zona waktu
                                </dt>
                                <dd class="mt-2 font-semibold">
                                    {{ selectedOutlet.timezone }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Stok minus
                                </dt>
                                <dd class="mt-2 font-semibold">
                                    {{
                                        selectedOutlet.prevent_negative_stock
                                            ? 'Tidak diizinkan'
                                            : 'Diizinkan'
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Kesiapan
                                </dt>
                                <dd
                                    class="mt-2 flex items-center gap-2 font-semibold"
                                >
                                    <ShieldCheck
                                        class="size-4 text-emerald-600"
                                    />
                                    {{ selectedOutlet.assignments_count }} staf
                                    ·
                                    {{ selectedOutlet.devices_count }} perangkat
                                </dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section
                    v-else-if="activeTab?.id === 'staff'"
                    class="overflow-hidden rounded-2xl border bg-card shadow-sm"
                >
                    <div class="flex items-center gap-3 border-b px-5 py-4">
                        <Users class="size-5 text-[#a66d20]" />
                        <div>
                            <h2 class="font-semibold">Staf & akses</h2>
                            <p class="text-xs text-muted-foreground">
                                Satu orang dapat memiliki role berbeda pada
                                outlet berbeda.
                            </p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <thead
                                class="bg-muted/60 text-xs tracking-wider text-muted-foreground uppercase"
                            >
                                <tr>
                                    <th class="px-5 py-3 font-medium">Nama</th>
                                    <th class="px-5 py-3 font-medium">
                                        Outlet
                                    </th>
                                    <th class="px-5 py-3 font-medium">Peran</th>
                                    <th class="px-5 py-3 font-medium">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="assignment in staff"
                                    :key="assignment.id"
                                    class="hover:bg-muted/30"
                                >
                                    <td class="px-5 py-4">
                                        <span class="block font-medium">
                                            {{ assignment.user.name }}
                                        </span>
                                        <span
                                            class="mt-1 block text-xs text-muted-foreground"
                                        >
                                            {{ assignment.user.email }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        {{ assignment.outlet.name }}
                                    </td>
                                    <td class="px-5 py-4 font-medium">
                                        {{ assignment.role.name }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <Badge variant="outline">
                                            {{
                                                statusLabel(
                                                    assignment.user.status,
                                                )
                                            }}
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section
                    v-else
                    class="overflow-hidden rounded-2xl border bg-card shadow-sm"
                >
                    <div class="flex items-center gap-3 border-b px-5 py-4">
                        <MonitorSmartphone class="size-5 text-[#a66d20]" />
                        <div>
                            <h2 class="font-semibold">Perangkat POS</h2>
                            <p class="text-xs text-muted-foreground">
                                Pemindahan outlet membutuhkan pencabutan dan
                                registrasi ulang.
                            </p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-left text-sm">
                            <thead
                                class="bg-muted/60 text-xs tracking-wider text-muted-foreground uppercase"
                            >
                                <tr>
                                    <th class="px-5 py-3 font-medium">
                                        Perangkat
                                    </th>
                                    <th class="px-5 py-3 font-medium">
                                        Outlet
                                    </th>
                                    <th class="px-5 py-3 font-medium">
                                        Laci kas
                                    </th>
                                    <th class="px-5 py-3 font-medium">
                                        Sinkron terakhir
                                    </th>
                                    <th class="px-5 py-3 font-medium">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="device in devices"
                                    :key="device.id"
                                    class="hover:bg-muted/30"
                                >
                                    <td class="px-5 py-4">
                                        <span class="block font-medium">
                                            {{ device.code }} ·
                                            {{ device.name }}
                                        </span>
                                        <span
                                            class="mt-1 block text-xs text-muted-foreground"
                                        >
                                            {{ device.platform }} · v{{
                                                device.app_version ?? '—'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        {{ device.outlet.name }}
                                        <span
                                            class="ml-1 text-xs text-muted-foreground"
                                        >
                                            {{
                                                device.outlet.profit_center_code
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        {{
                                            device.cash_drawer?.name ??
                                            'Tidak terhubung'
                                        }}
                                    </td>
                                    <td class="px-5 py-4">
                                        {{ dateTime(device.last_sync_at) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <Badge
                                            :variant="
                                                device.status === 'revoked'
                                                    ? 'destructive'
                                                    : 'outline'
                                            "
                                        >
                                            {{ statusLabel(device.status) }}
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <footer
                    v-if="records.total > 0"
                    class="flex flex-col gap-3 rounded-xl border bg-card px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-muted-foreground">
                        Menampilkan
                        <span class="font-medium text-foreground">
                            {{ records.from }}–{{ records.to }}
                        </span>
                        dari
                        <span class="font-medium text-foreground">
                            {{ records.total }}
                        </span>
                        data
                    </p>

                    <div class="flex items-center gap-2">
                        <Button
                            v-if="records.prev_page_url"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link
                                :href="records.prev_page_url"
                                preserve-scroll
                                :only="[
                                    'records',
                                    'filters',
                                    'selectedOutletId',
                                ]"
                            >
                                <ChevronLeft class="size-4" />
                                Sebelumnya
                            </Link>
                        </Button>
                        <Button v-else variant="outline" size="sm" disabled>
                            <ChevronLeft class="size-4" />
                            Sebelumnya
                        </Button>

                        <span
                            class="min-w-24 text-center text-xs text-muted-foreground"
                        >
                            Halaman {{ records.current_page }} dari
                            {{ records.last_page }}
                        </span>

                        <Button
                            v-if="records.next_page_url"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link
                                :href="records.next_page_url"
                                preserve-scroll
                                :only="[
                                    'records',
                                    'filters',
                                    'selectedOutletId',
                                ]"
                            >
                                Berikutnya
                                <ChevronRight class="size-4" />
                            </Link>
                        </Button>
                        <Button v-else variant="outline" size="sm" disabled>
                            Berikutnya
                            <ChevronRight class="size-4" />
                        </Button>
                    </div>
                </footer>
            </template>
        </main>

        <Sheet :open="outletSheetOpen" @update:open="setOutletSheetOpen">
            <SheetContent side="right" class="w-full gap-0 sm:max-w-lg">
                <Form
                    :key="editingOutlet?.id ?? 'create'"
                    v-bind="
                        editingOutlet
                            ? update.form(editingOutlet.id)
                            : store.form()
                    "
                    reset-on-success
                    :options="{ preserveScroll: true }"
                    class="flex min-h-0 flex-1 flex-col"
                    @success="setOutletSheetOpen(false)"
                    v-slot="{ errors, processing }"
                >
                    <SheetHeader class="border-b px-5 py-5 text-left">
                        <SheetTitle>
                            {{
                                editingOutlet ? 'Ubah outlet' : 'Tambah outlet'
                            }}
                        </SheetTitle>
                        <SheetDescription>
                            Kode outlet dan profit center memisahkan transaksi,
                            stok, serta laporan setiap cabang.
                        </SheetDescription>
                    </SheetHeader>

                    <div
                        class="min-h-0 flex-1 space-y-5 overflow-y-auto px-5 py-5"
                    >
                        <div class="grid gap-2">
                            <Label for="outlet-name">Nama outlet</Label>
                            <Input
                                id="outlet-name"
                                name="name"
                                :default-value="editingOutlet?.name"
                                placeholder="Kedai Utama"
                                required
                                autofocus
                                :aria-invalid="errors.name ? true : undefined"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="outlet-code">Kode outlet</Label>
                                <Input
                                    id="outlet-code"
                                    name="code"
                                    :default-value="editingOutlet?.code"
                                    placeholder="KU"
                                    maxlength="20"
                                    required
                                    class="uppercase"
                                    :aria-invalid="
                                        errors.code ? true : undefined
                                    "
                                />
                                <InputError :message="errors.code" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="profit-center-code">
                                    Profit center
                                </Label>
                                <Input
                                    id="profit-center-code"
                                    name="profit_center_code"
                                    :default-value="
                                        editingOutlet?.profit_center_code
                                    "
                                    placeholder="PC-KU"
                                    maxlength="40"
                                    required
                                    class="uppercase"
                                    :aria-invalid="
                                        errors.profit_center_code
                                            ? true
                                            : undefined
                                    "
                                />
                                <InputError
                                    :message="errors.profit_center_code"
                                />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="outlet-address">Alamat</Label>
                            <textarea
                                id="outlet-address"
                                name="address"
                                :value="editingOutlet?.address ?? ''"
                                rows="3"
                                maxlength="1000"
                                placeholder="Alamat operasional outlet"
                                class="min-h-24 w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 dark:bg-input/30"
                                :aria-invalid="
                                    errors.address ? true : undefined
                                "
                            />
                            <InputError :message="errors.address" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="business-day-start">
                                    Awal hari usaha
                                </Label>
                                <Input
                                    id="business-day-start"
                                    name="business_day_start"
                                    type="time"
                                    :default-value="
                                        editingOutlet?.business_day_start.slice(
                                            0,
                                            5,
                                        ) ?? '04:00'
                                    "
                                    required
                                    :aria-invalid="
                                        errors.business_day_start
                                            ? true
                                            : undefined
                                    "
                                />
                                <InputError
                                    :message="errors.business_day_start"
                                />
                            </div>
                            <div v-if="editingOutlet" class="grid gap-2">
                                <Label for="outlet-status">Status</Label>
                                <select
                                    id="outlet-status"
                                    name="status"
                                    :value="editingOutlet.status"
                                    class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                                <InputError :message="errors.status" />
                            </div>
                            <input
                                v-else
                                type="hidden"
                                name="status"
                                value="active"
                            />
                        </div>

                        <div
                            class="rounded-xl border bg-[#f8f6ef] p-4 dark:bg-[#17201d]"
                        >
                            <input
                                type="hidden"
                                name="prevent_negative_stock"
                                value="0"
                            />
                            <label
                                for="prevent-negative-stock"
                                class="flex cursor-pointer items-start gap-3"
                            >
                                <input
                                    id="prevent-negative-stock"
                                    type="checkbox"
                                    name="prevent_negative_stock"
                                    value="1"
                                    :checked="
                                        editingOutlet?.prevent_negative_stock ??
                                        true
                                    "
                                    class="mt-0.5 size-4 rounded border-input accent-[#163f35]"
                                />
                                <span>
                                    <span class="block text-sm font-medium">
                                        Tolak penjualan saat stok tidak cukup
                                    </span>
                                    <span
                                        class="mt-1 block text-xs leading-5 text-muted-foreground"
                                    >
                                        Produk tanpa pelacakan stok tetap dapat
                                        dijual.
                                    </span>
                                </span>
                            </label>
                            <InputError
                                class="mt-2"
                                :message="errors.prevent_negative_stock"
                            />
                        </div>
                    </div>

                    <SheetFooter
                        class="border-t bg-background px-5 py-4 sm:flex-row sm:justify-end"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="setOutletSheetOpen(false)"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            class="bg-[#163f35] text-white hover:bg-[#12352d]"
                            :disabled="processing"
                        >
                            {{ processing ? 'Menyimpan...' : 'Simpan outlet' }}
                        </Button>
                    </SheetFooter>
                </Form>
            </SheetContent>
        </Sheet>
    </div>
</template>
