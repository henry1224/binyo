<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\CashDrawer;
use App\Models\Device;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOutletRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('Demo seeder tidak boleh dijalankan di production.');
        }

        $business = Business::query()->firstOrCreate(
            ['name' => 'Binyo Coffee'],
            ['timezone_default' => 'Asia/Makassar', 'currency' => 'IDR', 'status' => 'active'],
        );

        $outlets = collect([
            ['code' => 'KU', 'name' => 'Kedai Utama', 'profit_center_code' => 'PC-KU'],
            ['code' => 'KT', 'name' => 'Kedai Timur', 'profit_center_code' => 'PC-KT'],
        ])->mapWithKeys(function (array $data) use ($business): array {
            $outlet = Outlet::query()->firstOrCreate(
                ['business_id' => $business->id, 'code' => $data['code']],
                [...$data, 'receipt_prefix' => $data['code'], 'status' => 'active'],
            );

            return [$data['code'] => $outlet];
        });

        $permissionDescriptions = [
            'outlet.view_all' => 'Melihat seluruh outlet dalam bisnis',
            'operations.manage' => 'Mengelola outlet, staf, dan perangkat',
            'pos.sell' => 'Menjalankan transaksi penjualan',
            'cash.movement.create' => 'Mencatat kas masuk dan kas keluar',
            'sale.void' => 'Menyetujui void transaksi',
            'sale.refund' => 'Menyetujui refund transaksi',
            'stock.manage' => 'Mengelola stok dan opname',
            'purchase.receive' => 'Menerima barang pembelian',
            'report.profit.view' => 'Melihat laporan laba kotor',
            'replica.manage' => 'Mengelola switch replica outlet',
        ];

        foreach ($permissionDescriptions as $code => $description) {
            Permission::query()->firstOrCreate(['code' => $code], ['description' => $description]);
        }

        $rolePermissions = [
            'Pemilik' => array_keys($permissionDescriptions),
            'Manajer' => ['pos.sell', 'cash.movement.create', 'sale.void', 'sale.refund', 'stock.manage', 'purchase.receive'],
            'Kasir' => ['pos.sell', 'cash.movement.create'],
            'Gudang' => ['stock.manage', 'purchase.receive'],
        ];

        $roles = collect($rolePermissions)->mapWithKeys(function (array $permissions, string $name) use ($business): array {
            $role = Role::query()->firstOrCreate(
                ['business_id' => $business->id, 'name' => $name],
                ['is_system_preset' => true, 'status' => 'active'],
            );
            $role->permissions()->sync($permissions);

            return [$name => $role];
        });

        $users = collect([
            'owner@binyo.test' => ['name' => 'Henry', 'role' => 'Pemilik', 'outlets' => ['KU', 'KT']],
            'manager@binyo.test' => ['name' => 'Rina', 'role' => 'Manajer', 'outlets' => ['KU']],
            'cashier.one@binyo.test' => ['name' => 'Ayu', 'role' => 'Kasir', 'outlets' => ['KU']],
            'cashier.two@binyo.test' => ['name' => 'Bima', 'role' => 'Kasir', 'outlets' => ['KT']],
            'warehouse@binyo.test' => ['name' => 'Dedi', 'role' => 'Gudang', 'outlets' => ['KU', 'KT']],
        ])->mapWithKeys(function (array $data, string $email) use ($business, $outlets, $roles): array {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'business_id' => $business->id,
                    'name' => $data['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ],
            );

            foreach ($data['outlets'] as $outletCode) {
                UserOutletRole::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'outlet_id' => $outlets->get($outletCode)->id,
                        'role_id' => $roles->get($data['role'])->id,
                    ],
                    ['business_id' => $business->id, 'assigned_at' => now()],
                );
            }

            return [$email => $user];
        });

        $drawers = $outlets->mapWithKeys(function (Outlet $outlet) use ($business): array {
            $drawer = CashDrawer::query()->firstOrCreate(
                ['business_id' => $business->id, 'code' => "DRAWER-{$outlet->code}"],
                ['outlet_id' => $outlet->id, 'name' => "Laci Kas {$outlet->name}", 'status' => 'active'],
            );

            return [$outlet->code => $drawer];
        });

        foreach ([
            ['code' => 'POS-01', 'outlet' => 'KU', 'name' => 'Tablet Kasir Utama', 'status' => 'active'],
            ['code' => 'POS-02', 'outlet' => 'KU', 'name' => 'Tablet Cadangan', 'status' => 'standby'],
            ['code' => 'POS-03', 'outlet' => 'KT', 'name' => 'Tablet Kasir Timur', 'status' => 'active'],
        ] as $device) {
            Device::query()->firstOrCreate(
                ['business_id' => $business->id, 'code' => $device['code']],
                [
                    'outlet_id' => $outlets->get($device['outlet'])->id,
                    'cash_drawer_id' => $device['status'] === 'active' ? $drawers->get($device['outlet'])->id : null,
                    'name' => $device['name'],
                    'platform' => 'android',
                    'installation_id' => (string) Str::uuid(),
                    'app_version' => '0.1.0',
                    'trusted_at' => now(),
                    'last_seen_at' => now(),
                    'last_sync_at' => now(),
                    'status' => $device['status'],
                ],
            );
        }

        $this->command?->info("Demo siap. Login: {$users->keys()->first()} / password");
    }
}
