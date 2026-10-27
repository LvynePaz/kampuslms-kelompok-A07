<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\GradeReleasedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSubmissionGradedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 10;

    public function handle(SubmissionGraded $event): void
    {
        $submission = $event->submission->loadMissing('student');

        $submission->student->notify(new GradeReleasedNotification($submission));
    }

    public function failed(SubmissionGraded $event, Throwable $exception): void
    {
        Log::error('Pengiriman notifikasi nilai gagal.', [
            'submission_id' => $event->submission->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
