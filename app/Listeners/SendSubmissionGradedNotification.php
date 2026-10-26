<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\GradeReleasedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubmissionGradedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SubmissionGraded $event): void
    {
        $submission = $event->submission->loadMissing('student');

        $submission->student->notify(new GradeReleasedNotification($submission));
    }
}
