<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_are_registered_with_admin_prefix(): void
    {
        $this->assertTrue(Route::has('admin.courses.index'));
        $this->assertTrue(Route::has('admin.users.index'));
    }

    public function test_admin_routes_require_an_admin_role(): void
    {
        $this->assertContains('role:admin', Route::getRoutes()->getByName('admin.courses.index')->gatherMiddleware());

        $this->get('/admin/courses')->assertForbidden();
        $this->get('/admin/courses/create')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/users/create')->assertForbidden();

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/courses')->assertOk();
        $this->get('/admin/courses/create')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users/create')->assertOk();
    }

    public function test_guest_is_forbidden_from_lecturer_routes(): void
    {
        $this->get('/dosen/courses')->assertForbidden();
    }

    public function test_student_role_is_forbidden_from_lecturer_routes(): void
    {
        $this->actingAs(User::factory()->mahasiswa()->create())
            ->get('/dosen/courses')
            ->assertForbidden();
    }

    public function test_assignment_show_works_with_shallow_route_binding(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $assignment = Assignment::factory()->for($course)->create();

        $this->actingAs($lecturer)
            ->get(route('dosen.assignments.show', $assignment))
            ->assertOk()
            ->assertSee($course->name)
            ->assertSee($assignment->title);
    }

    public function test_student_cannot_see_or_view_draft_assignment(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $course->students()->attach($student->id);

        $published = Assignment::factory()->for($course)->create([
            'title' => 'Tugas Published Siap Kerjakan',
            'status' => 'published',
        ]);
        $draft = Assignment::factory()->for($course)->create([
            'title' => 'Tugas Draft Rahasia Dosen',
            'status' => 'draft',
        ]);

        // Di halaman detail course mahasiswa, tugas draft TIDAK boleh muncul
        $this->actingAs($student)
            ->get(route('mahasiswa.courses.show', $course))
            ->assertOk()
            ->assertSee('Tugas Published Siap Kerjakan')
            ->assertDontSee('Tugas Draft Rahasia Dosen');

        // Mengakses langsung URL tugas draft ditolak oleh Policy (403)
        $this->actingAs($student)
            ->get(route('mahasiswa.assignments.show', $draft))
            ->assertForbidden();
    }
}