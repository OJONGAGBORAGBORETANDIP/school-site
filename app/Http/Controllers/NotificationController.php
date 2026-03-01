<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * List all notifications (read and unread) for the authenticated user.
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read and redirect to its action URL if any.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $data = $notification->data ?? [];
        $type = $data['type'] ?? '';
        $url = $request->query('redirect', url()->previous());

        if ($type === 'report_approved' && ! empty($data['student_id']) && ! empty($data['term_id'])) {
            $url = route('report-card.show', ['student' => $data['student_id'], 'term' => $data['term_id']]);
        } elseif ($type === 'report_rejected' && $request->user()->isTeacher()) {
            $url = route('teacher.marks-entry');
        }

        return redirect($url);
    }
}
