<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * List all audit logs
     */
    public function index()
    {
        $logs = AuditLog::with('user')->get();
        return response()->json($logs);
    }

    /**
     * Store a new audit log
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'description' => 'nullable|string',
            'ip_address'  => 'nullable|ip',
        ]);

        $log = AuditLog::create($request->only([
            'user_id',
            'description',
            'ip_address',
        ]));

        return response()->json([
            'message' => 'Audit log created successfully',
            'data'    => $log,
        ], 201);
    }

    /**
     * Show a specific audit log
     */
    public function show(string $id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        return response()->json($log);
    }

    /**
     * Update an audit log
     */
    public function update(Request $request, string $id)
    {
        $log = AuditLog::findOrFail($id);

        $request->validate([
            'description' => 'nullable|string',
            'ip_address'  => 'nullable|ip',
        ]);

        $log->update($request->only([
            'description',
            'ip_address',
        ]));

        return response()->json([
            'message' => 'Audit log updated successfully',
            'data'    => $log,
        ]);
    }

    /**
     * Delete an audit log
     */
    public function destroy(string $id)
    {
        $log = AuditLog::findOrFail($id);
        $log->delete();

        return response()->json([
            'message' => 'Audit log deleted successfully'
        ]);
    }
}