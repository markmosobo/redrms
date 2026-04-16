<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Deposit;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;

class DeductionController extends Controller
{
    use Auditable, NotifiesUsers;

    /**
     * List all deductions
     */
    public function index()
    {
        return response()->json(
            Deduction::with([
                'deposit.tenancy.tenant',
                'deposit.tenancy.unit.property',
                'inspection',
                'approver'
            ])->get()
        );
    }

    /**
     * Create deduction (inspection phase)
     */
    public function store(Request $request)
    {
        $request->validate([
            'deposit_id'    => 'required|exists:deposits,id',
            'inspection_id' => 'nullable|exists:inspections,id',
            'description'   => 'nullable|string',
            'amount'        => 'required|numeric|min:0',
        ]);

        $deduction = Deduction::create([
            'deposit_id'    => $request->deposit_id,
            'inspection_id' => $request->inspection_id,
            'description'   => $request->description,
            'amount'        => $request->amount,
            'status'        => 'pending',
        ]);

        $this->audit(
            'DEDUCTION_CREATED',
            auth()->id()
        );

        $deduction->load('deposit.tenancy');

        // 🔔 NOTIFY MANAGERS (NEW PENDING DEDUCTION)
        if ($deduction->deposit?->tenancy) {
            $this->notifyRoles(
                ['manager'],
                'New Deduction Pending Review',
                'A deduction of KES ' . $deduction->amount .
                ' has been added for Unit ' . $deduction->deposit->tenancy->unit_id,
                'deduction_created',
                $deduction->id,
                'deduction'
            );
        }

        return response()->json([
            'message' => 'Deduction created successfully',
            'data'    => $deduction,
        ], 201);
    }

    /**
     * Update deduction
     */
    public function update(Request $request, string $id)
    {
        $deduction = Deduction::findOrFail($id);

        if ($deduction->status === 'approved') {
            return response()->json([
                'message' => 'Cannot update approved deduction'
            ], 409);
        }

        $request->validate([
            'description' => 'nullable|string',
            'amount'      => 'sometimes|numeric|min:0',
        ]);

        $deduction->update($request->only(['description', 'amount']));

        $this->audit('DEDUCTION_UPDATED', auth()->id());

        return response()->json([
            'message' => 'Deduction updated successfully',
            'data'    => $deduction
        ]);
    }

    /**
     * Delete deduction
     */
    public function destroy(string $id)
    {
        $deduction = Deduction::findOrFail($id);

        if ($deduction->status === 'approved') {
            return response()->json([
                'message' => 'Cannot delete approved deduction'
            ], 409);
        }

        $this->audit('DEDUCTION_DELETED', auth()->id());

        $deduction->delete();

        return response()->json([
            'message' => 'Deduction deleted successfully'
        ]);
    }

    /**
     * Approve deduction
     */
    public function approve(Deduction $deduction)
    {
        if ($deduction->status === 'approved') {
            return response()->json(['message' => 'Already approved'], 409);
        }

        DB::transaction(function () use ($deduction) {

            $deduction->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $deposit = Deposit::with('deductions')->find($deduction->deposit_id);

            if ($deposit) {

                $totalDeducted = $deposit->deductions()
                    ->where('status', 'approved')
                    ->sum('amount');

                $balance = max(0, $deposit->amount_received - $totalDeducted);

                $deposit->update([
                    'amount_deducted' => $totalDeducted,
                    'balance'         => $balance,
                    'status'          => 'pending_refund',
                ]);

                Refund::updateOrCreate(
                    ['deposit_id' => $deposit->id],
                    [
                        'refundable_amount' => $balance,
                        'status'            => 'pending',
                    ]
                );
            }
        });

        $deduction->load('deposit.tenancy');

        $this->audit('DEDUCTION_APPROVED', auth()->id());

        // 🔔 NOTIFY STAKEHOLDERS
        if ($deduction->deposit?->tenancy) {

            $tenancy = $deduction->deposit->tenancy;

            $this->notifyUser(
                $tenancy->tenant_id,
                'Deposit Deduction Approved',
                'A deduction of KES ' . $deduction->amount . ' was approved.',
                'deduction_approved',
                $deduction->id,
                'deduction'
            );

            $this->notifyRoles(
                ['manager'],
                'Deduction Approved',
                'A deduction has been approved for Unit ' . $tenancy->unit_id,
                'deduction_approved',
                $deduction->id,
                'deduction'
            );
        }

        return response()->json([
            'message' => 'Deduction approved successfully',
            'data'    => $deduction
        ]);
    }

    /**
     * Reject deduction
     */
    public function reject(Request $request, Deduction $deduction)
    {
        $request->validate([
            'reason' => 'nullable|string'
        ]);

        if ($deduction->status === 'approved') {
            return response()->json([
                'message' => 'Cannot reject approved deduction'
            ], 409);
        }

        $deduction->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
        ]);

        $this->audit('DEDUCTION_REJECTED', auth()->id());

        if ($deduction->deposit?->tenancy) {

            $tenancy = $deduction->deposit->tenancy;

            $this->notifyUser(
                $tenancy->tenant_id,
                'Deduction Rejected',
                'A proposed deduction was rejected.',
                'deduction_rejected',
                $deduction->id,
                'deduction'
            );
        }

        return response()->json([
            'message' => 'Deduction rejected successfully',
            'data'    => $deduction
        ]);
    }
}