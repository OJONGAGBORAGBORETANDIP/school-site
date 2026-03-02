<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notify class/subject teacher(s) when CA or Exam marks are approved by the headteacher.
 */
class TeacherCaExamApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param string $component 'ca' or 'exam'
     */
    public function __construct(
        public string $component,
        public string $subjectName,
        public string $classLabel,
        public string $termName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $componentLabel = $this->component === 'exam' ? 'Exam' : 'CA';
        $subject = "{$componentLabel} marks approved – {$this->subjectName}";

        return (new MailMessage())
            ->subject($subject)
            ->line("{$componentLabel} marks for subject {$this->subjectName} ({$this->classLabel}, {$this->termName}) have been approved by the headteacher.");
    }

    public function toArray(object $notifiable): array
    {
        $componentLabel = $this->component === 'exam' ? 'Exam' : 'CA';

        return [
            'type' => 'teacher_ca_exam_approved',
            'component' => $this->component,
            'message' => "{$componentLabel} marks for {$this->subjectName} ({$this->classLabel}, {$this->termName}) have been approved by the headteacher.",
            'subject_name' => $this->subjectName,
            'class_label' => $this->classLabel,
            'term_name' => $this->termName,
        ];
    }
}

