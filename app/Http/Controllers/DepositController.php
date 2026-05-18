<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Tenancy;
use App\Models\Refund;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;

class DepositController extends Controller
{
    use Auditable, NotifiesUsers;

    /**
     * LIST
     */
    public function index()
    {
        return response()->json(
            Deposit::with([
                'tenancy.tenant',
                'tenancy.unit.property',
                'deductions'
            ])->get()
        );
    }

    /**
     * CREATE DEPOSIT (manual override if needed)
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenancy_id'      => 'required|exists:tenancies,id',
            'amount_received'  => 'required|numeric|min:0',
            'received_date'    => 'nullable|date',
        ]);

        $deposit = Deposit::create([
            'tenancy_id'      => $request->tenancy_id,
            'amount_received' => $request->amount_received,
            'received_date'   => $request->received_date ?? now(),
            'status'          => 'active',
        ]);

        $this->audit('DEPOSIT_CREATED', auth()->id());

        $deposit->load('tenancy.unit');

        // 🔔 NOTIFICATIONS
        $this->notifyUser(
            $deposit->tenancy->tenant_id,
            'Deposit Recorded',
            'Your deposit has been recorded for Unit ' . $deposit->tenancy->unit->unit_number,
            'deposit_created',
            $deposit->id,
            'deposit'
        );

        $this->notifyRoles(
            ['manager'],
            'Deposit Created',
            'A deposit has been recorded for Unit ' . $deposit->tenancy->unit->unit_number,
            'deposit_created',
            $deposit->id,
            'deposit'
        );

        return response()->json([
            'message' => 'Deposit recorded successfully',
            'data'    => $deposit
        ], 201);
    }

    /**
     * RECEIVE PAYMENT (IMPORTANT FLOW)
     */
public function receive(Request $request, Deposit $deposit)
{
    $request->validate([
        'amount' => 'required|numeric|min:1',
        'payment_method' => 'nullable|string',
        'mpesa_code' => 'nullable|string',
        'notes' => 'nullable|string',
        'type' => 'nullable|in:deposit,payment,refund'

    ]);


    // =========================
    // 1. DETERMINE TYPE
    // =========================
    $type = $request->type ?? 'deposit';

    // prevent nonsense (refund shouldn't increase deposit)
    if ($type === 'refund') {
        abort(422, 'Refunds must be processed via refund endpoint.');
    }

    // =========================
    // 1. UPDATE DEPOSIT
    // =========================
    $deposit->amount_received += $request->amount;
    $deposit->received_date = now();

    $deposit->current_balance =
        max(0, $deposit->required_amount - $deposit->amount_received);

    $deposit->status =
        $deposit->amount_received >= $deposit->required_amount
            ? 'held'
            : 'active';

    $deposit->save();

    // reload relationships for notifications
    $deposit->load('tenancy.unit', 'tenancy.tenant');

    // =========================
    // 2. CREATE RECEIPT 🔥 NEW PART
    // =========================
    $receipt = Receipt::create([
        'receipt_number' =>
            'RCP-' . now()->format('YmdHis') . '-' . $deposit->id,

        'type' => $type,
        
        'deposit_id' => $deposit->id,

        'amount' => $request->amount,

        'payment_method' => $request->payment_method,

        'mpesa_code' => $request->mpesa_code,

        'issued_at' => now(),

        'data' => [
            'tenant_name' => $deposit->tenancy->tenant->full_name,
            'unit' => $deposit->tenancy->unit->unit_number,
            'property' => $deposit->tenancy->unit->property->property_name ?? null,

            'balance' => $deposit->current_balance,
            'required_amount' => $deposit->required_amount,
            'total_received' => $deposit->amount_received,
            'paid_on' => now()->format('d M Y'),
            'cashier' => auth()->user()->name ?? 'System',
        ]
    ]);

    // =========================
    // 3. AUDIT LOG
    // =========================
    $this->audit('DEPOSIT_PAYMENT_RECEIVED', auth()->id());

    // =========================
    // 4. NOTIFICATIONS
    // =========================
    $this->notifyUser(
        $deposit->tenancy->tenant_id,
        'Deposit Payment Received',
        'KES ' . $request->amount .
        ' received for Unit ' . $deposit->tenancy->unit->unit_number .
        '. Balance: KES ' . $deposit->current_balance,
        'deposit_payment',
        $deposit->id,
        'deposit'
    );

    $this->notifyRoles(
        ['manager'],
        'Deposit Payment Update',
        'Payment received for Unit ' . $deposit->tenancy->unit->unit_number .
        ' (KES ' . $request->amount . ')',
        'deposit_payment',
        $deposit->id,
        'deposit'
    );

    // =========================
    // 5. RESPONSE (IMPORTANT)
    // =========================
    return response()->json([
        'message' => 'Deposit updated & receipt generated successfully',
        'receipt_id' => $receipt->id,
        'receipt_number' => $receipt->receipt_number,
        'deposit' => $deposit
    ]);
}

    /**
     * FINALIZE DEPOSIT
     */
    public function finalizeDeposit($depositId)
    {
        $result = DB::transaction(function () use ($depositId) {

            $deposit = Deposit::with(['tenancy.unit', 'tenancy.tenant', 'deductions'])
                ->lockForUpdate()
                ->findOrFail($depositId);

            if ($deposit->refund) {
                abort(400, 'Deposit already finalized');
            }

            if ($deposit->deductions()->whereNull('approved_at')->exists()) {
                abort(400, 'All deductions must be approved first');
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

            // 🔔 NOTIFY TENANT
            $this->notifyUser(
                $deposit->tenancy->tenant_id,
                'Deposit Finalized',
                'Your deposit has been finalized. Refund processing is underway.',
                'deposit_finalized',
                $deposit->id,
                'deposit'
            );

            // 🔔 NOTIFY MANAGER
            $this->notifyRoles(
                ['manager'],
                'Deposit Finalized',
                'Deposit finalized for Unit ' . $deposit->tenancy->unit->unit_number,
                'deposit_finalized',
                $deposit->id,
                'deposit'
            );

            // 🔔 NOTIFY LANDLORD
            $this->notifyRoles(
                ['landlord'],
                'Refund Processing Required',
                'Deposit for Unit ' . $deposit->tenancy->unit->unit_number . ' is ready for refund approval',
                'deposit_finalized',
                $deposit->id,
                'deposit'
            );

            return [
                'deposit' => $deposit,
                'refund'  => $refund,
                'amount'  => $refundableAmount
            ];
        });

        $this->audit('DEPOSIT_FINALIZED', auth()->id());

        return response()->json([
            'deposit_id'        => $result['deposit']->id,
            'refundable_amount' => $result['amount'],
            'refund'            => $result['refund'],
        ]);
    }
}