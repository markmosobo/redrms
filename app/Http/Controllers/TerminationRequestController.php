<?php

namespace App\Http\Controllers;

use App\Models\TerminationRequest;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class TerminationRequestController extends Controller
{
    use Auditable;

    /**
     * Tenant / Admin creates request
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string',
            'requested_end_date' => 'nullable|date',
        ]);

        // 🔒 Get tenancy automatically (NO frontend trust)
        $tenancy = Tenancy::where('tenant_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        $termination = TerminationRequest::create([
            'tenancy_id' => $tenancy->id,
            'requested_by' => auth()->id(),
            'reason' => $request->reason,
            'requested_end_date' => $request->requested_end_date,
            'status' => 'pending',
        ]);

        $this->audit(
            'TERMINATION_REQUEST_CREATED',
            auth()->id()
        );

        return response()->json([
            'message' => 'Termination request submitted',
            'data' => $termination
        ], 201);
    }

    /**
     * List all requests (admin/manager view)
     */
    public function index()
    {
        $requests = TerminationRequest::with([
            'tenancy.unit.property',
            'tenancy.tenant',
            'requester',
            'processor'
        ])->latest()->get();

        return response()->json($requests);
    }

    /**
     * Approve termination
     */
    public function approve(TerminationRequest $terminationRequest)
    {
        DB::transaction(function () use ($terminationRequest) {

            $terminationRequest->update([
                'status' => 'approved',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Optional: mark tenancy inactive
            $terminationRequest->tenancy->update([
                'status' => 'terminated'
            ]);

            $this->audit(
                'TERMINATION_APPROVED',
                auth()->id()
            );
        });

        return response()->json([
            'message' => 'Termination approved'
        ]);
    }

    /**
     * Reject termination
     */
    public function reject(TerminationRequest $terminationRequest)
    {
        $terminationRequest->update([
            'status' => 'rejected',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        $this->audit(
            'TERMINATION_REJECTED',
            auth()->id()
        );

        return response()->json([
            'message' => 'Termination rejected'
        ]);
    }

    /**
     * Tenant sees only their requests
     */
    public function myRequests()
    {
        $requests = TerminationRequest::with('tenancy.unit')
            ->where('requested_by', auth()->id())
            ->latest()
            ->get();

        return response()->json($requests);
    }

    public function myRequest()
    {
        $user = auth()->user();

        $request = TerminationRequest::with('tenancy.unit.property')
            ->where('requested_by', $user->id)
            ->latest()
            ->first();

        return response()->json($request);
    }    
}