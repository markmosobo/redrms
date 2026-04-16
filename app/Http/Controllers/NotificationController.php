<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for logged-in user
     */
    public function index()
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->latest()
            ->take(50)
            ->get();
    }

    public function all(Request $request)
    {
        $query = Notification::where('user_id', Auth::id());

        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($request->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->latest()->paginate(50);
    }

    public function adminAll(Request $request)
    {
        $user = Auth::user();

        // 🔒 HARD BLOCK non-admins
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $query = Notification::query(); // 🚨 NO user_id filter

        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($request->filter === 'read') {
            $query->whereNotNull('read_at');
        }

                // Optional future filters
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        return $query->latest()->paginate(50);
    }

    /**
     * Create notification (system use)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'nullable|string',
            'message' => 'nullable|string',
            'type'    => 'nullable|string',
        ]);

        return Notification::create([
            'user_id' => $request->user_id,
            'title'   => $request->title,
            'message' => $request->message,
            'type'    => $request->type,
        ]);
    }

    /**
     * Mark ONE notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->update([
            'read_at' => now()
        ]);

        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark ALL as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now()
            ]);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}