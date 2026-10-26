<?php

namespace Tests\Feature;

use App\Events\AssignmentPublished;
use App\Events\SubmissionGraded;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationEventIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_assignment_creation_as_published_dispatches_event(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $payload = $this->webAssignmentPayload('published');

        Event::fake();

        $this->actingAs($lecturer)
            ->post(route('dosen.courses.assignments.store', $course), $payload)
            ->assertRedirect(route('dosen.courses.show', $course));

        $assignment = $course->assignments()->where('title', $payload['title'])->firstOrFail();

        Event::assertDispatched(
            AssignmentPublished::class,
            fn (AssignmentPublished $event): bool => $event->assignment->is($assignment)
                && $event->assignment->status === 'published'
        );
    }

    public function test_api_assignment_creation_as_published_dispatches_event(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $payload = [
            'course_id' => $course->id,
            'title' => 'API Published Assignment',
            'due_at' => now()->addDays(5)->toDateTimeString(),
            'status' => 'published',
        ];

        Event::fake();

        $this->actingAs($lecturer)
            ->postJson('/api/v1/assignments', $payload)
            ->assertCreated();

        $assignment = $course->assignments()->where('title', $payload['title'])->firstOrFail();

        Event::assertDispatched(
            AssignmentPublished::class,
            fn (AssignmentPublished $event): bool => $event->assignment->is($assignment)
                && $event->assignment->status === 'published'
        );
    }

    public function test_web_transition_from_draft_to_published_dispatches_event(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->draft()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);

        Event::fake();

        $this->actingAs($lecturer)
            ->put(route('dosen.assignments.update', $assignment), $this->webAssignmentPayload('published'))
            ->assertRedirect(route('dosen.courses.show', $course));

        $assignment->refresh();

        Event::assertDispatched(
            AssignmentPublished::class,
            fn (AssignmentPublished $event): bool => $event->assignment->is($assignment)
                && $event->assignment->status === 'published'
        );
    }

    public function test_api_transition_from_draft_to_published_dispatches_event(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->draft()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);

        Event::fake();

        $this->actingAs($lecturer)
            ->putJson("/api/v1/assignments/{$assignment->id}", ['status' => 'published'])
            ->assertOk();

        $assignment->refresh();

        Event::assertDispatched(
            AssignmentPublished::class,
            fn (AssignmentPublished $event): bool => $event->assignment->is($assignment)
                && $event->assignment->status === 'published'
        );
    }

    public function test_web_edit_of_published_assignment_does_not_dispatch_event_again(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
        $payload = $this->webAssignmentPayload('published');
        $payload['title'] = 'Edited Published Assignment';

        Event::fake();

        $this->actingAs($lecturer)
            ->put(route('dosen.assignments.update', $assignment), $payload)
            ->assertRedirect(route('dosen.courses.show', $course));

        Event::assertNotDispatched(AssignmentPublished::class);
    }

    public function test_api_edit_of_published_assignment_does_not_dispatch_event_again(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);

        Event::fake();

        $this->actingAs($lecturer)
            ->putJson("/api/v1/assignments/{$assignment->id}", ['title' => 'Edited Published Assignment'])
            ->assertOk();

        Event::assertNotDispatched(AssignmentPublished::class);
    }

    public function test_api_assignment_creation_without_status_keeps_draft_and_does_not_dispatch_event(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $payload = [
            'course_id' => $course->id,
            'title' => 'API Draft Assignment',
            'due_at' => now()->addDays(5)->toDateTimeString(),
        ];

        Event::fake();

        $this->actingAs($lecturer)
            ->postJson('/api/v1/assignments', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('assignments', [
            'course_id' => $course->id,
            'title' => $payload['title'],
            'status' => 'draft',
        ]);
        Event::assertNotDispatched(AssignmentPublished::class);
    }

    public function test_successful_web_grading_dispatches_event_after_grade_is_saved(): void
    {
        [$lecturer, $submission] = $this->makeGradingFixture();

        Event::fake();

        $this->actingAs($lecturer)
            ->post(route('dosen.submissions.grades.store', $submission), [
                'score' => 85,
                'feedback' => 'Good work',
            ])
            ->assertSessionHas('success');

        Event::assertDispatched(
            SubmissionGraded::class,
            function (SubmissionGraded $event) use ($submission): bool {
                $this->assertDatabaseHas('grades', [
                    'submission_id' => $submission->id,
                    'score' => 85,
                ]);

                return $event->submission->is($submission);
            }
        );
    }

    public function test_successful_api_grading_dispatches_event_after_grade_is_saved(): void
    {
        [$lecturer, $submission] = $this->makeGradingFixture();

        Event::fake();

        $this->actingAs($lecturer)
            ->putJson("/api/v1/submissions/{$submission->id}/grade", [
                'score' => 85,
                'feedback' => 'Good work',
            ])
            ->assertCreated();

        Event::assertDispatched(
            SubmissionGraded::class,
            function (SubmissionGraded $event) use ($submission): bool {
                $this->assertDatabaseHas('grades', [
                    'submission_id' => $submission->id,
                    'score' => 85,
                ]);

                return $event->submission->is($submission);
            }
        );
    }

    public function test_invalid_web_grading_does_not_dispatch_event(): void
    {
        [$lecturer, $submission] = $this->makeGradingFixture();

        Event::fake();

        $this->actingAs($lecturer)
            ->post(route('dosen.submissions.grades.store', $submission), [
                'score' => 101,
                'feedback' => 'Invalid score',
            ])
            ->assertSessionHasErrors(['score']);

        Event::assertNotDispatched(SubmissionGraded::class);
    }

    public function test_invalid_api_grading_does_not_dispatch_event(): void
    {
        [$lecturer, $submission] = $this->makeGradingFixture();

        Event::fake();

        $this->actingAs($lecturer)
            ->putJson("/api/v1/submissions/{$submission->id}/grade", [
                'score' => 101,
                'feedback' => 'Invalid score',
            ])
            ->assertUnprocessable();

        Event::assertNotDispatched(SubmissionGraded::class);
    }

    private function webAssignmentPayload(string $status): array
    {
        return [
            'title' => 'Web Assignment '.fake()->unique()->word(),
            'instructions' => 'Complete the assignment.',
            'due_at' => now()->addDays(5)->toDateTimeString(),
            'max_score' => 100,
            'status' => $status,
        ];
    }

    private function makeGradingFixture(): array
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
            'max_score' => 100,
        ]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        return [$lecturer, $submission];
    }
}
