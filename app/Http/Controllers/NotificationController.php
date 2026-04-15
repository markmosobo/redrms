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