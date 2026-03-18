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
}