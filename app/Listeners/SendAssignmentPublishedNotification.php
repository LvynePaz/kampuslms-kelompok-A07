<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendAssignmentPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 10;

    public function handle(AssignmentPublished $event): void
    {
        $assignment = $event->assignment->loadMissing('course.students');

        Notification::send(
            $assignment->course->students,
            new NewAssignmentNotification($assignment)
        );
    }

    public function failed(AssignmentPublished $event, Throwable $exception): void
    {
        Log::error('Pengiriman notifikasi tugas baru gagal.', [
            'assignment_id' => $event->assignment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
