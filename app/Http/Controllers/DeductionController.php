<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    /**
     * List all deductions
     */
    public function index()
    {
        $deductions = Deduction::with([
            'deposit.tenancy.tenant',
            'deposit.tenancy.unit.property',
            'inspection',
            'approver'
        ])->get();

        return response()->json($deductions);
    }

    /**
     * Store a new deduction
     */
    public function store(Request $request)
    {
        $request->validate([
            'deposit_id'    => 'required|exists:deposits,id',
            'inspection_id' => 'nullable|exists:inspections,id',
            'description'   => 'nullable|string',
            'amount'        => 'required|numeric',
            'approved_by'   => 'nullable|exists:users,id',
            'approved_at'   => 'nullable|date',
        ]);

        $deduction = Deduction::create($request->only([
            'deposit_id',
            'inspection_id',
            'description',
            'amount',
            'approved_by',
            'approved_at',
        ]));

        return response()->json([
            'message' => 'Deduction created successfully',
            'data'    => $deduction,
        ], 201);
    }

    /**
     * Show a specific deduction
     */
    public function show(string $id)
    {
        $deduction = Deduction::with(['deposit.tenancy.tenant', 'inspection', 'approver'])->findOrFail($id);
        return response()->json($deduction);
    }

    /**
     * Update a deduction
     */
    public function update(Request $request, string $id)
    {
        $deduction = Deduction::findOrFail($id);

        $request->validate([
            'description'   => 'nullable|string',
            'amount'        => 'sometimes|numeric',
            'approved_by'   => 'nullable|exists:users,id',
            'approved_at'   => 'nullable|date',
        ]);

        $deduction->update($request->only([
            'description',
            'amount',
            'approved_by',
            'approved_at',
        ]));

        return response()->json([
            'message' => 'Deduction updated successfully',
            'data'    => $deduction,
        ]);
    }

    /**
     * Delete a deduction
     */
    public function destroy(string $id)
    {
        $deduction = Deduction::findOrFail($id);
        $deduction->delete();

        return response()->json([
            'message' => 'Deduction deleted successfully'
        ]);
    }

    public function approve(Deduction $deduction)
    {
        $deduction->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'approved_by' => $deduction->approved_by,
            'approved_at' => $deduction->approved_at,
            'approver'    => $deduction->approver,
        ]);
    }    
}