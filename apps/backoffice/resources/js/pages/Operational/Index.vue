<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Building2, MonitorSmartphone, ShieldCheck, Users } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

type Assignment = {
    id: number;
    user: { id: number; name: string; email: string; status: string };
    role: { id: number; name: string };
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
    assignments: Assignment[];
    devices: Device[];
};

const props = defineProps<{
    business: { id: number; name: string };
    outlets: Outlet[];
    activeTab: 'outlets' | 'staff' | 'devices';
    selectedOutletId: number | null;
}>();

const tabs = [
    { id: 'outlets', label: 'Outlet' },
    { id: 'staff', label: 'Staff & akses' },
    { id: 'devices', label: 'Perangkat' },
] as const;
const selectedOutlet = computed(
    () =>
        props.outlets.find((outlet) => outlet.id === props.selectedOutletId) ??
        props.outlets[0],
);
const staff = computed(() =>
    props.outlets.flatMap((outlet) =>
        outlet.assignments.map((assignment) => ({ ...assignment, outlet })),
    ),
);
const devices = computed(() =>
    props.outlets.flatMap((outlet) =>
        outlet.devices.map((device) => ({ ...device, outlet })),
    ),
);

const statusLabel = (status: string) =>
    ({ active: 'Aktif', standby: 'Cadangan', revoked: 'Dicabut' })[status] ??
    status;

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
            class="grid gap-4 bg-[#163f35] px-5 py-4 text-white sm:grid-cols-[1fr_auto] sm:items-center md:px-8"
            aria-label="Status operasional"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.18em] text-white/60 uppercase"
                >
                    {{ business.name }}
                </p>
                <p class="mt-1 text-sm text-white/90">
                    {{ outlets.length }} outlet dalam ruang kerja Anda
                </p>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-white/75">
                <span class="inline-flex items-center gap-2">
                    <span class="size-2 rounded-full bg-emerald-300" />
                    Data utama aktif
                </span>
                <span>{{ devices.length }} perangkat terdaftar</span>
            </div>
        </section>

        <main class="mx-auto w-full max-w-7xl space-y-6 p-5 md:p-8">
            <header
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-semibold tracking-[0.16em] text-[#a66d20] uppercase"
                    >
                        Fondasi organisasi
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em]">
                        Operasional
                    </h1>
                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground"
                    >
                        Outlet menentukan profit center. Role staf berlaku per
                        outlet, dan perangkat tetap terikat ke outlet saat
                        registrasi.
                    </p>
                </div>

                <div
                    class="inline-flex w-full rounded-xl border bg-card/80 p-1 shadow-sm backdrop-blur lg:w-auto"
                    role="tablist"
                    aria-label="Data operasional"
                >
                    <a
                        v-for="tab in tabs"
                        :key="tab.id"
                        :href="`/operational?tab=${tab.id}`"
                        role="tab"
                        :aria-selected="activeTab === tab.id"
                        class="min-h-11 flex-1 rounded-lg px-4 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none lg:flex-none"
                        :class="
                            activeTab === tab.id
                                ? 'bg-[#163f35] text-white shadow-sm'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                        "
                    >
                        {{ tab.label }}
                    </a>
                </div>
            </header>

            <section
                v-if="outlets.length === 0"
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

            <section
                v-else-if="activeTab === 'outlets'"
                class="grid overflow-hidden rounded-2xl border bg-card shadow-sm lg:grid-cols-[18rem_1fr]"
            >
                <div
                    class="border-b bg-[#f8f6ef] p-3 lg:border-r lg:border-b-0 dark:bg-[#17201d]"
                >
                    <a
                        v-for="outlet in outlets"
                        :key="outlet.id"
                        :href="`/operational?tab=outlets&outlet=${outlet.id}`"
                        class="mb-1 flex min-h-16 w-full items-center justify-between rounded-xl px-4 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        :class="
                            selectedOutlet?.id === outlet.id
                                ? 'bg-card text-foreground shadow-sm ring-1 ring-border'
                                : 'text-muted-foreground hover:bg-card/70 hover:text-foreground'
                        "
                    >
                        <span>
                            <span class="block text-sm font-semibold">
                                {{ outlet.name }}
                            </span>
                            <span class="mt-1 block text-xs">
                                {{ outlet.code }}
                            </span>
                        </span>
                        <span class="size-2 rounded-full bg-emerald-500" />
                    </a>
                </div>

                <div v-if="selectedOutlet" class="p-5 md:p-8">
                    <div
                        class="flex flex-col gap-3 border-b pb-6 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Outlet aktif
                            </p>
                            <h2
                                class="mt-1 text-xl font-semibold tracking-tight"
                            >
                                {{ selectedOutlet.name }}
                            </h2>
                        </div>
                        <Badge
                            variant="outline"
                            class="border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"
                        >
                            {{ statusLabel(selectedOutlet.status) }}
                        </Badge>
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
                            <dd class="mt-2 font-semibold text-[#163f35]">
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
                                {{ selectedOutlet.business_day_start }}
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
                                <ShieldCheck class="size-4 text-emerald-600" />
                                {{ selectedOutlet.assignments_count }} staf ·
                                {{ selectedOutlet.devices_count }} perangkat
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section
                v-else-if="activeTab === 'staff'"
                class="overflow-hidden rounded-2xl border bg-card shadow-sm"
            >
                <div class="flex items-center gap-3 border-b px-5 py-4">
                    <Users class="size-5 text-[#a66d20]" />
                    <div>
                        <h2 class="font-semibold">Staff & akses</h2>
                        <p class="text-xs text-muted-foreground">
                            Satu orang dapat memiliki role berbeda pada outlet
                            berbeda.
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
                                <th class="px-5 py-3 font-medium">Outlet</th>
                                <th class="px-5 py-3 font-medium">Role</th>
                                <th class="px-5 py-3 font-medium">Status</th>
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
                                            statusLabel(assignment.user.status)
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
                                <th class="px-5 py-3 font-medium">Perangkat</th>
                                <th class="px-5 py-3 font-medium">Outlet</th>
                                <th class="px-5 py-3 font-medium">
                                    Cash drawer
                                </th>
                                <th class="px-5 py-3 font-medium">
                                    Sinkron terakhir
                                </th>
                                <th class="px-5 py-3 font-medium">Status</th>
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
                                        {{ device.code }} · {{ device.name }}
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
                                        {{ device.outlet.profit_center_code }}
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
        </main>
    </div>
</template>
