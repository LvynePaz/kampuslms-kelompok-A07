<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialFileDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_material_pages_show_original_filename_and_human_readable_file_size(): void
    {
        [$lecturer, $course] = $this->createCourse();
        $material = $course->materials()->create([
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi Minggu Pertama',
            'type' => 'file',
            'file_path' => 'materials/random-server-name.pdf',
            'original_name' => 'materi-asli-minggu-pertama.pdf',
            'file_size' => 2 * 1024 * 1024,
        ]);
        Storage::disk('public')->put($material->file_path, 'file contents');

        $this->actingAs($lecturer)
            ->get(route('dosen.materials.show', $material))
            ->assertOk()
            ->assertSee('materi-asli-minggu-pertama.pdf')
            ->assertSee('2.0 MB')
            ->assertDontSee('random-server-name.pdf');

        $this->actingAs($lecturer)
            ->get(route('dosen.materials.edit', $material))
            ->assertOk()
            ->assertSee('materi-asli-minggu-pertama.pdf')
            ->assertSee('2.0 MB')
            ->assertDontSee('random-server-name.pdf');
    }

    public function test_material_page_formats_small_files_as_kilobytes(): void
    {
        [$lecturer, $course] = $this->createCourse();
        $material = $course->materials()->create([
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi Ringkas',
            'type' => 'file',
            'file_path' => 'materials/server-name.txt',
            'original_name' => 'catatan-asli.txt',
            'file_size' => 1536,
        ]);
        Storage::disk('public')->put($material->file_path, 'file contents');

        $this->actingAs($lecturer)
            ->get(route('dosen.materials.show', $material))
            ->assertOk()
            ->assertSee('catatan-asli.txt')
            ->assertSee('1.5 KB');
    }

    public function test_external_material_link_is_shown_without_local_file_size(): void
    {
        [$lecturer, $course] = $this->createCourse();
        $material = $course->materials()->create([
            'uploaded_by' => $lecturer->id,
            'title' => 'Tautan Referensi',
            'type' => 'link',
            'external_url' => 'https://example.test/reference',
            'file_size' => 4096,
        ]);

        $this->actingAs($lecturer)
            ->get(route('dosen.materials.show', $material))
            ->assertOk()
            ->assertSee('https://example.test/reference')
            ->assertDontSee('Ukuran Berkas')
            ->assertDontSee('4.0 KB');
    }

    private function createCourse(): array
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        return [$lecturer, $course];
    }
}
