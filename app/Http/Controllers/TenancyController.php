<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenancyController extends Controller
{
    public function index()
    {
        $tenancies = Tenancy::with(['tenant', 'unit.property','deposit'])->get();
        return response()->json($tenancies);
    }

    /**
     * 🔥 MAIN TENANCY CREATION (PRIMARY FLOW)
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id'       => 'required|exists:users,id',
            'unit_id'         => 'required|exists:units,id',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'deposit_amount'  => 'required|numeric|min:0',
        ]);

        $unit = Unit::where('id', $request->unit_id)
            ->where('status', 'vacant')
            ->first();

        if (!$unit) {
            return response()->json(['message' => 'Selected unit is not vacant'], 422);
        }

        $tenancy = null;

        DB::transaction(function () use ($request, $unit, &$tenancy) {

            // 1. Create tenancy
            $tenancy = Tenancy::create([
                'tenant_id'      => $request->tenant_id,
                'unit_id'        => $request->unit_id,
                'start_date'     => $request->start_date,
                'end_date'       => $request->end_date,
                'deposit_amount' => $request->deposit_amount,
                'status'         => 'active',
            ]);

            // 2. 🚀 AUTO CREATE DEPOSIT (CORE FEATURE)
            $this->createDepositForTenancy($tenancy);

            // 3. Mark unit occupied
            $unit->update(['status' => 'occupied']);

            // 4. Activate tenant
            User::where('id', $request->tenant_id)
                ->update(['status' => 'active']);
        });

        return response()->json([
            'message' => 'Tenancy created successfully',
            'data'    => $tenancy
        ], 201);
    }

    /**
     * 🔥 SHOW
     */
    public function show(string $id)
    {
        $tenancy = Tenancy::with(['tenant', 'unit.property'])->findOrFail($id);
        return response()->json($tenancy);
    }

    /**
     * 🔥 UPDATE TENANCY
     */
    public function update(Request $request, string $id)
    {
        $tenancy = Tenancy::findOrFail($id);

        $request->validate([
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'deposit_amount' => 'required|numeric|min:0',
            'status'         => 'required|in:active,terminated',
        ]);

        $tenancy->update($request->only([
            'start_date',
            'end_date',
            'deposit_amount',
            'status',
        ]));

        if ($request->status === 'terminated') {
            $tenancy->unit->update(['status' => 'vacant']);
        }

        return response()->json([
            'message' => 'Tenancy updated successfully',
            'data'    => $tenancy
        ]);
    }

    /**
     * 🔥 DELETE TENANCY
     */
    public function destroy(string $id)
    {
        $tenancy = Tenancy::findOrFail($id);

        $tenancy->unit->update(['status' => 'vacant']);

        $tenancy->delete();

        return response()->json([
            'message' => 'Tenancy deleted successfully'
        ]);
    }

    /**
     * 🔥 GET ACTIVE TENANCY BY UNIT
     */
    public function activeByUnit($unitId)
    {
        $tenancy = Tenancy::where('unit_id', $unitId)
            ->where('status', 'active')
            ->first();

        return response()->json($tenancy);
    }

    /**
     * 🔥 SHOW ACTIVE TENANCY FOR UNIT (RELATION VERSION)
     */
    public function showTenancy(Unit $unit)
    {
        return $unit->activeTenancy()->with('tenant')->first();
    }

    /**
     * 🔥 LEGACY STORE (NOW ALSO AUTO-DEPOSIT ENABLED)
     */
    public function storeTenancy(Request $request, Unit $unit)
    {
        if ($unit->activeTenancy) {
            return response()->json([
                'message' => 'Unit already has an active tenancy'
            ], 422);
        }

        $tenancy = Tenancy::create([
            'tenant_id'      => $request->tenant_id,
            'unit_id'        => $unit->id,
            'start_date'     => $request->start_date,
            'deposit_amount' => $request->deposit_amount,
            'status'         => 'active'
        ]);

        // 🚀 AUTO DEPOSIT
        $this->createDepositForTenancy($tenancy);

        $unit->update(['status' => 'occupied']);

        return response()->json($tenancy, 201);
    }

    /**
     * 🔥 UPDATE TENANCY (ALT)
     */
    public function updateTenancy(Request $request, Tenancy $tenancy)
    {
        $tenancy->update(
            $request->only([
                'start_date',
                'end_date',
                'deposit_amount',
                'status'
            ])
        );

        return response()->json($tenancy);
    }

    /**
     * 🔥 TERMINATE TENANCY
     */
    public function terminate(Tenancy $tenancy)
    {
        DB::transaction(function () use ($tenancy) {

            // Terminate tenancy
            $tenancy->update([
                'status' => 'terminated',
                'end_date' => now()
            ]);

            // Free unit
            $tenancy->unit->update(['status' => 'vacant']);

            $deposit = Deposit::where('tenancy_id', $tenancy->id)
                ->where('status', 'held')
                ->first();

            if ($deposit) {
                $deposit->update([
                    'status' => 'under_inspection'
                ]);

                // 🔔 NOTIFY MANAGERS
                $managers = User::where('role', 'manager')->get();

                foreach ($managers as $manager) {
                    Notification::create([
                        'user_id' => $manager->id,
                        'title' => 'Inspection Required',
                        'message' => 'Unit ' . $tenancy->unit->unit_number .
                            ' deposit is ready for inspection.',
                        'type' => 'inspection_required'
                    ]);
                }
            }        
        });

        return response()->noContent();
    }

    /**
     * 🔥 ASSIGN TENANT TO UNIT
     */
    public function assign(Request $request)
    {
        $request->validate([
            'tenant_id'      => 'required|exists:users,id',
            'unit_id'        => 'required|exists:units,id',
            'start_date'     => 'required|date',
            'deposit_amount' => 'required|numeric'
        ]);

        $unit = Unit::with('activeTenancy')->findOrFail($request->unit_id);

        if ($unit->activeTenancy) {
            return response()->json([
                'message' => 'Unit already occupied'
            ], 422);
        }

        $tenancy = Tenancy::create([
            'tenant_id'      => $request->tenant_id,
            'unit_id'        => $request->unit_id,
            'start_date'     => $request->start_date,
            'deposit_amount' => $request->deposit_amount,
            'status'         => 'active'
        ]);

        // 🚀 AUTO DEPOSIT
        $this->createDepositForTenancy($tenancy);

        $unit->update(['status' => 'occupied']);

        return response()->json([
            'message' => 'Tenant assigned successfully',
            'tenancy' => $tenancy
        ]);
    }

    /**
     * 🚀 CORE LOGIC: AUTO DEPOSIT CREATION
     */
    private function createDepositForTenancy(Tenancy $tenancy)
    {
        $deposit = Deposit::create([
            'tenancy_id'        => $tenancy->id,

            // 🔥 NEW: expected full deposit
            'required_amount'   => $tenancy->deposit_amount,

            // what has been paid so far
            'amount_received'   => 0,

            // remaining balance = required - received
            'current_balance'   => $tenancy->deposit_amount,

            'received_date'     => null,

            // starts as NOT paid yet
            'status'            => 'active'
        ]);

        /*
        |--------------------------------------------------------------------------
        | 🔔 NOTIFICATIONS
        |--------------------------------------------------------------------------
        */

        // 1. Notify tenant
        Notification::create([
            'user_id' => $tenancy->tenant_id,
            'title'   => 'Deposit Created',
            'message' => 'Your deposit of KES ' . $tenancy->deposit_amount .
                        ' has been set for Unit ' . $tenancy->unit->unit_number,
            'type'    => 'deposit_created'
        ]);

        // 2. Notify managers
        $managers = User::where('role', 'manager')->get();

        foreach ($managers as $manager) {
            Notification::create([
                'user_id' => $manager->id,
                'title'   => 'New Deposit Registered',
                'message' => 'Deposit tracking started for Unit ' .
                            $tenancy->unit->unit_number .
                            ' (Expected: KES ' . $tenancy->deposit_amount . ')',
                'type'    => 'deposit_created'
            ]);
        }

        return $deposit;
    }
}