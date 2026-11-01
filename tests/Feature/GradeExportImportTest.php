<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeExport;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GradeExportImportTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;
    private User $student;
    private Course $course;
    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lecturer = User::factory()->create(['role' => 'dosen']);
        $this->student = User::factory()->create([
            'role'    => 'mahasiswa',
            'nim_nip' => '10241099',
            'email'   => 'mahasiswa.test@kampuslms.test',
        ]);

        $this->course = Course::factory()->create([
            'lecturer_id' => $this->lecturer->id,
            'code'        => 'SI2514099',
            'name'        => 'Pemrograman Web Lanjut',
        ]);
        $this->course->students()->attach($this->student->id);

        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'max_score' => 100,
            'title'     => 'Tugas 1',
        ]);
    }

    public function test_lecturer_can_export_course_grades_to_csv(): void
    {
        $response = $this->actingAs($this->lecturer)
            ->get(route('dosen.courses.grades.export', $this->course));

        $response->assertOk();
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'rekap_nilai_'));

        // Pastikan tercatat di tabel baru grade_exports (Fitur Diferensiasi No. 8)
        $this->assertDatabaseHas('grade_exports', [
            'course_id' => $this->course->id,
            'user_id'   => $this->lecturer->id,
            'type'      => 'export',
        ]);
    }

    public function test_lecturer_can_import_grades_from_csv(): void
    {
        $csvContent = "NIM,Nilai,Feedback\n"
                    . "10241099,92.5,Tugas sangat baik dan rapi\n";

        $file = UploadedFile::fake()->createWithContent('nilai_tugas.csv', $csvContent);

        $response = $this->actingAs($this->lecturer)
            ->post(route('dosen.assignments.grades.import', $this->assignment), [
                'csv_file' => $file,
            ]);

        $response->assertSessionHas('success');

        // Pastikan submission dan grade berhasil dibuat/diperbarui
        $submission = Submission::where('assignment_id', $this->assignment->id)
            ->where('user_id', $this->student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'score'         => 92.5,
            'feedback'      => 'Tugas sangat baik dan rapi',
        ]);

        // Pastikan tercatat di tabel baru grade_exports
        $this->assertDatabaseHas('grade_exports', [
            'course_id' => $this->course->id,
            'user_id'   => $this->lecturer->id,
            'type'      => 'import',
            'row_count' => 1,
        ]);
    }

    public function test_unauthorized_user_cannot_export_or_import_grades(): void
    {
        $otherLecturer = User::factory()->create(['role' => 'dosen']);

        $exportResponse = $this->actingAs($otherLecturer)
            ->get(route('dosen.courses.grades.export', $this->course));
        $exportResponse->assertForbidden();

        $file = UploadedFile::fake()->createWithContent('nilai.csv', "NIM,Nilai,Feedback\n10241099,90,OK\n");
        $importResponse = $this->actingAs($otherLecturer)
            ->post(route('dosen.assignments.grades.import', $this->assignment), [
                'csv_file' => $file,
            ]);
        $importResponse->assertForbidden();
    }
}

