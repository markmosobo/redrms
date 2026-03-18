<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /**
     * List all refunds
     */
    public function index()
    {
        $refunds = Refund::with(['deposit.tenancy.tenant', 'approver'])->get();
        return response()->json($refunds);
    }

    /**
     * Store a new refund
     */
    public function store(Request $request)
    {
        $request->validate([
            'deposit_id'       => 'required|exists:deposits,id',
            'refundable_amount'=> 'required|numeric',
            'refund_date'      => 'required|date',
            'approval_status'  => 'nullable|in:pending,approved,rejected',
            'approved_by'      => 'nullable|exists:users,id',
            'approved_at'      => 'nullable|date',
        ]);

        $refund = Refund::create($request->only([
            'deposit_id',
            'refundable_amount',
            'refund_date',
            'approval_status',
            'approved_by',
            'approved_at',
        ]));

        return response()->json([
            'message' => 'Refund created successfully',
            'data'    => $refund,
        ], 201);
    }

    /**
     * Show a specific refund
     */
    public function show(string $id)
    {
        $refund = Refund::with(['deposit.tenancy.tenant', 'approver'])->findOrFail($id);
        return response()->json($refund);
    }

    /**
     * Update a refund
     */
    public function update(Request $request, string $id)
    {
        $refund = Refund::findOrFail($id);

        $request->validate([
            'refundable_amount'=> 'sometimes|numeric',
            'refund_date'      => 'nullable|date',
            'approval_status'  => 'nullable|in:pending,approved,rejected',
            'approved_by'      => 'nullable|exists:users,id',
            'approved_at'      => 'nullable|date',
        ]);

        $refund->update($request->only([
            'refundable_amount',
            'refund_date',
            'approval_status',
            'approved_by',
            'approved_at',
        ]));

        return response()->json([
            'message' => 'Refund updated successfully',
            'data'    => $refund,
        ]);
    }

    /**
     * Delete a refund
     */
    public function destroy(string $id)
    {
        $refund = Refund::findOrFail($id);
        $refund->delete();

        return response()->json([
            'message' => 'Refund deleted successfully'
        ]);
    }
}