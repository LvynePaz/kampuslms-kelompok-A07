<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Milestone4BuildTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji 1: Memastikan kode bersih dari pemanggilan env() di luar config/ (Pertanyaan 6 Checkpoint).
     */
    public function test_env_helper_is_not_called_outside_config_directory(): void
    {
        $directories = [
            app_path(),
            base_path('routes'),
            resource_path('views'),
        ];

        $envCalls = [];

        foreach ($directories as $directory) {
            if (! File::isDirectory($directory)) {
                continue;
            }

            $files = File::allFiles($directory);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $content = $file->getContents();

                // Cari fungsi env( kecuali comment
                if (preg_match('/\benv\s*\(/', $content)) {
                    $envCalls[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertEmpty(
            $envCalls,
            'Ditemukan fungsi env() di luar folder config: ' . implode(', ', $envCalls)
        );
    }

    /**
     * Uji 2: Pengunjung tanpa login DILARANG mengunduh submission (Anti-Diskualifikasi #3).
     */
    public function test_guest_cannot_download_submission(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'file_path'     => 'submissions/dummy-tugas.pdf',
        ]);

        $response = $this->get(route('submissions.download', $submission));

        // Harus dialihkan ke login (HTTP 302)
        $response->assertRedirect(route('login'));
    }

    /**
     * Uji 3: Submission mahasiswa lain tidak boleh diintip oleh mahasiswa yang tidak berhak (Anti IDOR).
     */
    public function test_student_cannot_view_or_download_other_student_submission(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $student1 = User::factory()->create(['role' => 'mahasiswa']);
        $student2 = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);

        $submissionMhs1 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student1->id,
            'file_path'     => 'submissions/tugas-mhs1.pdf',
        ]);

        // Mahasiswa 2 mencoba mengunduh tugas Mahasiswa 1
        $response = $this->actingAs($student2)->get(route('submissions.download', $submissionMhs1));

        // Harus ditolak 403 Forbidden oleh SubmissionPolicy
        $response->assertStatus(403);
    }

    /**
     * Uji 4: Akun demo admin, dosen, dan mahasiswa dapat dibuat melalui Seeder.
     */
    public function test_demo_accounts_can_be_seeded_and_authenticated(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'admin@kampuslms.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'dosen@kampuslms.test', 'role' => 'dosen']);
        $this->assertDatabaseHas('users', ['email' => 'mahasiswa@kampuslms.test', 'role' => 'mahasiswa']);
    }

    /**
     * Uji 5: Penyimpanan berkas submission wajib menggunakan disk privat 'local'.
     */
    public function test_filesystem_default_or_submission_disk_is_local(): void
    {
        $disk = config('filesystems.default');
        $this->assertEquals('local', $disk, 'Default filesystem disk wajib "local" (storage privat), bukan public!');
    }
}

