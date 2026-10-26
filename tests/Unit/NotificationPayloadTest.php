<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\Submission;
use App\Notifications\GradeReleasedNotification;
use App\Notifications\NewAssignmentNotification;
use PHPUnit\Framework\TestCase;
use stdClass;

class NotificationPayloadTest extends TestCase
{
    public function test_new_assignment_payload_contains_only_assignment_id_and_title(): void
    {
        $assignment = new Assignment;
        $assignment->setAttribute('id', 17);
        $assignment->setAttribute('title', 'Tugas Pengantar');

        $payload = (new NewAssignmentNotification($assignment))->toDatabase(new stdClass);

        $this->assertSame([
            'assignment_id' => 17,
            'title' => 'Tugas Pengantar',
        ], $payload);
        $this->assertSame(['assignment_id', 'title'], array_keys($payload));
    }

    public function test_grade_released_payload_contains_only_submission_id_and_assignment_title(): void
    {
        $assignment = new Assignment;
        $assignment->setAttribute('id', 31);
        $assignment->setAttribute('title', 'Tugas Basis Data');

        $submission = new Submission;
        $submission->setAttribute('id', 42);
        $submission->setRelation('assignment', $assignment);

        $payload = (new GradeReleasedNotification($submission))->toDatabase(new stdClass);

        $this->assertSame([
            'submission_id' => 42,
            'title' => 'Tugas Basis Data',
        ], $payload);
        $this->assertSame(['submission_id', 'title'], array_keys($payload));
    }
}
