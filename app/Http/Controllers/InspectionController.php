<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Inspection;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;

class InspectionController extends Controller
{
    use Auditable, NotifiesUsers;

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
     * STORE INSPECTION (MANAGER / SYSTEM)
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

        $inspection->load('tenancy.unit', 'tenancy.tenant');

        $this->audit('INSPECTION_CREATED', auth()->id());

        // 🔔 NOTIFICATIONS (VERY IMPORTANT)
        $this->notifyUser(
            $inspection->tenancy->tenant_id,
            'Inspection Scheduled',
            'Your property inspection has been scheduled for ' . $inspection->inspection_date,
            'inspection_scheduled',
            $inspection->id,
            'inspection'
        );

        $this->notifyRoles(
            ['manager'],
            'Inspection Scheduled',
            'A move-' . $inspection->inspection_type . ' inspection has been scheduled for Unit ' . $inspection->tenancy->unit->unit_number,
            'inspection_scheduled',
            $inspection->id,
            'inspection'
        );

        $this->notifyRoles(
            ['landlord'],
            'Inspection Created',
            'An inspection was scheduled for Unit ' . $inspection->tenancy->unit->unit_number,
            'inspection_scheduled',
            $inspection->id,
            'inspection'
        );

        return response()->json([
            'message' => 'Inspection created successfully',
            'data'    => $inspection
        ], 201);
    }

    /**
     * INSPECTION → DEDUCTION SUMMARY
     * Used by Deductions dashboard
     */
    public function byInspection()
    {
        $inspections = Inspection::query()
            ->where('inspection_type', 'move_out')
            ->whereHas('deductions') // only inspections with deductions
            ->with([
                'tenancy.tenant',
                'tenancy.unit.property',
                'deductions.approver',
            ])
            ->get()
            ->map(function ($inspection) {

                $total = $inspection->deductions
                    ->whereIn('status', ['pending', 'approved'])
                    ->sum('amount');

                return [
                    'inspection_id'   => $inspection->id,
                    'inspection_date' => $inspection->inspection_date,

                    'tenant' => $inspection->tenancy->tenant,

                    'unit' => [
                        'unit_number' => $inspection->tenancy->unit->unit_number ?? null,
                        'property'    => $inspection->tenancy->unit->property ?? null,
                    ],

                    'deductions' => $inspection->deductions,

                    'total_deductions' => $total,
                ];
            });

        return response()->json($inspections);
    }    


    /**
     * COMPLETE INSPECTION (NO DEDUCTIONS)
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

            $inspection = Inspection::where('tenancy_id', $deposit->tenancy_id)
                ->where('inspection_type', 'move_out')
                ->first();

            if ($inspection) {
                $inspection->update([
                    'notes'  => $request->damages . "\n" . $request->remarks,
                    'status' => 'completed',
                ]);

                // 🔔 NOTIFY TENANT
                $this->notifyUser(
                    $inspection->tenancy->tenant_id,
                    'Inspection Completed',
                    'Your move-out inspection is complete. Deposit processing will follow.',
                    'inspection_completed',
                    $inspection->id,
                    'inspection'
                );

                // 🔔 NOTIFY MANAGERS
                $this->notifyRoles(
                    ['manager'],
                    'Inspection Completed',
                    'Move-out inspection completed for Unit ' . $inspection->tenancy->unit->unit_number,
                    'inspection_completed',
                    $inspection->id,
                    'inspection'
                );
            }
        });

        $this->audit('INSPECTION_COMPLETED', auth()->id());

        return response()->json([
            'message' => 'Inspection completed successfully'
        ]);
    }

    /**
     * COMPLETE INSPECTION WITH DEDUCTIONS
     */
    public function completeInspection(Request $request)
    {
        $request->validate([
            'inspection_id' => 'required|exists:inspections,id',
            'damages'       => 'nullable|string',
            'remarks'       => 'nullable|string',
            'deductions'    => 'nullable|array',
            'deductions.*.amount' => 'required|numeric|min:0',
        ]);

        $inspection = Inspection::with('tenancy.unit')->findOrFail($request->inspection_id);

        DB::transaction(function () use ($request, $inspection) {

            $inspection->update([
                'notes' => trim(($request->damages ?? '') . "\n" . ($request->remarks ?? '')),
                'status' => 'completed',
                'inspection_date' => now(),
                'created_by' => auth()->id(),
            ]);

            // Create deductions
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

            // 🔔 NOTIFICATIONS

            $this->notifyUser(
                $inspection->tenancy->tenant_id,
                'Inspection Completed with Deductions',
                'Your inspection is complete. Deductions are being processed.',
                'inspection_deductions',
                $inspection->id,
                'inspection'
            );

            $this->notifyRoles(
                ['manager'],
                'Inspection Completed with Deductions',
                'Deductions have been recorded for Unit ' . $inspection->tenancy->unit->unit_number,
                'inspection_deductions',
                $inspection->id,
                'inspection'
            );

            $this->notifyRoles(
                ['landlord'],
                'Deposit Processing Required',
                'Unit ' . $inspection->tenancy->unit->unit_number . ' has deductions pending approval.',
                'inspection_deductions',
                $inspection->id,
                'inspection'
            );
        });

        $this->audit('INSPECTION_COMPLETED_WITH_DEDUCTIONS', auth()->id());

        return response()->json([
            'message' => 'Inspection completed with deductions'
        ]);
    }
}