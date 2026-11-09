<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOutletRole;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OperationalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('operational.index'))->assertRedirect(route('login'));
    }

    public function test_user_only_sees_assigned_outlets_in_their_business(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $assignedOutlet = $this->createOutlet($business, 'KU', 'Kedai Utama', 'PC-KU');
        $this->createOutlet($business, 'KT', 'Kedai Timur', 'PC-KT');
        $this->createOutlet($otherBusiness, 'KL', 'Kedai Lain', 'PC-KL');
        $role = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Kasir',
            'is_system_preset' => true,
            'status' => 'active',
        ]);
        $user = User::factory()->for($business)->create();
        $this->assign($user, $assignedOutlet, $role);

        $this->actingAs($user)
            ->get(route('operational.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Operational/Index')
                ->has('outlets', 1)
                ->where('outlets.0.name', 'Kedai Utama')
                ->where('outlets.0.profit_center_code', 'PC-KU')
                ->where('business.id', $business->id));
    }

    public function test_view_all_permission_is_limited_to_the_users_business(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $firstOutlet = $this->createOutlet($business, 'KU', 'Kedai Utama', 'PC-KU');
        $this->createOutlet($business, 'KT', 'Kedai Timur', 'PC-KT');
        $this->createOutlet($otherBusiness, 'KL', 'Kedai Lain', 'PC-KL');
        $permission = Permission::query()->create([
            'code' => 'outlet.view_all',
            'description' => 'Melihat seluruh outlet dalam bisnis',
        ]);
        $role = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Pemilik',
            'is_system_preset' => true,
            'status' => 'active',
        ]);
        $role->permissions()->attach($permission);
        $user = User::factory()->for($business)->create();
        $this->assign($user, $firstOutlet, $role);

        $this->actingAs($user)
            ->get(route('operational.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('outlets', 2)
                ->where('outlets.0.name', 'Kedai Timur')
                ->where('outlets.1.name', 'Kedai Utama'));
    }

    public function test_roles_are_returned_per_outlet(): void
    {
        $business = Business::factory()->create();
        $firstOutlet = $this->createOutlet($business, 'A', 'Outlet A', 'PC-A');
        $secondOutlet = $this->createOutlet($business, 'B', 'Outlet B', 'PC-B');
        $cashier = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Kasir',
            'status' => 'active',
        ]);
        $manager = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Manajer',
            'status' => 'active',
        ]);
        $user = User::factory()->for($business)->create();
        $this->assign($user, $firstOutlet, $cashier);
        $this->assign($user, $secondOutlet, $manager);

        $this->actingAs($user)
            ->get(route('operational.index', [
                'tab' => 'staff',
                'outlet' => $secondOutlet->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('activeTab', 'staff')
                ->where('selectedOutletId', $secondOutlet->id)
                ->where('outlets.0.assignments.0.role.name', 'Kasir')
                ->where('outlets.1.assignments.0.role.name', 'Manajer'));
    }

    public function test_role_assignment_cannot_cross_business_boundary(): void
    {
        $firstBusiness = Business::factory()->create();
        $secondBusiness = Business::factory()->create();
        $outlet = $this->createOutlet($firstBusiness, 'KU', 'Kedai Utama', 'PC-KU');
        $role = Role::query()->create([
            'business_id' => $firstBusiness->id,
            'name' => 'Kasir',
            'status' => 'active',
        ]);
        $foreignUser = User::factory()->for($secondBusiness)->create();

        $this->expectException(QueryException::class);

        UserOutletRole::query()->create([
            'business_id' => $firstBusiness->id,
            'user_id' => $foreignUser->id,
            'outlet_id' => $outlet->id,
            'role_id' => $role->id,
            'assigned_at' => now(),
        ]);
    }

    public function test_authorized_user_can_create_outlet_for_their_business(): void
    {
        $business = Business::factory()->create(['timezone_default' => 'Asia/Jakarta']);
        $existingOutlet = $this->createOutlet($business, 'KU', 'Kedai Utama', 'PC-KU');
        $user = $this->createUserWithPermission($business, $existingOutlet, 'operations.manage');

        $response = $this->actingAs($user)->post(route('operational.outlets.store'), [
            'code' => 'kt',
            'name' => ' Kedai Timur ',
            'profit_center_code' => 'pc-kt',
            'address' => ' Jalan Timur 10 ',
            'business_day_start' => '04:00',
            'prevent_negative_stock' => true,
            'status' => 'active',
        ]);

        $outlet = Outlet::query()->where('business_id', $business->id)->where('code', 'KT')->firstOrFail();

        $response->assertRedirect(route('operational.index', [
            'tab' => 'outlets',
            'outlet' => $outlet->id,
        ]));
        $this->assertDatabaseHas('outlets', [
            'id' => $outlet->id,
            'business_id' => $business->id,
            'name' => 'Kedai Timur',
            'profit_center_code' => 'PC-KT',
            'address' => 'Jalan Timur 10',
            'timezone' => 'Asia/Jakarta',
            'receipt_prefix' => 'KT',
            'prevent_negative_stock' => true,
            'status' => 'active',
        ]);
        $this->actingAs($user)
            ->get(route('operational.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('outlets', 2)
                ->where('canManageOperations', true));
    }

    public function test_user_without_permission_cannot_create_outlet(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama', 'PC-KU');
        $role = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Kasir',
            'status' => 'active',
        ]);
        $user = User::factory()->for($business)->create();
        $this->assign($user, $outlet, $role);

        $this->actingAs($user)
            ->post(route('operational.outlets.store'), [
                'code' => 'KT',
                'name' => 'Kedai Timur',
                'profit_center_code' => 'PC-KT',
                'business_day_start' => '04:00',
                'prevent_negative_stock' => true,
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('outlets', [
            'business_id' => $business->id,
            'code' => 'KT',
        ]);
    }

    public function test_authorized_user_can_update_own_outlet(): void
    {
        $business = Business::factory()->create();
        $outlet = $this->createOutlet($business, 'KU', 'Kedai Utama', 'PC-KU');
        $user = $this->createUserWithPermission($business, $outlet, 'operations.manage');

        $this->actingAs($user)
            ->put(route('operational.outlets.update', $outlet), [
                'code' => 'KU',
                'name' => 'Kedai Utama Baru',
                'profit_center_code' => 'PC-KU',
                'address' => null,
                'business_day_start' => '05:00',
                'prevent_negative_stock' => false,
                'status' => 'inactive',
            ])
            ->assertRedirect(route('operational.index', [
                'tab' => 'outlets',
                'outlet' => $outlet->id,
            ]));

        $this->assertDatabaseHas('outlets', [
            'id' => $outlet->id,
            'business_id' => $business->id,
            'name' => 'Kedai Utama Baru',
            'prevent_negative_stock' => false,
            'status' => 'inactive',
        ]);
        $outlet->refresh();
        $this->assertStringStartsWith('05:00', $outlet->business_day_start);
    }

    public function test_outlet_update_cannot_cross_business_boundary(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $assignedOutlet = $this->createOutlet($business, 'KU', 'Kedai Utama', 'PC-KU');
        $foreignOutlet = $this->createOutlet($otherBusiness, 'KL', 'Kedai Lain', 'PC-KL');
        $user = $this->createUserWithPermission($business, $assignedOutlet, 'operations.manage');

        $this->actingAs($user)
            ->put(route('operational.outlets.update', $foreignOutlet), [
                'code' => 'KL',
                'name' => 'Diubah Paksa',
                'profit_center_code' => 'PC-KL',
                'business_day_start' => '04:00',
                'prevent_negative_stock' => true,
                'status' => 'active',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('outlets', [
            'id' => $foreignOutlet->id,
            'name' => 'Kedai Lain',
        ]);
    }

    private function createOutlet(
        Business $business,
        string $code,
        string $name,
        string $profitCenter,
    ): Outlet {
        return Outlet::query()->create([
            'business_id' => $business->id,
            'code' => $code,
            'name' => $name,
            'profit_center_code' => $profitCenter,
            'receipt_prefix' => $code,
            'status' => 'active',
        ]);
    }

    private function assign(User $user, Outlet $outlet, Role $role): void
    {
        UserOutletRole::query()->create([
            'business_id' => $user->business_id,
            'user_id' => $user->id,
            'outlet_id' => $outlet->id,
            'role_id' => $role->id,
            'assigned_at' => now(),
        ]);
    }

    private function createUserWithPermission(
        Business $business,
        Outlet $outlet,
        string $permissionCode,
    ): User {
        $permission = Permission::query()->create([
            'code' => $permissionCode,
            'description' => 'Izin pengujian',
        ]);
        $role = Role::query()->create([
            'business_id' => $business->id,
            'name' => 'Pemilik',
            'status' => 'active',
        ]);
        $role->permissions()->attach($permission);
        $user = User::factory()->for($business)->create();
        $this->assign($user, $outlet, $role);

        return $user;
    }
}
