<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Inspection;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class InspectionController extends Controller
{
    use Auditable;

    /**
     * Display inspections
     */
    public function index()
    {
        return response()->json(
            Inspection::with([
                'tenancy.tenant',
                'tenancy.unit.property',
                'creator',
                'deductions'
            ])->get()
        );
    }

    /**
     * Store inspection
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenancy_id'      => 'required|exists:tenancies,id',
            'inspection_date' => 'required|date',
            'notes'           => 'nullable|string',
            'inspection_type' => 'required|in:move_in,move_out',
        ]);

        $inspection = Inspection::create([
            'tenancy_id'      => $request->tenancy_id,
            'inspection_date' => $request->inspection_date,
            'notes'           => $request->notes,
            'inspection_type' => $request->inspection_type,
            'created_by'      => auth()->id(),
        ]);

        $this->audit(
            'INSPECTION_CREATED',
            json_encode([
                'inspection_id'   => $inspection->id,
                'tenancy_id'      => $inspection->tenancy_id,
                'inspection_type' => $inspection->inspection_type,
                'inspection_date' => $inspection->inspection_date,
            ])
        );

        return response()->json([
            'message' => 'Inspection created successfully',
            'data'    => $inspection
        ], 201);
    }

    /**
     * Show inspection
     */
    public function show(string $id)
    {
        return response()->json(
            Inspection::with([
                'tenancy.tenant',
                'tenancy.unit.property',
                'creator',
                'deductions'
            ])->findOrFail($id)
        );
    }

    /**
     * Update inspection
     */
    public function update(Request $request, string $id)
    {
        $inspection = Inspection::findOrFail($id);

        $request->validate([
            'inspection_date' => 'sometimes|date',
            'notes'           => 'nullable|string',
            'inspection_type' => 'sometimes|in:move_in,move_out',
        ]);

        $old = $inspection->only([
            'inspection_date',
            'notes',
            'inspection_type'
        ]);

        $inspection->update($request->only([
            'inspection_date',
            'notes',
            'inspection_type'
        ]));

        $this->audit(
            'INSPECTION_UPDATED',
            json_encode([
                'inspection_id' => $inspection->id,
                'old' => $old,
                'new' => $inspection->only([
                    'inspection_date',
                    'notes',
                    'inspection_type'
                ])
            ])
        );

        return response()->json([
            'message' => 'Inspection updated successfully',
            'data'    => $inspection
        ]);
    }

    /**
     * Delete inspection
     */
    public function destroy(string $id)
    {
        $inspection = Inspection::findOrFail($id);

        $this->audit(
            'INSPECTION_DELETED',
            json_encode([
                'inspection_id' => $inspection->id,
                'tenancy_id'    => $inspection->tenancy_id
            ])
        );

        $inspection->delete();

        return response()->json([
            'message' => 'Inspection deleted successfully'
        ]);
    }

    /**
     * Complete inspection (no deductions)
     */
    public function complete(Request $request)
    {
        $request->validate([
            'deposit_id' => 'required|exists:deposits,id',
            'damages'    => 'nullable|string',
            'remarks'    => 'nullable|string',
        ]);

        $deposit = Deposit::findOrFail($request->deposit_id);

        DB::transaction(function () use ($request, $deposit) {

            $deposit->update([
                'status' => 'deductions_applied',
            ]);

            Inspection::where('tenancy_id', $deposit->tenancy_id)
                ->where('inspection_type', 'move_out')
                ->update([
                    'notes'  => $request->damages . "\n" . $request->remarks,
                    'status' => 'completed',
                ]);
        });

        $this->audit(
            'INSPECTION_COMPLETED',
            json_encode([
                'deposit_id' => $deposit->id,
                'tenancy_id'  => $deposit->tenancy_id
            ])
        );

        return response()->json([
            'message' => 'Inspection completed successfully'
        ]);
    }

    /**
     * Complete inspection with deductions
     */
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

            $inspection->update([
                'notes' => trim(
                    ($request->damages ?? '') . "\n" . ($request->remarks ?? '')
                ),
                'status' => 'completed',
                'inspection_date' => now(),
                'created_by' => auth()->id(),
            ]);

            if (!empty($request->deductions)) {
                foreach ($request->deductions as $item) {
                    Deduction::create([
                        'deposit_id'    => $inspection->tenancy->deposit->id ?? null,
                        'inspection_id' => $inspection->id,
                        'description'   => $item['description'] ?? null,
                        'amount'        => $item['amount'],
                        'status'        => 'pending'
                    ]);
                }
            }

            $deposit = Deposit::where('tenancy_id', $inspection->tenancy_id)->first();

            if ($deposit) {
                $deposit->update([
                    'status' => 'deductions_applied'
                ]);
            }
        });

        $this->audit(
            'INSPECTION_COMPLETED_WITH_DEDUCTIONS | ' . json_encode([
                'inspection_id' => $inspection->id,
                'deductions_count' => count($request->deductions ?? [])
            ]),
            auth()->id()
        );

        return response()->json([
            'message' => 'Inspection completed with deductions'
        ]);
    }

    /**
     * Grouped inspection view
     */
    public function byInspection()
    {
        return response()->json(
            Inspection::with([
                'tenancy.tenant',
                'tenancy.unit.property',
                'deductions',
                'deductions.approver'
            ])
            ->where('inspection_type', 'move_out')
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
            })
        );
    }
}