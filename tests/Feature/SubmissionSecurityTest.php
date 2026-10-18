<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $dosen;
    private User $student1;
    private User $student2;
    private User $admin;
    private Course $course;
    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->dosen = User::factory()->create(['role' => 'dosen']);
        $this->student1 = User::factory()->create(['role' => 'mahasiswa']);
        $this->student2 = User::factory()->create(['role' => 'mahasiswa']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->course = Course::factory()->create(['lecturer_id' => $this->dosen->id]);
        $this->course->students()->attach([$this->student1->id, $this->student2->id]);

        $this->assignment = Assignment::factory()->create([
            'course_id'   => $this->course->id,
            'created_by'  => $this->dosen->id,
            'status'      => 'published',
            'due_at'      => now()->addDays(3),
            'allow_late'  => true,
        ]);
    }

    public function test_student_can_submit_assignment_to_private_disk(): void
    {
        $file = UploadedFile::fake()->create('jawaban-tugas.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->student1)
            ->post(route('submissions.store', $this->assignment), [
                'file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $this->assignment->id,
            'user_id'       => $this->student1->id,
            'original_name' => 'jawaban-tugas.pdf',
            'is_late'       => false,
        ]);

        $submission = Submission::where('user_id', $this->student1->id)->first();
        $this->assertNotNull($submission);

        // Pastikan berkas disimpan di disk privat 'local', BUKAN public
        Storage::disk('local')->assertExists($submission->file_path);
        Storage::disk('public')->assertMissing($submission->file_path);
    }

    public function test_is_late_calculated_correctly_on_server(): void
    {
        // Ubah deadline ke masa lalu
        $pastAssignment = Assignment::factory()->create([
            'course_id'   => $this->course->id,
            'created_by'  => $this->dosen->id,
            'status'      => 'published',
            'due_at'      => now()->subDay(),
            'allow_late'  => true,
        ]);

        $file = UploadedFile::fake()->create('tugas-terlambat.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->student1)
            ->post(route('submissions.store', $pastAssignment), [
                'file' => $file,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $pastAssignment->id,
            'user_id'       => $this->student1->id,
            'is_late'       => true,
        ]);
    }

    public function test_submission_rejected_when_past_due_and_allow_late_is_false(): void
    {
        $strictAssignment = Assignment::factory()->create([
            'course_id'   => $this->course->id,
            'created_by'  => $this->dosen->id,
            'status'      => 'published',
            'due_at'      => now()->subDay(),
            'allow_late'  => false,
        ]);

        $file = UploadedFile::fake()->create('tugas.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->student1)
            ->post(route('submissions.store', $strictAssignment), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $strictAssignment->id,
            'user_id'       => $this->student1->id,
        ]);
    }

    public function test_resubmission_overwrites_existing_record_and_removes_old_file(): void
    {
        $file1 = UploadedFile::fake()->create('versi-1.pdf', 200, 'application/pdf');
        $this->actingAs($this->student1)->post(route('submissions.store', $this->assignment), ['file' => $file1]);

        $firstSubmission = Submission::where('user_id', $this->student1->id)->first();
        $oldPath = $firstSubmission->file_path;
        Storage::disk('local')->assertExists($oldPath);

        // Kumpulkan ulang versi 2
        $file2 = UploadedFile::fake()->create('versi-2.pdf', 250, 'application/pdf');
        $this->actingAs($this->student1)->post(route('submissions.store', $this->assignment), ['file' => $file2]);

        // Baris database harus tetap 1 (updateOrCreate)
        $this->assertEquals(1, Submission::where('assignment_id', $this->assignment->id)->where('user_id', $this->student1->id)->count());

        $updatedSubmission = Submission::where('user_id', $this->student1->id)->first();
        $this->assertEquals('versi-2.pdf', $updatedSubmission->original_name);

        // Berkas lama harus terhapus, berkas baru harus ada
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($updatedSubmission->file_path);
    }

    public function test_idor_protection_student_cannot_download_other_student_submission(): void
    {
        // Student 1 mengumpulkan tugas
        $file = UploadedFile::fake()->create('rahasia-student-1.pdf', 200, 'application/pdf');
        $this->actingAs($this->student1)->post(route('submissions.store', $this->assignment), ['file' => $file]);

        $submission = Submission::where('user_id', $this->student1->id)->first();

        // Student 1 (pemilik) bisa unduh
        $responseOwner = $this->actingAs($this->student1)->get(route('submissions.download', $submission));
        $responseOwner->assertOk();

        // Student 2 (mahasiswa lain) MENCOBA UNDUH -> Wajib ditolak (403 IDOR blocked)
        $responseIdor = $this->actingAs($this->student2)->get(route('submissions.download', $submission));
        $responseIdor->assertForbidden();

        // Dosen pengampu bisa unduh
        $responseDosen = $this->actingAs($this->dosen)->get(route('submissions.download', $submission));
        $responseDosen->assertOk();

        // Admin bisa unduh
        $responseAdmin = $this->actingAs($this->admin)->get(route('submissions.download', $submission));
        $responseAdmin->assertOk();
    }
}
