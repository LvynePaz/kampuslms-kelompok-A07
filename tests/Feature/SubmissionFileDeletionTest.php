<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SubmissionFileDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_deleting_submission_removes_its_database_record_and_file(): void
    {
        $submission = $this->createSubmission();
        Storage::disk('local')->put($submission->file_path, 'submission contents');

        $submission->delete();

        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        Storage::disk('local')->assertMissing($submission->file_path);
    }

    public function test_submission_record_is_not_deleted_when_file_deletion_fails(): void
    {
        $submission = $this->createSubmission();
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('exists')->once()->with($submission->file_path)->andReturn(true);
        $disk->shouldReceive('delete')->once()->with($submission->file_path)->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('local')->andReturn($disk);

        try {
            $submission->delete();
            $this->fail('Expected file deletion failure to stop record deletion.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString($submission->file_path, $exception->getMessage());
        }

        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
    }

    public function test_web_assignment_deletion_removes_submission_records_and_files(): void
    {
        [$lecturer, $assignment] = $this->createAssignment();
        $submissions = collect([
            $this->createSubmission($assignment),
            $this->createSubmission($assignment),
        ]);
        $submissions->each(fn (Submission $submission) => Storage::disk('local')->put($submission->file_path, 'contents'));

        $this->actingAs($lecturer)
            ->delete(route('dosen.assignments.destroy', $assignment))
            ->assertRedirect(route('dosen.courses.show', $assignment->course));

        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
        foreach ($submissions as $submission) {
            $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
            Storage::disk('local')->assertMissing($submission->file_path);
        }
    }

    public function test_api_assignment_deletion_removes_submission_records_and_files(): void
    {
        [$lecturer, $assignment] = $this->createAssignment();
        $submission = $this->createSubmission($assignment);
        Storage::disk('local')->put($submission->file_path, 'submission contents');

        $this->actingAs($lecturer)
            ->deleteJson("/api/v1/assignments/{$assignment->id}")
            ->assertOk();

        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        Storage::disk('local')->assertMissing($submission->file_path);
    }

    public function test_course_deletion_removes_all_descendant_submission_records_and_files(): void
    {
        [$lecturer, $assignment] = $this->createAssignment();
        $secondAssignment = Assignment::factory()->create([
            'course_id' => $assignment->course_id,
            'created_by' => $lecturer->id,
        ]);
        $submissions = collect([
            $this->createSubmission($assignment),
            $this->createSubmission($secondAssignment),
        ]);
        $submissions->each(fn (Submission $submission) => Storage::disk('local')->put($submission->file_path, 'contents'));

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.courses.destroy', $assignment->course))
            ->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseMissing('courses', ['id' => $assignment->course_id]);
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('assignments', ['id' => $secondAssignment->id]);
        foreach ($submissions as $submission) {
            $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
            Storage::disk('local')->assertMissing($submission->file_path);
        }
    }

    private function createAssignment(): array
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);

        return [$lecturer, $assignment];
    }

    private function createSubmission(?Assignment $assignment = null): Submission
    {
        if ($assignment === null) {
            [, $assignment] = $this->createAssignment();
        }

        return Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => User::factory()->mahasiswa()->create()->id,
            'file_path' => "submissions/{$assignment->id}/".fake()->uuid().'.pdf',
            'original_name' => 'answer.pdf',
            'file_size' => 17,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
    }
}
