<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\Tenancy;
use App\Models\Deposit;
use App\Traits\Auditable;

class DashboardController extends Controller
{
    use Auditable;

    public function index(Request $request)
    {
        $user = $request->user();

        $this->audit(
            $user->full_name . ' viewed dashboard (' . $user->role . ')',
            $user->id
        );

        return match ($user->role) {
            'admin'    => $this->adminDashboard(),
            'landlord' => $this->landlordDashboard($user),
            'manager'  => $this->managerDashboard($user),
            'tenant'   => $this->tenantDashboard($user),
            default    => response()->json(['message' => 'Invalid role'], 403),
        };
    }

    /* ========================= ADMIN ========================= */

    protected function adminDashboard()
    {
        return response()->json([
            'role' => 'admin',

            'cards' => [
                [
                    'title' => 'Landlords',
                    'value' => User::where('role', 'landlord')->count(),
                    'icon'  => 'bi-person-badge',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Managers',
                    'value' => User::where('role', 'manager')->count(),
                    'icon'  => 'bi-people',
                    'color' => 'info',
                ],
                [
                    'title' => 'Tenants',
                    'value' => User::where('role', 'tenant')->count(),
                    'icon'  => 'bi-people-fill',
                    'color' => 'success',
                ],
                [
                    'title' => 'Properties',
                    'value' => Property::count(),
                    'icon'  => 'bi-buildings',
                    'color' => 'warning',
                ],
                [
                    'title' => 'Units',
                    'value' => Unit::count(),
                    'icon'  => 'bi-door-open',
                    'color' => 'secondary',
                ],
            ],
        ]);
    }

    /* ========================= LANDLORD ========================= */

    protected function landlordDashboard(User $user)
    {
        $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');

        return response()->json([
            'role' => 'landlord',

            'cards' => [
                [
                    'title' => 'My Properties',
                    'value' => $propertyIds->count(),
                    'icon'  => 'bi-buildings',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Units',
                    'value' => Unit::whereIn('property_id', $propertyIds)->count(),
                    'icon'  => 'bi-door-open',
                    'color' => 'success',
                ],
                [
                    'title' => 'Active Tenancies',
                    'value' => Tenancy::whereIn('unit_id', function ($q) use ($propertyIds) {
                        $q->select('id')
                          ->from('units')
                          ->whereIn('property_id', $propertyIds);
                    })->count(),
                    'icon'  => 'bi-person-check',
                    'color' => 'info',
                ],
            ],
        ]);
    }

    /* ========================= MANAGER ========================= */

    protected function managerDashboard(User $user)
    {
        $propertyIds = Property::where('manager_id', $user->id)->pluck('id');

        $actionRequired = Deposit::with([
                'tenancy.tenant',
                'tenancy.unit.property'
            ])
            ->whereIn('status', [
                'under_inspection',
                'deductions_applied',
                'pending_refund'
            ])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'role' => 'manager',

            'cards' => [
                [
                    'title' => 'Managed Properties',
                    'value' => $propertyIds->count(),
                    'icon'  => 'bi-buildings',
                    'color' => 'primary',
                ],
            ],

            'widgets' => [
                'action_required_deposits' => $actionRequired
            ]
        ]);
    }

    /* ========================= TENANT ========================= */

    protected function tenantDashboard(User $user)
    {
        $tenancy = Tenancy::where('tenant_id', $user->id)
            ->with('unit.property')
            ->first();

        return response()->json([
            'role' => 'tenant',

            'cards' => [
                [
                    'title' => 'My Unit',
                    'value' => $tenancy ? 'Active' : 'None',
                    'icon'  => 'bi-house-door',
                    'color' => 'success',
                ],
                [
                    'title' => 'Tenancy Status',
                    'value' => $tenancy ? 'Occupied' : 'N/A',
                    'icon'  => 'bi-info-circle',
                    'color' => 'info',
                ],
            ],
        ]);
    }
}