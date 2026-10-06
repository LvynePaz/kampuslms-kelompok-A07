<?php

namespace Tests\Feature;

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

    public function test_admin_routes_remain_available_without_login(): void
    {
        $this->assertNotContains('role:admin', Route::getRoutes()->getByName('admin.courses.index')->gatherMiddleware());

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
        $user = new User();
        $user->role = 'mahasiswa';

        $this->actingAs($user)
            ->get('/dosen/courses')
            ->assertForbidden();
    }
}