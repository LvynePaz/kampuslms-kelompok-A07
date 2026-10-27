<?php

namespace Tests\Unit;

use App\Events\AssignmentPublished;
use App\Events\SubmissionGraded;
use App\Listeners\SendAssignmentPublishedNotification;
use App\Listeners\SendSubmissionGradedNotification;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradeReleasedNotification;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationListenerTest extends TestCase
{
    public function test_assignment_notification_is_sent_only_to_enrolled_students(): void
    {
        Notification::fake();

        $student = $this->makeUser(1, 'mahasiswa');
        $otherStudent = $this->makeUser(2, 'mahasiswa');
        $lecturer = $this->makeUser(3, 'dosen');
        $admin = $this->makeUser(4, 'admin');

        $course = new Course;
        $course->setRelation('students', new EloquentCollection([$student]));

        $assignment = new Assignment;
        $assignment->setAttribute('id', 15);
        $assignment->setAttribute('title', 'Tugas Terpublikasi');
        $assignment->setRelation('course', $course);

        (new SendAssignmentPublishedNotification)->handle(new AssignmentPublished($assignment));

        Notification::assertSentTo(
            $student,
            NewAssignmentNotification::class,
            fn (NewAssignmentNotification $notification): bool => $notification->assignment === $assignment
        );
        Notification::assertNotSentTo($otherStudent, NewAssignmentNotification::class);
        Notification::assertNotSentTo($lecturer, NewAssignmentNotification::class);
        Notification::assertNotSentTo($admin, NewAssignmentNotification::class);
    }

    public function test_grade_notification_is_sent_only_to_submission_owner(): void
    {
        Notification::fake();

        $owner = $this->makeUser(11, 'mahasiswa');
        $otherStudent = $this->makeUser(12, 'mahasiswa');
        $lecturer = $this->makeUser(13, 'dosen');

        $submission = new Submission;
        $submission->setAttribute('id', 25);
        $submission->setRelation('student', $owner);

        (new SendSubmissionGradedNotification)->handle(new SubmissionGraded($submission));

        Notification::assertSentTo(
            $owner,
            GradeReleasedNotification::class,
            fn (GradeReleasedNotification $notification): bool => $notification->submission === $submission
        );
        Notification::assertNotSentTo($otherStudent, GradeReleasedNotification::class);
        Notification::assertNotSentTo($lecturer, GradeReleasedNotification::class);
    }

    private function makeUser(int $id, string $role): User
    {
        $user = new User;
        $user->setAttribute('id', $id);
        $user->setAttribute('role', $role);

        return $user;
    }
}
