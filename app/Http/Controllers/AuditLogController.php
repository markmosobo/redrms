<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    /**
     * List all audit logs
     */
    public function index()
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->get();

        return response()->json($logs);
    }

    /**
     * Show a specific audit log
     */
    public function show(string $id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return response()->json($log);
    }
}