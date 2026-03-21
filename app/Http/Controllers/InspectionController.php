<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    /**
     * Display a listing of inspections
     */
    public function index()
    {
        $inspections = Inspection::with(['tenancy.tenant', 'tenancy.unit.property', 'creator', 'deductions'])->get();
        return response()->json($inspections);
    }

    /**
     * Store a newly created inspection
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenancy_id'      => 'required|exists:tenancies,id',
            'inspection_date' => 'required|date',
            'notes'           => 'nullable|string',
            'inspection_type' => 'required|in:move_in,move_out',
        ]);

        // Create inspection, automatically set created_by as current user
        $inspection = Inspection::create([
            'tenancy_id'      => $request->tenancy_id,
            'inspection_date' => $request->inspection_date,
            'notes'           => $request->notes,
            'inspection_type' => $request->inspection_type,
            'created_by'      => auth()->id(), // current user
        ]);

        return response()->json([
            'message' => 'Inspection created successfully',
            'data'    => $inspection
        ], 201);
    }

    /**
     * Display a specific inspection
     */
    public function show(string $id)
    {
        $inspection = Inspection::with(['tenancy.tenant', 'tenancy.unit.property', 'creator', 'deductions'])->findOrFail($id);
        return response()->json($inspection);
    }

    /**
     * Update a specific inspection
     */
    public function update(Request $request, string $id)
    {
        $inspection = Inspection::findOrFail($id);

        $request->validate([
            'inspection_date' => 'sometimes|date',
            'notes'           => 'nullable|string',
            'inspection_type' => 'sometimes|in:move_in,move_out',
        ]);

        $inspection->update($request->only([
            'inspection_date',
            'notes',
            'inspection_type'
        ]));

        return response()->json([
            'message' => 'Inspection updated successfully',
            'data'    => $inspection
        ]);
    }

    /**
     * Remove an inspection
     */
    public function destroy(string $id)
    {
        $inspection = Inspection::findOrFail($id);
        $inspection->delete();

        return response()->json([
            'message' => 'Inspection deleted successfully'
        ]);
    }
}