<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $subjectName,
        public string $classLabel,
        public string $termName,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_rejected',
            'message' => "Your {$this->type} result for {$this->subjectName} ({$this->classLabel}, {$this->termName}) has been rejected by the headteacher.",
            'reason' => $this->reason,
            'subject_name' => $this->subjectName,
            'class_label' => $this->classLabel,
            'term_name' => $this->termName,
            'component' => $this->type,
        ];
    }
}
