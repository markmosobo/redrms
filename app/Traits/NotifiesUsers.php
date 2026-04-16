<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Inspection;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;

class TenancyController extends Controller
{
    use Auditable, NotifiesUsers;

    public function index()
    {
        return response()->json(
            Tenancy::with(['tenant', 'unit.property', 'deposit'])->get()
        );
    }

    /**
     * 🔥 CREATE TENANCY
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id'      => 'required|exists:users,id',
            'unit_id'        => 'required|exists:units,id',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'deposit_amount' => 'required|numeric|min:0',
        ]);

        $unit = Unit::where('id', $request->unit_id)
            ->where('status', 'vacant')
            ->firstOrFail();

        $tenancy = null;

        DB::transaction(function () use ($request, $unit, &$tenancy) {

            $tenancy = Tenancy::create([
                'tenant_id'      => $request->tenant_id,
                'unit_id'        => $request->unit_id,
                'start_date'     => $request->start_date,
                'end_date'       => $request->end_date,
                'deposit_amount' => $request->deposit_amount,
                'status'         => 'active',
            ]);

            $this->createDepositForTenancy($tenancy);

            $unit->update(['status' => 'occupied']);

            User::where('id', $request->tenant_id)
                ->update(['status' => 'active']);
        });

        $this->audit('TENANCY_CREATED', auth()->id());

        // 🔔 NOTIFICATIONS (ALIGNED WITH TRAIT)

        $this->notifyUser(
            $tenancy->tenant_id,
            'Tenancy Activated',
            'Your tenancy for Unit ' . $unit->unit_number . ' has started.',
            'tenancy_created',
            $tenancy->id,
            'tenancy'
        );

        $this->notifyRoles(
            ['manager'],
            'New Tenancy Created',
            'A tenancy has been created for Unit ' . $unit->unit_number,
            'tenancy_created',
            $tenancy->id,
            'tenancy'
        );

        $this->notifyRoles(
            ['landlord'],
            'Unit Occupied',
            'Unit ' . $unit->unit_number . ' is now occupied.',
            'tenancy_created',
            $tenancy->id,
            'tenancy'
        );

        return response()->json([
            'message' => 'Tenancy created successfully',
            'data'    => $tenancy
        ], 201);
    }

    /**
     * 🔥 TERMINATE TENANCY
     */
    public function terminate(Tenancy $tenancy)
    {
        DB::transaction(function () use ($tenancy) {

            $tenancy->update([
                'status'   => 'terminated',
                'end_date' => now()
            ]);

            $tenancy->unit->update(['status' => 'vacant']);

            $deposit = Deposit::where('tenancy_id', $tenancy->id)
                ->where('status', 'held')
                ->first();

            if ($deposit) {

                $deposit->update(['status' => 'under_inspection']);

                Inspection::create([
                    'tenancy_id'      => $tenancy->id,
                    'unit_id'         => $tenancy->unit_id,
                    'inspection_type' => 'move_out',
                    'status'          => 'draft',
                ]);
            }

            $this->audit('TENANCY_TERMINATED', auth()->id());

            // 🔔 NOTIFICATIONS

            $this->notifyUser(
                $tenancy->tenant_id,
                'Tenancy Terminated',
                'Your tenancy for Unit ' . $tenancy->unit->unit_number . ' has ended.',
                'tenancy_terminated',
                $tenancy->id,
                'tenancy'
            );

            $this->notifyRoles(
                ['manager'],
                'Inspection Required',
                'Move-out inspection required for Unit ' . $tenancy->unit->unit_number,
                'inspection_required',
                $tenancy->id,
                'tenancy'
            );

            $this->notifyRoles(
                ['landlord'],
                'Unit Vacated',
                'Unit ' . $tenancy->unit->unit_number . ' is now vacant.',
                'tenancy_terminated',
                $tenancy->id,
                'tenancy'
            );
        });

        return response()->noContent();
    }

    /**
     * 🚀 CREATE DEPOSIT
     */
    private function createDepositForTenancy(Tenancy $tenancy)
    {
        $deposit = Deposit::create([
            'tenancy_id'      => $tenancy->id,
            'required_amount' => $tenancy->deposit_amount,
            'amount_received' => 0,
            'current_balance' => $tenancy->deposit_amount,
            'status'          => 'active'
        ]);

        $this->notifyUser(
            $tenancy->tenant_id,
            'Deposit Created',
            'Deposit of KES ' . $tenancy->deposit_amount . ' has been created.',
            'deposit_created',
            $deposit->id,
            'deposit'
        );

        $this->notifyRoles(
            ['manager'],
            'Deposit Tracking Started',
            'Deposit tracking started for Unit ' . $tenancy->unit->unit_number,
            'deposit_created',
            $deposit->id,
            'deposit'
        );

        return $deposit;
    }

    /**
     * 🔥 MY ACTIVE TENANCY
     */
    public function myActiveTenancy()
    {
        $tenancy = Tenancy::with(['unit.property'])
            ->where('tenant_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            return response()->json([
                'message' => 'No active tenancy found'
            ], 404);
        }

        return response()->json($tenancy);
    }
}