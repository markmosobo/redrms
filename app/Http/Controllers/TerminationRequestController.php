<?php

namespace App\Http\Controllers;

use App\Models\TerminationRequest;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;
use App\Services\TenancyTerminationService;

class TerminationRequestController extends Controller
{
    use Auditable, NotifiesUsers;

    /**
     * Tenant creates termination request
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string',
            'requested_end_date' => 'nullable|date',
        ]);

        // 🔒 Get active tenancy for tenant
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

        // 🔔 Notify admins / managers / landlords
        $this->notifyRoles(
            ['admin', 'manager', 'landlord'],
            'Termination Request Submitted',
            'A tenant has requested to terminate a tenancy.',
            'termination_request',
            $termination->id,
            'termination_requests'
        );

        $this->audit('TERMINATION_REQUEST_CREATED', auth()->id());

        return response()->json([
            'message' => 'Termination request submitted',
            'data' => $termination
        ], 201);
    }

    /**
     * Admin / Manager list
     */
    public function index()
    {
        return response()->json(
            TerminationRequest::with([
                'tenancy.unit.property',
                'tenancy.tenant',
                'requester',
                'processor'
            ])->latest()->get()
        );
    }

    /**
     * Approve termination
     */

    public function approve(
        TerminationRequest $terminationRequest,
        TenancyTerminationService $terminationService
    ) {
        DB::transaction(function () use ($terminationRequest, $terminationService) {

            $terminationRequest->update([
                'status'       => 'approved',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // 🔥 SAME termination logic
            $terminationService->terminate(
                $terminationRequest->tenancy,
                auth()->id()
            );

            $this->notifyUser(
                $terminationRequest->requested_by,
                'Termination Approved',
                'Your termination request has been approved.',
                'termination_request',
                $terminationRequest->id,
                'termination_requests'
            );

            $this->audit('TERMINATION_APPROVED', auth()->id());
        });

        return response()->json([
            'message' => 'Termination approved and inspection created'
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

        // 🔔 Notify tenant
        $this->notifyUser(
            $terminationRequest->requested_by,
            'Termination Rejected',
            'Your termination request was rejected.',
            'termination_request',
            $terminationRequest->id,
            'termination_requests'
        );

        $this->audit('TERMINATION_REJECTED', auth()->id());

        return response()->json([
            'message' => 'Termination rejected'
        ]);
    }

    /**
     * Tenant: all my requests
     */
    public function myRequests()
    {
        return response()->json(
            TerminationRequest::with('tenancy.unit')
                ->where('requested_by', auth()->id())
                ->latest()
                ->get()
        );
    }

    /**
     * Tenant: latest request
     */
    public function myRequest()
    {
        $tenancy = Tenancy::where('tenant_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            return response()->json(null);
        }

        $request = TerminationRequest::with('tenancy.unit.property')
            ->where('tenancy_id', $tenancy->id)
            ->latest()
            ->first();

        return response()->json($request);
    }
}