<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Tenancy;
use App\Models\Notification;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class DepositController extends Controller
{
    use Auditable;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deposits = Deposit::with([
            'tenancy.tenant',
            'tenancy.unit.property',
            'deductions'
        ])->get();

        return response()->json($deposits);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenancy_id'      => 'required|exists:tenancies,id',
            'amount_received' => 'required|numeric|min:0',
            'received_date'   => 'nullable|date',
        ]);

        $deposit = Deposit::create([
            'tenancy_id'      => $request->tenancy_id,
            'amount_received' => $request->amount_received,
            'received_date'   => $request->received_date ?? now(),
            'status'          => 'active',
        ]);

        $this->audit(
            'DEPOSIT_CREATED: #' . $deposit->id .
            ' amount=' . number_format($deposit->amount_received, 2)
        );

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
        $deposit = Deposit::with([
            'tenancy.tenant',
            'tenancy.unit.property',
            'deductions'
        ])->findOrFail($id);

        return response()->json($deposit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $deposit = Deposit::findOrFail($id);

        $request->validate([
            'amount_received' => 'sometimes|numeric|min:0',
            'received_date'   => 'sometimes|date',
            'status'          => 'sometimes|in:held,partially_deducted,refunded',
        ]);

        $old = $deposit->only([
            'amount_received',
            'received_date',
            'status'
        ]);

        $deposit->update($request->only([
            'amount_received',
            'received_date',
            'status'
        ]));

        $this->audit(
            'DEPOSIT_UPDATED: #' . $deposit->id .
            ' old=' . json_encode($old) .
            ' new=' . json_encode($deposit->only([
                'amount_received',
                'received_date',
                'status'
            ]))
        );

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

        $this->audit(
            'DEPOSIT_DELETED: #' . $deposit->id
        );

        $deposit->delete();

        return response()->json([
            'message' => 'Deposit deleted successfully'
        ]);
    }

    /**
     * Finalize deposit and compute refund
     */
    public function finalizeDeposit($depositId)
    {
        $result = DB::transaction(function () use ($depositId) {

            $deposit = Deposit::with(['deductions', 'refund'])
                ->lockForUpdate()
                ->findOrFail($depositId);

            // Prevent double finalization
            if ($deposit->refund) {
                abort(400, 'Deposit already finalized');
            }

            // Ensure deductions approved
            if ($deposit->deductions()->whereNull('approved_at')->exists()) {
                abort(400, 'All deductions must be approved before finalizing');
            }

            $totalDeductions = $deposit->deductions()->sum('amount');

            $refundableAmount = max(
                $deposit->amount_received - $totalDeductions,
                0
            );

            $refund = Refund::create([
                'deposit_id'        => $deposit->id,
                'refundable_amount' => $refundableAmount,
                'refund_date'       => now(),
                'approval_status'   => 'pending',
            ]);

            $deposit->update([
                'status' => $refundableAmount > 0
                    ? 'partially_deducted'
                    : 'refunded',
            ]);

            return [
                'deposit' => $deposit,
                'refund'  => $refund,
                'amount'  => $refundableAmount
            ];
        });

        $this->audit(
            'DEPOSIT_FINALIZED: #' . $result['deposit']->id .
            ' refundable=' . number_format($result['amount'], 2)
        );

        return response()->json([
            'deposit_id'        => $result['deposit']->id,
            'deposit_amount'    => $result['deposit']->amount_received,
            'total_deductions'  => $result['deposit']->deductions()->sum('amount'),
            'refundable_amount' => $result['amount'],
            'refund'            => $result['refund'],
        ]);
    }

    /**
     * Receive deposit payment
     */
    public function receive(Request $request, Deposit $deposit)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $deposit->amount_received += $request->amount;
        $deposit->received_date = now();

        $deposit->current_balance =
            max(0, $deposit->required_amount - $deposit->amount_received);

        if ($deposit->amount_received >= $deposit->required_amount) {
            $deposit->status = 'held';
        } else {
            $deposit->status = 'active';
        }

        $deposit->save();

        $this->audit(
            'DEPOSIT_PAYMENT_RECEIVED: #' . $deposit->id .
            ' amount=' . number_format($request->amount, 2)
        );

        // Safe notification (null check)
        if ($deposit->tenancy) {
            Notification::create([
                'user_id' => $deposit->tenancy->tenant_id,
                'title'   => 'Deposit Payment Received',
                'message' => 'KES ' . $request->amount .
                    ' received. Balance: KES ' . $deposit->current_balance,
                'type'    => 'deposit_received',
                'resource_type' => 'deposit',
                'resource_id'   => $deposit->id,
            ]);
        }

        return response()->json([
            'message' => 'Deposit updated successfully',
            'data'    => $deposit
        ]);
    }
}