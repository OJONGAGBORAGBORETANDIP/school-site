@extends('layouts.dashboard')

@section('title', 'Notifications')
@section('header-title', 'Notifications')
@section('header-subtitle', 'All your notifications')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">You can mark all as read from the sidebar dropdown, or open a notification to mark it read and go to the related page.</p>
        @if(auth()->user()->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($notifications->isEmpty())
        <div class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
            You have no notifications yet.
        </div>
        @else
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $type = $data['type'] ?? '';
                $message = $data['message'] ?? 'Notification';
                $actionLabel = 'View';
                $redirect = route('notifications.index');
                if ($type === 'report_approved' && !empty($data['student_id']) && !empty($data['term_id'])) {
                    $redirect = route('report-card.show', ['student' => $data['student_id'], 'term' => $data['term_id']]);
                    $actionLabel = 'View report card';
                } elseif ($type === 'ca_approved') {
                    $redirect = route('report-card.marks', ['type' => 'ca']);
                    $actionLabel = 'View Sequence results';
                } elseif ($type === 'exam_approved') {
                    $redirect = route('report-card.marks', ['type' => 'exam']);
                    $actionLabel = 'View Exam marks results';
                } elseif ($type === 'report_rejected' && auth()->user()->isTeacher()) {
                    $redirect = route('teacher.marks-entry');
                    $actionLabel = 'Go to marks entry';
                }
                $url = route('notifications.mark-read', $notification->id) . '?redirect=' . urlencode($redirect);
            @endphp
            <li class="px-6 py-4 flex flex-wrap items-start justify-between gap-3 {{ $notification->read_at ? '' : 'bg-indigo-50/50 dark:bg-indigo-900/10' }}">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ $notification->read_at ? '' : 'font-semibold' }}">
                        {{ $message }}
                    </p>
                    @if($type === 'report_rejected' && !empty($data['reason']))
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
                        <span class="font-medium">Reason:</span> {{ $data['reason'] }}
                    </p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ $url }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ $actionLabel }}
                    </a>
                </div>
            </li>
            @endforeach
        </ul>

        @if($notifications->hasPages())
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $notifications->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
