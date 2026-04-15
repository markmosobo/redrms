<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Inspection;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function complete(Request $request)
    {
        $request->validate([
            'deposit_id'  => 'required|exists:deposits,id',
            'damages'     => 'nullable|string',
            'deductions'  => 'required|numeric|min:0',
            'remarks'     => 'nullable|string',
        ]);

        $deposit = Deposit::findOrFail($request->deposit_id);

        DB::transaction(function () use ($request, $deposit) {

            // 1. Update deposit state
            $deposit->update([
                'status' => 'deductions_applied',
            ]);

            // 2. Find related inspection via TENANCY (correct relationship)
            Inspection::where('tenancy_id', $deposit->tenancy_id)
                ->where('inspection_type', 'move_out')
                ->update([
                    'notes' => $request->damages . "\n" . $request->remarks,
                    'status' => 'completed',
                ]);
        });

        return response()->json([
            'message' => 'Inspection completed successfully'
        ]);
    } 
    
    public function completeInspection(Request $request)
    {
        $request->validate([
            'inspection_id' => 'required|exists:inspections,id',
            'damages'       => 'nullable|string',
            'remarks'       => 'nullable|string',
            'deductions'    => 'nullable|array',
            'deductions.*.description' => 'nullable|string',
            'deductions.*.amount'      => 'required|numeric|min:0',
        ]);

        $inspection = Inspection::findOrFail($request->inspection_id);

        DB::transaction(function () use ($request, $inspection) {

            // 1. Update inspection report
            $inspection->update([
                'notes' => trim(
                    ($request->damages ?? '') . "\n" . ($request->remarks ?? '')
                ),
                'status' => 'completed',
                'inspection_date' => now(),
                'created_by' => auth()->id(),
            ]);

            // 2. Create deduction records
            if (!empty($request->deductions)) {
                foreach ($request->deductions as $item) {
                    Deduction::create([
                        'deposit_id'     => $inspection->tenancy->deposit->id ?? null,
                        'inspection_id'  => $inspection->id,
                        'description'    => $item['description'] ?? null,
                        'amount'         => $item['amount'],
                        'status'         => 'pending'
                    ]);
                }
            }

            // 3. Update deposit state
            $deposit = Deposit::where('tenancy_id', $inspection->tenancy_id)->first();

            if ($deposit) {
                $deposit->update([
                    'status' => 'deductions_applied'
                ]);
            }
        });

        return response()->json([
            'message' => 'Inspection completed with deductions'
        ]);
    } 
    
    public function byInspection()
    {
        $inspections = Inspection::with([
            'tenancy.tenant',
            'tenancy.unit.property',
            'deductions',        // relationship
            'deductions.approver'
        ])->where('inspection_type', 'move_out')
        ->get()
        ->map(function ($inspection) {
            return [
                'inspection_id' => $inspection->id,
                'tenant'        => $inspection->tenancy->tenant,
                'unit'          => $inspection->tenancy->unit,
                'inspection_date' => $inspection->inspection_date,
                'total_deductions' => $inspection->deductions->sum('amount'),
                'deductions'    => $inspection->deductions,
            ];
        });

        return response()->json($inspections);
    }     
}