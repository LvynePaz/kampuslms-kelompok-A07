<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_resource_and_sanctum_token_and_me_requires_authentication(): void
    {
        $user = User::factory()->mahasiswa()->create(['password' => 'secret-password']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.role', 'mahasiswa')
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertArrayNotHasKey('password', $response->json('data.user'));
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->withToken($response->json('data.token'))
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_course_collections_are_scoped_and_paginated(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id);
        Course::factory()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $course->id)
            ->assertJsonPath('data.0.lecturer.id', $lecturer->id)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'total']]);
    }

    public function test_students_cannot_create_assignments_and_forbidden_response_matches_contract(): void
    {
        $this->actingAs(User::factory()->mahasiswa()->create())
            ->postJson('/api/v1/assignments', [])
            ->assertForbidden()
            ->assertExactJson(['message' => 'Anda tidak memiliki akses ke sumber daya ini.']);
    }

    public function test_student_can_submit_and_course_owner_can_upsert_grade(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);

        $submission = $this->actingAs($student)
            ->postJson("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('answer.pdf', 100, 'application/pdf'),
                'note' => 'Jawaban tugas',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $student->id)
            ->json('data.id');

        $this->actingAs($lecturer)
            ->putJson("/api/v1/submissions/{$submission}/grade", ['score' => 80, 'feedback' => 'Baik'])
            ->assertCreated()
            ->assertJsonPath('data.score', '80.00');

        $this->putJson("/api/v1/submissions/{$submission}/grade", ['score' => 90, 'feedback' => 'Revisi nilai'])
            ->assertOk()
            ->assertJsonPath('data.score', '90.00');

        $this->assertSame(1, Grade::where('submission_id', $submission)->count());
    }

    public function test_api_validation_error_uses_contract_message_and_errors_shape(): void
    {
        $this->actingAs(User::factory()->dosen()->create())
            ->postJson('/api/v1/assignments', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonStructure(['message', 'errors' => ['course_id', 'title', 'due_at']]);
    }

    public function test_login_is_limited_to_five_attempts_per_minute(): void
    {
        $payload = ['email' => 'missing@example.test', 'password' => 'incorrect'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', $payload)->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', $payload)->assertTooManyRequests();
    }

    public function test_logout_revokes_the_bearer_token(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }
}
