<?php

namespace App\Http\Controllers;

use App\Http\Requests\Operational\SaveOutletRequest;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperationalController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $canManageOperations = $user->outletAssignments()
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('operations.manage'))
            ->exists();
        $canViewAllOutlets = $canManageOperations || $user->outletAssignments()
            ->whereHas('role.permissions', fn ($query) => $query->whereKey('outlet.view_all'))
            ->exists();

        $outlets = Outlet::query()
            ->where('business_id', $user->business_id)
            ->when(
                ! $canViewAllOutlets,
                fn ($query) => $query->whereHas(
                    'assignments',
                    fn ($assignment) => $assignment->where('user_id', $user->id),
                ),
            )
            ->with([
                'assignments:id,outlet_id,user_id,role_id',
                'assignments.user:id,name,email,status',
                'assignments.role:id,name',
                'devices:id,outlet_id,cash_drawer_id,code,name,platform,app_version,last_seen_at,last_sync_at,status',
                'devices.cashDrawer:id,name',
            ])
            ->withCount(['assignments', 'devices'])
            ->orderBy('name')
            ->get([
                'id',
                'business_id',
                'code',
                'name',
                'profit_center_code',
                'address',
                'timezone',
                'business_day_start',
                'prevent_negative_stock',
                'status',
            ]);

        $activeTab = $request->string('tab')->toString();
        $activeTab = in_array($activeTab, ['outlets', 'staff', 'devices'], true)
            ? $activeTab
            : 'outlets';
        $selectedOutletId = $request->integer('outlet');
        $selectedOutletId = $outlets->contains('id', $selectedOutletId)
            ? $selectedOutletId
            : $outlets->first()?->id;

        return Inertia::render('Operational/Index', [
            'business' => $user->business()->firstOrFail(['id', 'name']),
            'outlets' => $outlets,
            'activeTab' => $activeTab,
            'selectedOutletId' => $selectedOutletId,
            'canManageOperations' => $canManageOperations,
        ]);
    }

    public function store(SaveOutletRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $data = $request->validated();
        $outlet = Outlet::query()->create([
            ...$data,
            'business_id' => $user->business_id,
            'timezone' => $user->business()->value('timezone_default'),
            'receipt_prefix' => $data['code'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Outlet {$outlet->name} berhasil ditambahkan.",
        ]);

        return to_route('operational.index', [
            'tab' => 'outlets',
            'outlet' => $outlet->id,
        ]);
    }

    public function update(SaveOutletRequest $request, int $outlet): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $outletModel = Outlet::query()
            ->where('business_id', $user->business_id)
            ->findOrFail($outlet);
        $data = $request->validated();
        $outletModel->update([
            ...$data,
            'receipt_prefix' => $data['code'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Outlet {$outletModel->name} berhasil diperbarui.",
        ]);

        return to_route('operational.index', [
            'tab' => 'outlets',
            'outlet' => $outletModel->id,
        ]);
    }
}
