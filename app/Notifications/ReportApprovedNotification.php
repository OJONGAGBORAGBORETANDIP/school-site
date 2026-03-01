<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $studentName,
        public string $termName,
        public ?int $studentId = null,
        public ?int $termId = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_approved',
            'message' => "Report card for {$this->studentName} ({$this->termName}) has been approved. You can now view and download it.",
            'student_name' => $this->studentName,
            'term_name' => $this->termName,
            'student_id' => $this->studentId,
            'term_id' => $this->termId,
        ];
    }
}
