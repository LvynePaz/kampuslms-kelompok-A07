<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtsDemoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_uts_demo_flow_admin_lecturer_student(): void
    {
        // Setup akun demo
        $admin = User::factory()->admin()->create(['email' => 'admin@kampuslms.test']);
        $lecturer = User::factory()->dosen()->create(['email' => 'dosen@kampuslms.test']);
        $student = User::factory()->mahasiswa()->create(['email' => 'mahasiswa@kampuslms.test']);

        // Skenario a: Admin membuat mata kuliah, menugaskan dosen, dan mendaftarkan mahasiswa
        $response = $this->actingAs($admin)->post(route('admin.courses.store'), [
            'code'        => 'SI2514099',
            'name'        => 'Pemrograman Web Lanjut',
            'sks'         => 3,
            'lecturer_id' => $lecturer->id,
            'status'      => 'active',
            'description' => 'Kelas praktikum lanjutan',
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $course = Course::where('code', 'SI2514099')->firstOrFail();
        $this->assertEquals($lecturer->id, $course->lecturer_id);
        $this->assertTrue($course->students()->whereKey($student->id)->exists());

        // Skenario b: Dosen login, membuat materi dan tugas pada mata kuliahnya
        $matResponse = $this->actingAs($lecturer)->post(route('dosen.courses.materials.store', $course), [
            'title'        => 'Materi 1 - Konsep REST API',
            'type'         => 'link',
            'external_url' => 'https://example.com/slide1',
            'description'  => 'Slide materi pertemuan pertama',
        ]);
        $matResponse->assertRedirect(route('dosen.courses.show', $course));
        $this->assertDatabaseHas('materials', [
            'course_id'   => $course->id,
            'title'       => 'Materi 1 - Konsep REST API',
            'uploaded_by' => $lecturer->id,
        ]);

        $asgResponse = $this->actingAs($lecturer)->post(route('dosen.courses.assignments.store', $course), [
            'title'        => 'Tugas 1 - Implementasi Controller',
            'instructions' => 'Kumpulkan sebelum batas waktu',
            'due_at'       => now()->addDays(7)->toDateTimeString(),
            'max_score'    => 100,
            'status'       => 'published',
        ]);
        $asgResponse->assertRedirect(route('dosen.courses.show', $course));
        $this->assertDatabaseHas('assignments', [
            'course_id'  => $course->id,
            'title'      => 'Tugas 1 - Implementasi Controller',
            'created_by' => $lecturer->id,
        ]);

        // Skenario c: Mahasiswa login, lalu melihat mata kuliah yang diikutinya beserta materi dan tugasnya
        $studentIndex = $this->actingAs($student)->get(route('mahasiswa.courses.index'));
        $studentIndex->assertOk();
        $studentIndex->assertSee('Pemrograman Web Lanjut');

        $studentShow = $this->actingAs($student)->get(route('mahasiswa.courses.show', $course));
        $studentShow->assertOk();
        $studentShow->assertSee('Pemrograman Web Lanjut');
        $studentShow->assertSee('Materi 1 - Konsep REST API');
        $studentShow->assertSee('Tugas 1 - Implementasi Controller');
    }
}
