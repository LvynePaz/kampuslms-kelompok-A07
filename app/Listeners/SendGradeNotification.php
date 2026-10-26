<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\GradePublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendGradeNotification implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(SubmissionGraded $event): void
    {
        $grade = $event->grade->loadMissing('submission.student', 'submission.assignment');
        $student = $grade->submission->student;

        $student->notify(new GradePublishedNotification($grade));
    }

    public function failed(SubmissionGraded $event, Throwable $exception): void
    {
        Log::error('Pengiriman notifikasi nilai gagal.', [
            'grade_id' => $event->grade->getKey(),
            'exception' => $exception->getMessage(),
        ]);
    }
}
