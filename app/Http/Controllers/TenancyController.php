<?php

namespace App\Http\Controllers;

use App\Models\Tenancy;
use App\Models\Unit;
use Illuminate\Http\Request;

class TenancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenancies = Tenancy::with(['tenant', 'unit.property'])->get();
        return response()->json($tenancies);
    }

    /**
     * Store a newly created resource in storage.
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

        // Ensure unit is vacant
        $unit = Unit::findOrFail($request->unit_id);
        if ($unit->status === 'occupied') {
            return response()->json([
                'message' => 'Unit is already occupied'
            ], 422);
        }

        $tenancy = Tenancy::create([
            'tenant_id'      => $request->tenant_id,
            'unit_id'        => $request->unit_id,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'deposit_amount' => $request->deposit_amount,
            'status'         => 'active',
        ]);

        // Mark unit as occupied
        $unit->update(['status' => 'occupied']);

        return response()->json([
            'message' => 'Tenancy created successfully',
            'data'    => $tenancy
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tenancy = Tenancy::with(['tenant', 'unit.property'])->findOrFail($id);
        return response()->json($tenancy);
    }

    /**
     * Update the specified resource in storage.
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

        // If tenancy terminated, free the unit
        if ($request->status === 'terminated') {
            $tenancy->unit->update(['status' => 'vacant']);
        }

        return response()->json([
            'message' => 'Tenancy updated successfully',
            'data'    => $tenancy
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tenancy = Tenancy::findOrFail($id);

        // Free unit on delete
        $tenancy->unit->update(['status' => 'vacant']);

        $tenancy->delete();

        return response()->json([
            'message' => 'Tenancy deleted successfully'
        ]);
    }

    /**
     * Get active tenancy by unit
     */
    public function activeByUnit($unitId)
    {
        $tenancy = Tenancy::where('unit_id', $unitId)
                          ->where('status', 'active')
                          ->first();

        return response()->json($tenancy);
    }
}