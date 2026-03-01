<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notify parents when headteacher approves CA or Exam results for their child.
 */
class CaExamApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** 'ca' or 'exam' */
    public function __construct(
        public string $component,
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
        $label = $this->component === 'exam' ? 'Exam' : 'CA';
        $message = $this->component === 'exam'
            ? "Exam results for {$this->studentName} ({$this->termName}) have been approved. You can view them in View exam results."
            : "CA results for {$this->studentName} ({$this->termName}) have been approved. You can view them in View CA results.";

        return [
            'type' => $this->component === 'exam' ? 'exam_approved' : 'ca_approved',
            'message' => $message,
            'component' => $this->component,
            'student_name' => $this->studentName,
            'term_name' => $this->termName,
            'student_id' => $this->studentId,
            'term_id' => $this->termId,
        ];
    }
}
