<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Tenancy;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deposits = Deposit::with(['tenancy.tenant', 'tenancy.unit.property', 'deductions'])->get();
        return response()->json($deposits);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenancy_id'     => 'required|exists:tenancies,id',
            'amount_received'=> 'required|numeric|min:0',
            'received_date'  => 'nullable|date',
        ]);

        $deposit = Deposit::create([
            'tenancy_id'     => $request->tenancy_id,
            'amount_received'=> $request->amount_received,
            'received_date'  => $request->received_date ?? now(),
            'status'         => 'held',
        ]);

        return response()->json([
            'message' => 'Deposit recorded successfully',
            'data'    => $deposit
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $deposit = Deposit::with(['tenancy.tenant', 'tenancy.unit.property', 'deductions'])->findOrFail($id);
        return response()->json($deposit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $deposit = Deposit::findOrFail($id);

        $request->validate([
            'amount_received'=> 'sometimes|numeric|min:0',
            'received_date'  => 'sometimes|date',
            'status'         => 'sometimes|in:held,partially_deducted,refunded',
        ]);

        $deposit->update($request->only(['amount_received', 'received_date', 'status']));

        return response()->json([
            'message' => 'Deposit updated successfully',
            'data'    => $deposit
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deposit = Deposit::findOrFail($id);
        $deposit->delete();

        return response()->json([
            'message' => 'Deposit deleted successfully'
        ]);
    }

    public function finalizeDeposit($depositId)
    {
        return DB::transaction(function () use ($depositId) {

            $deposit = Deposit::with([
                'deductions',
                'refund'
            ])->lockForUpdate()->findOrFail($depositId);

            // ❌ Prevent double finalization
            if ($deposit->refund) {
                abort(400, 'Deposit already finalized');
            }

            // ❌ Ensure all deductions are approved
            if ($deposit->deductions()->whereNull('approved_at')->exists()) {
                abort(400, 'All deductions must be approved before finalizing');
            }

            $totalDeductions = $deposit->deductions()->sum('amount');

            $refundableAmount = max(
                $deposit->amount_received - $totalDeductions,
                0
            );

            // ✅ Create refund
            $refund = Refund::create([
                'deposit_id'        => $deposit->id,
                'refundable_amount' => $refundableAmount,
                'refund_date'       => now(),
                'approval_status'   => 'pending',
            ]);

            // ✅ Update deposit status
            $deposit->update([
                'status' => $refundableAmount > 0
                    ? 'partially_deducted'
                    : 'refunded',
            ]);

            return response()->json([
                'deposit_id'        => $deposit->id,
                'deposit_amount'    => $deposit->amount_received,
                'total_deductions'  => $totalDeductions,
                'refundable_amount' => $refundableAmount,
                'refund'            => $refund,
            ]);
        });
    }      
}