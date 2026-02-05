<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AdminNotificationController extends Controller
{
    /**
     * Return notifications as JSON for real-time polling (admin layout).
     */
    public function index(Request $request)
    {
        $adminId = session('admin_id');
        if (!$adminId) {
            return response()->json(['notifications' => [], 'unread_count' => 0], 401);
        }

        $notifications = AdminNotification::forUser($adminId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($notif) {
                return [
                    'id'           => $notif->id,
                    'type'         => $notif->type,
                    'title'        => $notif->title ?? 'Notification',
                    'message'      => $notif->message,
                    'image_url'    => $notif->image_url ? URL::asset($notif->image_url) : null,
                    'read_at'      => $notif->read_at?->toIso8601String(),
                    'data'         => $notif->data,
                    'related_type' => $notif->related_type,
                    'related_id'   => $notif->related_id,
                    'created_at'   => $notif->created_at->toIso8601String(),
                    'created_at_human' => $notif->created_at->diffForHumans(),
                ];
            });

        $unreadCount = AdminNotification::forUser($adminId)->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, int $id)
    {
        $adminId = session('admin_id');
        if (!$adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = AdminNotification::where('id', $id)
            ->where('user_id', $adminId)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the current admin.
     */
    public function markAllRead(Request $request)
    {
        $adminId = session('admin_id');
        if (!$adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        AdminNotification::forUser($adminId)->unread()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
