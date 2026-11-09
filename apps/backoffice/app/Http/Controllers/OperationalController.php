<?php

namespace App\Http\Controllers;

use App\Http\Requests\Operational\SaveOutletRequest;
use App\Models\Device;
use App\Models\Outlet;
use App\Models\UserOutletRole;
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

        $activeTab = $request->string('tab')->toString();
        $activeTab = in_array($activeTab, ['outlets', 'staff', 'devices'], true)
            ? $activeTab
            : 'outlets';
        $search = $request->query('search');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $requestedPerPage = $request->integer('per_page');
        $perPage = in_array($requestedPerPage, [10, 25, 50], true)
            ? $requestedPerPage
            : 10;

        $visibleOutlets = Outlet::query()
            ->where('business_id', $user->business_id)
            ->when(
                ! $canViewAllOutlets,
                fn ($query) => $query->whereHas(
                    'assignments',
                    fn ($assignment) => $assignment->where('user_id', $user->id),
                ),
            );
        $visibleOutletIds = (clone $visibleOutlets)->select('id');

        $records = match ($activeTab) {
            'staff' => UserOutletRole::query()
                ->where('business_id', $user->business_id)
                ->whereIn('outlet_id', clone $visibleOutletIds)
                ->with([
                    'user:id,name,email,status',
                    'role:id,name',
                    'outlet:id,name,code,profit_center_code',
                ])
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->whereHas('user', fn ($query) => $query
                        ->whereLike('name', "%{$search}%")
                        ->orWhereLike('email', "%{$search}%"))
                    ->orWhereHas('role', fn ($query) => $query->whereLike('name', "%{$search}%"))
                    ->orWhereHas('outlet', fn ($query) => $query
                        ->whereLike('name', "%{$search}%")
                        ->orWhereLike('code', "%{$search}%"))))
                ->orderByDesc('assigned_at')
                ->orderBy('id')
                ->paginate($perPage, [
                    'id',
                    'outlet_id',
                    'user_id',
                    'role_id',
                    'assigned_at',
                ])
                ->withQueryString(),
            'devices' => Device::query()
                ->where('business_id', $user->business_id)
                ->whereIn('outlet_id', clone $visibleOutletIds)
                ->with([
                    'outlet:id,name,code,profit_center_code',
                    'cashDrawer:id,name',
                ])
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('code', "%{$search}%")
                    ->orWhereLike('platform', "%{$search}%")
                    ->orWhereHas('outlet', fn ($query) => $query
                        ->whereLike('name', "%{$search}%")
                        ->orWhereLike('code', "%{$search}%"))
                    ->orWhereHas('cashDrawer', fn ($query) => $query->whereLike('name', "%{$search}%"))))
                ->orderBy('name')
                ->paginate($perPage, [
                    'id',
                    'outlet_id',
                    'cash_drawer_id',
                    'code',
                    'name',
                    'platform',
                    'app_version',
                    'last_seen_at',
                    'last_sync_at',
                    'status',
                ])
                ->withQueryString(),
            default => (clone $visibleOutlets)
                ->withCount(['assignments', 'devices'])
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('code', "%{$search}%")
                    ->orWhereLike('profit_center_code', "%{$search}%")
                    ->orWhereLike('address', "%{$search}%")))
                ->orderBy('name')
                ->paginate($perPage, [
                    'id',
                    'code',
                    'name',
                    'profit_center_code',
                    'address',
                    'timezone',
                    'business_day_start',
                    'prevent_negative_stock',
                    'status',
                ])
                ->withQueryString(),
        };

        $selectedOutletId = null;

        if ($activeTab === 'outlets' && $records->isNotEmpty()) {
            $requestedOutletId = $request->integer('outlet');
            $selectedOutletId = collect($records->items())->contains('id', $requestedOutletId)
                ? $requestedOutletId
                : $records->items()[0]->id;
        }

        return Inertia::render('Operational/Index', [
            'business' => fn () => $user->business()->firstOrFail(['id', 'name']),
            'records' => $records,
            'summary' => fn () => [
                'outlets' => (clone $visibleOutlets)->count(),
                'staff' => UserOutletRole::query()
                    ->where('business_id', $user->business_id)
                    ->whereIn('outlet_id', clone $visibleOutletIds)
                    ->count(),
                'devices' => Device::query()
                    ->where('business_id', $user->business_id)
                    ->whereIn('outlet_id', clone $visibleOutletIds)
                    ->count(),
            ],
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
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
