<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $this->audit(
            'Deduction created (KES ' . number_format($deduction->amount, 2) . ')',
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

        $this->audit(
            'Deduction #' . $deduction->id . ' updated'
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
        $deduction->delete();

        $this->audit(
            'Deduction #' . $deduction->id . ' deleted'
        );

        return response()->json([
            'message' => 'Deduction deleted successfully'
        ]);
    }

    public function approve(Deduction $deduction)
    {
        if ($deduction->status === 'approved') {
            return response()->json(['message' => 'Already approved'], 409);
        }

        $deduction->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $deduction->load('approver', 'deposit.tenancy');

        $this->audit(
            'Deduction #' . $deduction->id . ' approved (KES ' . number_format($deduction->amount, 2) . ')'
        );

        $deposit = $deduction->deposit;

        if ($deposit) {

            // 1. UPDATE DEPOSIT DEDUCTIONS TOTAL
            $totalDeducted = Deduction::where('deposit_id', $deposit->id)
                ->where('status', 'approved')
                ->sum('amount');

            $balance = $deposit->amount_received - $totalDeducted;

            $deposit->update([
                'amount_deducted' => $totalDeducted,
                'balance' => $balance,
                'status' => 'pending_refund',
            ]);

            // 2. CREATE OR UPDATE REFUND RECORD
            $refundAmount = max($balance, 0);

            \App\Models\Refund::updateOrCreate(
                [
                    'deposit_id' => $deposit->id,
                ],
                [
                    'refundable_amount' => $refundAmount,
                    'status' => 'pending',
                ]
            );
        }

        return response()->json([
            'approved_by' => $deduction->approved_by,
            'approved_at' => $deduction->approved_at,
            'approver'    => $deduction->approver,
            'deposit'     => $deposit->fresh(),
        ]);
    } 

    public function reject(Request $request, Deduction $deduction)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $deduction->status = 'rejected';
        $deduction->rejection_reason = $request->reason;
        $deduction->approved_by = Auth::id(); // same actor field
        $deduction->approved_at = now(); // action timestamp (or rename if you prefer)
        $deduction->save();

        $this->audit(
            'Deduction #' . $deduction->id . ' rejected'
        );

        return response()->json([
            'message' => 'Deduction rejected successfully',
            'approved_by' => $deduction->approved_by,
            'approved_at' => $deduction->approved_at,
            'approver' => $deduction->approver,
            'rejection_reason' => $deduction->rejection_reason,
        ]);
    }       
}