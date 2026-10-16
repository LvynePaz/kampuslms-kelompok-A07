<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_can_upload_pdf_material_to_private_disk_up_to_20mb(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        $pdfFile = UploadedFile::fake()->create('pertemuan_1_arsitektur.pdf', 15000, 'application/pdf'); // ~15 MB

        $response = $this->actingAs($lecturer)->post(route('dosen.courses.materials.store', $course), [
            'title'         => 'Modul 1 - Arsitektur Web',
            'type'          => 'file',
            'description'   => 'Modul kuliah pertemuan 1',
            'material_file' => $pdfFile,
        ]);

        $response->assertRedirect(route('dosen.courses.show', $course));
        $response->assertSessionHas('success');

        $material = Material::where('title', 'Modul 1 - Arsitektur Web')->first();
        $this->assertNotNull($material);
        $this->assertEquals('pertemuan_1_arsitektur.pdf', $material->original_name);
        $this->assertEquals('application/pdf', $material->mime_type);
        $this->assertTrue($material->file_size > 0);
        $this->assertStringContainsString('MB', $material->formatted_file_size);

        // Memastikan tersimpan di disk privat 'local' (storage/app/private)
        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_dosen_can_upload_pptx_and_docx_materials(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        // Uji berkas PPTX
        $pptxFile = UploadedFile::fake()->create('slide_presentasi.pptx', 5000, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $this->actingAs($lecturer)->post(route('dosen.courses.materials.store', $course), [
            'title'         => 'Slide Presentasi Kuliah',
            'type'          => 'file',
            'material_file' => $pptxFile,
        ])->assertRedirect(route('dosen.courses.show', $course));

        $matPptx = Material::where('title', 'Slide Presentasi Kuliah')->first();
        $this->assertNotNull($matPptx);
        Storage::disk('local')->assertExists($matPptx->file_path);

        // Uji berkas DOCX
        $docxFile = UploadedFile::fake()->create('panduan_praktikum.docx', 3000, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->actingAs($lecturer)->post(route('dosen.courses.materials.store', $course), [
            'title'         => 'Buku Panduan Praktikum',
            'type'          => 'file',
            'material_file' => $docxFile,
        ])->assertRedirect(route('dosen.courses.show', $course));

        $matDocx = Material::where('title', 'Buku Panduan Praktikum')->first();
        $this->assertNotNull($matDocx);
        Storage::disk('local')->assertExists($matDocx->file_path);
    }

    public function test_upload_rejects_files_larger_than_20mb(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        // 25 MB (lebih dari batas 20 MB = 20480 KB)
        $oversizedFile = UploadedFile::fake()->create('file_kebesaran.pdf', 25600, 'application/pdf');

        $response = $this->actingAs($lecturer)->post(route('dosen.courses.materials.store', $course), [
            'title'         => 'Materi Kebesaran',
            'type'          => 'file',
            'material_file' => $oversizedFile,
        ]);

        $response->assertSessionHasErrors('material_file');
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Kebesaran']);
    }

    public function test_upload_rejects_invalid_file_extensions(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        // Berkas terlarang (misal executable / script)
        $badFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($lecturer)->post(route('dosen.courses.materials.store', $course), [
            'title'         => 'Materi Bahaya',
            'type'          => 'file',
            'material_file' => $badFile,
        ]);

        $response->assertSessionHasErrors('material_file');
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Bahaya']);
    }

    public function test_dosen_can_create_material_with_external_link_alternative(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        $response = $this->actingAs($lecturer)->post(route('dosen.courses.materials.store', $course), [
            'title'        => 'Video Referensi Kuliah',
            'type'         => 'link',
            'external_url' => 'https://youtube.com/watch?v=example123',
            'description'  => 'Video pengantar',
        ]);

        $response->assertRedirect(route('dosen.courses.show', $course));
        $this->assertDatabaseHas('materials', [
            'course_id'    => $course->id,
            'title'        => 'Video Referensi Kuliah',
            'type'         => 'link',
            'external_url' => 'https://youtube.com/watch?v=example123',
        ]);
    }

    public function test_enrolled_student_can_download_material(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $file = UploadedFile::fake()->create('materi_resmi.pdf', 2048, 'application/pdf');
        $path = $file->store('materials', 'local');

        $material = Material::create([
            'course_id'     => $course->id,
            'uploaded_by'   => $lecturer->id,
            'title'         => 'Materi Resmi Kuliah',
            'type'          => 'file',
            'file_path'     => $path,
            'original_name' => 'materi_resmi.pdf',
            'file_size'     => 2048 * 1024,
            'mime_type'     => 'application/pdf',
        ]);

        // Mahasiswa terdaftar dapat mengunduh berkas via route download
        $response = $this->actingAs($student)->get(route('materials.download', $material));
        $response->assertOk();
        $response->assertHeader('content-disposition');

        // Mahasiswa terdaftar juga dapat membuka berkas via route file
        $responseView = $this->actingAs($student)->get(route('materials.file', $material));
        $responseView->assertOk();
    }

    public function test_unenrolled_student_is_forbidden_from_downloading_material(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $otherStudent = User::factory()->mahasiswa()->create(); // Tidak terdaftar di mata kuliah

        $file = UploadedFile::fake()->create('materi_rahasia.pdf', 1024, 'application/pdf');
        $path = $file->store('materials', 'local');

        $material = Material::create([
            'course_id'     => $course->id,
            'uploaded_by'   => $lecturer->id,
            'title'         => 'Materi Rahasia Kelas',
            'type'          => 'file',
            'file_path'     => $path,
            'original_name' => 'materi_rahasia.pdf',
            'file_size'     => 1024 * 1024,
            'mime_type'     => 'application/pdf',
        ]);

        // Akses ditolak HTTP 403 Forbidden via Policy
        $this->actingAs($otherStudent)
            ->get(route('materials.download', $material))
            ->assertForbidden();

        $this->actingAs($otherStudent)
            ->get(route('materials.file', $material))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_downloading_material(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        $material = Material::create([
            'course_id'     => $course->id,
            'uploaded_by'   => $lecturer->id,
            'title'         => 'Materi Kuliah',
            'type'          => 'file',
            'file_path'     => 'materials/dummy.pdf',
            'original_name' => 'dummy.pdf',
        ]);

        $this->get(route('materials.download', $material))->assertRedirect(route('login'));
        $this->get(route('materials.file', $material))->assertRedirect(route('login'));
    }

    public function test_physical_file_is_deleted_when_material_is_deleted(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        $file = UploadedFile::fake()->create('materi_hapus.pdf', 500, 'application/pdf');
        $path = $file->store('materials', 'local');

        $material = Material::create([
            'course_id'     => $course->id,
            'uploaded_by'   => $lecturer->id,
            'title'         => 'Materi Mau Dihapus',
            'type'          => 'file',
            'file_path'     => $path,
            'original_name' => 'materi_hapus.pdf',
        ]);

        Storage::disk('local')->assertExists($path);

        // Dosen menghapus materi
        $response = $this->actingAs($lecturer)->delete(route('dosen.materials.destroy', $material));
        $response->assertRedirect(route('dosen.courses.show', $course));

        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_physical_file_is_deleted_when_parent_course_is_deleted_cascade(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();

        $file = UploadedFile::fake()->create('materi_cascade.pdf', 500, 'application/pdf');
        $path = $file->store('materials', 'local');

        $material = Material::create([
            'course_id'     => $course->id,
            'uploaded_by'   => $lecturer->id,
            'title'         => 'Materi Cascade',
            'type'          => 'file',
            'file_path'     => $path,
            'original_name' => 'materi_cascade.pdf',
        ]);

        Storage::disk('local')->assertExists($path);

        // Admin menghapus course
        $response = $this->actingAs($admin)->delete(route('admin.courses.destroy', $course));
        $response->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($path);
    }
}
