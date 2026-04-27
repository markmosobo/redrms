<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\Http\Request;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;

class RefundController extends Controller
{
    use Auditable, NotifiesUsers;

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

    public function finalizedRefunds()
    {
        $refunds = Refund::with([
            'deposit.tenancy.tenant',
            'deposit.tenancy.unit.property',
        ])
        ->whereIn('status', ['approved', 'paid'])
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

                    'tenancy_start_date' => $deposit->tenancy->created_at ?? null,
                    'tenancy_end_date' => $deposit->tenancy->end_date ?? null,

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

public function pay($id)
{
    $refund = Refund::findOrFail($id);

    // 🔒 Only approved can be paid
    if ($refund->status !== 'approved') {
        return response()->json([
            'message' => 'Only approved refunds can be paid'
        ], 400);
    }

    $refund->update([
        'status' => 'paid',
        'paid_at' => now(), // optional but recommended
    ]);

    return response()->json([
        'message' => 'Refund paid successfully'
    ]);
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

        DB::transaction(function () use ($refund) {

            $refund->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'refund_date' => now(),
            ]);

            $refund->deposit->update([
                'status' => 'refunded'
            ]);
        });

        $this->audit(
            'REFUND_FINALIZED: refund_id=' . $refund->id .
            ', deposit_id=' . $refund->deposit_id .
            ', amount=' . $refund->refundable_amount .
            ', approved_by=' . auth()->id()
        );

        $refund->load('deposit.tenancy.unit', 'deposit.tenancy.tenant');

        $tenancy = $refund->deposit->tenancy;
        $unit = $tenancy->unit;

        // 🔔 1. Notify TENANT
        $this->notifyUser(
            $tenancy->tenant_id,
            'Refund Approved',
            'Your deposit refund of KES ' . $refund->refundable_amount .
            ' for Unit ' . $unit->unit_number . ' has been approved.',
            'refund_approved',
            $refund->id,
            'refund'
        );

        // 🔔 2. Notify MANAGERS
        $this->notifyRoles(
            ['manager'],
            'Refund Processed',
            'Refund for Unit ' . $unit->unit_number . ' has been finalized.',
            'refund_processed',
            $refund->id,
            'refund'
        );

        // 🔔 3. Notify LANDLORDS
        $this->notifyRoles(
            ['landlord'],
            'Deposit Refunded',
            'Deposit for Unit ' . $unit->unit_number . ' has been refunded.',
            'refund_processed',
            $refund->id,
            'refund'
        );

        return response()->json([
            'message' => 'Refund finalized successfully',
            'refund'  => $refund
        ]);
    }
}