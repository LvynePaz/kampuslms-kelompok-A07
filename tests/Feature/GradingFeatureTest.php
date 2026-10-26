<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;
    private User $student;
    private Course $course;
    private Assignment $assignment;
    private Submission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lecturer = User::factory()->create(['role' => 'dosen']);
        $this->student = User::factory()->create(['role' => 'mahasiswa']);

        $this->course = Course::factory()->create([
            'lecturer_id' => $this->lecturer->id,
        ]);
        $this->course->students()->attach($this->student->id);

        $this->assignment = Assignment::factory()->create([
            'course_id'  => $this->course->id,
            'max_score'  => 100,
            'status'     => 'published',
            'due_at'     => now()->addDays(3),
        ]);

        $this->submission = Submission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $this->student->id,
            'file_path'     => 'submissions/test.pdf',
            'original_name' => 'jawaban_tugas.pdf',
            'file_size'     => 1024,
            'submitted_at'  => now(),
            'is_late'       => false,
        ]);
    }

    public function test_lecturer_can_grade_submission_idempotently(): void
    {
        // 1. Dosen memberi nilai pertama kali
        $response = $this->actingAs($this->lecturer)
            ->post(route('dosen.submissions.grades.store', $this->submission), [
                'score'    => 85.5,
                'feedback' => 'Penjelasan sudah baik.',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('grades', [
            'submission_id' => $this->submission->id,
            'score'         => 85.5,
            'feedback'      => 'Penjelasan sudah baik.',
            'graded_by'     => $this->lecturer->id,
        ]);

        // 2. Dosen memperbarui nilai (idempotent: tidak menumpuk baris baru)
        $response2 = $this->actingAs($this->lecturer)
            ->post(route('dosen.submissions.grades.store', $this->submission), [
                'score'    => 95.0,
                'feedback' => 'Setelah revisi, jawaban sempurna.',
            ]);

        $response2->assertSessionHas('success');
        $this->assertEquals(1, Grade::where('submission_id', $this->submission->id)->count());
        $this->assertDatabaseHas('grades', [
            'submission_id' => $this->submission->id,
            'score'         => 95.0,
            'feedback'      => 'Setelah revisi, jawaban sempurna.',
        ]);
    }

    public function test_other_lecturer_cannot_grade_submission(): void
    {
        $otherLecturer = User::factory()->create(['role' => 'dosen']);

        $response = $this->actingAs($otherLecturer)
            ->post(route('dosen.submissions.grades.store', $this->submission), [
                'score'    => 80,
                'feedback' => 'Bukan dosen kelas ini.',
            ]);

        $response->assertForbidden();
    }

    public function test_student_cannot_grade_submission(): void
    {
        $response = $this->actingAs($this->student)
            ->post(route('dosen.submissions.grades.store', $this->submission), [
                'score'    => 100,
                'feedback' => 'Mencoba menilai sendiri.',
            ]);

        $response->assertForbidden();
    }

    public function test_score_validation_enforces_max_score(): void
    {
        $response = $this->actingAs($this->lecturer)
            ->post(route('dosen.submissions.grades.store', $this->submission), [
                'score'    => 120, // melebih max_score (100)
                'feedback' => 'Nilai terlalu tinggi',
            ]);

        $response->assertSessionHasErrors(['score']);
    }

    public function test_student_can_see_grade_on_assignment_show_page(): void
    {
        Grade::create([
            'submission_id' => $this->submission->id,
            'graded_by'     => $this->lecturer->id,
            'score'         => 92.5,
            'feedback'      => 'Sangat memuaskan.',
            'graded_at'     => now(),
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('mahasiswa.assignments.show', $this->assignment));

        $response->assertOk();
        $response->assertSee('Hasil Penilaian Dosen');
        $response->assertSee('92.5');
        $response->assertSee('Sangat memuaskan.');
    }

    public function test_lecturer_can_view_course_grades_recap(): void
    {
        Grade::create([
            'submission_id' => $this->submission->id,
            'graded_by'     => $this->lecturer->id,
            'score'         => 88.0,
            'feedback'      => 'Bagus',
            'graded_at'     => now(),
        ]);

        $response = $this->actingAs($this->lecturer)
            ->get(route('dosen.courses.grades', $this->course));

        $response->assertOk();
        $response->assertSee('Rekap Nilai Perkuliahan');
        $response->assertSee($this->student->name);
        $response->assertSee('88');
    }

    public function test_student_can_view_my_grades_recap(): void
    {
        Grade::create([
            'submission_id' => $this->submission->id,
            'graded_by'     => $this->lecturer->id,
            'score'         => 90.0,
            'feedback'      => 'Mantap',
            'graded_at'     => now(),
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('mahasiswa.grades.index'));

        $response->assertOk();
        $response->assertSee('Rekap Nilai Perkuliahan');
        $response->assertSee($this->assignment->title);
        $response->assertSee('90');
    }
}
