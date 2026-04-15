<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\Http\Request;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * List refundable deposits
     */
    public function refundableDeposits()
    {
        $refunds = Refund::with([
            'deposit.tenancy.tenant',
            'deposit.tenancy.unit.property',
        ])
        ->where('status', 'pending')
        ->get();

        return response()->json(
            $refunds->map(function ($refund) {

                $deposit = $refund->deposit;

                return [
                    'refund_id' => $refund->id,
                    'deposit_id' => $deposit->id,

                    'tenant' => $deposit->tenancy->tenant ?? null,
                    'unit' => $deposit->tenancy->unit ?? null,
                    'property' => $deposit->tenancy->unit->property ?? null,

                    'amount_received' => $deposit->amount_received,

                    'total_deductions' => $deposit->deductions()
                        ->whereNotNull('approved_at')
                        ->sum('amount'),

                    'refundable_amount' => $refund->refundable_amount,

                    'status' => $refund->status,
                ];
            })
        );
    }

    /**
     * Finalize a refund for a specific deposit
     */
    public function finalize(Refund $refund)
    {
        if ($refund->status !== 'pending') {
            return response()->json([
                'message' => 'Refund already processed'
            ], 409);
        }

        // 🔒 wrap in transaction for safety
        DB::transaction(function () use ($refund) {

            // 1️⃣ Update refund
            $refund->update([
                'status'       => 'approved',
                'approved_by'  => auth()->id(),
                'approved_at'  => now(),
                'refund_date'  => now(),
            ]);

            // 2️⃣ Update linked deposit
            $refund->deposit->update([
                'status' => 'refunded'
            ]);
        });

        // 🔄 reload relations for frontend sync
        $refund->load('deposit');

        return response()->json([
            'message' => 'Refund finalized successfully',
            'refund'  => $refund
        ]);
    }
}