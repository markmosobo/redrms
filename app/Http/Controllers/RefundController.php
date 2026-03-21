<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposit;

class RefundController extends Controller
{
    /**
     * List refundable deposits
     */
    public function refundableDeposits()
    {
        // Only 'held' deposits are refundable
        $deposits = Deposit::with([
            'tenancy.tenant',
            'tenancy.unit.property',
            'deductions' => function ($q) {
                $q->whereNotNull('approved_at'); // Only approved deductions
            }
        ])->where('status', 'held')->get();

        // Map data for front-end
        $data = $deposits->map(function ($deposit) {
            $totalDeductions = $deposit->deductions->sum('amount');

            return [
                'id' => $deposit->id,
                'amount_received' => $deposit->amount_received,
                'status' => $deposit->status,
                'total_deductions' => $totalDeductions,
                'deductions' => $deposit->deductions,
                'tenancy' => $deposit->tenancy,
            ];
        });

        return response()->json($data);
    }

    /**
     * Finalize a refund for a specific deposit
     */
    public function finalizeDepositFromDeposit(Deposit $deposit)
    {
        // Only 'held' deposits can be finalized
        if ($deposit->status !== 'held') {
            return response()->json([
                'message' => 'Deposit is not held and cannot be refunded'
            ], 400);
        }

        // Calculate total approved deductions
        $totalDeductions = $deposit->deductions()->whereNotNull('approved_at')->sum('amount');

        // Refund amount
        $refundAmount = $deposit->amount_received - $totalDeductions;

        // Mark deposit as refunded
        $deposit->status = 'refunded';
        $deposit->save();

        return response()->json([
            'message' => 'Refund finalized successfully',
            'deposit_id' => $deposit->id,
            'refund_amount' => $refundAmount,
        ]);
    }
}