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

        // Admin melihat detail mata kuliah dan melihat tombol Edit
        $adminShow = $this->actingAs($admin)->get(route('admin.courses.show', $course));
        $adminShow->assertOk();
        $adminShow->assertSee('Edit Mata Kuliah');

        // Skenario b: Dosen login, melihat tombol Tambah Materi & Buat Tugas pada mata kuliahnya
        $dosenShow = $this->actingAs($lecturer)->get(route('dosen.courses.show', $course));
        $dosenShow->assertOk();
        $dosenShow->assertSee('Tambah Materi');
        $dosenShow->assertSee('Buat Tugas');

        // Dosen membuka form buat materi & tugas
        $this->actingAs($lecturer)->get(route('dosen.courses.materials.create', $course))->assertOk();
        $this->actingAs($lecturer)->get(route('dosen.courses.assignments.create', $course))->assertOk();

        // Dosen membuat materi dan tugas
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
        $assignment = $course->assignments()->first();

        // Skenario c: Mahasiswa login, lalu melihat mata kuliah yang diikutinya beserta materi dan tugasnya
        $studentIndex = $this->actingAs($student)->get(route('mahasiswa.courses.index'));
        $studentIndex->assertOk();
        $studentIndex->assertSee('Pemrograman Web Lanjut');

        $studentShow = $this->actingAs($student)->get(route('mahasiswa.courses.show', $course));
        $studentShow->assertOk();
        $studentShow->assertSee('Pemrograman Web Lanjut');
        $studentShow->assertSee('Materi 1 - Konsep REST API');
        $studentShow->assertSee('Tugas 1 - Implementasi Controller');
        $studentShow->assertSee('Buka Tugas');
        // Mahasiswa tidak melihat tombol tambah materi atau buat tugas
        $studentShow->assertDontSee('Tambah Materi');
        $studentShow->assertDontSee('Buat Tugas');

        // Mahasiswa membuka detail tugas
        $studentAsgShow = $this->actingAs($student)->get(route('mahasiswa.assignments.show', $assignment));
        $studentAsgShow->assertOk();
        $studentAsgShow->assertSee('Tugas 1 - Implementasi Controller');
        $studentAsgShow->assertSee('Kumpulkan sebelum batas waktu');

        // Skenario Tambahan: Akses Berkas Materi
        $material = $course->materials()->first();
        $matFileResponse = $this->actingAs($student)->get(route('materials.file', $material));
        $this->assertTrue(in_array($matFileResponse->status(), [200, 302]));

        // Skenario Tambahan: Dosen mengedit materi (CRUD Materi - Edit & Update)
        $editMatPage = $this->actingAs($lecturer)->get(route('dosen.materials.edit', $material));
        $editMatPage->assertOk();
        $editMatPage->assertSee('Edit Materi Perkuliahan');
        $editMatPage->assertSee('Materi 1 - Konsep REST API');

        $updateMatResponse = $this->actingAs($lecturer)->put(route('dosen.materials.update', $material), [
            'title'        => 'Materi 1 - Revisi Arsitektur RESTful API',
            'type'         => 'link',
            'external_url' => 'https://example.com/slide1-revisi',
            'description'  => 'Slide materi telah direvisi oleh dosen pengampu',
        ]);
        $updateMatResponse->assertRedirect(route('dosen.courses.show', $course));
        $this->assertDatabaseHas('materials', [
            'id'           => $material->id,
            'title'        => 'Materi 1 - Revisi Arsitektur RESTful API',
            'external_url' => 'https://example.com/slide1-revisi',
        ]);

        // Skenario Tambahan: Halaman detail materi dapat diakses via browser
        $dosenMatShow = $this->actingAs($lecturer)->get(route('dosen.materials.show', $material));
        $dosenMatShow->assertOk();
        $dosenMatShow->assertSee('Materi 1 - Revisi Arsitektur RESTful API');
        $dosenMatShow->assertSee('Edit Materi');

        $studentMatShow = $this->actingAs($student)->get(route('mahasiswa.materials.show', $material));
        $studentMatShow->assertOk();
        $studentMatShow->assertSee('Materi 1 - Revisi Arsitektur RESTful API');
        $studentMatShow->assertDontSee('Edit Materi');

        // Skenario Tambahan: Dosen lain & mahasiswa dicegah mengubah materi (Otorisasi)
        $otherLecturer = User::factory()->dosen()->create();
        $this->actingAs($otherLecturer)->get(route('dosen.materials.edit', $material))->assertForbidden();
        $this->actingAs($otherLecturer)->put(route('dosen.materials.update', $material), [
            'title' => 'Materi Bajakan',
            'type'  => 'link',
        ])->assertForbidden();
        $this->actingAs($otherLecturer)->delete(route('dosen.materials.destroy', $material))->assertForbidden();
        $this->actingAs($student)->get(route('dosen.materials.edit', $material))->assertForbidden();

        // Skenario Tambahan: Dosen menghapus materi (CRUD Materi - Destroy)
        $deleteMatResponse = $this->actingAs($lecturer)->delete(route('dosen.materials.destroy', $material));
        $deleteMatResponse->assertRedirect(route('dosen.courses.show', $course));
        $this->assertDatabaseMissing('materials', [
            'id' => $material->id,
        ]);

        // Skenario Tambahan: Dosen mengedit tugas (CRUD Tugas - Edit & Update)
        $editAsgPage = $this->actingAs($lecturer)->get(route('dosen.assignments.edit', $assignment));
        $editAsgPage->assertOk();
        $editAsgPage->assertSee('Edit Tugas Perkuliahan');

        $updateAsgResponse = $this->actingAs($lecturer)->put(route('dosen.assignments.update', $assignment), [
            'title'        => 'Tugas 1 - Revisi Implementasi Controller',
            'instructions' => 'Instruksi telah diperbarui oleh dosen pengampu',
            'due_at'       => now()->addDays(10)->toDateTimeString(),
            'max_score'    => 95,
            'status'       => 'published',
        ]);
        $updateAsgResponse->assertRedirect(route('dosen.courses.show', $course));
        $this->assertDatabaseHas('assignments', [
            'id'    => $assignment->id,
            'title' => 'Tugas 1 - Revisi Implementasi Controller',
            'max_score' => 95,
        ]);

        // Skenario Tambahan: Dosen menghapus tugas (CRUD Tugas - Destroy)
        $deleteAsgResponse = $this->actingAs($lecturer)->delete(route('dosen.assignments.destroy', $assignment));
        $deleteAsgResponse->assertRedirect(route('dosen.courses.show', $course));
        $this->assertDatabaseMissing('assignments', [
            'id' => $assignment->id,
        ]);

        // Skenario Tambahan: Tombol login cepat tidak ada pada halaman login
        $this->post(route('logout'));
        $loginPage = $this->get(route('login'));
        $loginPage->assertOk();
        $loginPage->assertDontSee('Pilih Akun Demo (1-Klik Isi)');
    }
}
