<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradePublishedNotification extends Notification
{
    use Queueable;

    public function __construct(public Grade $grade)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $grade = $this->grade->loadMissing('submission.assignment.course');
        $assignment = $grade->submission->assignment;

        return [
            'title' => 'Nilai tugas tersedia',
            'message' => sprintf(
                'Nilai untuk tugas "%s" telah diberikan: %s.',
                $assignment->title,
                $grade->score
            ),
            'course_name' => $assignment->course->name,
            'url' => route('mahasiswa.courses.show', $assignment->course),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Nilai tugas Anda telah tersedia.')
            ->action('Lihat mata kuliah', route('mahasiswa.courses.show', $this->grade->submission->assignment->course));
    }
}
