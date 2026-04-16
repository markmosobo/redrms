<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Deposit;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class DeductionController extends Controller
{
    use Auditable;

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
            auth()->id(),
            [
                'deduction_id' => $deduction->id,
                'deposit_id'   => $deduction->deposit_id,
                'amount'       => $deduction->amount
            ]
        );

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
        $deduction = Deduction::with([
            'deposit.tenancy.tenant',
            'inspection',
            'approver'
        ])->findOrFail($id);

        return response()->json($deduction);
    }

    /**
     * Update a deduction
     */
    public function update(Request $request, string $id)
    {
        $deduction = Deduction::findOrFail($id);

        if ($deduction->status === 'approved') {
            return response()->json([
                'message' => 'Cannot update an approved deduction'
            ], 409);
        }

        $request->validate([
            'description' => 'nullable|string',
            'amount'      => 'sometimes|numeric|min:0',
        ]);

        $old = $deduction->only([
            'description',
            'amount',
            'status'
        ]);

        $deduction->update($request->only([
            'description',
            'amount',
        ]));

        $this->audit(
            'DEDUCTION_UPDATED',
            auth()->id(),
            [
                'deduction_id' => $deduction->id,
                'old' => $old,
                'new' => $deduction->only(['description', 'amount', 'status'])
            ]
        );

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

        if ($deduction->status === 'approved') {
            return response()->json([
                'message' => 'Cannot delete an approved deduction'
            ], 409);
        }

        $this->audit(
            'DEDUCTION_DELETED',
            auth()->id(),
            [
                'deduction_id' => $deduction->id,
                'deposit_id'   => $deduction->deposit_id
            ]
        );

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
            return response()->json([
                'message' => 'Already approved'
            ], 409);
        }

        DB::transaction(function () use ($deduction) {

            $deduction->update([
                'status'       => 'approved',
                'approved_by'  => auth()->id(),
                'approved_at'  => now(),
            ]);

            $deposit = Deposit::with('deductions')->find($deduction->deposit_id);

            if ($deposit) {

                // recalc safely
                $totalDeducted = $deposit->deductions()
                    ->where('status', 'approved')
                    ->sum('amount');

                $balance = max(0, $deposit->amount_received - $totalDeducted);

                $deposit->update([
                    'amount_deducted' => $totalDeducted,
                    'balance'          => $balance,
                    'status'           => 'pending_refund',
                ]);

                // update or create refund
                Refund::updateOrCreate(
                    ['deposit_id' => $deposit->id],
                    [
                        'refundable_amount' => $balance,
                        'status'            => 'pending',
                    ]
                );
            }
        });

        $deduction->load('approver', 'deposit.tenancy');

        $this->audit(
            'DEDUCTION_APPROVED',
            auth()->id(),
            [
                'deduction_id' => $deduction->id,
                'amount'       => $deduction->amount
            ]
        );

        return response()->json([
            'message'  => 'Deduction approved successfully',
            'data'     => $deduction,
            'deposit'  => $deduction->deposit->fresh()
        ]);
    }

    /**
     * Reject deduction
     */
    public function reject(Request $request, Deduction $deduction)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($deduction->status === 'approved') {
            return response()->json([
                'message' => 'Cannot reject an approved deduction'
            ], 409);
        }

        $deduction->update([
            'status'            => 'rejected',
            'rejection_reason'  => $request->reason,
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
        ]);

        $this->audit(
            'DEDUCTION_REJECTED',
            auth()->id(),
            [
                'deduction_id' => $deduction->id,
                'reason'       => $request->reason
            ]
        );

        return response()->json([
            'message' => 'Deduction rejected successfully',
            'data'    => $deduction->load('approver')
        ]);
    }
}