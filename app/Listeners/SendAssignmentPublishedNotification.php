<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendAssignmentPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AssignmentPublished $event): void
    {
        $assignment = $event->assignment->loadMissing('course.students');

        Notification::send(
            $assignment->course->students,
            new NewAssignmentNotification($assignment)
        );
    }
}
